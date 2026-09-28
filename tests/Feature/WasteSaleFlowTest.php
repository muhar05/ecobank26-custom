<?php

namespace Tests\Feature;

use App\Models\Collector;
use App\Models\Deposit;
use App\Models\DepositDetail;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\User;
use App\Models\WasteBankCashLedger;
use App\Models\WasteCategory;
use App\Models\WasteCustomer;
use App\Models\WastePrice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WasteSaleFlowTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected WasteCustomer $customer;
    protected Collector $collectorA;
    protected Collector $collectorB;
    protected WasteCategory $catKardus;
    protected WasteCategory $catBesi;

    protected function setUp(): void
    {
        parent::setUp();

        // Permissions & Admin setup
        $permDeposit = Permission::firstOrCreate(['name' => 'manage_deposits']);
        $permSale = Permission::firstOrCreate(['name' => 'manage_sales']);
        $role = Role::firstOrCreate(['name' => 'admin_bank_sampah']);
        $role->givePermissionTo([$permDeposit, $permSale]);

        $this->admin = User::factory()->create();
        $this->admin->assignRole($role);

        // Master Data
        $this->catKardus = WasteCategory::create([
            'name' => 'Kardus',
            'unit' => 'kg',
            'member_price' => 2000,
        ]);

        $this->catBesi = WasteCategory::create([
            'name' => 'Besi',
            'unit' => 'kg',
            'member_price' => 4000,
        ]);

        $this->customer = WasteCustomer::create([
            'customer_code' => 'NSB-001',
            'name' => 'Nasabah Budi',
            'status' => 'active',
        ]);

        $this->collectorA = Collector::create([
            'name' => 'Aggregator A',
            'phone' => '0811111111',
        ]);

        $this->collectorB = Collector::create([
            'name' => 'Aggregator B',
            'phone' => '0822222222',
        ]);

        // Aggregator Prices
        WastePrice::create([
            'collector_id' => $this->collectorA->id,
            'waste_category_id' => $this->catKardus->id,
            'collector_price' => 2500,
        ]);

        WastePrice::create([
            'collector_id' => $this->collectorA->id,
            'waste_category_id' => $this->catBesi->id,
            'collector_price' => 5000,
        ]);

        WastePrice::create([
            'collector_id' => $this->collectorB->id,
            'waste_category_id' => $this->catKardus->id,
            'collector_price' => 2800,
        ]);
    }

    public function test_deposit_uses_master_member_price_without_aggregator(): void
    {
        $date = '2026-09-27';

        $response = $this->actingAs($this->admin)->post(route('bank-sampah.deposits.store'), [
            'waste_customer_id' => $this->customer->id,
            'date' => $date,
            'notes' => 'Setoran kardus nasabah',
            'details' => [
                [
                    'waste_category_id' => $this->catKardus->id,
                    'weight' => 100,
                ],
            ],
        ]);

        $response->assertRedirect(route('bank-sampah.deposits.index'));

        $deposit = Deposit::first();
        $this->assertNotNull($deposit);
        $this->assertEquals(200000, $deposit->total_amount);

        $detail = DepositDetail::first();
        $this->assertNotNull($detail);
        $this->assertEquals(100, $detail->weight);
        $this->assertEquals(2000, $detail->price_per_unit); // Snapshot from master member_price
        $this->assertEquals(200000, $detail->subtotal);

        // Balance credited
        $this->assertDatabaseHas('savings_ledgers', [
            'waste_customer_id' => $this->customer->id,
            'type' => 'credit',
            'amount' => 200000,
        ]);
    }

    public function test_daily_recap_endpoint_calculates_remaining_weight_correctly(): void
    {
        $date = '2026-09-27';

        // Deposit 100 kg Kardus
        $deposit = Deposit::create([
            'waste_customer_id' => $this->customer->id,
            'date' => $date,
            'total_amount' => 200000,
        ]);

        DepositDetail::create([
            'deposit_id' => $deposit->id,
            'waste_category_id' => $this->catKardus->id,
            'weight' => 100,
            'price_per_unit' => 2000,
            'subtotal' => 200000,
        ]);

        $response = $this->actingAs($this->admin)->getJson(route('bank-sampah.sales.daily-recap', [
            'date' => $date,
            'collector_id' => $this->collectorA->id,
        ]));

        $response->assertOk();
        $response->assertJsonPath('status', 'success');

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($this->catKardus->id, $data[0]['waste_category_id']);
        $this->assertEquals(100, $data[0]['remaining_weight']);
        $this->assertEquals(2500, $data[0]['collector_price']);
        $this->assertEquals(2000, $data[0]['member_price']);
    }

    public function test_sale_records_snapshots_and_margin_correctly(): void
    {
        $date = '2026-09-27';

        // Deposit 100 kg Kardus
        $deposit = Deposit::create([
            'waste_customer_id' => $this->customer->id,
            'date' => $date,
            'total_amount' => 200000,
        ]);

        DepositDetail::create([
            'deposit_id' => $deposit->id,
            'waste_category_id' => $this->catKardus->id,
            'weight' => 100,
            'price_per_unit' => 2000,
            'subtotal' => 200000,
        ]);

        $response = $this->actingAs($this->admin)->post(route('bank-sampah.sales.store'), [
            'collector_id' => $this->collectorA->id,
            'date' => $date,
            'notes' => 'Penjualan ke Aggregator A',
            'details' => [
                [
                    'waste_category_id' => $this->catKardus->id,
                    'weight' => 60,
                ],
            ],
        ]);

        $response->assertRedirect(route('bank-sampah.sales.index'));

        $sale = Sale::first();
        $this->assertNotNull($sale);
        $this->assertEquals(150000, $sale->total_amount); // 60 * 2500

        $detail = SaleDetail::first();
        $this->assertNotNull($detail);
        $this->assertEquals(60, $detail->weight);
        $this->assertEquals(2500, $detail->price_per_unit); // collector_price snapshot
        $this->assertEquals(2000, $detail->member_price);    // member_price snapshot
        $this->assertEquals(150000, $detail->subtotal);      // 60 * 2500
        $this->assertEquals(30000, $detail->margin);         // (2500 - 2000) * 60 = 500 * 60 = 30000

        // Margin booked to cash ledger
        $this->assertDatabaseHas('waste_bank_cash_ledgers', [
            'type' => 'in',
            'amount' => 30000,
            'reference_type' => Sale::class,
            'reference_id' => $sale->id,
        ]);
    }

    public function test_anti_double_sale_rejects_weight_exceeding_remaining(): void
    {
        $date = '2026-09-27';

        // Deposit 100 kg Kardus
        $deposit = Deposit::create([
            'waste_customer_id' => $this->customer->id,
            'date' => $date,
            'total_amount' => 200000,
        ]);

        DepositDetail::create([
            'deposit_id' => $deposit->id,
            'waste_category_id' => $this->catKardus->id,
            'weight' => 100,
            'price_per_unit' => 2000,
            'subtotal' => 200000,
        ]);

        // Sale A: 60 kg to Aggregator A
        $this->actingAs($this->admin)->post(route('bank-sampah.sales.store'), [
            'collector_id' => $this->collectorA->id,
            'date' => $date,
            'details' => [
                ['waste_category_id' => $this->catKardus->id, 'weight' => 60],
            ],
        ])->assertRedirect(route('bank-sampah.sales.index'));

        // Remaining is now 40 kg.
        // Sale B attempts 45 kg -> MUST BE REJECTED
        $responseFail = $this->actingAs($this->admin)->post(route('bank-sampah.sales.store'), [
            'collector_id' => $this->collectorB->id,
            'date' => $date,
            'details' => [
                ['waste_category_id' => $this->catKardus->id, 'weight' => 45],
            ],
        ]);

        $responseFail->assertSessionHasErrors('details');

        // Sale B requests 40 kg -> MUST BE ACCEPTED
        $responseOk = $this->actingAs($this->admin)->post(route('bank-sampah.sales.store'), [
            'collector_id' => $this->collectorB->id,
            'date' => $date,
            'details' => [
                ['waste_category_id' => $this->catKardus->id, 'weight' => 40],
            ],
        ]);

        $responseOk->assertRedirect(route('bank-sampah.sales.index'));

        // Remaining is now 0 kg.
        // Sale C attempts even 1 kg -> MUST BE REJECTED
        $responseFailZero = $this->actingAs($this->admin)->post(route('bank-sampah.sales.store'), [
            'collector_id' => $this->collectorA->id,
            'date' => $date,
            'details' => [
                ['waste_category_id' => $this->catKardus->id, 'weight' => 1],
            ],
        ]);

        $responseFailZero->assertSessionHasErrors('details');
    }

    public function test_multi_aggregator_partial_sales_on_same_date(): void
    {
        $date = '2026-09-27';

        // Deposit 100 kg Kardus
        $deposit = Deposit::create([
            'waste_customer_id' => $this->customer->id,
            'date' => $date,
            'total_amount' => 200000,
        ]);

        DepositDetail::create([
            'deposit_id' => $deposit->id,
            'waste_category_id' => $this->catKardus->id,
            'weight' => 100,
            'price_per_unit' => 2000,
            'subtotal' => 200000,
        ]);

        // Sale 1: 60 kg to Aggregator A
        $this->actingAs($this->admin)->post(route('bank-sampah.sales.store'), [
            'collector_id' => $this->collectorA->id,
            'date' => $date,
            'details' => [
                ['waste_category_id' => $this->catKardus->id, 'weight' => 60],
            ],
        ])->assertRedirect(route('bank-sampah.sales.index'));

        // Sale 2: Remaining 40 kg to Aggregator B on the same date
        $this->actingAs($this->admin)->post(route('bank-sampah.sales.store'), [
            'collector_id' => $this->collectorB->id,
            'date' => $date,
            'details' => [
                ['waste_category_id' => $this->catKardus->id, 'weight' => 40],
            ],
        ])->assertRedirect(route('bank-sampah.sales.index'));

        $this->assertCount(2, Sale::whereDate('date', $date)->get());

        // Margin: (2500 - 2000)*60 = 30000, (2800 - 2000)*40 = 32000. Total margin = 62000
        $totalMarginInLedger = WasteBankCashLedger::where('type', 'in')->sum('amount');
        $this->assertEquals(62000, $totalMarginInLedger);
    }

    public function test_sale_rejects_collector_price_lower_than_member_price(): void
    {
        $date = '2026-09-27';

        // Set Aggregator price below member_price (e.g. 1500 < 2000)
        WastePrice::where('collector_id', $this->collectorA->id)
            ->where('waste_category_id', $this->catKardus->id)
            ->update(['collector_price' => 1500]);

        $deposit = Deposit::create([
            'waste_customer_id' => $this->customer->id,
            'date' => $date,
            'total_amount' => 100000,
        ]);

        DepositDetail::create([
            'deposit_id' => $deposit->id,
            'waste_category_id' => $this->catKardus->id,
            'weight' => 50,
            'price_per_unit' => 2000,
            'subtotal' => 100000,
        ]);

        $response = $this->actingAs($this->admin)->post(route('bank-sampah.sales.store'), [
            'collector_id' => $this->collectorA->id,
            'date' => $date,
            'details' => [
                ['waste_category_id' => $this->catKardus->id, 'weight' => 50],
            ],
        ]);

        $response->assertSessionHasErrors('details');
        $this->assertCount(0, Sale::all());
    }

    public function test_update_sale_excludes_itself_from_remaining_weight(): void
    {
        $date = '2026-09-27';

        // Deposit 100 kg Kardus
        $deposit = Deposit::create([
            'waste_customer_id' => $this->customer->id,
            'date' => $date,
            'total_amount' => 200000,
        ]);

        DepositDetail::create([
            'deposit_id' => $deposit->id,
            'waste_category_id' => $this->catKardus->id,
            'weight' => 100,
            'price_per_unit' => 2000,
            'subtotal' => 200000,
        ]);

        // Create Sale of 60 kg
        $this->actingAs($this->admin)->post(route('bank-sampah.sales.store'), [
            'collector_id' => $this->collectorA->id,
            'date' => $date,
            'details' => [
                ['waste_category_id' => $this->catKardus->id, 'weight' => 60],
            ],
        ])->assertRedirect(route('bank-sampah.sales.index'));

        $sale = Sale::first();

        // Update Sale to 80 kg (80 <= 100 total deposit) -> MUST SUCCEED
        $this->actingAs($this->admin)->put(route('bank-sampah.sales.update', $sale), [
            'collector_id' => $this->collectorA->id,
            'date' => $date,
            'details' => [
                ['waste_category_id' => $this->catKardus->id, 'weight' => 80],
            ],
        ])->assertRedirect(route('bank-sampah.sales.index'));

        $this->assertEquals(80, $sale->fresh()->details->first()->weight);

        // Update Sale to 120 kg (120 > 100 total deposit) -> MUST BE REJECTED
        $responseFail = $this->actingAs($this->admin)->put(route('bank-sampah.sales.update', $sale), [
            'collector_id' => $this->collectorA->id,
            'date' => $date,
            'details' => [
                ['waste_category_id' => $this->catKardus->id, 'weight' => 120],
            ],
        ]);

        $responseFail->assertSessionHasErrors('details');
        $this->assertEquals(80, $sale->fresh()->details->first()->weight);
    }

    public function test_changing_master_price_does_not_affect_old_sale_snapshots(): void
    {
        $date = '2026-09-27';

        // Deposit 50 kg Kardus
        $deposit = Deposit::create([
            'waste_customer_id' => $this->customer->id,
            'date' => $date,
            'total_amount' => 100000,
        ]);

        DepositDetail::create([
            'deposit_id' => $deposit->id,
            'waste_category_id' => $this->catKardus->id,
            'weight' => 50,
            'price_per_unit' => 2000,
            'subtotal' => 100000,
        ]);

        // Sale of 50 kg
        $this->actingAs($this->admin)->post(route('bank-sampah.sales.store'), [
            'collector_id' => $this->collectorA->id,
            'date' => $date,
            'details' => [
                ['waste_category_id' => $this->catKardus->id, 'weight' => 50],
            ],
        ]);

        $detailBefore = SaleDetail::first();
        $this->assertEquals(2000, $detailBefore->member_price);
        $this->assertEquals(25000, $detailBefore->margin); // (2500 - 2000) * 50 = 25000

        // Admin changes master member_price
        $this->catKardus->update(['member_price' => 3500]);

        $detailAfter = SaleDetail::first()->fresh();
        // Snapshots must remain unchanged!
        $this->assertEquals(2000, $detailAfter->member_price);
        $this->assertEquals(25000, $detailAfter->margin);
        $this->assertEquals(2500, $detailAfter->price_per_unit);
    }
}
