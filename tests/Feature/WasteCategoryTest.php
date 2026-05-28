<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WasteCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WasteCategoryTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
        
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin_bank_sampah');
    }

    public function test_can_create_category_with_manual_code()
    {
        $response = $this->actingAs($this->admin)->post(route('bank-sampah.waste-categories.store'), [
            'name' => 'Besi Tua',
            'unit' => 'kg',
            'category_group' => 'Logam',
            'code' => 'bsi.01',
        ]);

        $response->assertRedirect(route('bank-sampah.waste-categories.index'));
        
        $this->assertDatabaseHas('waste_categories', [
            'name' => 'Besi Tua',
            'category_group' => 'Logam',
            'code' => 'BSI.01', // Automatically uppercased
        ]);
    }

    public function test_can_create_category_with_auto_generated_code()
    {
        // First plastic
        $this->actingAs($this->admin)->post(route('bank-sampah.waste-categories.store'), [
            'name' => 'Botol Plastik',
            'unit' => 'kg',
            'category_group' => 'Plastik',
            'code' => '',
        ]);

        $this->assertDatabaseHas('waste_categories', [
            'name' => 'Botol Plastik',
            'code' => 'PLS.01',
        ]);

        // Second plastic
        $this->actingAs($this->admin)->post(route('bank-sampah.waste-categories.store'), [
            'name' => 'Gelas Plastik',
            'unit' => 'kg',
            'category_group' => 'Plastik',
            'code' => '',
        ]);

        $this->assertDatabaseHas('waste_categories', [
            'name' => 'Gelas Plastik',
            'code' => 'PLS.02',
        ]);
    }

    public function test_duplicate_code_fails_validation()
    {
        WasteCategory::create([
            'name' => 'Existing',
            'unit' => 'kg',
            'category_group' => 'Kertas',
            'code' => 'KRT.99',
        ]);

        $response = $this->actingAs($this->admin)->post(route('bank-sampah.waste-categories.store'), [
            'name' => 'New Paper',
            'unit' => 'kg',
            'category_group' => 'Kertas',
            'code' => 'KRT.99',
        ]);

        $response->assertSessionHasErrors('code');
    }

    public function test_legacy_data_can_be_filtered_and_displayed()
    {
        WasteCategory::create([
            'name' => 'Legacy category',
            'unit' => 'kg',
            'category_group' => null,
            'code' => null,
        ]);

        $response = $this->actingAs($this->admin)->get(route('bank-sampah.waste-categories.index', ['category_group' => 'uncategorized']));
        $response->assertStatus(200);
        $response->assertSee('Legacy category');
        $response->assertSee('Belum Dikategorikan');
    }
}
