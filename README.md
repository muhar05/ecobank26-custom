# EcoBank026

**Sistem Informasi Bank Sampah Digital**

Aplikasi web berbasis Laravel untuk pengelolaan bank sampah: data nasabah, katalog harga dual-pricing, setoran sampah, tabungan digital, penarikan saldo, penjualan ke pengepul, kas operasional, hingga laporan dan audit trail. Dilengkapi sistem multi-role, dashboard analytics, dan halaman cek saldo publik untuk nasabah.

---

## Overview

Ecobank026 berfokus pada operasional bank sampah tingkat RW dengan modul utama:

- **Nasabah & Tabungan** — Profil nasabah, buku tabungan digital (savings ledger), dan portal mandiri warga.
- **Master Sampah & Harga** — Kategori sampah (grup & item), pengepul (collector), harga dual-pricing (harga nasabah vs harga pengepul), riwayat harga, serta material alias/mapping.
- **Transaksi** — Setoran sampah, penarikan saldo, penjualan ke pengepul, dan pengeluaran operasional.
- **Kas Bank Sampah** — Kas operasional riil dari margin penjualan dan biaya operasional.
- **Laporan** — Laporan setoran, penjualan, jurnal tabungan, arus kas, saldo nasabah, dan kas bank sampah (Excel/print/PDF).
- **Import Excel/CSV** — Import kategori, harga, dan setoran dengan mapping material & penanganan baris gagal.
- **Kontrol & Audit** — Manajemen perangkat admin, pengaturan aplikasi, dan audit trail (activity log).
- **Multi-Role** — Role dengan permission berbeda plus dashboard analytics (ApexCharts).

---

## Fitur Utama

### Nasabah & Tabungan

| Fitur | Keterangan |
|-------|------------|
| Data Nasabah | Kelola profil nasabah (CRUD), pencarian, dan detail saldo |
| Buku Tabungan | Ledger mutasi credit (setoran) & debit (penarikan) |
| Saldo Nasabah | Laporan saldo seluruh nasabah + export |
| Portal Warga | Halaman tabungan pribadi & riwayat (read-only) |
| Cek Saldo Publik | `/cek-saldo` tanpa login via kode nasabah + nomor HP |

### Master Sampah & Harga

| Fitur | Keterangan |
|-------|------------|
| Grup Kategori | Kelola kelompok sampah (Plastik, Kertas, Logam, Kaca, Organik, dll) |
| Kategori Sampah | Kelola item sampah, satuan, dan kode |
| Pengepul | Kelola data mitra pengepul |
| Harga Dual Pricing | Harga nasabah (`member_price`) dan harga pengepul (`collector_price`) per pengepul |
| Riwayat Harga | Histori perubahan harga sampah |
| Material Alias | Mapping nama eksternal ke material kanonik |
| Import Harga/Kategori | Import massal via Excel/CSV + template & baris gagal |

### Transaksi

| Fitur | Keterangan |
|-------|------------|
| Setoran Sampah | Catat setoran dengan auto harga nasabah (CRUD) + import |
| Penarikan Saldo | Penarikan dengan aturan minimal 2x setoran (CRUD) |
| Penjualan ke Pengepul | Penjualan, rekap harian, dan inline price (CRUD) |
| Pengeluaran Operasional | Catat biaya operasional + upload bukti nota |
| Kas Bank Sampah | Kas operasional dari margin penjualan (in) & biaya (out) |

### Laporan, Audit & Sistem

| Fitur | Keterangan |
|-------|------------|
| Laporan Setoran / Penjualan | Filter periode + export Excel & print |
| Jurnal Tabungan | Mutasi tabungan nasabah + export |
| Arus Kas | Cashflow bank sampah + export |
| Laporan Kas | Cash report bank sampah + PDF |
| Manajemen Perangkat | Penamaan & revoke perangkat admin |
| Audit Trail | Log aktivitas immutable (Admin RW) |
| Pengaturan Aplikasi | Setting global dinamis (nama RW, kontak, prefix dokumen) |

---

## Roles & Permissions

| Role | Permission | Akses |
|------|------------|-------|
| **admin_rw** | `view_waste_bank`, `view_waste_reports` | Pengawasan: dashboard, laporan, kas, pengeluaran, devices, audit trail, pengaturan aplikasi |
| **admin_bank_sampah** | `view_waste_bank`, `manage_deposits`, `manage_withdrawals`, `manage_sales`, `manage_waste_prices`, `view_waste_reports`, `manage_waste_customers` | Full operasional Bank Sampah (nasabah, harga, setoran, penarikan, penjualan, pengeluaran) |
| **warga** | `view_own_savings`, `view_own_dashboard` | Portal mandiri: saldo & riwayat tabungan pribadi |
| **admin_rt** | — | Role legacy (tanpa permission Bank Sampah), dipertahankan untuk kompatibilitas authorization test |

---

## Demo Credentials (Multi-Credential Login)

Login mendukung **nomor telepon, username, atau email** (Password: `password`). Nomor telepon dinormalisasi otomatis (mis. `620844444444` → `0844444444`).

| Nama | No HP (Input Login) | Format DB | Role | Password |
|---|---|---|---|---|
| Admin Bank Sampah | `620844444444` | `0844444444` | admin_bank_sampah | `password` |
| Admin Bank Sampah 2 | `620855555555` | `0855555555` | admin_bank_sampah | `password` |
| Admin Bank Sampah 3 | `620866666666` | `0866666666` | admin_bank_sampah | `password` |

---

## Tech Stack

- **Backend:** Laravel 13, PHP 8.3+
- **Frontend:** Blade, Tailwind CSS, Alpine.js, Vite
- **Charts:** ApexCharts
- **Auth:** Laravel Breeze (login nomor telepon/username/email)
- **Roles:** Spatie Laravel Permission
- **Excel:** Maatwebsite Excel
- **Database:** MySQL (production) / SQLite (default lokal)
- **Opsional:** Docker (lihat `docs/DOCKER_DEV.md`)

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

# Configure database in .env (default: sqlite)
# DB_CONNECTION=mysql
# DB_DATABASE=ecobank026
# DB_USERNAME=root
# DB_PASSWORD=

# Migrate and seed
php artisan migrate --seed

# Seed data demo (opsional, hanya untuk development)
# SEED_DEMO_DATA=true di .env, lalu:
php artisan migrate:fresh --seed

# Build assets
npm run build

# Start server
php artisan serve
```

Akses aplikasi di: http://localhost:8000

> Production: gunakan `php artisan migrate --force` (jangan `migrate:fresh`). Lihat `docs/DEPLOYMENT_PRODUCTION.md`.

---

## Demo Flow

### 1. Admin Bank Sampah (`620844444444`)

- Dashboard Bank Sampah dengan chart aliran kas & komposisi sampah.
- Alur: Kategori → Harga (dual pricing) → Nasabah → Setoran → Penarikan → Penjualan → Pengeluaran.
- Kelola import Excel setoran/harga beserta mapping material.
- Akses laporan & manajemen perangkat.

### 2. Admin RW (`admin_rw`)

- Dashboard & laporan tingkat pengawasan.
- Read-only untuk modul transaksi inti, akses penuh ke audit trail dan pengaturan aplikasi.

### 3. Warga (`warga`)

- Halaman Tabungan (`/warga/savings`): saldo & riwayat mutasi tabungan sampah pribadi (read-only).
- Akses halaman admin → 403 Forbidden.

### 4. Publik (tanpa login)

- `/cek-saldo`: masukkan kode nasabah + nomor HP untuk melihat saldo & 10 mutasi terakhir. Endpoint dilindungi rate limit anti brute-force.

---

## Business Rules

- **Penarikan:** Minimal 2x setoran sebelum penarikan pertama. Nominal tidak boleh melebihi saldo tabungan.
- **Penjualan:** Kas Bank Sampah hanya menerima margin (`collector_price − member_price` × berat). Total margin negatif ditolak.
- **Nasabah:** Nasabah dengan riwayat transaksi tidak boleh dihapus permanen; ubah status menjadi `inactive`.
- **Ledger:** Semua saldo dihitung dari ledger entries (source of truth). Edit/delete otomatis recalculate.
- **Snapshot Harga:** Perubahan master harga tidak mengubah data historis transaksi.

---

## Project Structure

```
app/
├── Http/Controllers/     # All controllers (Deposit, Sale, Withdrawal, Import, Report, dll)
├── Models/               # Eloquent models
├── Services/             # Business logic (BankSampahService, BankSampahAuditService, dll)
├── Exceptions/           # Custom exceptions
resources/views/
├── components/layouts/   # Dashboard layout component
├── dashboard/            # Role-specific dashboard
├── bank-sampah/          # Nasabah, setoran, penarikan, penjualan, harga, laporan, dll
├── warga/                # Portal tabungan warga
├── cek-saldo/            # Halaman cek saldo publik
├── admin/                # Audit logs
├── settings/             # Pengaturan aplikasi
database/
├── migrations/           # All table migrations
├── seeders/              # Role, admin, settings, demo data seeders
docs/                     # Knowledge base, deployment, docker, UML
```

---

## License

Proyek ini dibuat untuk keperluan Kerja Praktik (KP) Semester 6.
