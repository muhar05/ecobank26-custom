<?php

namespace Tests\Feature;

use App\Models\AdminDevice;
use App\Models\Collector;
use App\Models\Deposit;
use App\Models\DepositDetail;
use App\Models\User;
use App\Models\WasteCategory;
use App\Models\WasteCustomer;
use App\Models\WastePrice;
use App\Models\WastePriceHistory;
use App\Services\DeviceTrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SaleInlinePriceTest extends TestCase
{
    use RefreshDatabase;

    private function createAdminClient(): array
    {
        $perms = [
            'view_waste_bank',
            'manage_sales',
            'manage_waste_prices',
            'manage_deposits',
            'manage_waste_customers',
        ];
        foreach ($perms as $permName) {
            $perm = Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
            Role::firstOrCreate(['name' => 'admin_bank_sampah', 'guard_name' => 'web'])->givePermissionTo($perm);
        }
        $role = Role::where('name', 'admin_bank_sampah')->first();

        $admin = User::factory()->create();
        $admin->assignRole($role);

        $device = AdminDevice::create([
            'user_id' => $admin->id,
            'device_token' => 'inline-price-token-' . uniqid(),
            'device_name' => 'Admin PC',
            'browser' => 'Chrome',
            'platform' => 'Darwin',
            'is_revoked' => false,
            'first_seen_at' => now()->subDay(),
            'last_seen_at' => now()->subDay(),
        ]);

        return [$admin, $device];
    }

    private function asAdmin(User $admin, AdminDevice $device)
    {
        return $this->actingAs($admin)
            ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $device->device_token);
    }

    public function test_create_page_exposes_tabs_and_inline_edit_without_modal(): void
    {
        [$admin, $device] = $this->createAdminClient();

        $res = $this->asAdmin($admin, $device)->get(route('bank-sampah.sales.create'));

        $res->assertStatus(200);
        $res->assertSee('Semua', false);
        $res->assertSee('Belum Terdaftar', false);
        $res->assertSee('Rugi', false);
        $res->assertSee('Edit Harga', false);
        $res->assertSee("x-model=\"activeTab\"", false);
        // Inline edit only, no modal, no manual row flow
        $res->assertDontSee('price-confirmation', false);
        $res->assertDontSee('+ Tambah Baris', false);
    }

    public function test_inline_price_creates_master_price_and_null_to_new_history(): void
    {
        [$admin, $device] = $this->createAdminClient();
        $collector = Collector::create(['name' => 'Agregator A', 'phone' => '0811']);
        $cat = WasteCategory::create(['name' => 'Kardus', 'code' => 'KRD.01', 'unit' => 'kg', 'member_price' => 1000]);

        $this->assertDatabaseMissing('waste_prices', [
            'collector_id' => $collector->id,
            'waste_category_id' => $cat->id,
        ]);

        $res = $this->asAdmin($admin, $device)->postJson(route('bank-sampah.sales.inline-price'), [
            'collector_id' => $collector->id,
            'waste_category_id' => $cat->id,
            'collector_price' => 2500,
        ]);

        $res->assertStatus(200);
        $res->assertJsonPath('status', 'success');
        $res->assertJsonPath('data.collector_price', 2500);

        $price = WastePrice::where('collector_id', $collector->id)->where('waste_category_id', $cat->id)->first();
        $this->assertNotNull($price);
        $this->assertEquals(2500.0, (float) $price->collector_price);

        $history = WastePriceHistory::where('waste_price_id', $price->id)->first();
        $this->assertNotNull($history);
        $this->assertNull($history->old_price);
        $this->assertEquals(2500.0, (float) $history->new_price);
        $this->assertSame(WastePriceHistory::SOURCE_MANUAL, $history->source);
    }

    public function test_inline_price_updates_master_price_and_logs_old_to_new_history(): void
    {
        [$admin, $device] = $this->createAdminClient();
        $collector = Collector::create(['name' => 'Agregator B', 'phone' => '0822']);
        $cat = WasteCategory::create(['name' => 'Plastik', 'code' => 'PLS.01', 'unit' => 'kg', 'member_price' => 1000]);
        $price = WastePrice::create([
            'collector_id' => $collector->id,
            'waste_category_id' => $cat->id,
            'collector_price' => 2000,
        ]);

        $res = $this->asAdmin($admin, $device)->postJson(route('bank-sampah.sales.inline-price'), [
            'collector_id' => $collector->id,
            'waste_category_id' => $cat->id,
            'collector_price' => 3000,
        ]);

        $res->assertStatus(200);
        $this->assertEquals(3000.0, (float) $price->fresh()->collector_price);

        $history = WastePriceHistory::where('waste_price_id', $price->id)->latest('id')->first();
        $this->assertNotNull($history);
        $this->assertEquals(2000.0, (float) $history->old_price);
        $this->assertEquals(3000.0, (float) $history->new_price);
    }

    public function test_inline_price_unchanged_does_not_create_history(): void
    {
        [$admin, $device] = $this->createAdminClient();
        $collector = Collector::create(['name' => 'Agregator C', 'phone' => '0833']);
        $cat = WasteCategory::create(['name' => 'Besi', 'code' => 'BSI.01', 'unit' => 'kg', 'member_price' => 1000]);
        $price = WastePrice::create([
            'collector_id' => $collector->id,
            'waste_category_id' => $cat->id,
            'collector_price' => 2000,
        ]);

        $res = $this->asAdmin($admin, $device)->postJson(route('bank-sampah.sales.inline-price'), [
            'collector_id' => $collector->id,
            'waste_category_id' => $cat->id,
            'collector_price' => 2000,
        ]);

        $res->assertStatus(200);
        $this->assertEquals(2000.0, (float) $price->fresh()->collector_price);
        $this->assertSame(0, WastePriceHistory::where('waste_price_id', $price->id)->count());
    }

    public function test_inline_price_rejects_negative_value(): void
    {
        [$admin, $device] = $this->createAdminClient();
        $collector = Collector::create(['name' => 'Agregator D', 'phone' => '0844']);
        $cat = WasteCategory::create(['name' => 'Kaca', 'code' => 'KCA.01', 'unit' => 'kg', 'member_price' => 1000]);

        $res = $this->asAdmin($admin, $device)->postJson(route('bank-sampah.sales.inline-price'), [
            'collector_id' => $collector->id,
            'waste_category_id' => $cat->id,
            'collector_price' => -5,
        ]);

        $res->assertStatus(422);
        $this->assertDatabaseMissing('waste_prices', [
            'collector_id' => $collector->id,
            'waste_category_id' => $cat->id,
        ]);
    }

    public function test_recap_shows_material_without_waste_price_as_null(): void
    {
        [$admin, $device] = $this->createAdminClient();
        $collector = Collector::create(['name' => 'Agregator E', 'phone' => '0855']);
        // Category WITHOUT WastePrice for this collector
        $cat = WasteCategory::create(['name' => 'Jelantah', 'code' => 'JLT.01', 'unit' => 'kg', 'member_price' => 5000]);
        $customer = WasteCustomer::create(['name' => 'Nasabah', 'customer_code' => 'NSB-T1', 'status' => 'active']);
        $deposit = Deposit::create(['waste_customer_id' => $customer->id, 'date' => now()->toDateString(), 'total_amount' => 0]);
        DepositDetail::create(['deposit_id' => $deposit->id, 'waste_category_id' => $cat->id, 'weight' => 15, 'price_per_unit' => 5000, 'subtotal' => 75000]);

        $res = $this->asAdmin($admin, $device)
            ->getJson(route('bank-sampah.sales.daily-recap', ['date' => now()->toDateString(), 'collector_id' => $collector->id]));

        $res->assertStatus(200);
        $res->assertJsonPath('status', 'success');
        $data = $res->json('data');
        $found = collect($data)->firstWhere('waste_category_id', $cat->id);
        $this->assertNotNull($found, 'Material tanpa WastePrice harus tetap muncul di recap.');
        $this->assertNull($found['collector_price']);
        $this->assertEquals(15, (float) $found['remaining_weight']);
    }

    public function test_inline_price_becomes_available_in_recap_after_save(): void
    {
        [$admin, $device] = $this->createAdminClient();
        $collector = Collector::create(['name' => 'Agregator F', 'phone' => '0866']);
        $cat = WasteCategory::create(['name' => 'Kaleng', 'code' => 'KLG.01', 'unit' => 'kg', 'member_price' => 1500]);
        $customer = WasteCustomer::create(['name' => 'Nasabah 2', 'customer_code' => 'NSB-T2', 'status' => 'active']);
        $deposit = Deposit::create(['waste_customer_id' => $customer->id, 'date' => now()->toDateString(), 'total_amount' => 0]);
        DepositDetail::create(['deposit_id' => $deposit->id, 'waste_category_id' => $cat->id, 'weight' => 12, 'price_per_unit' => 1500, 'subtotal' => 18000]);

        // Before inline edit: null price
        $before = $this->asAdmin($admin, $device)
            ->getJson(route('bank-sampah.sales.daily-recap', ['date' => now()->toDateString(), 'collector_id' => $collector->id]));
        $beforeItem = collect($before->json('data'))->firstWhere('waste_category_id', $cat->id);
        $this->assertNull($beforeItem['collector_price']);

        // Inline save
        $this->asAdmin($admin, $device)->postJson(route('bank-sampah.sales.inline-price'), [
            'collector_id' => $collector->id,
            'waste_category_id' => $cat->id,
            'collector_price' => 4000,
        ])->assertStatus(200);

        // After inline edit: price available
        $after = $this->asAdmin($admin, $device)
            ->getJson(route('bank-sampah.sales.daily-recap', ['date' => now()->toDateString(), 'collector_id' => $collector->id]));
        $afterItem = collect($after->json('data'))->firstWhere('waste_category_id', $cat->id);
        $this->assertEquals(4000.0, (float) $afterItem['collector_price']);
    }

    public function test_negative_margin_sale_is_rejected(): void
    {
        [$admin, $device] = $this->createAdminClient();
        $collector = Collector::create(['name' => 'Agregator G', 'phone' => '0877']);
        $cat = WasteCategory::create(['name' => 'Asoy', 'code' => 'ASY.01', 'unit' => 'kg', 'member_price' => 2000]);
        $customer = WasteCustomer::create(['name' => 'Nasabah 3', 'customer_code' => 'NSB-T3', 'status' => 'active']);
        $deposit = Deposit::create(['waste_customer_id' => $customer->id, 'date' => now()->toDateString(), 'total_amount' => 0]);
        DepositDetail::create(['deposit_id' => $deposit->id, 'waste_category_id' => $cat->id, 'weight' => 10, 'price_per_unit' => 2000, 'subtotal' => 20000]);

        $res = $this->asAdmin($admin, $device)->post(route('bank-sampah.sales.store'), [
            'collector_id' => $collector->id,
            'date' => now()->toDateString(),
            'details' => [
                ['waste_category_id' => $cat->id, 'weight' => 5, 'custom_price' => 1000],
            ],
        ]);

        $res->assertSessionHasErrors('details');
        $this->assertDatabaseCount('sales', 0);
    }
}
