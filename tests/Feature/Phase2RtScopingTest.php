<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\CommunityContribution;
use App\Models\CommunityExpense;
use App\Models\FundCategory;
use App\Models\Kk;
use App\Models\Rt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class Phase2RtScopingTest extends TestCase
{
    use RefreshDatabase;

    private Rt $rt1;
    private Rt $rt2;
    private User $adminRw;
    private User $adminRt1;
    private User $adminRt2;
    private FundCategory $globalCategory;
    private FundCategory $rt1Category;
    private FundCategory $rt2Category;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles and permissions
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        // Create RTs
        $this->rt1 = Rt::create(['rt_number' => '001', 'description' => 'RT 001']);
        $this->rt2 = Rt::create(['rt_number' => '002', 'description' => 'RT 002']);

        // Create users
        $this->adminRw = User::factory()->create(['rt_id' => null]);
        $this->adminRw->assignRole('admin_rw');

        $this->adminRt1 = User::factory()->create(['rt_id' => $this->rt1->id]);
        $this->adminRt1->assignRole('admin_rt');

        $this->adminRt2 = User::factory()->create(['rt_id' => $this->rt2->id]);
        $this->adminRt2->assignRole('admin_rt');

        // Create fund categories
        $this->globalCategory = FundCategory::create([
            'name' => 'Dana Global RW',
            'rt_id' => null,
            'is_active' => true,
            'is_mandatory' => false,
        ]);

        $this->rt1Category = FundCategory::create([
            'name' => 'Dana RT 001',
            'rt_id' => $this->rt1->id,
            'is_active' => true,
            'is_mandatory' => false,
        ]);

        $this->rt2Category = FundCategory::create([
            'name' => 'Dana RT 002',
            'rt_id' => $this->rt2->id,
            'is_active' => true,
            'is_mandatory' => false,
        ]);
    }

    // =========================================
    // 1. Fund Category Visibility Tests
    // =========================================

    #[Test]
    public function admin_rw_can_see_all_fund_categories()
    {
        $response = $this->actingAs($this->adminRw)->get('/community-cash/categories');
        $response->assertStatus(200);
        $response->assertSee('Dana Global RW');
        $response->assertSee('Dana RT 001');
        $response->assertSee('Dana RT 002');
    }

    #[Test]
    public function admin_rt_can_see_global_and_own_rt_categories_only()
    {
        $response = $this->actingAs($this->adminRt1)->get('/community-cash/categories');
        $response->assertStatus(200);
        $response->assertSee('Dana Global RW');   // global → visible
        $response->assertSee('Dana RT 001');       // own RT → visible
        $response->assertDontSee('Dana RT 002');   // other RT → hidden
    }

    #[Test]
    public function admin_rt_cannot_see_other_rt_categories()
    {
        $response = $this->actingAs($this->adminRt2)->get('/community-cash/categories');
        $response->assertSee('Dana RT 002');
        $response->assertDontSee('Dana RT 001');   // RT 001 → tidak boleh lihat
    }

    // =========================================
    // 2. Fund Category Creation Tests
    // =========================================

    #[Test]
    public function admin_rt_store_category_auto_fills_rt_id()
    {
        $response = $this->actingAs($this->adminRt1)->post('/community-cash/categories', [
            'name' => 'Dana Baru RT 001',
            'is_active' => '1',
            'is_mandatory' => '0',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('fund_categories', [
            'name' => 'Dana Baru RT 001',
            'rt_id' => $this->rt1->id,
        ]);
    }

    #[Test]
    public function admin_rw_store_category_has_null_rt_id()
    {
        $response = $this->actingAs($this->adminRw)->post('/community-cash/categories', [
            'name' => 'Dana Global Baru',
            'is_active' => '1',
            'is_mandatory' => '0',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('fund_categories', [
            'name' => 'Dana Global Baru',
            'rt_id' => null,
        ]);
    }

    // =========================================
    // 3. Fund Category Edit Authorization Tests
    // =========================================

    #[Test]
    public function admin_rt_cannot_edit_other_rt_category()
    {
        // Admin RT 1 mencoba edit kategori RT 2
        $response = $this->actingAs($this->adminRt1)->get("/community-cash/categories/{$this->rt2Category->id}/edit");
        $response->assertStatus(403);
    }

    #[Test]
    public function admin_rt_cannot_edit_global_rw_category()
    {
        // Admin RT tidak boleh edit kategori global (rt_id=null)
        $response = $this->actingAs($this->adminRt1)->get("/community-cash/categories/{$this->globalCategory->id}/edit");
        $response->assertStatus(403);
    }

    #[Test]
    public function admin_rw_can_edit_any_category()
    {
        $response = $this->actingAs($this->adminRw)->get("/community-cash/categories/{$this->rt1Category->id}/edit");
        $response->assertStatus(200);

        $response2 = $this->actingAs($this->adminRw)->get("/community-cash/categories/{$this->globalCategory->id}/edit");
        $response2->assertStatus(200);
    }

    // =========================================
    // 4. Community Contributions — RT Scoping
    // =========================================

    #[Test]
    public function admin_rt_contributions_scoped_to_own_rt()
    {
        // Contribution RT 1
        CommunityContribution::create([
            'fund_category_id' => $this->rt1Category->id,
            'rt_id' => $this->rt1->id,
            'member_name' => 'Warga RT 1',
            'amount' => 50000,
            'date' => now()->format('Y-m-d'),
            'recorded_by' => $this->adminRt1->id,
        ]);

        // Contribution RT 2
        CommunityContribution::create([
            'fund_category_id' => $this->rt2Category->id,
            'rt_id' => $this->rt2->id,
            'member_name' => 'Warga RT 2',
            'amount' => 60000,
            'date' => now()->format('Y-m-d'),
            'recorded_by' => $this->adminRt2->id,
        ]);

        // Legacy (NULL rt_id)
        CommunityContribution::create([
            'fund_category_id' => $this->globalCategory->id,
            'rt_id' => null,
            'member_name' => 'Warga Legacy',
            'amount' => 30000,
            'date' => now()->format('Y-m-d'),
            'recorded_by' => $this->adminRw->id,
        ]);

        $response = $this->actingAs($this->adminRt1)->get('/community-cash/contributions');
        $response->assertSee('Warga RT 1');
        $response->assertDontSee('Warga RT 2');    // cross RT → blocked
        $response->assertDontSee('Warga Legacy'); // legacy NULL → not visible to admin_rt
    }

    #[Test]
    public function admin_rw_can_see_all_contributions_including_legacy()
    {
        CommunityContribution::create([
            'fund_category_id' => $this->globalCategory->id,
            'rt_id' => null,
            'member_name' => 'Warga Legacy',
            'amount' => 30000,
            'date' => now()->format('Y-m-d'),
            'recorded_by' => $this->adminRw->id,
        ]);

        $response = $this->actingAs($this->adminRw)->get('/community-cash/contributions');
        $response->assertSee('Warga Legacy');
    }

    // =========================================
    // 5. URL Tampering Tests — Bills
    // =========================================

    #[Test]
    public function admin_rt_url_tampering_rt_id_is_overridden()
    {
        $kk1 = Kk::create(['rt_id' => $this->rt1->id, 'family_head' => 'KK RT 1', 'status' => 'active']);
        $kk2 = Kk::create(['rt_id' => $this->rt2->id, 'family_head' => 'KK RT 2', 'status' => 'active']);

        $category = FundCategory::create([
            'name' => 'Iuran Wajib', 'is_mandatory' => true,
            'monthly_amount' => 10000, 'rt_id' => null, 'is_active' => true,
        ]);

        Bill::create([
            'kk_id' => $kk1->id, 'fund_category_id' => $category->id,
            'amount' => 10000, 'month' => 1, 'year' => 2026, 'status' => 'unpaid',
        ]);
        Bill::create([
            'kk_id' => $kk2->id, 'fund_category_id' => $category->id,
            'amount' => 10000, 'month' => 1, 'year' => 2026, 'status' => 'unpaid',
        ]);

        // Admin RT 1 mencoba tamper URL: rt_id=2 (RT 2)
        // Sistem HARUS override dan paksa ke rt_id milik user (RT 1)
        $response = $this->actingAs($this->adminRt1)->get('/iuran/tagihan?rt_id=' . $this->rt2->id);
        $response->assertStatus(200);
        $response->assertSee('KK RT 1');
        $response->assertDontSee('KK RT 2');
    }

    // =========================================
    // 6. Contribution URL Tampering — Category
    // =========================================

    #[Test]
    public function admin_rt_cannot_post_contribution_to_other_rt_category()
    {
        // Admin RT 1 mencoba submit contribution ke kategori RT 2
        $response = $this->actingAs($this->adminRt1)->post('/community-cash/contributions', [
            'fund_category_id' => $this->rt2Category->id, // Tampering!
            'member_name' => 'Hacker',
            'amount' => 50000,
            'date' => now()->format('Y-m-d'),
        ]);

        $response->assertStatus(403);

        $this->assertDatabaseMissing('community_contributions', [
            'member_name' => 'Hacker',
        ]);
    }

    // =========================================
    // 7. KK Scoping Tests
    // =========================================

    #[Test]
    public function admin_rt_only_sees_kks_in_own_rt()
    {
        Kk::create(['rt_id' => $this->rt1->id, 'family_head' => 'Keluarga RT 1', 'status' => 'active']);
        Kk::create(['rt_id' => $this->rt2->id, 'family_head' => 'Keluarga RT 2', 'status' => 'active']);

        $response = $this->actingAs($this->adminRt1)->get('/kks');
        $response->assertSee('Keluarga RT 1');
        $response->assertDontSee('Keluarga RT 2');
    }

    #[Test]
    public function admin_rw_sees_all_kks()
    {
        Kk::create(['rt_id' => $this->rt1->id, 'family_head' => 'Keluarga RT 1', 'status' => 'active']);
        Kk::create(['rt_id' => $this->rt2->id, 'family_head' => 'Keluarga RT 2', 'status' => 'active']);

        $response = $this->actingAs($this->adminRw)->get('/kks');
        $response->assertSee('Keluarga RT 1');
        $response->assertSee('Keluarga RT 2');
    }
}
