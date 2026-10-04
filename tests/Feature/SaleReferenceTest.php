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

        // Section 1: Compact Sisa Setoran Siap Dijual
        $res->assertSee('Sisa Setoran Siap Dijual', false);
        $res->assertSee('1 Material &bull; 40,00 kg siap dijual', false);
        $res->assertSee('placeholder="Cari material..."', false);
        $res->assertSee('Kardus Box', false);
        $res->assertSee('40,00', false); // remaining weight in id-ID format
        $res->assertSee('Rp 2.000', false); // member price
        $res->assertSee('✓ Siap Dijual', false);
        $res->assertSee('Catat Penjualan ke Agregator &rarr;', false);

        // Compact table: Total Setoran & Sudah Terjual omitted from main headers
        $res->assertDontSee('<th class="py-2.5 px-4 text-right">Total Setoran</th>', false);
        $res->assertDontSee('<th class="py-2.5 px-4 text-right">Sudah Terjual</th>', false);
        $res->assertSee('max-h-[360px] overflow-y-auto', false);
        $res->assertSee('sticky top-0', false);

        // Section 2: Riwayat Penjualan (Grouped by Date & Agregator)
        $res->assertSee(\Carbon\Carbon::parse($date)->translatedFormat('d F Y'), false);
        $res->assertSee('PT Mitra Daur Ulang', false);
        $res->assertSee('1 material', false);
        $res->assertSee('60,00 kg', false);
        $res->assertSee('Rp 150.000', false);
        $res->assertSee('Margin +Rp 30.000', false);
        $res->assertSee('Truk 1 Muatan Pagi', false);
        $res->assertSee('Detail &rarr;', false);
        $res->assertSee(route('bank-sampah.sales.show', $sale), false);
        // Material details are NOT expanded on index (no collapse accordion)
        $res->assertDontSee('Lihat Rincian', false);
        $res->assertDontSee('Rincian Muatan Penjualan:', false);
    }

    public function test_sales_index_groups_multiple_transactions_by_date_and_agregator(): void
    {
        [$admin, $device] = $this->createAdminClient();

        $col1 = Collector::create(['name' => 'Lapak Jaya', 'phone' => '081111']);
        $col2 = Collector::create(['name' => 'Berkah Jaya', 'phone' => '082222']);
        $cat1 = WasteCategory::create(['name' => 'Kardus', 'code' => 'KRD.01', 'unit' => 'kg', 'member_price' => 1000]);
        $cat2 = WasteCategory::create(['name' => 'Botol PET', 'code' => 'PET.01', 'unit' => 'kg', 'member_price' => 2000]);

        $date1 = '2026-09-12';
        $date2 = '2026-09-11';

        // Date 1: 2 sales with different collectors
        $sale1 = Sale::create(['collector_id' => $col1->id, 'date' => $date1, 'total_amount' => 1250000]);
        SaleDetail::create([
            'sale_id' => $sale1->id,
            'waste_category_id' => $cat1->id,
            'weight' => 125,
            'price_per_unit' => 10000,
            'member_price' => 7440,
            'subtotal' => 1250000,
            'margin' => 320000,
        ]);

        $sale2 = Sale::create(['collector_id' => $col2->id, 'date' => $date1, 'total_amount' => 540000]);
        SaleDetail::create([
            'sale_id' => $sale2->id,
            'waste_category_id' => $cat2->id,
            'weight' => 60,
            'price_per_unit' => 9000,
            'member_price' => 7000,
            'subtotal' => 540000,
            'margin' => 120000,
        ]);

        // Date 2: 1 sale
        $sale3 = Sale::create(['collector_id' => $col1->id, 'date' => $date2, 'total_amount' => 300000]);
        SaleDetail::create([
            'sale_id' => $sale3->id,
            'waste_category_id' => $cat1->id,
            'weight' => 30,
            'price_per_unit' => 10000,
            'member_price' => 8000,
            'subtotal' => 300000,
            'margin' => 60000,
        ]);

        $res = $this->actingAs($admin)
            ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $device->device_token)
            ->get(route('bank-sampah.sales.index'));

        $res->assertStatus(200);

        // Date 1 group header
        $res->assertSee(\Carbon\Carbon::parse($date1)->translatedFormat('d F Y'), false);
        // Date 2 group header
        $res->assertSee(\Carbon\Carbon::parse($date2)->translatedFormat('d F Y'), false);

        // Agregators on Date 1
        $res->assertSee('Lapak Jaya', false);
        $res->assertSee('125,00 kg', false);
        $res->assertSee('Rp 1.250.000', false);
        $res->assertSee('Margin +Rp 320.000', false);
        $res->assertSee(route('bank-sampah.sales.show', $sale1), false);

        $res->assertSee('Berkah Jaya', false);
        $res->assertSee('60,00 kg', false);
        $res->assertSee('Rp 540.000', false);
        $res->assertSee('Margin +Rp 120.000', false);
        $res->assertSee(route('bank-sampah.sales.show', $sale2), false);

        // Agregator on Date 2
        $res->assertSee('30,00 kg', false);
        $res->assertSee('Rp 300.000', false);
        $res->assertSee('Margin +Rp 60.000', false);
        $res->assertSee(route('bank-sampah.sales.show', $sale3), false);
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

        // Controls (material selection workspace, no manual row flow)
        $res->assertSee('Tarik Sisa Setoran', false);
        $res->assertSee('Material Agregator', false);
        $res->assertSee('Pilih Semua', false);
        $res->assertSee('Pilih Semua Berat', false);
        $res->assertSee('type="checkbox"', false);
        $res->assertDontSee('+ Tambah Baris', false);
        $res->assertSee('Konfirmasi Penjualan', false);

        // Layout & Padding consistency with Index
        $res->assertSee('space-y-6 max-w-7xl mx-auto pb-10', false);
        $res->assertSee('p-4 sm:p-5', false);
        $res->assertSee('py-3.5 px-4', false);

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

        // Header & Breadcrumb
        $res->assertSee('Detail Penjualan', false);
        $res->assertSee('SAL-00001', false);
        $res->assertSee('✓ Selesai', false);
        $res->assertSee('PT Mitra Daur Ulang', false);
        $res->assertSee('Truk 1 Muatan Pagi', false);
        $res->assertSee('&larr; Kembali ke Penjualan', false);

        // Summary 4 Metrics
        $res->assertSee('Total Material', false);
        $res->assertSee('2', false); // 2 jenis material
        $res->assertSee('Total Berat', false);
        $res->assertSee('80,00', false); // 60 + 20 kg
        $res->assertSee('Total Penjualan', false);
        $res->assertSee('Rp 250.000', false);
        $res->assertSee('Margin Bank Sampah', false);
        $res->assertSee('+Rp 50.000', false); // 30.000 + 20.000

        // Table headers (Harga Lapak, not generic Harga)
        $res->assertSee('No', false);
        $res->assertSee('Jenis Sampah', false);
        $res->assertSee('Harga Lapak', false);
        $res->assertSee('Berat', false);
        $res->assertSee('Total Harga', false);

        // Material rows (Historical snapshot)
        $res->assertSee('Kardus Box', false);
        $res->assertSee('Rp 2.500', false);
        $res->assertSee('60,00 kg', false);
        $res->assertSee('Rp 150.000', false);

        $res->assertSee('Besi Padat', false);
        $res->assertSee('Rp 5.000', false);
        $res->assertSee('20,00 kg', false);
        $res->assertSee('Rp 100.000', false);

        // Print button
        $res->assertSee('window.print()', false);
        $res->assertSee('Cetak / Print', false);
    }
}
