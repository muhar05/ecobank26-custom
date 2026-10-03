<?php

namespace Tests\Feature;

use App\Models\AdminDevice;
use App\Models\Deposit;
use App\Models\DepositDetail;
use App\Models\SavingsLedger;
use App\Models\User;
use App\Models\WasteCategory;
use App\Models\WasteCustomer;
use App\Models\Withdrawal;
use App\Services\DeviceTrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SavingsReportTest extends TestCase
{
    use RefreshDatabase;

    private function createAdminClient(): array
    {
        $perms = [
            'view_waste_bank',
            'view_waste_reports',
            'manage_deposits',
            'manage_withdrawals',
            'manage_waste_customers',
        ];
        foreach ($perms as $permName) {
            $perm = Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
        }
        $role = Role::firstOrCreate(['name' => 'admin_bank_sampah', 'guard_name' => 'web']);
        $role->syncPermissions($perms);

        $admin = User::factory()->create();
        $admin->assignRole($role);

        $device = AdminDevice::create([
            'user_id' => $admin->id,
            'device_token' => 'savings-test-token-' . uniqid(),
            'device_name' => 'Admin PC',
            'browser' => 'Chrome',
            'platform' => 'Darwin',
            'is_revoked' => false,
            'first_seen_at' => now()->subDay(),
            'last_seen_at' => now()->subDay(),
        ]);

        return [$admin, $device];
    }

    public function test_savings_index_renders_customer_balance_list_with_columns(): void
    {
        [$admin, $device] = $this->createAdminClient();

        $c1 = WasteCustomer::create([
            'name' => 'Vina Wijaya',
            'customer_code' => 'NSB-000001',
            'address' => 'RT 03 / RW 26, Blok C',
            'status' => 'active',
        ]);
        $c2 = WasteCustomer::create([
            'name' => 'Budi Santoso',
            'customer_code' => 'NSB-000002',
            'address' => 'RT 05 / RW 26',
            'status' => 'active',
        ]);

        // Transactions for C1: Credit 50.000, Debit 10.000 -> Balance 40.000
        SavingsLedger::create([
            'waste_customer_id' => $c1->id,
            'type' => 'credit',
            'amount' => 50000,
            'description' => 'Setoran kardus',
        ]);
        SavingsLedger::create([
            'waste_customer_id' => $c1->id,
            'type' => 'debit',
            'amount' => 10000,
            'description' => 'Penarikan saldo',
        ]);

        $res = $this->actingAs($admin)
            ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $device->device_token)
            ->get(route('bank-sampah.savings'));

        $res->assertStatus(200);

        // Header & Title
        $res->assertSee('Saldo Nasabah', false);
        $res->assertSee('Export CSV', false);

        // Summary cards
        $res->assertSee('Total Nasabah', false);
        $res->assertSee('Total Saldo Simpanan', false);
        $res->assertSee('Total Setoran (Kredit)', false);
        $res->assertSee('Total Penarikan (Debit)', false);

        // Table headers (Main Columns)
        $res->assertSee('No', false);
        $res->assertSee('Nama Nasabah', false);
        $res->assertSee('No. RT', false);
        $res->assertSee('Aktivitas / Transaksi Terakhir', false);
        $res->assertSee('Saldo Saat Ini', false);
        $res->assertSee('Aksi', false);

        // Customer row data
        $res->assertSee('Vina Wijaya', false);
        $res->assertSee('NSB-000001', false);
        $res->assertSee('RT 03', false);
        $res->assertSee('Rp 40.000', false);
        $res->assertSee(route('bank-sampah.savings.show', $c1), false);

        // Customer 2
        $res->assertSee('Budi Santoso', false);
        $res->assertSee('RT 05', false);
    }

    public function test_savings_index_search_by_name_and_rt(): void
    {
        [$admin, $device] = $this->createAdminClient();

        $c1 = WasteCustomer::create([
            'name' => 'Siti Nurhaliza',
            'customer_code' => 'NSB-000010',
            'address' => 'RT 02, No 12',
            'status' => 'active',
        ]);
        $c2 = WasteCustomer::create([
            'name' => 'Ahmad Dahlan',
            'customer_code' => 'NSB-000011',
            'address' => 'RT 07, No 44',
            'status' => 'active',
        ]);

        // Search by name "Siti"
        $resSearch = $this->actingAs($admin)
            ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $device->device_token)
            ->get(route('bank-sampah.savings', ['search' => 'Siti']));

        $resSearch->assertStatus(200);
        $resSearch->assertSee('Siti Nurhaliza', false);
        $resSearch->assertDontSee('Ahmad Dahlan', false);

        // Search by RT "07"
        $resRt = $this->actingAs($admin)
            ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $device->device_token)
            ->get(route('bank-sampah.savings', ['search' => 'RT 07']));

        $resRt->assertStatus(200);
        $resRt->assertSee('Ahmad Dahlan', false);
        $resRt->assertDontSee('Siti Nurhaliza', false);
    }

    public function test_savings_show_renders_customer_passbook_with_vertical_history(): void
    {
        [$admin, $device] = $this->createAdminClient();

        $customer = WasteCustomer::create([
            'name' => 'Mama Adam',
            'customer_code' => 'NSB-000025',
            'address' => 'RT 04 / RW 26',
            'status' => 'active',
        ]);

        $cat = WasteCategory::create(['name' => 'Botol Putih', 'unit' => 'kg', 'member_price' => 2000]);

        // 1. First transaction: Deposit 600 on 11 Juli 2026
        $dep1 = Deposit::create([
            'waste_customer_id' => $customer->id,
            'date' => '2026-07-11',
            'total_amount' => 600,
            'notes' => 'Setoran botol pertama',
        ]);
        SavingsLedger::create([
            'waste_customer_id' => $customer->id,
            'type' => 'credit',
            'amount' => 600,
            'description' => 'Setoran botol pertama',
            'reference_type' => Deposit::class,
            'reference_id' => $dep1->id,
            'created_at' => '2026-07-11 08:00:00',
        ]);

        // 2. Second transaction: Deposit 3.068 on 1 Agustus 2026
        $dep2 = Deposit::create([
            'waste_customer_id' => $customer->id,
            'date' => '2026-08-01',
            'total_amount' => 3068,
            'notes' => 'Setoran kardus minggu 1',
        ]);
        SavingsLedger::create([
            'waste_customer_id' => $customer->id,
            'type' => 'credit',
            'amount' => 3068,
            'description' => 'Setoran kardus minggu 1',
            'reference_type' => Deposit::class,
            'reference_id' => $dep2->id,
            'created_at' => '2026-08-01 09:00:00',
        ]);

        // 3. Third transaction: Deposit 315 on 12 September 2026
        $dep3 = Deposit::create([
            'waste_customer_id' => $customer->id,
            'date' => '2026-09-12',
            'total_amount' => 315,
            'notes' => 'Setoran kaleng',
        ]);
        SavingsLedger::create([
            'waste_customer_id' => $customer->id,
            'type' => 'credit',
            'amount' => 315,
            'description' => 'Setoran kaleng',
            'reference_type' => Deposit::class,
            'reference_id' => $dep3->id,
            'created_at' => '2026-09-12 10:00:00',
        ]);

        // 4. Fourth transaction: Withdrawal 500 on 12 September 2026
        $wd = Withdrawal::create([
            'waste_customer_id' => $customer->id,
            'date' => '2026-09-12',
            'amount' => 500,
            'notes' => 'Beli sembako warung',
        ]);
        SavingsLedger::create([
            'waste_customer_id' => $customer->id,
            'type' => 'debit',
            'amount' => 500,
            'description' => 'Beli sembako warung',
            'reference_type' => Withdrawal::class,
            'reference_id' => $wd->id,
            'created_at' => '2026-09-12 11:00:00',
        ]);

        $res = $this->actingAs($admin)
            ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $device->device_token)
            ->get(route('bank-sampah.savings.show', $customer));

        $res->assertStatus(200);

        // Customer Info Header
        $res->assertSee('Buku Tabungan Nasabah', false);
        $res->assertSee('Mama Adam', false);
        $res->assertSee('RT 04', false);
        $res->assertSee('NSB-000025', false);
        $res->assertSee('&larr; Kembali ke Saldo Nasabah', false);

        // Summary Cards
        // Final balance: 600 + 3068 + 315 - 500 = 3483
        $res->assertSee('Saldo Saat Ini', false);
        $res->assertSee('Rp 3.483', false);
        $res->assertSee('+Rp 3.983', false); // Total Credit
        $res->assertSee('-Rp 500', false);   // Total Debit
        $res->assertSee('4', false);         // Total Transactions

        // Passbook Table Headers
        $res->assertSee('Tanggal', false);
        $res->assertSee('Jenis Transaksi', false);
        $res->assertSee('Nominal Masuk / Keluar', false);
        $res->assertSee('Saldo Setelah Transaksi', false);
        $res->assertSee('Keterangan', false);

        // Vertical History Items
        $res->assertSee(\Carbon\Carbon::parse('2026-09-12')->translatedFormat('d F Y'), false);
        $res->assertSee(\Carbon\Carbon::parse('2026-08-01')->translatedFormat('d F Y'), false);
        $res->assertSee(\Carbon\Carbon::parse('2026-07-11')->translatedFormat('d F Y'), false);

        $res->assertSee('Setoran', false);
        $res->assertSee('Penarikan', false);
        $res->assertSee('+ Rp 315', false);
        $res->assertSee('- Rp 500', false);
        $res->assertSee('Rp 3.483', false);
        $res->assertSee('Rp 3.983', false);
        $res->assertSee('Rp 3.668', false);
        $res->assertSee('Rp 600', false);

        $res->assertSee('Beli sembako warung', false);
    }

    public function test_savings_export_streams_csv_with_rt_and_balance(): void
    {
        [$admin, $device] = $this->createAdminClient();

        $c = WasteCustomer::create([
            'name' => 'Dewi Persik',
            'customer_code' => 'NSB-000099',
            'address' => 'RT 01 / RW 26',
            'status' => 'active',
        ]);
        SavingsLedger::create([
            'waste_customer_id' => $c->id,
            'type' => 'credit',
            'amount' => 12500,
        ]);

        $res = $this->actingAs($admin)
            ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $device->device_token)
            ->get(route('bank-sampah.savings.export'));

        $res->assertStatus(200);
        $res->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $content = $res->streamedContent();
        $this->assertStringContainsString('Kode Nasabah', $content);
        $this->assertStringContainsString('No. RT / Alamat', $content);
        $this->assertStringContainsString('Dewi Persik', $content);
        $this->assertStringContainsString('RT 01', $content);
        $this->assertStringContainsString('12500', $content);
    }

    public function test_spa_navigation_works_on_savings_and_detail(): void
    {
        [$admin, $device] = $this->createAdminClient();

        $customer = WasteCustomer::create([
            'name' => 'Warga SPA',
            'customer_code' => 'NSB-000100',
            'address' => 'RT 09',
            'status' => 'active',
        ]);

        // 1. Index SPA Fragment
        $resIndex = $this->actingAs($admin)
            ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $device->device_token)
            ->withHeaders(['X-Eco-Nav' => '1'])
            ->get(route('bank-sampah.savings'));

        $resIndex->assertStatus(200);
        $resIndex->assertSee('id="eco-spa-fragment"', false);
        $resIndex->assertSee('id="app-content-slot"', false);
        $resIndex->assertDontSee('<!DOCTYPE html>', false);

        // 2. Detail SPA Fragment
        $resDetail = $this->actingAs($admin)
            ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $device->device_token)
            ->withHeaders(['X-Eco-Nav' => '1'])
            ->get(route('bank-sampah.savings.show', $customer));

        $resDetail->assertStatus(200);
        $resDetail->assertSee('id="eco-spa-fragment"', false);
        $resDetail->assertSee('data-title="Buku Tabungan - Warga SPA"', false);
        $resDetail->assertSee('id="app-content-slot"', false);
        $resDetail->assertDontSee('<!DOCTYPE html>', false);
    }
}
