<?php

namespace Tests\Feature;

use App\Models\AdminDevice;
use App\Models\SavingsLedger;
use App\Models\User;
use App\Models\WasteCustomer;
use App\Services\DeviceTrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CustomerReferenceTest extends TestCase
{
    use RefreshDatabase;

    private function createAdminClient(): array
    {
        $perms = [
            'view_waste_bank',
            'manage_waste_customers',
            'manage_deposits',
            'manage_withdrawals',
            'view_waste_reports'
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
            'device_token' => 'cust-test-token-' . uniqid(),
            'device_name' => 'Admin PC',
            'browser' => 'Chrome',
            'platform' => 'Darwin',
            'is_revoked' => false,
            'first_seen_at' => now()->subDay(),
            'last_seen_at' => now()->subDay(),
        ]);

        return [$admin, $device];
    }

    public function test_customer_index_renders_desktop_table_and_mobile_cards(): void
    {
        [$admin, $device] = $this->createAdminClient();
        $code = WasteCustomer::generateNextCustomerCode();
        $customer = WasteCustomer::create([
            'name' => 'Ibu Siti Aminah',
            'customer_code' => $code,
            'phone' => '081234567890',
            'address' => 'RT 03 / RW 05, No. 12',
            'status' => 'active',
        ]);

        $res = $this->actingAs($admin)
            ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $device->device_token)
            ->get(route('bank-sampah.customers.index'));

        $res->assertStatus(200);

        // Header & Actions
        $res->assertSee('Data Nasabah', false);
        $res->assertSee('Nasabah Baru', false);

        // Customer Details
        $res->assertSee('Ibu Siti Aminah', false);
        $res->assertSee($code, false);
        $res->assertSee('081234567890', false);
        $res->assertSee('RT 03 / RW 05, No. 12', false);

        // Desktop and Mobile views
        $res->assertSee('hidden md:block', false);
        $res->assertSee('block md:hidden', false);

        // Semantic tokens & Clean modal
        $res->assertSee('confirm-delete-modal', false);
        $res->assertDontSee('bg-slate-900', false);
    }

    public function test_customer_index_empty_state_and_search_no_result(): void
    {
        [$admin, $device] = $this->createAdminClient();

        // 1. Zero database empty state
        $resEmpty = $this->actingAs($admin)
            ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $device->device_token)
            ->get(route('bank-sampah.customers.index'));

        $resEmpty->assertStatus(200);
        $resEmpty->assertSee('Belum Ada Data Nasabah', false);
        $resEmpty->assertSee('+ Tambah Nasabah Pertama', false);

        // 2. Search no result empty state
        $resSearch = $this->actingAs($admin)
            ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $device->device_token)
            ->get(route('bank-sampah.customers.index', ['search' => 'NamaTidakAda']));

        $resSearch->assertStatus(200);
        $resSearch->assertSee('Nasabah Tidak Ditemukan', false);
        $resSearch->assertSee('Reset Filter', false);
    }

    public function test_customer_detail_shows_financial_kpis_and_connect_the_dots(): void
    {
        [$admin, $device] = $this->createAdminClient();
        $code = WasteCustomer::generateNextCustomerCode();
        $customer = WasteCustomer::create([
            'name' => 'Bapak Sudirman',
            'customer_code' => $code,
            'phone' => '081987654321',
            'address' => 'Blok C No. 5',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        // Add 2 savings deposits (credit) and 1 withdrawal (debit)
        SavingsLedger::create([
            'waste_customer_id' => $customer->id,
            'type' => 'credit',
            'amount' => 50000,
            'description' => 'Setoran Sampah Plastik',
        ]);
        SavingsLedger::create([
            'waste_customer_id' => $customer->id,
            'type' => 'credit',
            'amount' => 25000,
            'description' => 'Setoran Kardus',
        ]);
        SavingsLedger::create([
            'waste_customer_id' => $customer->id,
            'type' => 'debit',
            'amount' => 15000,
            'description' => 'Penarikan Kas Tunai',
        ]);

        $res = $this->actingAs($admin)
            ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $device->device_token)
            ->get(route('bank-sampah.customers.show', $customer));

        $res->assertStatus(200);

        // Customer Info
        $res->assertSee('Bapak Sudirman', false);
        $res->assertSee($code, false);

        // Connect The Dots Operational Actions
        $res->assertSee('Catat Setoran', false);
        $res->assertSee('customer_id=' . $customer->id, false);
        $res->assertSee('Tarik Saldo', false);
        $res->assertSee('Buku Tabungan', false);

        // Financial KPIs (Balance: 50000 + 25000 - 15000 = 60000)
        $res->assertSee('Rp 60.000', false);
        $res->assertSee('Saldo Tabungan Aktif', false);
        $res->assertSee('Frekuensi Setoran', false);
        $res->assertSee('Frekuensi Penarikan', false);
    }

    public function test_customer_create_and_validation(): void
    {
        [$admin, $device] = $this->createAdminClient();

        // 1. Render create form
        $res = $this->actingAs($admin)
            ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $device->device_token)
            ->get(route('bank-sampah.customers.create'));

        $res->assertStatus(200);
        $res->assertSee('Tambah Nasabah Baru', false);
        $res->assertSee('Nama Lengkap Nasabah', false);
        $res->assertSee('(Wajib diisi)', false);

        // 2. Validation error on empty submit
        $postFail = $this->actingAs($admin)
            ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $device->device_token)
            ->post(route('bank-sampah.customers.store'), [
                'name' => '',
                'status' => 'active',
            ]);
        $postFail->assertSessionHasErrors('name');

        // 3. Successful customer creation
        $postSuccess = $this->actingAs($admin)
            ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $device->device_token)
            ->post(route('bank-sampah.customers.store'), [
                'name' => 'Ibu Ratna',
                'phone' => '081299998888',
                'address' => 'Jl. Mawar No. 4',
                'status' => 'active',
            ]);

        $postSuccess->assertRedirect(route('bank-sampah.customers.index'));
        $this->assertDatabaseHas('waste_customers', [
            'name' => 'Ibu Ratna',
            'phone' => '081299998888',
            'status' => 'active',
        ]);
    }

    public function test_customer_edit_and_update(): void
    {
        [$admin, $device] = $this->createAdminClient();
        $code = WasteCustomer::generateNextCustomerCode();
        $customer = WasteCustomer::create([
            'name' => 'Pak Hendra',
            'customer_code' => $code,
            'phone' => '0811223344',
            'status' => 'active',
        ]);

        // 1. Render edit form
        $res = $this->actingAs($admin)
            ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $device->device_token)
            ->get(route('bank-sampah.customers.edit', $customer));

        $res->assertStatus(200);
        $res->assertSee('Edit Nasabah', false);
        $res->assertSee('Pak Hendra', false);
        $res->assertSee($code, false);

        // 2. Update customer
        $putRes = $this->actingAs($admin)
            ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $device->device_token)
            ->put(route('bank-sampah.customers.update', $customer), [
                'name' => 'Pak Hendra Wijaya',
                'phone' => '0811223399',
                'address' => 'Jl. Kenanga 12',
                'status' => 'inactive',
            ]);

        $putRes->assertRedirect(route('bank-sampah.customers.index'));
        $this->assertDatabaseHas('waste_customers', [
            'id' => $customer->id,
            'name' => 'Pak Hendra Wijaya',
            'status' => 'inactive',
        ]);
    }

    public function test_customer_spa_fragments_render_cleanly(): void
    {
        [$admin, $device] = $this->createAdminClient();
        $customer = WasteCustomer::create([
            'name' => 'Ibu Kartini',
            'customer_code' => WasteCustomer::generateNextCustomerCode(),
            'phone' => '0815555555',
            'status' => 'active',
        ]);

        $routes = [
            'index' => route('bank-sampah.customers.index'),
            'show' => route('bank-sampah.customers.show', $customer),
            'create' => route('bank-sampah.customers.create'),
            'edit' => route('bank-sampah.customers.edit', $customer),
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
}
