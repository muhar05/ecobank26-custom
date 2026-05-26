<?php

namespace App\Http\Requests;

use App\Models\Kk;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateKkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $kkId = $this->route('kk') ? $this->route('kk')->id : $this->kk;

        return [
            'rt_id' => 'required|exists:rts,id',
            'kk_number' => [
                'nullable',
                'numeric',
                'digits:16',
                Rule::unique('kks', 'kk_number')->ignore($kkId),
            ],
            'family_head' => 'required|string|max:100',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'status' => 'required|in:' . implode(',', [
                Kk::STATUS_ACTIVE,
                Kk::STATUS_CONTRACT,
                Kk::STATUS_MOVED,
                Kk::STATUS_VACANT
            ]),
        ];
    }

    public function messages(): array
    {
        return [
            'rt_id.required' => 'RT wajib dipilih.',
            'rt_id.exists' => 'RT yang dipilih tidak valid.',
            'kk_number.numeric' => 'Nomor KK harus berupa angka.',
            'kk_number.digits' => 'Nomor KK harus tepat 16 digit.',
            'kk_number.unique' => 'Nomor KK ini sudah terdaftar.',
            'family_head.required' => 'Nama Kepala Keluarga wajib diisi.',
            'family_head.max' => 'Nama Kepala Keluarga tidak boleh lebih dari 100 karakter.',
            'status.required' => 'Status hunian wajib dipilih.',
            'status.in' => 'Status hunian yang dipilih tidak valid.',
        ];
    }
}
