<?php

namespace Tests\Feature;

use App\Models\Collector;
use App\Models\Deposit;
use App\Models\SavingsLedger;
use App\Models\User;
use App\Models\WasteCategory;
use App\Models\WasteCustomer;
use App\Models\WastePrice;
use App\Services\BankSampahService;
use App\Services\DepositImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Historical exception support (Opsi A): deposit detail boleh tanpa
 * WasteCategory asalkan punya material_label.
 */
class HistoricalExceptionMaterialTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected WasteCustomer $customer;

    protected Collector $collector;

    protected WasteCategory $kardus;

    protected BankSampahService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin_bank_sampah');

        $this->service = app(BankSampahService::class);

        $this->kardus = WasteCategory::create(['name' => 'Kardus', 'unit' => 'kg', 'member_price' => 1000]);

        $this->customer = WasteCustomer::create([
            'customer_code' => 'NSB-HIST-001',
            'name' => 'Nasabah Historis',
            'status' => 'active',
        ]);

        $this->collector = Collector::create(['name' => 'Aggregator Hist']);
        WastePrice::create([
            'collector_id' => $this->collector->id,
            'waste_category_id' => $this->kardus->id,
            'collector_price' => 1500,
        ]);
    }

    private function recordException(string $label, float $weight, float $price): Deposit
    {
        return $this->service->recordDeposit([
            'waste_customer_id' => $this->customer->id,
            'date' => '2026-05-09',
            'details' => [[
                'material_label' => $label,
                'weight' => $weight,
                'price_per_unit' => $price,
                'subtotal' => round($weight * $price, 0),
            ]],
        ]);
    }

    public function test_deposit_detail_without_category_but_with_material_label_is_stored(): void
    {
        $deposit = $this->recordException('Buku, Majalah, Brosur, Koran', 5, 800);

        $detail = $deposit->details()->first();
        $this->assertNull($detail->waste_category_id);
        $this->assertEquals('Buku, Majalah, Brosur, Koran', $detail->material_label);
        $this->assertEquals('Buku, Majalah, Brosur, Koran', $detail->display_label);
        $this->assertTrue($detail->isHistoricalException());

        // Tidak membuat WasteCategory baru.
        $this->assertEquals(1, WasteCategory::count());
    }

    public function test_operational_deposit_detail_without_category_or_label_is_rejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->service->recordDeposit([
            'waste_customer_id' => $this->customer->id,
            'date' => '2026-05-09',
            'details' => [[
                'weight' => 1,
                'price_per_unit' => 1000,
                'subtotal' => 1000,
            ]],
        ]);

        $this->assertEquals(0, Deposit::count());
    }

    public function test_operational_category_deposit_still_works(): void
    {
        $deposit = $this->service->recordDeposit([
            'waste_customer_id' => $this->customer->id,
            'date' => '2026-05-09',
            'details' => [[
                'waste_category_id' => $this->kardus->id,
                'weight' => 10,
                'price_per_unit' => 1000,
                'subtotal' => 10000,
            ]],
        ]);

        $detail = $deposit->details()->first();
        $this->assertEquals($this->kardus->id, $detail->waste_category_id);
        $this->assertNull($detail->material_label);
        $this->assertEquals('Kardus', $detail->display_label);
        $this->assertFalse($detail->isHistoricalException());
    }

    public function test_historical_exception_counted_in_stock_with_original_label(): void
    {
        $this->recordException('Seng/Kawat', 4, 500);

        $recap = $this->service->getDailyRemainingWaste('2026-05-10', $this->collector->id);

        $item = $recap->firstWhere('name', 'Seng/Kawat');
        $this->assertNotNull($item, 'Historical exception harus masuk stock.');
        $this->assertNull($item['waste_category_id']);
        $this->assertEquals('Seng/Kawat', $item['material_label']);
        $this->assertEquals(4.0, (float) $item['remaining_weight']);
        $this->assertTrue($item['is_historical_exception']);
    }

    public function test_historical_exceptions_included_in_stock_total_alongside_categories(): void
    {
        // operational 10 kg (Kardus)
        $this->service->recordDeposit([
            'waste_customer_id' => $this->customer->id,
            'date' => '2026-05-09',
            'details' => [[
                'waste_category_id' => $this->kardus->id,
                'weight' => 10,
                'price_per_unit' => 1000,
                'subtotal' => 10000,
            ]],
        ]);
        // dua exception dengan label berbeda => dua bucket terpisah
        $this->recordException('Seng/Kawat', 4, 500);
        $this->recordException('Buku, Majalah, Brosur, Koran', 3, 800);

        $recap = $this->service->getDailyRemainingWaste('2026-05-10', $this->collector->id);

        $this->assertCount(3, $recap);
        $this->assertEquals(17.0, (float) $recap->sum('remaining_weight'));
        $this->assertNotNull($recap->firstWhere('name', 'Seng/Kawat'));
        $this->assertNotNull($recap->firstWhere('name', 'Buku, Majalah, Brosur, Koran'));
    }

    public function test_historical_exception_subtotal_and_ledger_are_correct(): void
    {
        $deposit = $this->service->recordDeposit([
            'waste_customer_id' => $this->customer->id,
            'date' => '2026-05-09',
            'details' => [
                ['material_label' => 'Buku, Majalah, Brosur, Koran', 'weight' => 6, 'price_per_unit' => 800, 'subtotal' => 4800],
                ['waste_category_id' => $this->kardus->id, 'weight' => 2, 'price_per_unit' => 1000, 'subtotal' => 2000],
            ],
        ]);

        $this->assertEquals(6800, (float) $deposit->total_amount);

        $ledger = SavingsLedger::where('reference_type', Deposit::class)
            ->where('reference_id', $deposit->id)
            ->first();
        $this->assertNotNull($ledger);
        $this->assertEquals(6800, (float) $ledger->amount);
        $this->assertEquals(6800, $this->service->getCustomerBalance($this->customer->id));

        $exc = $deposit->details->firstWhere('material_label', 'Buku, Majalah, Brosur, Koran');
        $this->assertEquals(4800, (float) $exc->subtotal);
    }

    public function test_historical_exception_appears_in_deposit_report(): void
    {
        $this->recordException('Seng/Kawat', 4, 500);

        $response = $this->actingAs($this->admin)->get(route('bank-sampah.reports.deposits', [
            'start_date' => '2026-05-01',
            'end_date' => '2026-05-31',
        ]));

        $response->assertOk();
        $response->assertSee('Seng/Kawat');
        $response->assertSee('Historical Exception');
        $response->assertSee('4,00');
    }

    public function test_operational_import_commit_rejects_row_without_category(): void
    {
        $this->expectException(\RuntimeException::class);

        app(DepositImportService::class)->commit([
            'fingerprint' => 'test-fp-no-category',
            'rows' => [[
                'status' => 'VALID',
                'customer_id' => $this->customer->id,
                'category_id' => null,
                'weight' => 1,
                'used_price' => 1000,
                'system_total' => 1000,
                'excel_price' => 1000,
                'has_existing_deposit' => false,
            ]],
            'confirmed_date' => '2026-05-09',
            'batch_code' => 'BATCH-TEST',
            'filename' => 'test.xlsx',
            'sheet_name' => 'Sheet1',
            'duplicate_policies' => [],
        ]);
    }
}
