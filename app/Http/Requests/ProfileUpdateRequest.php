<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('phone')) {
            $raw = (string) $this->input('phone');

            // Validasi raw input: hanya izinkan digit, spasi, '+', '-', '(', ')'
            // Tolak alfabet, simbol, dan email sebelum normalisasi agar tidak diubah diam-diam
            if (preg_match('/[^\d\s\+\-\(\)]/', $raw)) {
                return;
            }

            // Setelah raw input lolos validasi dasar, jalankan User::normalizePhone()
            $this->merge([
                'phone' => User::normalizePhone($raw),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'nullable',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'phone' => [
                'required',
                'string',
                'regex:/^08[0-9]{7,14}$/',
                Rule::unique(User::class, 'phone')->ignore($this->user()->id),
            ],
            'current_password' => ['required', 'current_password'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.required' => 'Nomor telepon wajib diisi.',
            'phone.regex' => 'Format nomor telepon tidak valid. Gunakan format seperti 08123456789.',
            'phone.unique' => 'Nomor telepon sudah digunakan oleh akun lain.',
            'current_password.required' => 'Password saat ini wajib diisi.',
            'current_password.current_password' => 'Password saat ini tidak sesuai.',
        ];
    }
}
