<?php

namespace Tests\Feature;

use App\Models\Collector;
use App\Models\Deposit;
use App\Models\User;
use App\Models\WasteCategory;
use App\Models\WasteCustomer;
use App\Models\WastePrice;
use App\Services\DepositImportService;
use App\Services\DeviceTrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ImportTemplateTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $regularUser;
    protected Collector $collector;

    protected function setUp(): void
    {
        parent::setUp();

        $permDeposit = Permission::firstOrCreate(['name' => 'manage_deposits', 'guard_name' => 'web']);
        $permPrice = Permission::firstOrCreate(['name' => 'manage_waste_prices', 'guard_name' => 'web']);
        $role = Role::firstOrCreate(['name' => 'admin_bank_sampah', 'guard_name' => 'web']);
        $role->givePermissionTo([$permDeposit, $permPrice]);

        $this->adminUser = User::factory()->create([
            'phone' => '081234567890',
        ]);
        $this->adminUser->assignRole($role);

        $this->regularUser = User::factory()->create([
            'phone' => '081234567891',
        ]);

        $this->collector = Collector::create([
            'name'  => 'Lapak Berkah',
            'phone' => '081234000',
        ]);

        // Create standard categories so templates match if tested
        WasteCategory::create([
            'code'         => 'KRT.01',
            'name'         => 'Kardus Bersih',
            'unit'         => 'kg',
            'member_price' => 1200,
        ]);

        WasteCategory::create([
            'code'         => 'PLS.01',
            'name'         => 'Botol Putih Bersih',
            'unit'         => 'kg',
            'member_price' => 2800,
        ]);
    }

    /**
     * Test 1: Deposit template download endpoint returns valid xlsx attachment.
     */
    public function test_deposit_template_endpoint_returns_valid_xlsx(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('bank-sampah.deposits.import.template'));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('template-import-setoran.xlsx', $response->headers->get('Content-Disposition'));
    }

    /**
     * Test 2: Aggregator template download endpoint returns valid xlsx attachment.
     */
    public function test_aggregator_template_endpoint_returns_valid_xlsx(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('bank-sampah.waste-prices.import.template'));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('template-harga-aggregator.xlsx', $response->headers->get('Content-Disposition'));
    }

    /**
     * Test 3: Deposit template workbook has expected sheets and header columns.
     */
    public function test_deposit_template_workbook_structure(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('bank-sampah.deposits.import.template'));

        $content = $response->streamedContent();
        $tempPath = tempnam(sys_get_temp_dir(), 'test_tpl_dep_') . '.xlsx';
        file_put_contents($tempPath, $content);

        $reader = IOFactory::createReaderForFile($tempPath);
        $sheetNames = $reader->listWorksheetNames($tempPath);

        // Must have 'Data Setoran' and 'Petunjuk'
        $this->assertContains('Data Setoran', $sheetNames);
        $this->assertContains('Petunjuk', $sheetNames);

        // Load 'Data Setoran'
        $spreadsheet = $reader->load($tempPath);
        $sheet = $spreadsheet->getSheetByName('Data Setoran');
        $rows = $sheet->toArray(null, true, false, false);

        // Verify header columns
        $this->assertEquals(['Nasabah', 'Material', 'Berat', 'Harga', 'Total'], $rows[0]);

        // Verify sample rows exist with numeric weights and prices
        $this->assertGreaterThanOrEqual(2, count($rows));
        $this->assertEquals('Budi Santoso', $rows[1][0]);
        $this->assertEquals(2.5, (float) $rows[1][2]);
        $this->assertEquals(1200, (float) $rows[1][3]);
        $this->assertEquals(3000, (float) $rows[1][4]);

        @unlink($tempPath);
    }

    /**
     * Test 4: Aggregator template workbook has expected sheets and header columns.
     */
    public function test_aggregator_template_workbook_structure(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('bank-sampah.waste-prices.import.template'));

        $content = $response->streamedContent();
        $tempPath = tempnam(sys_get_temp_dir(), 'test_tpl_agg_') . '.xlsx';
        file_put_contents($tempPath, $content);

        $reader = IOFactory::createReaderForFile($tempPath);
        $sheetNames = $reader->listWorksheetNames($tempPath);

        // Must have 'Harga' and 'Petunjuk'
        $this->assertContains('Harga', $sheetNames);
        $this->assertContains('Petunjuk', $sheetNames);

        // Load 'Harga'
        $spreadsheet = $reader->load($tempPath);
        $sheet = $spreadsheet->getSheetByName('Harga');
        $rows = $sheet->toArray(null, true, false, false);

        // Verify header columns
        $this->assertEquals(['Material', 'Harga'], $rows[0]);

        // Verify sample rows exist with numeric prices
        $this->assertGreaterThanOrEqual(2, count($rows));
        $this->assertEquals('Kardus Bersih', $rows[1][0]);
        $this->assertEquals(2200, (float) $rows[1][1]);

        @unlink($tempPath);
    }

    /**
     * Test 5: Deposit template is 100% readable by DepositImportService parser.
     */
    public function test_deposit_template_readable_by_importer(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('bank-sampah.deposits.import.template'));

        $content = $response->streamedContent();
        $tempPath = tempnam(sys_get_temp_dir(), 'test_tpl_dep_import_') . '.xlsx';
        file_put_contents($tempPath, $content);

        $service = app(DepositImportService::class);
        $rawRows = $service->parseSheet($tempPath, 'Data Setoran');

        $this->assertNotEmpty($rawRows);
        $this->assertCount(3, $rawRows);

        // Verify extracted row 1
        $this->assertEquals('Budi Santoso', $rawRows[0]['raw_customer']);
        $this->assertEquals('Kardus Bersih', $rawRows[0]['raw_material']);
        $this->assertEquals(2.5, (float) $rawRows[0]['weight']);
        $this->assertEquals(1200, (float) $rawRows[0]['excel_price']);
        $this->assertEquals(3000, (float) $rawRows[0]['excel_total']);

        @unlink($tempPath);
    }

    /**
     * Test 6: Aggregator template is 100% readable by WastePriceImportController.
     */
    public function test_aggregator_template_readable_by_importer(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('bank-sampah.waste-prices.import.template'));

        $content = $response->streamedContent();
        $tempPath = tempnam(sys_get_temp_dir(), 'test_tpl_agg_import_') . '.xlsx';
        file_put_contents($tempPath, $content);

        $file = new UploadedFile($tempPath, 'template-harga-aggregator.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $importResponse = $this->actingAs($this->adminUser)
            ->post(route('bank-sampah.waste-prices.import.store'), [
                'collector_id' => $this->collector->id,
                'file'         => $file,
            ]);

        $importResponse->assertRedirect();
        $importResponse->assertSessionHas('import_result');

        // Verify prices from template are imported!
        $this->assertDatabaseHas('waste_prices', [
            'collector_id'    => $this->collector->id,
            'collector_price' => 2200,
        ]);

        $this->assertDatabaseHas('waste_prices', [
            'collector_id'    => $this->collector->id,
            'collector_price' => 4000,
        ]);
    }

    /**
     * Test 7: Unauthorized users are blocked from downloading templates.
     */
    public function test_unauthorized_users_cannot_download_templates(): void
    {
        $res1 = $this->actingAs($this->regularUser)
            ->get(route('bank-sampah.deposits.import.template'));
        $res1->assertStatus(403);

        $res2 = $this->actingAs($this->regularUser)
            ->get(route('bank-sampah.waste-prices.import.template'));
        $res2->assertStatus(403);
    }

    /**
     * Test 8: Downloading template does not pollute database.
     */
    public function test_downloading_template_does_not_alter_database(): void
    {
        $depCountBefore = Deposit::count();
        $custCountBefore = WasteCustomer::count();
        $priceCountBefore = WastePrice::count();

        $this->actingAs($this->adminUser)->get(route('bank-sampah.deposits.import.template'));
        $this->actingAs($this->adminUser)->get(route('bank-sampah.waste-prices.import.template'));

        $this->assertEquals($depCountBefore, Deposit::count());
        $this->assertEquals($custCountBefore, WasteCustomer::count());
        $this->assertEquals($priceCountBefore, WastePrice::count());
    }
}
