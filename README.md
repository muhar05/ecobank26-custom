# Ecobank026 Custom

**Sistem Informasi Pengelolaan Kas RT/RW & Bank Sampah**

Aplikasi web berbasis Laravel untuk pengelolaan keuangan komunitas RT/RW dan operasional bank sampah dengan sistem multi-role, dashboard analytics, dan transparansi laporan keuangan.

---

## Overview

Ecobank026 Custom menggabungkan dua modul utama dalam satu platform:

- **Kas RT/RW** — Pencatatan iuran warga, pengeluaran dana, dan buku kas umum per kategori dana dengan target progress.
- **Bank Sampah** — Pengelolaan setoran sampah, penarikan saldo nasabah, penjualan ke pengepul dengan sistem dual pricing dan margin otomatis.
- **Multi-Role** — 4 role dengan permission berbeda (Admin RT, Bendahara, Admin Bank Sampah, Warga).
- **Dashboard Analytics** — Chart interaktif per role menggunakan ApexCharts.
- **Import/Export** — Import harga sampah via CSV, export laporan ke CSV.

---

## Fitur Utama

### Kas RT/RW (Community Cash)

| Fitur | Keterangan |
|-------|------------|
| Kategori Dana | Kelola kategori dengan target amount dan progress |
| Pemasukan Warga | Catat iuran/kontribusi per kategori (CRUD) |
| Pengeluaran Dana | Catat pengeluaran dengan validasi saldo (CRUD) |
| Buku Kas Umum | Laporan lengkap dengan filter tanggal dan kategori |
| Export CSV | Download laporan kas dalam format CSV |
| Dashboard | Chart pemasukan/pengeluaran dan saldo per kategori |

### Bank Sampah

| Fitur | Keterangan |
|-------|------------|
| Data Nasabah | Kelola data warga/nasabah bank sampah |
| Kategori Sampah | Kelola jenis sampah dan satuan |
| Harga Dual Pricing | Harga nasabah (member_price) dan harga pengepul (collector_price) |
| Import Harga CSV | Import massal harga sampah dengan template |
| Setoran Sampah | Catat setoran dengan auto-fill harga nasabah (CRUD) |
| Penarikan Saldo | Penarikan dengan aturan minimal 2x setoran (CRUD) |
| Penjualan ke Pengepul | Catat penjualan, kas hanya menerima margin (CRUD) |
| Kas Bank Sampah | Laporan kas dari margin penjualan |
| Saldo Nasabah | Laporan saldo seluruh nasabah + export |
| Dashboard | Chart aliran kas dan komposisi sampah |

---

## Roles & Permissions

| Role | Akses |
|------|-------|
| **admin_rw** | Full akses tingkat RW (Laporan Gabungan, Kas, & Bank Sampah) |
| **bendahara_rw** / **bendahara** | Full akses Kas RT/RW + data warga. Tidak bisa akses Bank Sampah |
| **admin_rt** | Kelola RT, data KK, warga, serta tagihan iuran bulanan |
| **admin_bank_sampah** | Full akses operasional Bank Sampah (Setoran, Penarikan, Penjualan) |
| **warga** | Dashboard personal, status iuran bulanan, & tabungan bank sampah |

---

## Demo Credentials (Phone Login)

Semua akun demo menggunakan **Nomor Telepon** sebagai login identifier (Password: `password`).

| Role | No HP (Input Login) | Format DB | Password |
|---|---|---|---|
| Admin RW | `620811111111` | `0811111111` | `password` |
| Bendahara RW | `620822222222` | `0822222222` | `password` |
| Admin RT | `620833333333` | `0833333333` | `password` |
| Admin Bank Sampah | `620844444444` | `0844444444` | `password` |
| Warga | `620855555555` | `0855555555` | `password` |

---

## Tech Stack

- **Backend:** Laravel 12, PHP 8.2+
- **Frontend:** Blade, Tailwind CSS, Alpine.js
- **Charts:** ApexCharts
- **Auth:** Laravel Breeze
- **Roles:** Spatie Laravel Permission
- **Database:** MySQL

---

## Installation

```bash
# Clone repository
git clone <repository-url>
cd ecobank026-custom

# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Configure database in .env
# DB_DATABASE=eco_bank_26
# DB_USERNAME=root
# DB_PASSWORD=

# Migrate and seed (Local Development Only)
php artisan migrate:fresh --seed

# Production Deployment (NEVER use migrate:fresh in production!)
php artisan migrate --force
php artisan db:seed --class=DefaultAppSettingsSeeder

# Build assets
npm run build

# Start server
php artisan serve
```

Akses aplikasi di: http://localhost:8000

---

## Demo Flow

### 1. Admin RW (`620811111111`)
- Dashboard gabungan tingkat RW untuk visualisasi total kas & bank sampah.
- Full access untuk pengawasan laporan terpadu.

### 2. Bendahara RW (`620822222222`)
- Dashboard fokus Kas RT/RW dengan chart bulanan.
- CRUD pemasukan dan pengeluaran.
- Akses `/bank-sampah/*` → 403 Forbidden.

### 3. Admin RT (`620833333333`)
- Dashboard gabungan: Kas RT/RW + ringkasan Bank Sampah.
- Kelola wilayah RT 001, data KK, warga, serta tagihan iuran bulanan.

### 4. Admin Bank Sampah (`620844444444`)
- Dashboard Bank Sampah dengan chart.
- Harga Sampah → Import CSV → Setoran → Penarikan → Penjualan.
- Laporan: Saldo Nasabah + Kas Bank Sampah + Export.
- Akses `/community-cash/*` → 403 Forbidden.

### 5. Warga (`620855555555`)
- Dashboard ringkas: tagihan KK bulan berjalan + saldo tabungan bank sampah.
- Halaman "Tagihan Saya" (read-only) lengkap dengan status iuran & kuitansi.
- Akses halaman admin → 403 Forbidden.

---

## Business Rules

- **Kas RT/RW:** Pengeluaran tidak boleh melebihi saldo kategori dana.
- **Bank Sampah:** Penarikan minimal setelah 2x setoran. Penarikan tidak boleh melebihi saldo.
- **Penjualan:** Kas Bank Sampah hanya menerima margin (selisih harga pengepul − harga nasabah).
- **Ledger:** Semua saldo dihitung dari ledger entries (source of truth). Edit/delete otomatis recalculate.

---

## Project Structure

```
app/
├── Http/Controllers/     # All controllers
├── Models/               # Eloquent models
├── Services/             # Business logic (CommunityCashService, BankSampahService)
├── Exceptions/           # Custom exceptions
resources/views/
├── components/layouts/   # Dashboard layout component
├── dashboard/            # 4 role-specific dashboards
├── community-cash/       # Kas RT/RW views
├── bank-sampah/          # Bank Sampah views
├── warga/                # Warga views
├── members/              # Data warga views
database/
├── migrations/           # All table migrations
├── seeders/              # Demo data seeders
```

---

## License

Proyek ini dibuat untuk keperluan Kerja Praktik (KP) Semester 6.
