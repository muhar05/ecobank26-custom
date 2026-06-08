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
            'deposit.create' => 'Setoran Sampah',
            'withdrawal.create' => 'Penarikan Tabungan',
            'bill.payment' => 'Pembayaran Iuran',
            'waste_customer.create' => 'Nasabah Baru',
            'waste_customer.update' => 'Update Nasabah',
            'waste_customer.delete_attempt' => 'Hapus Nasabah',
            'settings.update' => 'Update Pengaturan',
            'member.import' => 'Import Warga',
            'kk.import' => 'Import KK',
            'report.export' => 'Export Laporan',
        ];

        return $map[$this->event_type] ?? ucfirst(str_replace(['.', '_'], ' ', $this->event_type));
    }

    public function getHumanSeverityAttribute(): string
    {
        $map = [
            'info' => 'Info',
            'warning' => 'Perhatian',
            'critical' => 'Penting',
        ];

        return $map[$this->severity] ?? $this->severity;
    }

    public function getEventBadgeClassAttribute(): string
    {
        $map = [
            'deposit.create' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950 dark:text-emerald-300 dark:border-emerald-800',
            'withdrawal.create' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950 dark:text-blue-300 dark:border-blue-800',
            'bill.payment' => 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950 dark:text-purple-300 dark:border-purple-800',
            'settings.update' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950 dark:text-amber-300 dark:border-amber-800',
            'member.import' => 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-950 dark:text-indigo-300 dark:border-indigo-800',
            'kk.import' => 'bg-cyan-50 text-cyan-700 border-cyan-200 dark:bg-cyan-950 dark:text-cyan-300 dark:border-cyan-800',
            'report.export' => 'bg-slate-50 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
        ];

        return $map[$this->event_type] ?? 'bg-slate-100 text-slate-700 border-slate-200';
    }

    public function getSeverityBadgeClassAttribute(): string
    {
        $map = [
            'info' => 'bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-950 dark:text-blue-300 dark:border-blue-800',
            'warning' => 'bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950 dark:text-amber-300 dark:border-amber-800',
            'critical' => 'bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-950 dark:text-rose-300 dark:border-rose-800',
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
                return "{$actorName} menambahkan setoran sampah senilai Rp{$amount} untuk Nasabah {$customer}.";
                
            case 'withdrawal.create':
                $amount = number_format($payload['amount'] ?? 0, 0, ',', '.');
                $customer = $payload['customer_name'] ?? 'Warga';
                return "Nasabah {$customer} mencairkan saldo Rp{$amount}. Disetujui oleh {$actorName}.";
                
            case 'bill.payment':
                $amount = number_format($payload['amount'] ?? 0, 0, ',', '.');
                $kk = $payload['kk_number'] ?? '—';
                return "Pembayaran iuran senilai Rp{$amount} untuk KK {$kk} dikonfirmasi oleh {$actorName}.";
                
            case 'waste_customer.create':
                $customer = $payload['name'] ?? 'Nasabah';
                return "Pendaftaran nasabah baru bernama {$customer} oleh {$actorName}.";
                
            case 'settings.update':
                $changesCount = count($payload['changes'] ?? []);
                return "{$actorName} memperbarui {$changesCount} item pengaturan sistem.";

            case 'member.import':
            case 'kk.import':
                $type = $this->event_type === 'member.import' ? 'Warga' : 'Kartu Keluarga';
                return "{$actorName} berhasil mengimpor data massal {$type}.";

            case 'report.export':
                return "{$actorName} mengunduh file laporan excel.";
                
            default:
                return $this->description;
        }
    }
}
