<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRtRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rt_number' => 'required|string|max:10|unique:rts,rt_number',
            'description' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'rt_number.required' => 'Nomor RT wajib diisi.',
            'rt_number.unique' => 'Nomor RT ini sudah terdaftar.',
            'rt_number.max' => 'Nomor RT tidak boleh lebih dari 10 karakter.',
        ];
    }
}
