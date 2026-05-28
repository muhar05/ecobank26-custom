<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class AppSettingController extends Controller
{
    protected ActivityLogService $activityLog;

    public function __construct(ActivityLogService $activityLog)
    {
        $this->activityLog = $activityLog;
    }

    /**
     * Show the settings management form.
     */
    public function index()
    {
        $settings = [
            'rw_name' => AppSetting::getValue('app.rw_name', 'RW 026'),
            'rw_contact_phone' => AppSetting::getValue('app.rw_contact_phone', '08123456789'),
            
            'bank_sampah_name' => AppSetting::getValue('app.bank_sampah_name', 'EcoBank026'),
            'bank_sampah_contact_phone' => AppSetting::getValue('app.bank_sampah_contact_phone', '08123456789'),
            
            'default_due_days' => AppSetting::getValue('billing.default_due_days', 10),
            
            'bill_prefix' => AppSetting::getValue('billing.bill_prefix', 'BILL'),
            'receipt_prefix' => AppSetting::getValue('billing.receipt_prefix', 'RCPT'),
            
            'last_updated_by' => AppSetting::getValue('app.last_updated_by', 'Sistem'),
            'last_updated_at' => AppSetting::getValue('app.last_updated_at', date('d/m/Y H:i')),
        ];

        return view('settings.index', compact('settings'));
    }

    /**
     * Update application settings.
     */
    public function update(Request $request)
    {
        // Trim spasi dan buat prefix otomatis UPPERCASE sebelum divalidasi
        if ($request->has('bill_prefix')) {
            $request->merge(['bill_prefix' => strtoupper(trim($request->input('bill_prefix')))]);
        }
        if ($request->has('receipt_prefix')) {
            $request->merge(['receipt_prefix' => strtoupper(trim($request->input('receipt_prefix')))]);
        }

        $validated = $request->validate([
            'rw_name' => 'required|string|max:100',
            'rw_contact_phone' => 'required|string|max:20',
            'bank_sampah_name' => 'required|string|max:100',
            'bank_sampah_contact_phone' => 'required|string|max:20',
            'default_due_days' => 'required|integer|min:1|max:90',
            'bill_prefix' => 'required|string|max:10|alpha_dash',
            'receipt_prefix' => 'required|string|max:10|alpha_dash',
        ], [
            'default_due_days.min' => 'Default tenggat pembayaran minimal 1 hari.',
            'default_due_days.max' => 'Default tenggat pembayaran maksimal 90 hari.',
            'bill_prefix.alpha_dash' => 'Prefix kode tagihan hanya boleh berisi huruf, angka, dash (-), dan underscore (_).',
            'receipt_prefix.alpha_dash' => 'Prefix kuitansi hanya boleh berisi huruf, angka, dash (-), dan underscore (_).',
        ]);

        // Sanitize whitespace
        foreach ($validated as $key => $value) {
            if (is_string($value)) {
                $validated[$key] = trim($value);
            }
        }

        // Keep track of changes for Audit Trail log
        $changes = [];
        $oldValues = [];
        $newValues = [];

        // 1. RW Name
        $oldRw = AppSetting::getValue('app.rw_name', 'RW 026');
        if ($oldRw !== $validated['rw_name']) {
            AppSetting::setValue('app.rw_name', $validated['rw_name'], 'string', 'rw_info');
            $changes[] = "Nama RW diubah dari '{$oldRw}' menjadi '{$validated['rw_name']}'";
            $oldValues['app.rw_name'] = $oldRw;
            $newValues['app.rw_name'] = $validated['rw_name'];
        }

        // 2. RW Phone
        $oldRwPhone = AppSetting::getValue('app.rw_contact_phone', '08123456789');
        if ($oldRwPhone !== $validated['rw_contact_phone']) {
            AppSetting::setValue('app.rw_contact_phone', $validated['rw_contact_phone'], 'string', 'rw_info');
            $changes[] = "Kontak RW diubah dari '{$oldRwPhone}' menjadi '{$validated['rw_contact_phone']}'";
            $oldValues['app.rw_contact_phone'] = $oldRwPhone;
            $newValues['app.rw_contact_phone'] = $validated['rw_contact_phone'];
        }

        // 3. Bank Sampah Name
        $oldBs = AppSetting::getValue('app.bank_sampah_name', 'EcoBank026');
        if ($oldBs !== $validated['bank_sampah_name']) {
            AppSetting::setValue('app.bank_sampah_name', $validated['bank_sampah_name'], 'string', 'bank_sampah');
            $changes[] = "Nama Bank Sampah diubah dari '{$oldBs}' menjadi '{$validated['bank_sampah_name']}'";
            $oldValues['app.bank_sampah_name'] = $oldBs;
            $newValues['app.bank_sampah_name'] = $validated['bank_sampah_name'];
        }

        // 4. Bank Sampah Phone
        $oldBsPhone = AppSetting::getValue('app.bank_sampah_contact_phone', '08123456789');
        if ($oldBsPhone !== $validated['bank_sampah_contact_phone']) {
            AppSetting::setValue('app.bank_sampah_contact_phone', $validated['bank_sampah_contact_phone'], 'string', 'bank_sampah');
            $changes[] = "Kontak Bank Sampah diubah dari '{$oldBsPhone}' menjadi '{$validated['bank_sampah_contact_phone']}'";
            $oldValues['app.bank_sampah_contact_phone'] = $oldBsPhone;
            $newValues['app.bank_sampah_contact_phone'] = $validated['bank_sampah_contact_phone'];
        }

        // 5. Default Due Days
        $oldDue = AppSetting::getValue('billing.default_due_days', 10);
        if ((int)$oldDue !== (int)$validated['default_due_days']) {
            AppSetting::setValue('billing.default_due_days', $validated['default_due_days'], 'integer', 'billing');
            $changes[] = "tenggat pembayaran iuran dari {$oldDue} hari menjadi {$validated['default_due_days']} hari";
            $oldValues['billing.default_due_days'] = $oldDue;
            $newValues['billing.default_due_days'] = $validated['default_due_days'];
        }

        // 6. Bill Prefix
        $oldBillPref = AppSetting::getValue('billing.bill_prefix', 'BILL');
        if ($oldBillPref !== $validated['bill_prefix']) {
            AppSetting::setValue('billing.bill_prefix', $validated['bill_prefix'], 'string', 'document');
            $changes[] = "Prefix tagihan diubah dari '{$oldBillPref}' menjadi '{$validated['bill_prefix']}'";
            $oldValues['billing.bill_prefix'] = $oldBillPref;
            $newValues['billing.bill_prefix'] = $validated['bill_prefix'];
        }

        // 7. Receipt Prefix
        $oldRcptPref = AppSetting::getValue('billing.receipt_prefix', 'RCPT');
        if ($oldRcptPref !== $validated['receipt_prefix']) {
            AppSetting::setValue('billing.receipt_prefix', $validated['receipt_prefix'], 'string', 'document');
            $changes[] = "Prefix kuitansi diubah dari '{$oldRcptPref}' menjadi '{$validated['receipt_prefix']}'";
            $oldValues['billing.receipt_prefix'] = $oldRcptPref;
            $newValues['billing.receipt_prefix'] = $validated['receipt_prefix'];
        }

        if (!empty($changes)) {
            // Catat pembaru terakhir secara dinamis
            $userName = auth()->user()->name ?? 'Admin RW';
            $currentTime = date('d/m/Y H:i');
            AppSetting::setValue('app.last_updated_by', $userName, 'string', 'system');
            AppSetting::setValue('app.last_updated_at', $currentTime, 'string', 'system');

            // Activity log formatting dengan JSON metadata terperinci
            $logDescription = "Admin RW memperbarui pengaturan: " . implode(', ', $changes) . ".";
            $this->activityLog->logInfo('settings.update', $logDescription, [
                'changes' => $changes,
                'metadata' => [
                    'old_values' => $oldValues,
                    'new_values' => $newValues,
                ],
                'user' => $userName,
                'updated_at' => $currentTime,
            ]);
        }

        return redirect()->route('admin.settings.index')
            ->with('success', 'Pengaturan sistem berhasil diperbarui.');
    }
}
