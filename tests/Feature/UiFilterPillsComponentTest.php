<?php

namespace Tests\Feature;

use App\Models\AdminDevice;
use App\Models\Collector;
use App\Models\User;
use App\Models\WasteCategory;
use App\Models\WasteCustomer;
use App\Services\DeviceTrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UiFilterPillsComponentTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected AdminDevice $device;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::firstOrCreate(['name' => 'admin_bank_sampah', 'guard_name' => 'web']);
        foreach ([
            'view_waste_bank',
            'manage_waste_prices',
            'manage_waste_customers',
            'manage_sales',
            'manage_deposits',
            'view_waste_reports',
        ] as $p) {
            $perm = Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
            $role->givePermissionTo($perm);
        }

        $this->admin = User::factory()->create();
        $this->admin->assignRole($role);

        $this->device = AdminDevice::create([
            'user_id' => $this->admin->id,
            'device_token' => 'filter-pills-test-' . uniqid(),
            'device_name' => 'Test Laptop',
            'browser' => 'Chrome',
            'platform' => 'Darwin',
            'is_revoked' => false,
            'first_seen_at' => now()->subDay(),
            'last_seen_at' => now()->subDay(),
        ]);
    }

    private function getAsAdmin(string $url)
    {
        return $this->actingAs($this->admin)
            ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $this->device->device_token)
            ->get($url);
    }

    public function test_customer_index_renders_filter_pills(): void
    {
        WasteCustomer::create(['name' => 'Customer Aktif', 'customer_code' => 'NSB-001', 'status' => 'active']);
        WasteCustomer::create(['name' => 'Customer Nonaktif', 'customer_code' => 'NSB-002', 'status' => 'inactive']);

        $res = $this->getAsAdmin(route('bank-sampah.customers.index'));
        $res->assertStatus(200);
        $res->assertSee('Semua Status', false);
        $res->assertSee('Aktif', false);
        $res->assertSee('Nonaktif', false);
        $res->assertSee('name="status"', false);
        $res->assertSee('pr-6', false);
        $res->assertSee('aria-label="status"', false);
    }

    public function test_customer_create_and_edit_render_choice_cards(): void
    {
        $customer = WasteCustomer::create(['name' => 'Pak Rudi', 'customer_code' => 'NSB-003', 'status' => 'active']);

        $resCreate = $this->getAsAdmin(route('bank-sampah.customers.create'));
        $resCreate->assertStatus(200);
        $resCreate->assertSee('name="status"', false);
        $resCreate->assertSee('Aktif', false);
        $resCreate->assertSee('Nonaktif', false);

        $resEdit = $this->getAsAdmin(route('bank-sampah.customers.edit', $customer));
        $resEdit->assertStatus(200);
        $resEdit->assertSee('name="status"', false);
        $resEdit->assertSee('Aktif', false);
    }

    public function test_waste_prices_history_renders_filter_pills_and_combobox(): void
    {
        $res = $this->getAsAdmin(route('bank-sampah.waste-prices.history'));
        $res->assertStatus(200);
        $res->assertSee('name="source"', false);
        $res->assertSee('name="waste_category_id"', false);
        $res->assertSee('Manual', false);
        $res->assertSee('Import', false);
    }

    public function test_waste_categories_import_renders_mode_choice_pills(): void
    {
        $res = $this->getAsAdmin(route('bank-sampah.waste-categories.import'));
        $res->assertStatus(200);
        $res->assertSee('name="mode"', false);
        $res->assertSee('Update + Tambah', false);
        $res->assertSee('insert_or_update', false);
    }

    public function test_sales_create_and_edit_render_contextual_collector_selector(): void
    {
        Collector::create(['name' => 'Agregator Makmur', 'phone' => '0812345']);

        $res = $this->getAsAdmin(route('bank-sampah.sales.create'));
        $res->assertStatus(200);
        $res->assertSee('collector_id', false);
        $res->assertSee('Agregator Makmur', false);
        $res->assertSee('Agregator acuan harga lapak', false);
        $res->assertSee('Pilih agregator pembeli untuk memuat sisa setoran dan mengaktifkan harga lapak.', false);
    }
}
