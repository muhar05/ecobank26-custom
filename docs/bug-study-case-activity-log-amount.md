# Studi Kasus Bug: Activity Log Menampilkan "Rp0" untuk Setoran Sampah

## 1. Background

**Project:** EcoBank — Sistem Informasi Bank Sampah Digital  
**Stack:** Laravel 11, MySQL, Eloquent ORM  
**Modul:** Activity Log (Audit Trail)

Sistem ini mencatat semua transaksi keuangan nasabah (setoran & penarikan) dan menghasilkan log aktivitas yang ditampilkan di halaman audit log (`admin/audit-logs.blade.php`). Log aktivitas menggunakan accessor `human_description` dari model `ActivityLog` untuk menampilkan deskripsi yang mudah dibaca oleh admin.

**Arsitektur Logging:**
```
Controller → BankSampahService → DB::transaction (create data)
                                → ActivityLogService::logInfo() (setelah commit)
                                                          ↓
                                          ActivityLog::getHumanDescriptionAttribute()
                                                          ↓
                                              admin/audit-logs.blade.php
```

## 2. Problem

Setelah admin mencatat setoran sampah dari nasabah sebesar Rp500.000, halaman audit log menampilkan:

> "Admin menambahkan setoran sampah senilai **Rp0** untuk Nasabah Budi Santoso."

Jumlah yang benar (Rp500.000) tercatat di database table `deposits` dan `savings_ledgers`, tetapi **activity log selalu menampilkan Rp0** untuk event `deposit.create`.

Uniknya, untuk event `withdrawal.create` (penarikan), jumlah di activity log ditampilkan dengan benar. Hanya setoran yang terpengaruh.

## 3. Root Cause

**Payload key mismatch** antara producer (Service) dan consumer (Model accessor).

**Producer — `BankSampahService::recordDeposit()` (baris 73-83):**
```php
app(ActivityLogService::class)->logInfo(
    'deposit.create',
    "Mencatat setoran sampah sebesar Rp " . number_format($totalAmount, ...) . "...",
    [
        'deposit_id'       => $deposit->id,
        'waste_customer_id'=> $customer->id,
        'customer_name'    => $customer->name,
        'total_amount'     => (float) $totalAmount,  // ← KEY: 'total_amount'
        'details'          => $data['details'],
    ]
);
```

**Consumer — `ActivityLog::getHumanDescriptionAttribute()` (baris 114-117):**
```php
case 'deposit.create':
    $amount = number_format($payload['amount'] ?? 0, 0, ',', '.');  // ← READS: 'amount'
    $customer = $payload['customer_name'] ?? 'Warga';
    return "{$actorName} menambahkan setoran sampah senilai Rp{$amount}...";
```

Accessor membaca `$payload['amount']`, tetapi payload menyimpan key `total_amount`. Karena key `amount` tidak ada, PHP null-coalescing `?? 0` mengembalikan `0`, sehingga selalu menampilkan **Rp0**.

**Perbandingan dengan Withdrawal (yang BENAR):**
```php
// recordWithdrawal() payload:
'amount' => (float) $data['amount'],  // ← KEY: 'amount' ✓

// getHumanDescriptionAttribute() withdrawal:
$amount = number_format($payload['amount'] ?? 0, ...);  // ← READS: 'amount' ✓
```

Withdrawal menggunakan key `amount` di kedua sisi → cocok → tampil benar.  
Deposit menggunakan key `total_amount` di producer dan `amount` di consumer → **mismatch**.

## 4. Investigation Process

### Langkah 1: Identifikasi Gejala
Admin melaporkan: "Nominal setoran di halaman activity log selalu Rp0, tapi di halaman data setoran benar."

### Langkah 2: Trace ke View
```
admin/audit-logs.blade.php → {{ $log->human_description }}
```
Aksesornya ada di `ActivityLog.php`, method `getHumanDescriptionAttribute()`.

### Langkah 3: Analisis Accessor
```php
case 'deposit.create':
    $amount = number_format($payload['amount'] ?? 0, 0, ',', '.');
```
Variabel `$payload` berasal dari kolom JSON `payload` di database. Accessor mencari key `'amount'`.

### Langkah 4: Inspeksi Data di Database
```sql
SELECT payload FROM activity_logs WHERE event_type = 'deposit.create' ORDER BY id DESC LIMIT 1;
```
```json
{
    "deposit_id": 42,
    "waste_customer_id": 7,
    "customer_name": "Budi Santoso",
    "total_amount": 500000,    ← KEY 'total_amount', bukan 'amount'
    "details": [...]
}
```

**Key `amount` tidak ada di payload.** Yang ada adalah `total_amount`.

### Langkah 5: Trace ke Producer
`BankSampahService::recordDeposit()` menulis key `total_amount`:
```php
'total_amount' => (float) $totalAmount,
```

### Langkah 6: Perbandingan dengan Withdrawal
`recordWithdrawal()` menulis key `amount`:
```php
'amount' => (float) $data['amount'],
```
Dan accessor membaca `$payload['amount']` → **match**.

**Kesimpulan:** Inkonsistensi penamaan key antara `recordDeposit` (pakai `total_amount`) dan `recordWithdrawal` (pakai `amount`), sedangkan accessor konsisten membaca `amount`.

## 5. Solution

### Opsi A: Ubah payload key di Service (Recommended)
```php
// BankSampahService::recordDeposit(), baris 80
- 'total_amount'     => (float) $totalAmount,
+ 'amount'           => (float) $totalAmount,
```

### Opsi B: Ubah accessor di Model
```php
// ActivityLog::getHumanDescriptionAttribute(), baris 115
- $amount = number_format($payload['amount'] ?? 0, 0, ',', '.');
+ $amount = number_format($payload['total_amount'] ?? $payload['amount'] ?? 0, 0, ',', '.');
```

**Opsi A lebih baik** karena:
- Withdrawal sudah pakai `amount` → konsistensi antar kedua event
- Accessor sudah benar (membaca `amount`) → yang perlu diperbaiki adalah data yang ditulis
- Lebih sedikit perubahan kode

## 6. Result

Setelah perbaikan, activity log menampilkan:

> "Admin menambahkan setoran sampah senilai **Rp500.000** untuk Nasabah Budi Santoso." ✅

| Before | After |
|--------|-------|
| Rp0 | Rp500.000 |
| Selalu fallback ke 0 | Menampilkan jumlah aktual |
| Withdrawal benar, Deposit salah | Keduanya benar |

## 7. Lessons Learned

### 7.1. Payload Contract Harus Konsisten
Ketika producer dan consumer berada di file/service berbeda, key payload harus menggunakan nama yang konsisten. Tidak ada interface atau type safety untuk JSON payload → mismatch hanya terdeteksi saat runtime.

### 7.2. Null Coalescing `?? 0` Menyembunyikan Bug
Operator `?? 0` mencegah error tapi menghasilkan output salah secara **silent**. Tanpa pengecekan manual, bug ini bisa bertahan sangat lama karena:
- Tidak ada exception
- Tidak ada error di log Laravel
- Hanya visual output yang salah

**Rekomendasi:** Untuk field kritis, pertimbangkan assertion atau warning saat key tidak ditemukan:
```php
if (!isset($payload['amount'])) {
    \Log::warning("Missing 'amount' key in activity log payload", ['event' => $this->event_type]);
}
```

### 7.3. Inkonsistensi Naming dari Copy-Paste
Bug ini kemungkinan terjadi karena `recordWithdrawal` ditulis/diubah terpisah dari `recordDeposit`. Keduanya melakukan hal serupa (log transaksi) tapi dengan key naming yang berbeda (`amount` vs `total_amount`). Code review harus memeriksa konsistensi antara fungsi-fungsi sejenis.

### 7.4. Activity Log Adalah Evidence Trail
Activity log bukan fitur "nice to have" — ini adalah jejak audit untuk kepatuhan. Data yang salah di activity log sama berbahayanya dengan tidak ada log sama sekali, karena bisa menyesatkan investigasi.

### 7.5. Bug Bonus: Logging Masih di Dalam Transaction
Di `recordExpense()` (baris 304-314), `ActivityLogService::logInfo()` masih dipanggil **di dalam** `DB::transaction()`. Komentar di `recordDeposit` dan `recordWithdrawal` (baris 20-23, 90-91) secara eksplisit menyebutkan ini sebagai **BUG FIX #4** yang sudah diperbaiki dengan memindahkan logging ke luar transaction. Namun `recordExpense` belum diperbaiki → logging bisa ikut ter-rollback jika terjadi exception setelah logging.

---

**Files Involved:**
| File | Role |
|------|------|
| `app/Services/BankSampahService.php` | Producer — writes payload with `total_amount` key |
| `app/Models/ActivityLog.php` | Consumer — reads `$payload['amount']` in accessor |
| `app/Services/ActivityLogService.php` | Logging service — writes to DB |
| `resources/views/admin/audit-logs.blade.php` | View — renders `human_description` |