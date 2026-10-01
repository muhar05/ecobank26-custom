<?php

namespace Tests\Feature;

use App\Models\AdminDevice;
use App\Models\Deposit;
use App\Models\DepositDetail;
use App\Models\User;
use App\Models\WasteCategory;
use App\Models\WasteCategoryGroup;
use App\Models\WasteCustomer;
use App\Services\DeviceTrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DepositIndexReferenceTest extends TestCase
{
    use RefreshDatabase;

    private function createAdminClient(): array
    {
        $perms = [
            'view_waste_bank',
            'manage_deposits',
            'manage_waste_customers',
            'view_waste_reports',
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
            'device_token' => 'dep-idx-token-' . uniqid(),
            'device_name' => 'Admin PC',
            'browser' => 'Chrome',
            'platform' => 'Darwin',
            'is_revoked' => false,
            'first_seen_at' => now()->subDay(),
            'last_seen_at' => now()->subDay(),
        ]);

        return [$admin, $device];
    }

    public function test_deposit_index_empty_state_and_header(): void
    {
        [$admin, $device] = $this->createAdminClient();

        $res = $this->actingAs($admin)
            ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $device->device_token)
            ->get(route('bank-sampah.deposits.index'));

        $res->assertStatus(200);

        // Header & Actions
        $res->assertSee('Setoran Sampah', false);
        $res->assertSee('Data Nasabah &rarr;', false);
        $res->assertSee('Import Excel', false);
        $res->assertSee('Catat Setoran Baru', false);

        // Honest Empty State (Zero database)
        $res->assertSee('Belum Ada Riwayat Setoran', false);
        $res->assertSee('+ Catat Setoran Pertama', false);

        // Anti-Slop
        $res->assertDontSee('bg-slate-900', false);
        $res->assertDontSee('rounded-[2rem]', false);
    }

    public function test_deposit_index_search_empty_state(): void
    {
        [$admin, $device] = $this->createAdminClient();

        $res = $this->actingAs($admin)
            ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $device->device_token)
            ->get(route('bank-sampah.deposits.index', ['search' => 'NamaPalsuYangTidakAda']));

        $res->assertStatus(200);
        $res->assertSee('Setoran Tidak Ditemukan', false);
        $res->assertSee('Reset Filter', false);
        $res->assertSee('+ Catat Setoran Baru', false);
    }

    public function test_deposit_index_renders_desktop_table_and_mobile_cards(): void
    {
        [$admin, $device] = $this->createAdminClient();
        $date = now()->format('Y-m-d');

        $group = WasteCategoryGroup::create(['name' => 'Plastik', 'code' => 'PLS']);
        $cat = WasteCategory::create([
            'name' => 'Botol PET Bening',
            'code' => 'PET.01',
            'unit' => 'kg',
            'member_price' => 3000,
            'waste_category_group_id' => $group->id,
        ]);
        $customer = WasteCustomer::create([
            'name' => 'Ibu Fatimah',
            'customer_code' => WasteCustomer::generateNextCustomerCode(),
            'phone' => '081233445566',
            'status' => 'active',
        ]);

        $deposit = Deposit::create([
            'waste_customer_id' => $customer->id,
            'date' => $date,
            'total_amount' => 45000,
            'notes' => 'Barang titipan RT 04',
        ]);
        DepositDetail::create([
            'deposit_id' => $deposit->id,
            'waste_category_id' => $cat->id,
            'weight' => 15,
            'price_per_unit' => 3000,
            'subtotal' => 45000,
        ]);

        $res = $this->actingAs($admin)
            ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $device->device_token)
            ->get(route('bank-sampah.deposits.index'));

        $res->assertStatus(200);

        // Customer Details
        $res->assertSee('Ibu Fatimah', false);
        $res->assertSee($customer->customer_code, false);
        $res->assertSee('DEP-' . str_pad($deposit->id, 5, '0', STR_PAD_LEFT), false);

        // Material & Values
        $res->assertSee('Botol PET Bening', false);
        $res->assertSee('15,00', false); // weight in id-ID format
        $res->assertSee('Rp 45.000', false);
        $res->assertSee('Barang titipan RT 04', false);

        // Responsive structure
        $res->assertSee('hidden md:block', false);
        $res->assertSee('block md:hidden', false);

        // Actions
        $res->assertSee('Rincian', false);
        $res->assertSee('Edit', false);
        $res->assertSee('Hapus', false);
        $res->assertSee('confirm-delete-modal', false);
    }

    public function test_deposit_index_spa_fragment_contains_no_micro_text_or_slate(): void
    {
        [$admin, $device] = $this->createAdminClient();

        $res = $this->actingAs($admin)
            ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $device->device_token)
            ->withHeaders(['X-Eco-Nav' => '1'])
            ->get(route('bank-sampah.deposits.index'));

        $res->assertStatus(200);
        $res->assertSee('id="eco-spa-fragment"', false);
        $res->assertDontSee('<!DOCTYPE html>', false);
        $res->assertDontSee('bg-slate-900', false);
        $res->assertDontSee('text-[10px]', false);
        $res->assertDontSee('text-[11px]', false);
        $res->assertDontSee('text-xs', false);
    }
}
