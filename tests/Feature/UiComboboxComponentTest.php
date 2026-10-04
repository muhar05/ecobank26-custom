<?php

namespace Tests\Feature;

use App\Models\AdminDevice;
use App\Models\Collector;
use App\Models\Deposit;
use App\Models\User;
use App\Models\WasteCategory;
use App\Models\WasteCustomer;
use App\Models\Withdrawal;
use App\Services\DeviceTrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UiComboboxComponentTest extends TestCase
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
            'manage_withdrawals',
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
            'device_token' => 'combobox-test-token-' . uniqid(),
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

    public function test_withdrawals_create_and_edit_render_combobox(): void
    {
        $customer = WasteCustomer::create([
            'name' => 'Pak Budi Santoso',
            'customer_code' => 'NSB-000123',
            'status' => 'active',
        ]);

        // Create page
        $resCreate = $this->getAsAdmin(route('bank-sampah.withdrawals.create'));
        $resCreate->assertStatus(200);
        $resCreate->assertSee('id="waste_customer_id"', false);
        $resCreate->assertSee('focus-visible:ring-primary', false);
        $resCreate->assertSee('waste_customer_id', false);
        $resCreate->assertSee('Pak Budi Santoso', false);
        $resCreate->assertSee('NSB-000123', false);

        // Edit page
        $withdrawal = Withdrawal::create([
            'waste_customer_id' => $customer->id,
            'amount' => 50000,
            'date' => now()->toDateString(),
            'notes' => 'Test penarikan',
        ]);

        $resEdit = $this->getAsAdmin(route('bank-sampah.withdrawals.edit', $withdrawal));
        $resEdit->assertStatus(200);
        $resEdit->assertSee('waste_customer_id', false);
        $resEdit->assertSee('Pak Budi Santoso', false);
    }

    public function test_sales_create_and_edit_render_material_combobox(): void
    {
        WasteCategory::create([
            'name' => 'Kardus Super Tebal',
            'code' => 'KRT.11',
            'unit' => 'kg',
            'member_price' => 1500,
        ]);

        $resCreate = $this->getAsAdmin(route('bank-sampah.sales.create'));
        $resCreate->assertStatus(200);
        $resCreate->assertSee('Kardus Super Tebal', false);
        $resCreate->assertSee('min-w-[260px]', false);
    }

    public function test_reports_deposits_and_savings_journal_render_combobox(): void
    {
        WasteCustomer::create([
            'name' => 'Ibu Rahmawati',
            'customer_code' => 'NSB-000789',
            'status' => 'active',
        ]);

        // Deposits report
        $resDep = $this->getAsAdmin(route('bank-sampah.reports.deposits'));
        $resDep->assertStatus(200);
        $resDep->assertSee('waste_customer_id', false);
        $resDep->assertSee('Ibu Rahmawati', false);

        // Savings Journal report
        $resJournal = $this->getAsAdmin(route('bank-sampah.reports.savings-journal'));
        $resJournal->assertStatus(200);
        $resJournal->assertSee('waste_customer_id', false);
        $resJournal->assertSee('Ibu Rahmawati', false);
    }
}
