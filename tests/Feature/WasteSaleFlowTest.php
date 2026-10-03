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

    public function test_edit_sale_preserves_historical_price_snapshots(): void
    {
        $date = '2026-09-27';

        // 1. Initial Deposit 100 kg Kardus @ 2000
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

        // 2. Initial Sale of 50 kg (collector_price=2500, member_price=2000)
        $this->actingAs($this->admin)->post(route('bank-sampah.sales.store'), [
            'collector_id' => $this->collectorA->id,
            'date' => $date,
            'notes' => 'Truk Awal',
            'details' => [
                ['waste_category_id' => $this->catKardus->id, 'weight' => 50],
            ],
        ])->assertRedirect(route('bank-sampah.sales.index'));

        $sale = Sale::first();
        $this->assertEquals(125000, $sale->total_amount); // 50 * 2500
        $this->assertEquals(25000, $sale->details->first()->margin); // (2500 - 2000) * 50

        // 3. Master prices are drastically changed in the system!
        // Aggregator price jumps to 4000, member price jumps to 3000
        WastePrice::where('collector_id', $this->collectorA->id)
            ->where('waste_category_id', $this->catKardus->id)
            ->update(['collector_price' => 4000]);
        $this->catKardus->update(['member_price' => 3000]);

        // 4. Admin edits the existing sale (e.g. adjusts weight from 50 kg to 60 kg)
        $response = $this->actingAs($this->admin)->put(route('bank-sampah.sales.update', $sale), [
            'collector_id' => $this->collectorA->id,
            'date' => $date,
            'notes' => 'Truk Awal (Revisi Berat)',
            'details' => [
                ['waste_category_id' => $this->catKardus->id, 'weight' => 60],
            ],
        ]);

        $response->assertRedirect(route('bank-sampah.sales.index'));

        // 5. Verification: SNAPSHOTS MUST BE PRESERVED!
        $saleFresh = $sale->fresh(['details']);
        $detailFresh = $saleFresh->details->first();

        // Must still be 2500 and 2000, NOT 4000 and 3000!
        $this->assertEquals(2500, $detailFresh->price_per_unit, 'price_per_unit should retain old snapshot');
        $this->assertEquals(2000, $detailFresh->member_price, 'member_price should retain old snapshot');
        $this->assertEquals(150000, $saleFresh->total_amount, 'total_amount = 60 * 2500 = 150000');
        $this->assertEquals(30000, $detailFresh->margin, 'margin = (2500 - 2000) * 60 = 30000');

        // Cash ledger must reflect the preserved margin (30000)
        $this->assertDatabaseHas('waste_bank_cash_ledgers', [
            'reference_type' => Sale::class,
            'reference_id' => $sale->id,
            'amount' => 30000,
        ]);
    }

    public function test_duplicate_material_in_payload_rejected_if_total_exceeds_remaining(): void
    {
        $date = '2026-09-27';

        // Deposit 40 kg Kardus
        $deposit = Deposit::create([
            'waste_customer_id' => $this->customer->id,
            'date' => $date,
            'total_amount' => 80000,
        ]);
        DepositDetail::create([
            'deposit_id' => $deposit->id,
            'waste_category_id' => $this->catKardus->id,
            'weight' => 40,
            'price_per_unit' => 2000,
            'subtotal' => 80000,
        ]);

        // Payload with 2 rows of the same category: 30 kg + 30 kg = 60 kg > 40 kg
        // (Even though individually 30 <= 40, the aggregated total exceeds remaining 40 kg)
        $response = $this->actingAs($this->admin)->post(route('bank-sampah.sales.store'), [
            'collector_id' => $this->collectorA->id,
            'date' => $date,
            'details' => [
                ['waste_category_id' => $this->catKardus->id, 'weight' => 30],
                ['waste_category_id' => $this->catKardus->id, 'weight' => 30],
            ],
        ]);

        $response->assertSessionHasErrors('details');
        $this->assertCount(0, Sale::all());

        // When valid (e.g. 20 kg + 15 kg = 35 kg <= 40 kg), it should succeed
        $responseOk = $this->actingAs($this->admin)->post(route('bank-sampah.sales.store'), [
            'collector_id' => $this->collectorA->id,
            'date' => $date,
            'details' => [
                ['waste_category_id' => $this->catKardus->id, 'weight' => 20],
                ['waste_category_id' => $this->catKardus->id, 'weight' => 15],
            ],
        ]);

        $responseOk->assertRedirect(route('bank-sampah.sales.index'));
        $this->assertCount(1, Sale::all());
        $this->assertEquals(35, SaleDetail::sum('weight'));
    }

    public function test_concurrent_sales_cannot_double_sell(): void
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

        // Sale 1 requests 40 kg
        $this->actingAs($this->admin)->post(route('bank-sampah.sales.store'), [
            'collector_id' => $this->collectorA->id,
            'date' => $date,
            'details' => [
                ['waste_category_id' => $this->catKardus->id, 'weight' => 40],
            ],
        ])->assertRedirect(route('bank-sampah.sales.index'));

        // Sale 2 (which might have seen 50 kg initially) tries to sell 30 kg concurrently
        // It must be rejected because remaining in transaction is now only 10 kg
        $response2 = $this->actingAs($this->admin)->post(route('bank-sampah.sales.store'), [
            'collector_id' => $this->collectorB->id,
            'date' => $date,
            'details' => [
                ['waste_category_id' => $this->catKardus->id, 'weight' => 30],
            ],
        ]);

        $response2->assertSessionHasErrors('details');
        $this->assertCount(1, Sale::all());
        $this->assertEquals(40, SaleDetail::sum('weight'));
    }

    public function test_sold_deposit_cannot_be_deleted_or_reduced_below_sold_weight(): void
    {
        $date = '2026-09-27';

        // Deposit 1: 100 kg Kardus
        $deposit1 = Deposit::create([
            'waste_customer_id' => $this->customer->id,
            'date' => $date,
            'total_amount' => 200000,
        ]);
        $depDetail1 = DepositDetail::create([
            'deposit_id' => $deposit1->id,
            'waste_category_id' => $this->catKardus->id,
            'weight' => 100,
            'price_per_unit' => 2000,
            'subtotal' => 200000,
        ]);

        // Deposit 2: 50 kg Besi (Unsold deposit)
        $deposit2 = Deposit::create([
            'waste_customer_id' => $this->customer->id,
            'date' => $date,
            'total_amount' => 200000,
        ]);
        DepositDetail::create([
            'deposit_id' => $deposit2->id,
            'waste_category_id' => $this->catBesi->id,
            'weight' => 50,
            'price_per_unit' => 4000,
            'subtotal' => 200000,
        ]);

        // Sale: 70 kg Kardus sold to Aggregator A
        $this->actingAs($this->admin)->post(route('bank-sampah.sales.store'), [
            'collector_id' => $this->collectorA->id,
            'date' => $date,
            'details' => [
                ['waste_category_id' => $this->catKardus->id, 'weight' => 70],
            ],
        ])->assertRedirect(route('bank-sampah.sales.index'));

        // 1. Attempt to DELETE Deposit 1 (100 kg Kardus, 70 kg sold) -> MUST FAIL!
        $responseDelete = $this->actingAs($this->admin)->delete(route('bank-sampah.deposits.destroy', $deposit1));
        $responseDelete->assertSessionHas('error');
        $this->assertNotNull(Deposit::find($deposit1->id), 'Deposit 1 must not be deleted');

        // 2. Attempt to UPDATE Deposit 1 weight to 50 kg (< 70 kg sold) -> MUST FAIL!
        $responseUpdateLow = $this->actingAs($this->admin)->put(route('bank-sampah.deposits.update', $deposit1), [
            'waste_customer_id' => $this->customer->id,
            'date' => $date,
            'details' => [
                ['waste_category_id' => $this->catKardus->id, 'weight' => 50],
            ],
        ]);
        $responseUpdateLow->assertSessionHasErrors('details');
        $this->assertEquals(100, $deposit1->fresh()->details->first()->weight);

        // 3. Attempt to change Deposit 1 date to another date -> MUST FAIL!
        $responseUpdateDate = $this->actingAs($this->admin)->put(route('bank-sampah.deposits.update', $deposit1), [
            'waste_customer_id' => $this->customer->id,
            'date' => '2026-09-28',
            'details' => [
                ['waste_category_id' => $this->catKardus->id, 'weight' => 100],
            ],
        ]);
        $responseUpdateDate->assertSessionHasErrors('details');

        // 4. Update Deposit 1 weight to 80 kg (>= 70 kg sold) -> MUST SUCCEED!
        $responseUpdateOk = $this->actingAs($this->admin)->put(route('bank-sampah.deposits.update', $deposit1), [
            'waste_customer_id' => $this->customer->id,
            'date' => $date,
            'details' => [
                ['waste_category_id' => $this->catKardus->id, 'weight' => 80],
            ],
        ]);
        $responseUpdateOk->assertRedirect(route('bank-sampah.deposits.index'));
        $this->assertEquals(80, $deposit1->fresh()->details->first()->weight);

        // 5. Unsold Deposit 2 (50 kg Besi) CAN BE DELETED without restriction
        $responseDeleteUnsold = $this->actingAs($this->admin)->delete(route('bank-sampah.deposits.destroy', $deposit2));
        $responseDeleteUnsold->assertRedirect(route('bank-sampah.deposits.index'));
        $this->assertNull(Deposit::find($deposit2->id), 'Unsold Deposit 2 can be deleted');
    }

    public function test_rounding_subtotal_total_margin_and_ledger_are_consistent(): void
    {
        $date = '2026-09-27';

        // Category with odd prices
        $catOdd = WasteCategory::create([
            'name' => 'Kabel Tembaga',
            'unit' => 'kg',
            'member_price' => 1333,
        ]);

        WastePrice::create([
            'collector_id' => $this->collectorA->id,
            'waste_category_id' => $catOdd->id,
            'collector_price' => 2111,
        ]);

        // Deposit 12.35 kg
        $deposit = Deposit::create([
            'waste_customer_id' => $this->customer->id,
            'date' => $date,
            'total_amount' => round(12.35 * 1333, 0),
        ]);
        DepositDetail::create([
            'deposit_id' => $deposit->id,
            'waste_category_id' => $catOdd->id,
            'weight' => 12.35,
            'price_per_unit' => 1333,
            'subtotal' => round(12.35 * 1333, 0),
        ]);

        // Sale of 12.35 kg
        $this->actingAs($this->admin)->post(route('bank-sampah.sales.store'), [
            'collector_id' => $this->collectorA->id,
            'date' => $date,
            'details' => [
                ['waste_category_id' => $catOdd->id, 'weight' => 12.35],
            ],
        ])->assertRedirect(route('bank-sampah.sales.index'));

        $sale = Sale::first();
        $detail = $sale->details->first();

        // subtotal = round(12.35 * 2111, 0) = 26071
        $expectedSubtotal = round(12.35 * 2111, 0);
        // margin = round((2111 - 1333) * 12.35, 0) = round(778 * 12.35, 0) = round(9608.3, 0) = 9608
        $expectedMargin = round((2111 - 1333) * 12.35, 0);

        $this->assertEquals($expectedSubtotal, $detail->subtotal);
        $this->assertEquals($expectedSubtotal, $sale->total_amount);
        $this->assertEquals($expectedMargin, $detail->margin);

        // WasteBankCashLedger entry amount MUST be identical to margin without Rp 1 discrepancy
        $ledger = WasteBankCashLedger::where('reference_type', Sale::class)->first();
        $this->assertEquals($expectedMargin, $ledger->amount);
    }

    public function test_deposit_ignores_forged_browser_price_uses_master_member_price(): void
    {
        // CASE A: browser sends wildly inflated price_per_unit, backend must overwrite with master.
        $this->catKardus->update(['member_price' => 5000]);

        $response = $this->actingAs($this->admin)->post(route('bank-sampah.deposits.store'), [
            'waste_customer_id' => $this->customer->id,
            'date' => '2026-09-27',
            'details' => [
                [
                    'waste_category_id' => $this->catKardus->id,
                    'weight' => 10,
                    'price_per_unit' => 999999, // forged
                ],
            ],
        ]);

        $response->assertRedirect(route('bank-sampah.deposits.index'));

        $detail = DepositDetail::first();
        $this->assertEquals(5000, $detail->price_per_unit);
        $this->assertEquals(50000, $detail->subtotal);
        $this->assertEquals(50000, Deposit::first()->total_amount);
        $this->assertDatabaseHas('savings_ledgers', [
            'waste_customer_id' => $this->customer->id,
            'type' => 'credit',
            'amount' => 50000,
        ]);
    }

    public function test_deposit_ignores_deflated_browser_price(): void
    {
        // CASE B: browser sends deflated price_per_unit, backend must still use master.
        $this->catKardus->update(['member_price' => 5000]);

        $this->actingAs($this->admin)->post(route('bank-sampah.deposits.store'), [
            'waste_customer_id' => $this->customer->id,
            'date' => '2026-09-27',
            'details' => [
                [
                    'waste_category_id' => $this->catKardus->id,
                    'weight' => 10,
                    'price_per_unit' => 1000, // deflated
                ],
            ],
        ])->assertRedirect(route('bank-sampah.deposits.index'));

        $this->assertEquals(5000, DepositDetail::first()->price_per_unit);
    }

    public function test_old_deposit_snapshot_survives_master_price_change(): void
    {
        // CASE C: changing master price later must not rewrite historical snapshot.
        $this->actingAs($this->admin)->post(route('bank-sampah.deposits.store'), [
            'waste_customer_id' => $this->customer->id,
            'date' => '2026-09-27',
            'details' => [
                ['waste_category_id' => $this->catKardus->id, 'weight' => 10],
            ],
        ])->assertRedirect(route('bank-sampah.deposits.index'));

        $this->assertEquals(2000, DepositDetail::first()->price_per_unit);

        $this->catKardus->update(['member_price' => 7777]);

        $detail = DepositDetail::first()->fresh();
        $this->assertEquals(2000, $detail->price_per_unit);
        $this->assertEquals(20000, $detail->subtotal);
    }

    public function test_collector_price_never_sources_deposit_price(): void
    {
        // CASE D: collector_price (2500/2800) must never leak into deposit pricing.
        // Kardus master member_price is 2000; both aggregators price it higher.
        $this->actingAs($this->admin)->post(route('bank-sampah.deposits.store'), [
            'waste_customer_id' => $this->customer->id,
            'date' => '2026-09-27',
            'details' => [
                ['waste_category_id' => $this->catKardus->id, 'weight' => 10],
            ],
        ])->assertRedirect(route('bank-sampah.deposits.index'));

        $detail = DepositDetail::first();
        $this->assertEquals(2000, $detail->price_per_unit);
        $this->assertNotEquals(2500, $detail->price_per_unit);
        $this->assertNotEquals(2800, $detail->price_per_unit);
    }

    public function test_zero_member_price_preserved_without_blocking(): void
    {
        // member_price = 0 (e.g. Quick Add material) must not be blocked in Phase 2.
        $this->catKardus->update(['member_price' => 0]);

        $this->actingAs($this->admin)->post(route('bank-sampah.deposits.store'), [
            'waste_customer_id' => $this->customer->id,
            'date' => '2026-09-27',
            'details' => [
                ['waste_category_id' => $this->catKardus->id, 'weight' => 10],
            ],
        ])->assertRedirect(route('bank-sampah.deposits.index'));

        $detail = DepositDetail::first();
        $this->assertEquals(0, $detail->price_per_unit);
        $this->assertEquals(0, $detail->subtotal);
    }
}
