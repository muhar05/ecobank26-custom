<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HumanizedActivityLogsTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminRw;
    protected User $wargaUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles
        Role::firstOrCreate(['name' => 'admin_rw']);
        Role::firstOrCreate(['name' => 'warga']);

        $this->adminRw = User::factory()->create();
        $this->adminRw->assignRole('admin_rw');

        $this->wargaUser = User::factory()->create();
        $this->wargaUser->assignRole('warga');
    }

    public function test_audit_logs_default_opens_human_tab()
    {
        $response = $this->actingAs($this->adminRw)
            ->get(route('admin.audit-logs'));

        $response->assertStatus(200);
        // Asserts view state switches to human by default
        $response->assertSee('Riwayat Aktivitas Sistem');
    }

    public function test_human_event_for_deposit_create_is_correct()
    {
        $log = new ActivityLog([
            'event_type' => 'deposit.create',
            'severity' => 'info',
            'description' => 'original description'
        ]);

        $this->assertEquals('Setoran Sampah Dibuat', $log->human_event);
    }

    public function test_human_severity_for_critical_is_correct()
    {
        $log = new ActivityLog([
            'event_type' => 'bill.payment',
            'severity' => 'critical',
            'description' => 'critical description'
        ]);

        $this->assertEquals('Risiko Tinggi', $log->human_severity);
    }

    public function test_warga_cannot_access_audit_logs_dashboard()
    {
        $response = $this->actingAs($this->wargaUser)
            ->get(route('admin.audit-logs'));

        $response->assertStatus(403);
    }

    public function test_admin_rw_can_access_audit_logs_dashboard()
    {
        $response = $this->actingAs($this->adminRw)
            ->get(route('admin.audit-logs'));

        $response->assertStatus(200);
        $response->assertSee('Log & Audit Trail Sistem', false);
    }
}
