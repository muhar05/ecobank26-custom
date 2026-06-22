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
        // No normalization needed here because login can be either phone or username.
        // We will handle normalization in the authenticate method.
    }

    /**
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $login = $this->input('login');
        
        $phone = preg_replace('/[^0-9]/', '', $login);
        if (str_starts_with($phone, '620')) {
            $phone = '0' . substr($phone, 3);
        } elseif (str_starts_with($phone, '62')) {
            $phone = '0' . substr($phone, 2);
        }

        $user = \App\Models\User::where('username', $login)
            ->when($phone, function($query) use ($phone) {
                return $query->orWhere('phone', $phone);
            })
            ->first();

        if (! $user || ! Auth::attempt(['id' => $user->id, 'password' => $this->input('password')], $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'login' => 'Username/No.Telepon atau password salah.',
            ]);
        }

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
