<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Batasi upaya per IP untuk mencegah enumerasi kombinasi customer_code + phone.
        RateLimiter::for('cek-saldo', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip());
        });

        // Batasi per kombinasi (IP + hash credential) agar satu nasabah tidak dipukul berulang.
        // Credential di-hash, tidak disimpan mentah sebagai rate-limit key.
        RateLimiter::for('cek-saldo-credential', function (Request $request) {
            $code  = strtolower(trim((string) $request->input('customer_code')));
            $phone = strtolower(trim((string) $request->input('phone')));
            $key   = $request->ip() . '|' . hash('sha256', $code . '|' . $phone);

            return Limit::perMinute(5)->by($key);
        });
    }
}
