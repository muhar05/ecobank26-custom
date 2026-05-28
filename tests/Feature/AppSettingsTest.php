<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\AppSetting;
use App\Models\Bill;
use App\Models\FundCategory;
use App\Models\Kk;
use App\Models\Rt;
use App\Models\User;
use App\Services\BillService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AppSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminRw;
    protected User $adminRt;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed Spatie roles
        Role::firstOrCreate(['name' => 'admin_rw']);
        Role::firstOrCreate(['name' => 'admin_rt']);

        $this->adminRw = User::factory()->create();
        $this->adminRw->assignRole('admin_rw');

        $this->adminRt = User::factory()->create();
        $this->adminRt->assignRole('admin_rt');
    }

    /**
     * Test that admin_rw can successfully open settings page.
     */
    public function test_admin_rw_can_access_settings()
    {
        $response = $this->actingAs($this->adminRw)
            ->get(route('admin.settings.index'));

        $response->assertStatus(200);
        $response->assertSee('Pengaturan Sistem');
        $response->assertSee('Informasi RW');
    }

    /**
     * Test that other roles like admin_rt get 403 Forbidden.
     */
    public function test_admin_rt_cannot_access_settings()
    {
        $response = $this->actingAs($this->adminRt)
            ->get(route('admin.settings.index'));

        $response->assertStatus(403);
    }

    /**
     * Test that updating settings strips whitespace, uppercases prefixes, clears cache, and records detailed audit log.
     */
    public function test_admin_rw_can_update_settings()
    {
        // 1. Pre-seed default settings
        AppSetting::setValue('billing.default_due_days', 10, 'integer', 'billing');
        AppSetting::setValue('billing.bill_prefix', 'BILL', 'string', 'document');
        AppSetting::setValue('billing.receipt_prefix', 'RCPT', 'string', 'document');

        $response = $this->actingAs($this->adminRw)
            ->post(route('admin.settings.update'), [
                'rw_name' => '  RW 026 Baru  ',
                'rw_contact_phone' => '08122334455',
                'bank_sampah_name' => 'EcoBank Baru',
                'bank_sampah_contact_phone' => '0899887766',
                'default_due_days' => 15,
                'bill_prefix' => '  tag-iuran  ', // Should trim and uppercase to TAG-IURAN
                'receipt_prefix' => '  rcpt-i  ', // Should trim and uppercase to RCPT-I
            ]);

        $response->assertRedirect(route('admin.settings.index'));

        // Verify database updates with sanitization and uppercase conversions
        $this->assertEquals('RW 026 Baru', AppSetting::getValue('app.rw_name'));
        $this->assertEquals('TAG-IURAN', AppSetting::getValue('billing.bill_prefix'));
        $this->assertEquals('RCPT-I', AppSetting::getValue('billing.receipt_prefix'));
        $this->assertEquals(15, AppSetting::getInt('billing.default_due_days'));

        // Verify cache is cleared & holds correct value
        $this->assertEquals(15, Cache::get('app_setting:billing.default_due_days')->value);

        // Verify Audit Log includes metadata JSON with old and new values
        $log = ActivityLog::where('event_type', 'settings.update')->latest()->first();
        $this->assertNotNull($log);
        
        $payload = $log->payload;
        $this->assertArrayHasKey('metadata', $payload);
        $this->assertEquals(10, $payload['metadata']['old_values']['billing.default_due_days']);
        $this->assertEquals(15, $payload['metadata']['new_values']['billing.default_due_days']);
    }

    /**
     * Test that bill due date automatically follows dynamic settings.
     */
    public function test_due_date_follows_setting()
    {
        // Setup a mock active RT and KK
        $rt = Rt::create(['rt_number' => '001']);
        $kk = Kk::create([
            'rt_id' => $rt->id,
            'kk_number' => '1234567890123456',
            'family_head' => 'Kepala Keluarga Moko',
            'status' => 'active',
        ]);

        $category = FundCategory::create([
            'name' => 'Iuran Wajib Bulanan',
            'monthly_amount' => 50000,
            'is_mandatory' => true,
            'is_active' => true,
        ]);

        // Pre-seed setting as 25 days
        AppSetting::setValue('billing.default_due_days', 25, 'integer', 'billing');
        AppSetting::setValue('billing.bill_prefix', 'TAG-RW', 'string', 'document');

        $billService = app(BillService::class);
        $generated = $billService->generateMonthlyBills(5, 2026);

        $this->assertEquals(1, $generated);

        $bill = Bill::first();
        $this->assertNotNull($bill);

        // Assert code uses custom prefix
        $this->assertStringStartsWith('TAG-RW-', $bill->bill_code);

        // Assert due date is exactly 25 days from today
        $expectedDate = now()->addDays(25)->toDateString();
        $this->assertEquals($expectedDate, $bill->due_date->toDateString());
    }
}
