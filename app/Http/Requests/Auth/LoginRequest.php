<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Normalize login input before validation.
     */
    protected function prepareForValidation(): void
    {
        if (! $this->has('login') && $this->has('phone')) {
            $this->merge([
                'login' => $this->input('phone'),
            ]);
        }
    }

    /**
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $login = trim((string) $this->input('login'));
        
        // 1. Normalisasi nomor telepon menggunakan logic terpusat jika input berupa digit/nomor telepon
        $phone = \App\Models\User::normalizePhone($login);

        // 2. Cari user multi-kredensial: phone, username, email, atau name
        $user = \App\Models\User::where(function ($query) use ($login, $phone) {
            if (! empty($phone)) {
                $query->where('phone', $phone);
            }
            $query->orWhere('username', $login)
                  ->orWhere('email', $login)
                  ->orWhere('name', $login);
        })->first();

        // 3. Verifikasi user dan kecocokan password teks mentahnya
        if (! $user || ! \Illuminate\Support\Facades\Hash::check($this->input('password'), $user->password)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'login' => 'No. Telepon/Nama atau password salah.',
            ]);
        }

        // 4. Jika password cocok, lakukan login langsung menggunakan objek User
        Auth::login($user, $this->boolean('remember'));

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'login' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate($this->string('login') . '|' . $this->ip());
    }
}
