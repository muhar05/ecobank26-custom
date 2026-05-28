<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WasteBankCashLedger;
use App\Models\WasteBankExpense;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WasteBankExpenseTest extends TestCase
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

    public function test_can_view_expenses_index()
    {
        $response = $this->actingAs($this->admin)->get(route('bank-sampah.expenses.index'));
        $response->assertStatus(200);
    }

    public function test_can_create_expense_with_sufficient_balance()
    {
        Storage::fake('public');

        // First, add some balance to the bank sampah cash ledger
        WasteBankCashLedger::create([
            'type' => 'in',
            'amount' => 50000,
            'balance' => 50000,
            'date' => now(),
            'description' => 'Initial balance',
        ]);

        $file = UploadedFile::fake()->image('nota.jpg');

        $response = $this->actingAs($this->admin)->post(route('bank-sampah.expenses.store'), [
            'expense_code' => 'EXP-202605-0001',
            'amount' => 15000,
            'description' => 'Beli sapu',
            'expense_date' => now()->format('Y-m-d'),
            'proof' => $file,
        ]);

        $response->assertRedirect(route('bank-sampah.expenses.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('waste_bank_expenses', [
            'expense_code' => 'EXP-202605-0001',
            'amount' => 15000,
            'description' => 'Beli sapu',
        ]);

        $this->assertDatabaseHas('waste_bank_cash_ledgers', [
            'type' => 'out',
            'amount' => 15000,
            'balance' => 35000,
        ]);

        // Check if file was uploaded
        $expense = WasteBankExpense::first();
        Storage::disk('public')->assertExists($expense->proof_path);
    }

    public function test_cannot_create_expense_when_balance_insufficient()
    {
        // Balance is 0 by default
        $response = $this->actingAs($this->admin)->post(route('bank-sampah.expenses.store'), [
            'expense_code' => 'EXP-202605-0002',
            'amount' => 10000,
            'description' => 'Beli plastik',
            'expense_date' => now()->format('Y-m-d'),
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('waste_bank_expenses', 0);
    }

    public function test_cannot_view_expenses_if_not_admin()
    {
        $warga = User::factory()->create();
        $warga->assignRole('warga');

        $response = $this->actingAs($warga)->get(route('bank-sampah.expenses.index'));
        $response->assertStatus(403);
    }
}
