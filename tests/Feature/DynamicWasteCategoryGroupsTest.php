<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WasteCategory;
use App\Models\WasteCategoryGroup;
use App\Models\Collector;
use App\Models\WastePrice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DynamicWasteCategoryGroupsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin_bank_sampah');

        // Run the dynamic waste category group seeder
        $this->artisan('db:seed', ['--class' => 'DefaultWasteCategoryGroupSeeder']);
    }

    public function test_waste_category_groups_are_seeded()
    {
        $this->assertDatabaseHas('waste_category_groups', ['code' => 'PLS', 'name' => 'Plastik']);
        $this->assertDatabaseHas('waste_category_groups', ['code' => 'KRT', 'name' => 'Kertas']);
        $this->assertDatabaseHas('waste_category_groups', ['code' => 'LOG', 'name' => 'Logam']);
    }

    public function test_can_toggle_waste_category_group_status()
    {
        $group = WasteCategoryGroup::where('code', 'PLS')->first();
        $this->assertTrue($group->is_active);

        // Toggle inactive
        $response = $this->actingAs($this->admin)->patch(route('bank-sampah.waste-category-groups.toggle', $group));
        $response->assertRedirect();
        
        $group->refresh();
        $this->assertFalse($group->is_active);

        // Toggle active
        $this->actingAs($this->admin)->patch(route('bank-sampah.waste-category-groups.toggle', $group));
        $group->refresh();
        $this->assertTrue($group->is_active);
    }

    public function test_artisan_sync_command_with_normalization()
    {
        // Create categories with different casing legacy groups
        $cat1 = WasteCategory::create(['name' => 'Legacy Plastik', 'unit' => 'kg', 'category_group' => 'plastik']);
        $cat2 = WasteCategory::create(['name' => 'Legacy Kertas', 'unit' => 'kg', 'category_group' => 'KERTAS']);
        $cat3 = WasteCategory::create(['name' => 'Legacy Logam', 'unit' => 'kg', 'category_group' => '  LoGaM ']);
        $cat4 = WasteCategory::create(['name' => 'Legacy Unknown', 'unit' => 'kg', 'category_group' => 'Tidak Dikenal']);

        $this->artisan('bank-sampah:sync-waste-category-groups')
            ->assertSuccessful();

        $cat1->refresh();
        $cat2->refresh();
        $cat3->refresh();
        $cat4->refresh();

        $this->assertEquals('PLS', $cat1->wasteCategoryGroup->code);
        $this->assertEquals('KRT', $cat2->wasteCategoryGroup->code);
        $this->assertEquals('LOG', $cat3->wasteCategoryGroup->code);
        $this->assertNull($cat4->wasteCategoryGroup); // Unknown sets to null (Belum Dikategorikan)
    }

    public function test_category_code_auto_generation_from_dynamic_group()
    {
        $group = WasteCategoryGroup::where('code', 'KCA')->first(); // Kaca

        // Create first glass category
        $response = $this->actingAs($this->admin)->post(route('bank-sampah.waste-categories.store'), [
            'name' => 'Botol Kaca Bening',
            'unit' => 'pcs',
            'waste_category_group_id' => $group->id,
            'code' => '',
        ]);

        $response->assertRedirect(route('bank-sampah.waste-categories.index'));
        $this->assertDatabaseHas('waste_categories', [
            'name' => 'Botol Kaca Bening',
            'code' => 'KCA.01',
            'waste_category_group_id' => $group->id,
        ]);

        // Create second glass category
        $this->actingAs($this->admin)->post(route('bank-sampah.waste-categories.store'), [
            'name' => 'Cermin Pecah',
            'unit' => 'kg',
            'waste_category_group_id' => $group->id,
            'code' => '',
        ]);

        $this->assertDatabaseHas('waste_categories', [
            'name' => 'Cermin Pecah',
            'code' => 'KCA.02',
        ]);
    }

    public function test_failed_rows_are_stored_on_failed_excel_import()
    {
        // 1. Create a dummy category
        $group = WasteCategoryGroup::where('code', 'PLS')->first();
        WasteCategory::create([
            'name' => 'Botol Plastik Bersih',
            'unit' => 'kg',
            'waste_category_group_id' => $group->id,
            'code' => 'PLS.01'
        ]);

        // 2. Prepare mock array for Maatwebsite Excel reader
        $importData = [
            // Header
            ['Kode Grup', 'Nama Grup', 'Kode Kategori', 'Nama Kategori Sampah', 'Satuan', 'Harga Beli dari Nasabah', 'Harga Jual ke Agregator', 'Tanggal Berlaku'],
            // Valid row
            ['PLS', 'Plastik', 'PLS.01', 'Botol Plastik Bersih', 'kg', '3000', '3500', '2026-05-28'],
            // Invalid price row
            ['PLS', 'Plastik', 'PLS.01', 'Botol Plastik Bersih', 'kg', '3000', '2000', '2026-05-28'], // Jual < Beli
            // Unregistered category row
            ['KRT', 'Kertas', 'KRT.99', 'Kardus Khayalan', 'kg', '1000', '1200', '2026-05-28'],
        ];

        // We can mock Excel facade to return this array
        \Excel::fake();

        \Excel::shouldReceive('toArray')
            ->once()
            ->andReturn([$importData]);

        // Create a dummy uploaded file
        $file = \Illuminate\Http\UploadedFile::fake()->create('test-import.xlsx');

        $response = $this->actingAs($this->admin)->post(route('bank-sampah.waste-prices.import.store'), [
            'file' => $file
        ]);

        $response->assertRedirect();
        
        // Assert session has correct success and failed metrics
        $result = session('import_result');
        $this->assertEquals(1, $result['created'] + $result['updated']); // PLS.01 succeeded
        $this->assertEquals(2, count($result['errors'])); // 2 failed rows

        // Assert failed rows are recorded in session
        $this->assertTrue(session()->has('waste_price_import_failed_rows'));
        $failedRows = session('waste_price_import_failed_rows');
        
        $this->assertEquals('Harga Jual ke Agregator harus lebih besar atau sama dengan Harga Beli dari Nasabah.', $failedRows[0][8]);
        $this->assertEquals('Kategori sampah belum terdaftar', $failedRows[1][8]);
    }
}
