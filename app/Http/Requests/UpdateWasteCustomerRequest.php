<?php

namespace App\Http\Requests;

use App\Models\WasteCustomer;
use Illuminate\Foundation\Http\FormRequest;

class UpdateWasteCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage_waste_customers');
    }

    public function rules(): array
    {
        $customerId = $this->route('customer') ? $this->route('customer')->id : null;

        return [
            'mode' => 'required|in:existing,manual',
            'member_id' => [
                'nullable',
                'required_if:mode,existing',
                'exists:members,id',
                function ($attribute, $value, $fail) use ($customerId) {
                    if ($this->input('mode') === 'existing' && $value) {
                        $exists = WasteCustomer::where('member_id', $value)
                            ->where('status', 'active')
                            ->where('id', '!=', $customerId)
                            ->exists();

                        if ($exists) {
                            $fail('Warga ini sudah terdaftar sebagai nasabah aktif Bank Sampah.');
                        }
                    }
                }
            ],
            'name' => 'required_if:mode,manual|nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ];
    }

    public function messages(): array
    {
        return [
            'member_id.required_if' => 'Warga harus dipilih jika menggunakan mode Hubungkan dari Warga.',
            'name.required_if' => 'Nama harus diisi jika menggunakan mode Buat Nasabah Manual.',
        ];
    }
}
