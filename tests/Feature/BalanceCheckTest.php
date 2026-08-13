<?php

namespace Tests\Feature;

use App\Models\SavingsLedger;
use App\Models\WasteCustomer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BalanceCheckTest extends TestCase
{
    use RefreshDatabase;

    private function makeCustomer(string $code = 'NSB-000001', string $phone = '081234567890'): WasteCustomer
    {
        return WasteCustomer::create([
            'customer_code' => $code,
            'name' => 'Nasabah Test',
            'phone' => $phone,
            'status' => 'active',
        ]);
    }

    public function test_cek_saldo_form_is_publicly_accessible(): void
    {
        $this->get(route('cek-saldo.index'))
            ->assertStatus(200)
            ->assertSee('Cek Saldo');
    }

    public function test_valid_code_and_phone_shows_balance(): void
    {
        $customer = $this->makeCustomer();

        $customer->savingsLedgers()->create(['type' => 'credit', 'amount' => 15000, 'description' => 'Setoran A']);
        $customer->savingsLedgers()->create(['type' => 'debit', 'amount' => 4000, 'description' => 'Tarik A']);

        $this->post(route('cek-saldo.check'), [
            'customer_code' => 'NSB-000001',
            'phone' => '081234567890',
        ])
            ->assertStatus(200)
            ->assertSee('Nasabah Test')
            ->assertSee('NSB-000001')
            ->assertSee('Rp 11.000')
            ->assertSee('Total Setoran')
            ->assertSee('Total Penarikan')
            ->assertSee('Setoran A')
            ->assertSee('Tarik A');
    }

    public function test_mismatched_combo_shows_generic_not_found(): void
    {
        $this->makeCustomer('NSB-000001', '081234567890');

        // Kode benar, HP salah
        $this->post(route('cek-saldo.check'), [
            'customer_code' => 'NSB-000001',
            'phone' => '089999999999',
        ])->assertSessionHasErrors(['not_found' => 'Data nasabah tidak ditemukan.']);
    }

    public function test_wrong_code_shows_generic_not_found(): void
    {
        $this->makeCustomer('NSB-000001', '081234567890');

        $this->post(route('cek-saldo.check'), [
            'customer_code' => 'NSB-999999',
            'phone' => '081234567890',
        ])->assertSessionHasErrors('not_found');
    }

    public function test_validation_requires_code_and_phone(): void
    {
        $this->post(route('cek-saldo.check'), [])
            ->assertSessionHasErrors(['customer_code', 'phone']);
    }

    public function test_customer_code_generation_uses_sequential_format(): void
    {
        $this->makeCustomer('NSB-000001');

        $this->assertSame('NSB-000002', WasteCustomer::generateNextCustomerCode());
    }

    public function test_rate_limit_reached_returns_429(): void
    {
        // Bersihkan cache rate limiter agar deterministik (cache store: array pada test).
        \Illuminate\Support\Facades\Cache::flush();

        $this->makeCustomer();
        $payload = ['customer_code' => 'NSB-000001', 'phone' => '081234567890'];

        // Ambang credential = 5/menit; 5 request pertama sukses (200).
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('cek-saldo.check'), $payload)->assertStatus(200);
        }

        // Request ke-6 ditolak rate limiter (429).
        $this->post(route('cek-saldo.check'), $payload)->assertStatus(429);
    }
}
