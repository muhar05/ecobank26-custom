<?php

namespace Tests\Feature;

use App\Models\AdminDevice;
use App\Models\Collector;
use App\Models\User;
use App\Services\DeviceTrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CollectorPriceTemplateTest extends TestCase
{
    use RefreshDatabase;

    protected function createAdminClient(): array
    {
        foreach (['view_waste_bank', 'manage_waste_prices'] as $permName) {
            $perm = Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
            Role::firstOrCreate(['name' => 'admin_bank_sampah', 'guard_name' => 'web'])->givePermissionTo($perm);
        }
        $role = Role::where('name', 'admin_bank_sampah')->first();

        $admin = User::factory()->create();
        $admin->assignRole($role);

        $device = AdminDevice::create([
            'user_id' => $admin->id,
            'device_token' => 'template-test-token-' . uniqid(),
            'device_name' => 'Admin PC',
            'browser' => 'Chrome',
            'platform' => 'Darwin',
            'is_revoked' => false,
            'first_seen_at' => now()->subDay(),
            'last_seen_at' => now()->subDay(),
        ]);

        return [$admin, $device];
    }

    public function test_download_template_returns_valid_xlsx_with_official_format(): void
    {
        [$admin, $device] = $this->createAdminClient();
        $collector = Collector::create(['name' => 'Lapak Berkah Jaya']);

        $res = $this->actingAs($admin)
            ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $device->device_token)
            ->get(route('bank-sampah.collectors.import.template', $collector));

        $res->assertStatus(200);
        $res->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString(
            'template_import_harga_agregator.xlsx',
            $res->headers->get('Content-Disposition')
        );

        $tempPath = tempnam(sys_get_temp_dir(), 'tpl_check_') . '.xlsx';
        file_put_contents($tempPath, $res->streamedContent());

        $reader = IOFactory::createReaderForFile($tempPath);
        $this->assertContains('Data Harga', $reader->listWorksheetNames($tempPath));

        $sheet = $reader->load($tempPath)->getSheetByName('Data Harga');
        $this->assertEquals('Jenis Barang', $sheet->getCell('A1')->getValue());
        $this->assertEquals('Harga Lapak', $sheet->getCell('B1')->getValue());

        // Header bold, no dummy data rows
        $this->assertTrue($sheet->getStyle('A1')->getFont()->getBold());
        $this->assertEmpty($sheet->getCell('A2')->getValue());
        $this->assertEmpty($sheet->getCell('B2')->getValue());

        // Column widths and frozen header
        $this->assertEquals(32, $sheet->getColumnDimension('A')->getWidth());
        $this->assertEquals(18, $sheet->getColumnDimension('B')->getWidth());
        $this->assertEquals('A2', $sheet->getFreezePane());

        @unlink($tempPath);
    }
}
