<?php

namespace Tests\Feature;

use App\Models\AdminDevice;
use App\Models\Collector;
use App\Models\Deposit;
use App\Models\DepositDetail;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\User;
use App\Models\WasteCategory;
use App\Models\WasteCategoryGroup;
use App\Models\WasteCustomer;
use App\Models\WastePrice;
use App\Services\DeviceTrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SaleReferenceTest extends TestCase
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
            'device_token' => 'sale-test-token-' . uniqid(),
            'device_name' => 'Admin PC',
            'browser' => 'Chrome',
            'platform' => 'Darwin',
            'is_revoked' => false,
            'first_seen_at' => now()->subDay(),
            'last_seen_at' => now()->subDay(),
        ]);

        return [$admin, $device];
    }

    public function test_sales_index_empty_state_and_header(): void
    {
        [$admin, $device] = $this->createAdminClient();

        $res = $this->actingAs($admin)
            ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $device->device_token)
            ->get(route('bank-sampah.sales.index'));

        $res->assertStatus(200);

        // Header & Contextual Links
        $res->assertSee('Penjualan ke Agregator', false);
        $res->assertSee('Agregator &rarr;', false);
        $res->assertSee('Harga Sampah &rarr;', false);
        $res->assertSee('Catat Penjualan', false);

        // Honest Empty States
        $res->assertSee('Saat ini belum ada setoran yang siap dijual pada tanggal ini.', false);
        $res->assertSee('Belum ada riwayat transaksi penjualan ke agregator.', false);

        // Anti-Slop
        $res->assertDontSee('bg-slate-900', false);
        $res->assertDontSee('rounded-[2rem]', false);
    }

    public function test_sales_index_renders_ready_to_sell_and_sales_history(): void
    {
        [$admin, $device] = $this->createAdminClient();
        $date = now()->format('Y-m-d');

        $group = WasteCategoryGroup::create(['name' => 'Kertas', 'code' => 'KRT']);
        $cat = WasteCategory::create([
            'name' => 'Kardus Box',
            'code' => 'KRD.01',
            'unit' => 'kg',
            'member_price' => 2000,
            'waste_category_group_id' => $group->id,
        ]);
        $collector = Collector::create([
            'name' => 'PT Mitra Daur Ulang',
            'phone' => '0812345678',
        ]);
        $customer = WasteCustomer::create([
            'name' => 'Bu Sarti',
            'customer_code' => WasteCustomer::generateNextCustomerCode(),
            'status' => 'active',
        ]);

        // Deposit 100 kg
        $deposit = Deposit::create([
            'waste_customer_id' => $customer->id,
            'date' => $date,
            'total_amount' => 200000,
        ]);
        DepositDetail::create([
            'deposit_id' => $deposit->id,
            'waste_category_id' => $cat->id,
            'weight' => 100,
            'price_per_unit' => 2000,
            'subtotal' => 200000,
        ]);

        // Sale 60 kg (leaving 40 kg sisa)
        $sale = Sale::create([
            'collector_id' => $collector->id,
            'date' => $date,
            'total_amount' => 150000, // 60 * 2500
            'notes' => 'Truk 1 Muatan Pagi',
        ]);
        SaleDetail::create([
            'sale_id' => $sale->id,
            'waste_category_id' => $cat->id,
            'weight' => 60,
            'price_per_unit' => 2500,
            'member_price' => 2000,
            'subtotal' => 150000,
            'margin' => 30000,
        ]);

        $res = $this->actingAs($admin)
            ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $device->device_token)
            ->get(route('bank-sampah.sales.index', ['date' => $date]));

        $res->assertStatus(200);

        // Section 1: Sisa Setoran Siap Dijual (100 - 60 = 40 kg remaining)
        $res->assertSee('Kardus Box', false);
        $res->assertSee('40,00', false); // remaining weight in id-ID format
        $res->assertSee('Siap Dijual', false);
        $res->assertSee('Catat Penjualan ke Agregator &rarr;', false);

        // Section 2: Riwayat Penjualan
        $res->assertSee('PT Mitra Daur Ulang', false);
        $res->assertSee('Truk 1 Muatan Pagi', false);
        $res->assertSee('Rp 150.000', false);
        $res->assertSee('+Rp 30.000', false); // Margin Bank Sampah
        $res->assertSee('Print Timbangan', false);
        $res->assertSee('Lihat Rincian', false);
    }

    public function test_sale_create_view_and_zero_modal_architecture(): void
    {
        [$admin, $device] = $this->createAdminClient();

        $res = $this->actingAs($admin)
            ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $device->device_token)
            ->get(route('bank-sampah.sales.create'));

        $res->assertStatus(200);

        // Header & Guidance
        $res->assertSee('Catat Penjualan ke Agregator', false);
        $res->assertSee('Prinsip Operasional:', false);
        $res->assertSee('Margin Bank Sampah = (Harga Agregator &minus; Harga Nasabah)', false);

        // Controls
        $res->assertSee('Tarik Sisa Setoran', false);
        $res->assertSee('+ Tambah Baris', false);
        $res->assertSee('Konfirmasi Penjualan', false);

        // Anti-Slop: Zero 80vh popup modal / broken duplicate templates
        $res->assertDontSee('modalOpen', false);
        $res->assertDontSee('Pilih Kategori Sampah', false);
    }

    public function test_sale_edit_view_loads_existing_details(): void
    {
        [$admin, $device] = $this->createAdminClient();

        $collector = Collector::create(['name' => 'Pengepul Berkah', 'phone' => '08111']);
        $group = WasteCategoryGroup::create(['name' => 'Besi', 'code' => 'BSI']);
        $cat = WasteCategory::create(['name' => 'Besi Padat', 'code' => 'BSI.01', 'unit' => 'kg', 'member_price' => 5000, 'waste_category_group_id' => $group->id]);

        $sale = Sale::create([
            'collector_id' => $collector->id,
            'date' => now()->toDateString(),
            'total_amount' => 70000,
            'notes' => 'Catatan Khusus Muatan',
        ]);
        SaleDetail::create([
            'sale_id' => $sale->id,
            'waste_category_id' => $cat->id,
            'weight' => 10,
            'price_per_unit' => 7000,
            'member_price' => 5000,
            'subtotal' => 70000,
            'margin' => 20000,
        ]);

        $res = $this->actingAs($admin)
            ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $device->device_token)
            ->get(route('bank-sampah.sales.edit', $sale));

        $res->assertStatus(200);

        // Edit Title & Warning Banner
        $res->assertSee('Edit Penjualan ke Agregator', false);
        $res->assertSee('Perhatian:', false);
        $res->assertSee('Catatan Khusus Muatan', false);
        $res->assertSee('Perbarui Penjualan', false);
    }

    public function test_sales_spa_fragments_render_cleanly(): void
    {
        [$admin, $device] = $this->createAdminClient();

        $collector = Collector::create(['name' => 'Pengepul Sejahtera', 'phone' => '08122']);
        $sale = Sale::create([
            'collector_id' => $collector->id,
            'date' => now()->toDateString(),
            'total_amount' => 10000,
        ]);

        $routes = [
            'index' => route('bank-sampah.sales.index'),
            'create' => route('bank-sampah.sales.create'),
            'edit' => route('bank-sampah.sales.edit', $sale),
        ];

        foreach ($routes as $name => $url) {
            $res = $this->actingAs($admin)
                ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $device->device_token)
                ->withHeaders(['X-Eco-Nav' => '1'])
                ->get($url);

            $res->assertStatus(200, "SPA fragment failed for route: {$name}");
            $res->assertSee('id="eco-spa-fragment"', false);
            $res->assertDontSee('<!DOCTYPE html>', false);
            $res->assertDontSee('bg-slate-900', false);
        }
    }

    public function test_sales_index_has_no_selection_or_modal_dead_code(): void
    {
        [$admin, $device] = $this->createAdminClient();
        $date = now()->format('Y-m-d');

        $cat = WasteCategory::create(['name' => 'Kardus', 'code' => 'KRD.01', 'unit' => 'kg', 'member_price' => 2000]);
        $customer = WasteCustomer::create(['name' => 'Pak Joko', 'customer_code' => WasteCustomer::generateNextCustomerCode(), 'status' => 'active']);

        $dep = Deposit::create(['waste_customer_id' => $customer->id, 'date' => $date, 'total_amount' => 50000]);
        DepositDetail::create(['deposit_id' => $dep->id, 'waste_category_id' => $cat->id, 'weight' => 25, 'price_per_unit' => 2000, 'subtotal' => 50000]);

        $res = $this->actingAs($admin)
            ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $device->device_token)
            ->get(route('bank-sampah.sales.index', ['date' => $date]));

        $res->assertStatus(200);

        // Selection, checkboxes, and modal must be REMOVED (no dead code)
        $res->assertDontSee('readyToSellWorkflow', false);
        $res->assertDontSee('Pilih semua material', false);
        $res->assertDontSee('material dipilih', false);
        $res->assertDontSee('id="modal_collector_id"', false);
        $res->assertDontSee('Pilih Mitra Agregator', false);
        $res->assertDontSee('type="checkbox"', false);
    }

    public function test_sales_show_renders_timbangan_kami_box_and_print_button(): void
    {
        [$admin, $device] = $this->createAdminClient();

        $collector = Collector::create(['name' => 'PT Mitra Daur Ulang', 'phone' => '0812345678', 'address' => 'Jl. Industri No. 5']);
        $cat1 = WasteCategory::create(['name' => 'Kardus Box', 'code' => 'KRD.01', 'unit' => 'kg', 'member_price' => 2000]);
        $cat2 = WasteCategory::create(['name' => 'Besi Padat', 'code' => 'BSI.01', 'unit' => 'kg', 'member_price' => 5000]);

        $sale = Sale::create([
            'collector_id' => $collector->id,
            'date' => '2026-10-03',
            'total_amount' => 250000,
            'notes' => 'Truk 1 Muatan Pagi',
        ]);

        SaleDetail::create([
            'sale_id' => $sale->id,
            'waste_category_id' => $cat1->id,
            'weight' => 60,
            'price_per_unit' => 2500,
            'member_price' => 2000,
            'subtotal' => 150000,
            'margin' => 30000,
        ]);

        SaleDetail::create([
            'sale_id' => $sale->id,
            'waste_category_id' => $cat2->id,
            'weight' => 20,
            'price_per_unit' => 5000,
            'member_price' => 4000,
            'subtotal' => 100000,
            'margin' => 20000,
        ]);

        $res = $this->actingAs($admin)
            ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $device->device_token)
            ->get(route('bank-sampah.sales.show', $sale));

        $res->assertStatus(200);

        // Format checks
        $res->assertSee('Timbangan Kami Box', false);
        $res->assertSee('PT Mitra Daur Ulang', false);
        $res->assertSee('Truk 1 Muatan Pagi', false);

        // Table headers
        $res->assertSee('No', false);
        $res->assertSee('Jenis Sampah', false);
        $res->assertSee('Harga', false);
        $res->assertSee('Berat', false);
        $res->assertSee('Total Harga', false);

        // Material rows
        $res->assertSee('Kardus Box', false);
        $res->assertSee('Rp 2.500', false);
        $res->assertSee('60,00 kg', false);
        $res->assertSee('Rp 150.000', false);

        $res->assertSee('Besi Padat', false);
        $res->assertSee('Rp 5.000', false);
        $res->assertSee('20,00 kg', false);
        $res->assertSee('Rp 100.000', false);

        // Below table totals
        $res->assertSee('Total Berat', false);
        $res->assertSee('80,00', false); // 60 + 20
        $res->assertSee('Total Harga', false);
        $res->assertSee('Rp 250.000', false);

        // Print button
        $res->assertSee('window.print()', false);
        $res->assertSee('Print', false);
    }
}
