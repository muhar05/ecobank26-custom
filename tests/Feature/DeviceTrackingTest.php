<?php

namespace Tests\Feature;

use App\Models\AdminDevice;
use App\Models\User;
use App\Services\DeviceTrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DeviceTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $perm = Permission::firstOrCreate(['name' => 'manage_deposits', 'guard_name' => 'web']);
        $role = Role::firstOrCreate(['name' => 'admin_bank_sampah', 'guard_name' => 'web']);
        $role->givePermissionTo($perm);

        $this->adminUser = User::factory()->create([
            'phone' => '081234567890',
        ]);
        $this->adminUser->assignRole($role);
    }

    /**
     * Test 1: First device: Admin requests protected route without device token.
     * Device record is created, cookie issued, and redirected to name device screen.
     */
    public function test_first_device_detected_and_prompts_for_name(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->withHeader('X-Enforce-Device-Naming', 'true')
            ->get(route('bank-sampah.deposits.index'));

        // Must redirect to name device
        $response->assertRedirect(route('bank-sampah.devices.name'));
        $response->assertCookie(DeviceTrackingService::COOKIE_NAME);

        // An unnamed device record must exist in DB
        $this->assertDatabaseHas('admin_devices', [
            'user_id'     => $this->adminUser->id,
            'device_name' => null,
            'is_revoked'  => false,
        ]);
    }

    /**
     * Test 2: Name device: Admin provides "PC Admin" -> device_name saved.
     */
    public function test_name_device_saves_device_name(): void
    {
        // First access to create unnamed device
        $res = $this->actingAs($this->adminUser)
            ->withHeader('X-Enforce-Device-Naming', 'true')
            ->get(route('bank-sampah.deposits.index'));

        $cookieVal = $res->getCookie(DeviceTrackingService::COOKIE_NAME, false)->getValue();
        $device = AdminDevice::where('device_token', $cookieVal)->first();
        $this->assertNotNull($device);

        // Submit name
        $postRes = $this->actingAs($this->adminUser)
            ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $cookieVal)
            ->post(route('bank-sampah.devices.store-name'), [
                'device_name' => 'PC Admin Gudang',
            ]);

        $postRes->assertRedirect();

        $device->refresh();
        $this->assertEquals('PC Admin Gudang', $device->device_name);
    }

    /**
     * Test 3: Known device: Subsequent requests with same device token reuse device and update last_seen_at.
     */
    public function test_known_device_reuses_record_and_updates_last_seen(): void
    {
        $device = AdminDevice::create([
            'user_id'       => $this->adminUser->id,
            'device_token'  => 'test_token_1234567890_abcdef',
            'device_name'   => 'Laptop Operasional',
            'browser'       => 'Chrome',
            'platform'      => 'Windows',
            'is_revoked'    => false,
            'first_seen_at' => now()->subDay(),
            'last_seen_at'  => now()->subDay(),
        ]);

        $initialLastSeen = $device->last_seen_at;

        // Request with the cookie
        $response = $this->actingAs($this->adminUser)
            ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $device->device_token)
            ->get(route('bank-sampah.deposits.index'));

        $response->assertStatus(200);

        // No duplicate device
        $this->assertEquals(1, AdminDevice::where('user_id', $this->adminUser->id)->count());

        $device->refresh();
        $this->assertTrue($device->last_seen_at->greaterThan($initialLastSeen));
    }

    /**
     * Test 4: New device: Different browser/token creates another device record.
     */
    public function test_new_device_creates_separate_record(): void
    {
        // Device 1
        AdminDevice::create([
            'user_id'       => $this->adminUser->id,
            'device_token'  => 'token_device_one',
            'device_name'   => 'PC Kantor',
            'browser'       => 'Chrome',
            'platform'      => 'Windows',
            'is_revoked'    => false,
            'first_seen_at' => now(),
            'last_seen_at'  => now(),
        ]);

        // Device 2 requests from new browser
        $response = $this->actingAs($this->adminUser)
            ->withHeader('X-Enforce-Device-Naming', 'true')
            ->get(route('bank-sampah.deposits.index'));

        $response->assertRedirect(route('bank-sampah.devices.name'));

        // Total devices must now be 2
        $this->assertEquals(2, AdminDevice::where('user_id', $this->adminUser->id)->count());
    }

    /**
     * Test 5: Revoke device marks it revoked and forces new device on subsequent login.
     */
    public function test_revoke_device_invalidates_it(): void
    {
        $device = AdminDevice::create([
            'user_id'       => $this->adminUser->id,
            'device_token'  => 'token_to_be_revoked',
            'device_name'   => 'Laptop Lama',
            'browser'       => 'Safari',
            'platform'      => 'macOS',
            'is_revoked'    => false,
            'first_seen_at' => now(),
            'last_seen_at'  => now(),
        ]);

        // Revoke device
        $revokeRes = $this->actingAs($this->adminUser)
            ->post(route('bank-sampah.devices.revoke', $device));

        $revokeRes->assertRedirect();
        $device->refresh();
        $this->assertTrue($device->is_revoked);

        // Subsequent request using that revoked token must be treated as new device!
        $newReq = $this->actingAs($this->adminUser)
            ->withHeader('X-Enforce-Device-Naming', 'true')
            ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $device->device_token)
            ->get(route('bank-sampah.deposits.index'));

        $newReq->assertRedirect(route('bank-sampah.devices.name'));

        // A new device record was created
        $this->assertEquals(2, AdminDevice::where('user_id', $this->adminUser->id)->count());
    }
}
