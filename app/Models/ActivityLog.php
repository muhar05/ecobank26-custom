<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    // Append-only, no updated_at needed
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'severity',
        'event_type',
        'description',
        'ip_address',
        'user_agent',
        'correlation_id',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Disable updates and deletes to maintain immutable integrity.
     */
    protected static function boot()
    {
        parent::boot();

        static::updating(function ($model) {
            throw new \Exception('Activity logs are immutable (append-only) and cannot be updated.');
        });

        static::deleting(function ($model) {
            throw new \Exception('Activity logs are immutable (append-only) and cannot be deleted.');
        });
    }

    /**
     * Relasi ke model User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getHumanEventAttribute(): string
    {
        $map = [
            'deposit.create' => 'Setoran Sampah Dibuat',
            'withdrawal.create' => 'Penarikan Tabungan Bank Sampah',
            'bill.payment' => 'Pembayaran Iuran Kas',
            'waste_customer.create' => 'Pendaftaran Nasabah Baru',
            'waste_customer.update' => 'Pembaruan Profil Nasabah',
            'waste_customer.delete_attempt' => 'Percobaan Penghapusan Nasabah',
        ];

        return $map[$this->event_type] ?? $this->event_type;
    }

    public function getHumanSeverityAttribute(): string
    {
        $map = [
            'info' => 'Informasi',
            'warning' => 'Perlu Perhatian',
            'critical' => 'Risiko Tinggi',
        ];

        return $map[$this->severity] ?? $this->severity;
    }

    public function getEventBadgeClassAttribute(): string
    {
        $map = [
            'deposit.create' => 'bg-emerald-50 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
            'withdrawal.create' => 'bg-blue-50 dark:bg-blue-950 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-800',
            'bill.payment' => 'bg-purple-50 dark:bg-purple-950 text-purple-700 dark:text-purple-300 border-purple-200 dark:border-purple-800',
            'waste_customer.create' => 'bg-teal-50 dark:bg-teal-950 text-teal-700 dark:text-teal-300 border-teal-200 dark:border-teal-800',
            'waste_customer.update' => 'bg-slate-50 dark:bg-slate-950 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-800',
            'waste_customer.delete_attempt' => 'bg-amber-50 dark:bg-amber-950 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800',
        ];

        return $map[$this->event_type] ?? 'bg-slate-100 text-slate-700 border-slate-200';
    }

    public function getSeverityBadgeClassAttribute(): string
    {
        $map = [
            'info' => 'bg-blue-50 dark:bg-blue-950 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800',
            'warning' => 'bg-amber-50 dark:bg-amber-950 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800',
            'critical' => 'bg-rose-50 dark:bg-rose-950 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800',
        ];

        return $map[$this->severity] ?? 'bg-slate-100 text-slate-700';
    }

    public function getHumanDescriptionAttribute(): string
    {
        $payload = $this->payload ?? [];
        $actorName = $this->user ? $this->user->name : 'Sistem';

        switch ($this->event_type) {
            case 'deposit.create':
                $amount = number_format($payload['amount'] ?? 0, 0, ',', '.');
                $customer = $payload['customer_name'] ?? 'Warga';
                return "{$actorName} (Petugas) berhasil menambahkan setoran sampah senilai Rp{$amount} untuk Nasabah {$customer}.";
                
            case 'withdrawal.create':
                $amount = number_format($payload['amount'] ?? 0, 0, ',', '.');
                $customer = $payload['customer_name'] ?? 'Warga';
                return "Nasabah {$customer} mencairkan saldo tabungan bank sampah senilai Rp{$amount}. Disetujui oleh {$actorName}.";
                
            case 'bill.payment':
                $amount = number_format($payload['amount'] ?? 0, 0, ',', '.');
                $kk = $payload['kk_number'] ?? '—';
                return "Pembayaran iuran kas bulanan senilai Rp{$amount} dikonfirmasi untuk KK dengan nomor {$kk}.";
                
            case 'waste_customer.create':
                $customer = $payload['name'] ?? 'Nasabah';
                return "Pendaftaran profil nasabah bank sampah baru bernama {$customer} berhasil dilakukan oleh {$actorName}.";
                
            case 'waste_customer.update':
                $customer = $payload['name'] ?? 'Nasabah';
                return "Pembaruan informasi data diri profil nasabah {$customer} berhasil disimpan oleh {$actorName}.";
                
            case 'waste_customer.delete_attempt':
                $customer = $payload['name'] ?? 'Nasabah';
                $success = $payload['success'] ?? false;
                $status = $success ? 'berhasil dihapus' : 'gagal (masih memiliki saldo aktif)';
                return "Upaya menghapus data nasabah {$customer} oleh {$actorName} status: {$status}.";
                
            default:
                return $this->description;
        }
    }
}
