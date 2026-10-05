<?php

namespace Tests\Feature;

use App\Models\Collector;
use App\Models\Deposit;
use App\Models\DepositDetail;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\User;
use App\Models\WasteCategory;
use App\Models\WasteCustomer;
use App\Models\WastePrice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CumulativeStockTest extends TestCase
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

        $permDeposit = Permission::firstOrCreate(['name' => 'manage_deposits']);
        $permSale = Permission::firstOrCreate(['name' => 'manage_sales']);
        $role = Role::firstOrCreate(['name' => 'admin_bank_sampah']);
        $role->givePermissionTo([$permDeposit, $permSale]);

        $this->admin = User::factory()->create();
        $this->admin->assignRole($role);

        $this->catKardus = WasteCategory::create(['name' => 'Kardus', 'unit' => 'kg', 'member_price' => 2000]);
        $this->catBesi = WasteCategory::create(['name' => 'Besi', 'unit' => 'kg', 'member_price' => 4000]);

        $this->customer = WasteCustomer::create([
            'customer_code' => 'NSB-CUM-001',
            'name' => 'Nasabah Cumulative',
            'status' => 'active',
        ]);

        $this->collectorA = Collector::create(['name' => 'Aggregator A', 'phone' => '0811111111']);
        $this->collectorB = Collector::create(['name' => 'Aggregator B', 'phone' => '0822222222']);

        WastePrice::create(['collector_id' => $this->collectorA->id, 'waste_category_id' => $this->catKardus->id, 'collector_price' => 2500]);
        WastePrice::create(['collector_id' => $this->collectorA->id, 'waste_category_id' => $this->catBesi->id, 'collector_price' => 5000]);
        WastePrice::create(['collector_id' => $this->collectorB->id, 'waste_category_id' => $this->catKardus->id, 'collector_price' => 2800]);
    }

    private function deposit(string $date, int $categoryId, float $weight, float $price = 2000): Deposit
    {
        $deposit = Deposit::create([
            'waste_customer_id' => $this->customer->id,
            'date' => $date,
            'total_amount' => round($weight * $price, 0),
        ]);

        DepositDetail::create([
            'deposit_id' => $deposit->id,
            'waste_category_id' => $categoryId,
            'weight' => $weight,
            'price_per_unit' => $price,
            'subtotal' => round($weight * $price, 0),
        ]);

        return $deposit;
    }

    private function recap(string $date, int $collectorId): array
    {
        $res = $this->actingAs($this->admin)->getJson(route('bank-sampah.sales.daily-recap', [
            'date' => $date,
            'collector_id' => $collectorId,
        ]));
        $res->assertOk();

        return $res->json('data');
    }

    public function test_old_deposit_appears_as_stock_on_later_date(): void
    {
        $this->deposit('2026-09-20', $this->catKardus->id, 100);

        // No deposit on 2026-09-25, only carry-over from 2026-09-20
        $data = $this->recap('2026-09-25', $this->collectorA->id);

        $kardus = collect($data)->firstWhere('waste_category_id', $this->catKardus->id);
        $this->assertNotNull($kardus, 'Carry-over deposit must appear as available stock.');
        $this->assertEquals(100, (float) $kardus['remaining_weight']);
        $this->assertEquals(2500, (float) $kardus['collector_price']);
    }

    public function test_sale_can_take_carry_over_stock(): void
    {
        $this->deposit('2026-09-20', $this->catKardus->id, 100);

        // Sell 100 kg on a later date using only carry-over stock
        $this->actingAs($this->admin)->post(route('bank-sampah.sales.store'), [
            'collector_id' => $this->collectorA->id,
            'date' => '2026-09-25',
            'details' => [
                ['waste_category_id' => $this->catKardus->id, 'weight' => 100],
            ],
        ])->assertRedirect(route('bank-sampah.sales.index'));

        $this->assertEquals(100, (float) SaleDetail::sum('weight'));

        // Recap after sale is empty
        $this->assertEmpty($this->recap('2026-09-25', $this->collectorA->id));
    }

    public function test_sale_can_combine_carry_over_and_same_day_stock(): void
    {
        $this->deposit('2026-09-20', $this->catKardus->id, 100);
        $this->deposit('2026-09-25', $this->catKardus->id, 50);

        // 150 total available (100 carry-over + 50 same-day); sell 120
        $this->actingAs($this->admin)->post(route('bank-sampah.sales.store'), [
            'collector_id' => $this->collectorA->id,
            'date' => '2026-09-25',
            'details' => [
                ['waste_category_id' => $this->catKardus->id, 'weight' => 120],
            ],
        ])->assertRedirect(route('bank-sampah.sales.index'));

        $data = $this->recap('2026-09-25', $this->collectorA->id);
        $kardus = collect($data)->firstWhere('waste_category_id', $this->catKardus->id);
        $this->assertEquals(30, (float) $kardus['remaining_weight']);
    }

    public function test_sale_cannot_make_cumulative_stock_negative(): void
    {
        $this->deposit('2026-09-20', $this->catKardus->id, 100);

        $response = $this->actingAs($this->admin)->post(route('bank-sampah.sales.store'), [
            'collector_id' => $this->collectorA->id,
            'date' => '2026-09-25',
            'details' => [
                ['waste_category_id' => $this->catKardus->id, 'weight' => 101],
            ],
        ]);

        $response->assertSessionHasErrors('details');
        $this->assertEquals(0, SaleDetail::count());
    }

    public function test_edit_sale_excludes_itself_from_cumulative_stock(): void
    {
        $this->deposit('2026-09-20', $this->catKardus->id, 100);

        $this->actingAs($this->admin)->post(route('bank-sampah.sales.store'), [
            'collector_id' => $this->collectorA->id,
            'date' => '2026-09-25',
            'details' => [['waste_category_id' => $this->catKardus->id, 'weight' => 60]],
        ])->assertRedirect(route('bank-sampah.sales.index'));

        $sale = Sale::first();

        // Edit to 100 (self excluded) -> allowed
        $this->actingAs($this->admin)->put(route('bank-sampah.sales.update', $sale), [
            'collector_id' => $this->collectorA->id,
            'date' => '2026-09-25',
            'details' => [['waste_category_id' => $this->catKardus->id, 'weight' => 100]],
        ])->assertRedirect(route('bank-sampah.sales.index'));

        $this->assertEquals(100, (float) $sale->fresh()->details->first()->weight);

        // Edit to 101 -> rejected
        $this->actingAs($this->admin)->put(route('bank-sampah.sales.update', $sale), [
            'collector_id' => $this->collectorA->id,
            'date' => '2026-09-25',
            'details' => [['waste_category_id' => $this->catKardus->id, 'weight' => 101]],
        ])->assertSessionHasErrors('details');

        $this->assertEquals(100, (float) $sale->fresh()->details->first()->weight);
    }

    public function test_delete_old_deposit_rejected_when_it_makes_cumulative_stock_negative(): void
    {
        $deposit = $this->deposit('2026-09-20', $this->catKardus->id, 100);

        // Sale on later date consumes carry-over
        $this->actingAs($this->admin)->post(route('bank-sampah.sales.store'), [
            'collector_id' => $this->collectorA->id,
            'date' => '2026-09-25',
            'details' => [['waste_category_id' => $this->catKardus->id, 'weight' => 70]],
        ])->assertRedirect(route('bank-sampah.sales.index'));

        $response = $this->actingAs($this->admin)->delete(route('bank-sampah.deposits.destroy', $deposit));
        $response->assertSessionHas('error');
        $this->assertNotNull(Deposit::find($deposit->id));
    }

    public function test_edit_old_deposit_rejected_when_it_makes_cumulative_stock_negative(): void
    {
        $deposit = $this->deposit('2026-09-20', $this->catKardus->id, 100);

        $this->actingAs($this->admin)->post(route('bank-sampah.sales.store'), [
            'collector_id' => $this->collectorA->id,
            'date' => '2026-09-25',
            'details' => [['waste_category_id' => $this->catKardus->id, 'weight' => 70]],
        ])->assertRedirect(route('bank-sampah.sales.index'));

        // Reduce weight below sold amount -> rejected
        $this->actingAs($this->admin)->put(route('bank-sampah.deposits.update', $deposit), [
            'waste_customer_id' => $this->customer->id,
            'date' => '2026-09-20',
            'details' => [['waste_category_id' => $this->catKardus->id, 'weight' => 50]],
        ])->assertSessionHasErrors('details');

        // Move date past the sale date -> rejected (negative at sale date)
        $this->actingAs($this->admin)->put(route('bank-sampah.deposits.update', $deposit), [
            'waste_customer_id' => $this->customer->id,
            'date' => '2026-09-26',
            'details' => [['waste_category_id' => $this->catKardus->id, 'weight' => 100]],
        ])->assertSessionHasErrors('details');

        $this->assertEquals(100, (float) $deposit->fresh()->details->first()->weight);
    }

    public function test_multi_aggregator_share_same_cumulative_pool(): void
    {
        $this->deposit('2026-09-20', $this->catKardus->id, 100);

        $this->actingAs($this->admin)->post(route('bank-sampah.sales.store'), [
            'collector_id' => $this->collectorA->id,
            'date' => '2026-09-25',
            'details' => [['waste_category_id' => $this->catKardus->id, 'weight' => 60]],
        ])->assertRedirect(route('bank-sampah.sales.index'));

        $this->actingAs($this->admin)->post(route('bank-sampah.sales.store'), [
            'collector_id' => $this->collectorB->id,
            'date' => '2026-09-25',
            'details' => [['waste_category_id' => $this->catKardus->id, 'weight' => 40]],
        ])->assertRedirect(route('bank-sampah.sales.index'));

        $this->assertEquals(2, Sale::count());
        $this->assertEquals(100, (float) SaleDetail::sum('weight'));

        // Third sale exceeding the shared pool is rejected
        $this->actingAs($this->admin)->post(route('bank-sampah.sales.store'), [
            'collector_id' => $this->collectorA->id,
            'date' => '2026-09-25',
            'details' => [['waste_category_id' => $this->catKardus->id, 'weight' => 1]],
        ])->assertSessionHasErrors('details');
    }

    public function test_historical_sale_snapshot_remains_immutable(): void
    {
        $this->deposit('2026-09-20', $this->catKardus->id, 100);

        $this->actingAs($this->admin)->post(route('bank-sampah.sales.store'), [
            'collector_id' => $this->collectorA->id,
            'date' => '2026-09-25',
            'details' => [['waste_category_id' => $this->catKardus->id, 'weight' => 50]],
        ])->assertRedirect(route('bank-sampah.sales.index'));

        $detailBefore = SaleDetail::first();
        $this->assertEquals(2500, (float) $detailBefore->price_per_unit);
        $this->assertEquals(2000, (float) $detailBefore->member_price);
        $this->assertEquals(25000, (float) $detailBefore->margin);

        // Master prices change later; snapshot must not change
        WastePrice::where('collector_id', $this->collectorA->id)
            ->where('waste_category_id', $this->catKardus->id)
            ->update(['collector_price' => 9000]);
        $this->catKardus->update(['member_price' => 8000]);

        $detailAfter = SaleDetail::first()->fresh();
        $this->assertEquals(2500, (float) $detailAfter->price_per_unit);
        $this->assertEquals(2000, (float) $detailAfter->member_price);
        $this->assertEquals(25000, (float) $detailAfter->margin);
    }

    public function test_backdated_sale_rejected_when_it_makes_intermediate_date_negative(): void
    {
        // B: 09-01=100, 09-15=10, 09-20=80
        $this->deposit('2026-09-01', $this->catKardus->id, 100);

        $this->actingAs($this->admin)->post(route('bank-sampah.sales.store'), [
            'collector_id' => $this->collectorA->id,
            'date' => '2026-09-15',
            'details' => [['waste_category_id' => $this->catKardus->id, 'weight' => 90]],
        ])->assertRedirect(route('bank-sampah.sales.index'));

        $this->deposit('2026-09-20', $this->catKardus->id, 70);

        // Backdated sale 09-05 of 50: B(09-05)=100 and B(all)=80 look safe,
        // but 09-15 balance would drop to -40.
        $response = $this->actingAs($this->admin)->post(route('bank-sampah.sales.store'), [
            'collector_id' => $this->collectorA->id,
            'date' => '2026-09-05',
            'details' => [['waste_category_id' => $this->catKardus->id, 'weight' => 50]],
        ]);

        $response->assertSessionHasErrors('details');
        $this->assertEquals(90, (float) SaleDetail::sum('weight'));
    }

    public function test_edit_sale_increasing_weight_rejected_when_it_makes_later_date_negative(): void
    {
        $this->deposit('2026-09-01', $this->catKardus->id, 100);

        // Sale E 09-20 40
        $this->actingAs($this->admin)->post(route('bank-sampah.sales.store'), [
            'collector_id' => $this->collectorA->id,
            'date' => '2026-09-20',
            'details' => [['waste_category_id' => $this->catKardus->id, 'weight' => 40]],
        ])->assertRedirect(route('bank-sampah.sales.index'));

        $this->deposit('2026-09-22', $this->catKardus->id, 50);

        // Sale X 09-25 80
        $this->actingAs($this->admin)->post(route('bank-sampah.sales.store'), [
            'collector_id' => $this->collectorA->id,
            'date' => '2026-09-25',
            'details' => [['waste_category_id' => $this->catKardus->id, 'weight' => 80]],
        ])->assertRedirect(route('bank-sampah.sales.index'));

        $this->deposit('2026-09-30', $this->catKardus->id, 70);

        $saleE = Sale::whereDate('date', '2026-09-20')->firstOrFail();

        // Edit E to 85: B(09-20)=100 and B(all)=140 look safe,
        // but 09-25 balance would drop to -15.
        $response = $this->actingAs($this->admin)->put(route('bank-sampah.sales.update', $saleE), [
            'collector_id' => $this->collectorA->id,
            'date' => '2026-09-20',
            'details' => [['waste_category_id' => $this->catKardus->id, 'weight' => 85]],
        ]);

        $response->assertSessionHasErrors('details');
        $this->assertEquals(40, (float) $saleE->fresh()->details->first()->weight);
    }

    public function test_moving_sale_date_earlier_rejected_when_it_makes_intermediate_date_negative(): void
    {
        $this->deposit('2026-09-01', $this->catKardus->id, 100);

        // Sale Y 09-10 70
        $this->actingAs($this->admin)->post(route('bank-sampah.sales.store'), [
            'collector_id' => $this->collectorA->id,
            'date' => '2026-09-10',
            'details' => [['waste_category_id' => $this->catKardus->id, 'weight' => 70]],
        ])->assertRedirect(route('bank-sampah.sales.index'));

        $this->deposit('2026-09-18', $this->catKardus->id, 20);

        // Sale S 09-20 45
        $this->actingAs($this->admin)->post(route('bank-sampah.sales.store'), [
            'collector_id' => $this->collectorA->id,
            'date' => '2026-09-20',
            'details' => [['waste_category_id' => $this->catKardus->id, 'weight' => 45]],
        ])->assertRedirect(route('bank-sampah.sales.index'));

        $saleS = Sale::whereDate('date', '2026-09-20')->firstOrFail();

        // Move S to 09-05: B(09-05)=100 and B(all)=50 look safe,
        // but 09-10 balance would drop to -15.
        $response = $this->actingAs($this->admin)->put(route('bank-sampah.sales.update', $saleS), [
            'collector_id' => $this->collectorA->id,
            'date' => '2026-09-05',
            'details' => [['waste_category_id' => $this->catKardus->id, 'weight' => 45]],
        ]);

        $response->assertSessionHasErrors('details');
        $this->assertEquals('2026-09-20', $saleS->fresh()->date->format('Y-m-d'));
    }

    public function test_backdated_sale_accepted_when_timeline_stays_non_negative(): void
    {
        $this->deposit('2026-09-01', $this->catKardus->id, 100);

        // Sale 09-10 40
        $this->actingAs($this->admin)->post(route('bank-sampah.sales.store'), [
            'collector_id' => $this->collectorA->id,
            'date' => '2026-09-10',
            'details' => [['waste_category_id' => $this->catKardus->id, 'weight' => 40]],
        ])->assertRedirect(route('bank-sampah.sales.index'));

        // Backdated sale 09-05 of 30 keeps every date >= 0
        $response = $this->actingAs($this->admin)->post(route('bank-sampah.sales.store'), [
            'collector_id' => $this->collectorA->id,
            'date' => '2026-09-05',
            'details' => [['waste_category_id' => $this->catKardus->id, 'weight' => 30]],
        ]);

        $response->assertRedirect(route('bank-sampah.sales.index'));
        $this->assertEquals(70, (float) SaleDetail::sum('weight'));
    }
}
