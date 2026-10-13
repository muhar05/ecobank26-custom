# Dokumen Spesifikasi Infrastruktur & Hosting Server
## Sistem Informasi Manajemen Bank Sampah Digital — EcoBank026

> **Status Dokumen:** Resmi (Production-Ready)  
> **Terakhir Diperbarui:** 11 Oktober 2026  
> **Target Platform:** Cloud / Shared Hosting (Hostinger), VPS Linux (Ubuntu/Debian), atau Docker Container  
> **Domain Produksi:** `ecobank026.my.id`

---

## 1. Ringkasan Eksekutif Arsitektur Aplikasi

Aplikasi **EcoBank026** dibangun di atas ekosistem modern Laravel dengan kebutuhan komputasi yang menitikberatkan pada:
1. **Pemrosesan File Spreadsheet Berat**: Import dan export data timbangan nasabah, harga lapak, dan buku kas ribuan baris (`maatwebsite/excel` & `PhpSpreadsheet`).
2. **Kalkulasi Buku Besar Akuntansi (Ledger Consistency)**: Agregasi saldo nasabah dan arus kas operasional berbasis transaksi waktu-nyata (*real-time ledger balance*).
3. **Penyimpanan Berkas Digital Terproteksi**: Dokumen bukti nota transaksi operasional disimpan pada disk privat berizin khusus.
4. **Keamanan Jaringan & Sesi**: Memerlukan enkripsi HTTPS/TLS wajib untuk *cookie* session `Secure` dan proteksi manipulasi data.

---

## 2. Spesifikasi Lingkungan Server (Runtime Environment)

### 2.1. Versi PHP & Konfigurasi Runtime

| Komponen | Spesifikasi Minimal | Spesifikasi Rekomendasi | Catatan Khusus |
|---|---|---|---|
| **Versi PHP** | `PHP 8.3.x` | `PHP 8.4.x` | Hostinger saat ini menggunakan binary path: `/opt/alt/php84/usr/bin/php` |
| **Memory Limit (`memory_limit`)** | `256M` | `512M` | **Sangat krusial** saat mengimpor/mengekspor ribuan baris Excel (seperti 1.092 setoran & 7.000 detail timbangan) agar tidak terjadi *Out of Memory (OOM)*. |
| **Max Execution Time (`max_execution_time`)** | `60 detik` | `120 detik` | Mencegah proses parsing sheet Excel bertanggal terputus di tengah jalan (*timeout*). |
| **Upload Max Filesize (`upload_max_filesize`)** | `10M` | `20M` | Maksimal unggah file Excel di aplikasi dibatasi 5MB, berkas nota JPG/PDF dibatasi 2MB. |
| **Post Max Size (`post_max_size`)** | `12M` | `25M` | Harus selalu lebih besar dari `upload_max_filesize`. |
| **Max Input Vars (`max_input_vars`)** | `3000` | `5000` | Diperlukan saat menyimpan form matriks harga atau input batch setoran banyak baris sekaligus. |
| **Timezone (`date.timezone`)** | `Asia/Jakarta` | `Asia/Jakarta` | Menjamin kesesuaian waktu audit trail dan pencatatan kas di Indonesia. |

### 2.2. Ekstensi PHP Wajib (Required PHP Extensions)

Server hosting **wajib** mengaktifkan ekstensi berikut:

| Ekstensi | Fungsi & Kebutuhan dalam Aplikasi | Status |
|---|---|---|
| `pdo_mysql` | Koneksi database MySQL / MariaDB | **Wajib Mutlak** |
| `zip` | Ekstraksi dan pembuatan file XLSX oleh `PhpSpreadsheet` / `maatwebsite/excel` | **Wajib Mutlak** |
| `gd` atau `imagick` | Manipulasi gambar, kompresi foto nota bukti pengeluaran, dan barcode/chart | **Wajib Mutlak** |
| `mbstring` | Normalisasi string nama material, pembersihan karakter non-ASCII, dan sanitasi multi-byte | **Wajib Mutlak** |
| `xml` / `xmlwriter` / `dom` | Serialisasi struktur dokumen XML di dalam file XLSX | **Wajib Mutlak** |
| `fileinfo` | Validasi tipe MIME berkas unggahan bukti nota (`jpg`, `jpeg`, `png`, `pdf`) | **Wajib Mutlak** |
| `bcmath` | Kalkulasi desimal presisi tinggi pada formula keuangan, margin, dan timbangan | **Wajib** |
| `ctype` & `tokenizer` | Inti kompilasi template Blade dan dependency Laravel | **Wajib** |
| `openssl` | Enkripsi payload sesi, hash kata sandi, token CSRF, dan Device Tracking | **Wajib** |
| `curl` | Komunikasi HTTP client ke API luar / GitHub webhook deployment | **Wajib** |

---

## 3. Spesifikasi Basis Data (Database Engine)

Aplikasi menggunakan engine relasional ACID dengan integritas referensial penuh (*Foreign Key Constraints*).

| Parameter | Spesifikasi Minimal | Spesifikasi Rekomendasi |
|---|---|---|
| **DBMS Engine** | MySQL `8.0+` atau MariaDB `10.6+` | MySQL `8.0.33+` / Percona Server |
| **Storage Engine** | `InnoDB` | `InnoDB` (Dukungan penuh row-level locking `lockForUpdate` & Foreign Key) |
| **Default Charset** | `utf8mb4` | `utf8mb4` (Dukungan simbol, nama lengkap, dan emoji) |
| **Default Collation** | `utf8mb4_unicode_ci` | `utf8mb4_unicode_ci` |
| **SQL Mode** | Default Laravel | `NO_ENGINE_SUBSTITUTION`, non-strict group by dihindari |
| **Kapasitas Penyimpanan DB** | `500 MB` | `1 GB - 2 GB` (Dapat menampung ratusan ribu log audit trail & detail setoran hingga bertahun-tahun) |
| **Hak Akses User DB (Privileges)** | `SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, ALTER, INDEX, REFERENCES, LOCK TABLES` | User MySQL terisolasi (bukan user `root`), dengan kata sandi acak minimal 16 karakter. |

---

## 4. Spesifikasi Web Server & Konfigurasi Jaringan

### 4.1. Server Engine yang Didukung
1. **LiteSpeed Web Server (LSWS)** / **OpenLiteSpeed** *(Default di Hostinger Business)*
2. **Nginx** `1.20+` (Reverse Proxy PHP-FPM)
3. **Apache HTTP Server** `2.4+`

### 4.2. Document Root & Struktur Akses Publik
- **⚠️ SYARAT WAJIB KEAMANAN**:  
  Document Root domain **HARUS mengarah ke subfolder `/public`**, BUKAN ke root folder project!  
  - **Di Hostinger**: Domain diatur agar root directory-nya adalah:  
    `domains/ecobank026.my.id/public_html` (di mana folder `public_html` berisi berkas dari folder `public` aplikasi, atau diarahkan melalui symlink / vhost config).
  - Menyematkan root project langsung ke internet dapat mengekspos berkas `.env`, kode sumber PHP, dan berkas penyimpanan privat!

### 4.3. Modul Apache / LiteSpeed Wajib
Konfigurasi `public/.htaccess` pada aplikasi telah dioptimasi dan memerlukan modul server berikut:
- `mod_rewrite`: Penanganan routing URL Laravel ke `index.php`.
- `mod_deflate`: Kompresi teks Gzip otomatis untuk HTML, CSS, JS, JSON, dan SVG (menekan waktu muat halaman hingga 70%).
- `mod_expires` & `mod_headers`: Cache seumur 1 tahun (`max-age=31536000, immutable`) untuk asset statis Vite (CSS, JS terkompilasi, dan font WOFF2) serta header keamanan browser (`X-Frame-Options`, `X-Content-Type-Options`).

### 4.4. Sertifikat Keamanan SSL / TLS
- **Wajib Aktif (HTTPS Only)**.
- Protokol minimal: **TLS 1.2** atau **TLS 1.3**.
- Disediakan gratis oleh Hostinger (Let's Encrypt Free SSL).
- **Alasan Teknis**: Pengaturan cookie session aplikasi telah dikonfigurasi dengan flag `Secure=true` di lingkungan produksi. Jika website diakses via HTTP biasa tanpa SSL, pengguna tidak akan bisa login karena browser menolak menyimpan cookie session.

---

## 5. Spesifikasi Hardware & Pilihan Paket Hosting

### 5.1. Opsi A: Shared Cloud Hosting (Pilihan Paling Hemat & Efisien)

Untuk operasional bank sampah tingkat RW/komunitas dengan beban operasional harian:
- **Penyedia Direkomendasikan:** Hostinger
- **Pilihan Paket:**
  - **Paket Minimal:** *Premium Web Hosting*
  - **Paket Rekomendasi (Sangat Disarankan):** **Hostinger Business Web Hosting**
- **Keunggulan Paket Business untuk EcoBank026:**
  - Akses **SSH (Terminal)** bawaan (wajib untuk menjalankan perintah `artisan migrate`, `artisan optimize:clear`, dan penarikan Git otomatis).
  - Resource RAM: **1.5 GB RAM Dedicated per Akun** (sangat aman untuk ekspor Excel ribuan baris).
  - Penyimpanan: **200 GB NVMe Storage** (kecepatan I/O tinggi untuk transaksi database).
  - Keamanan: Cloudflare Protected Nameservers + SSL Gratis Seumur Hidup.

### 5.2. Opsi B: VPS (Virtual Private Server) — Jika Menampung Skala Banyak RW / Kecamatan

Jika aplikasi direncanakan menampung puluhan unit bank sampah dalam satu server:
- **Sistem Operasi:** Ubuntu 22.04 LTS atau 24.04 LTS (64-bit).
- **vCPU:** Minimal 1 Core, Rekomendasi 2 Core.
- **RAM:** Minimal 2 GB, Rekomendasi 4 GB.
- **Penyimpanan:** 20 GB - 50 GB SSD / NVMe.
- **Web Server Stack:** LEMP (Linux, Nginx, MariaDB, PHP 8.4-FPM).

---

## 6. Kebutuhan Otomasi & Task Scheduler (Background Jobs)

### 6.1. Cron Job (Laravel Task Scheduling)
Aplikasi membutuhkan 1 baris cron job sistem yang berjalan setiap menit untuk membersihkan cache kedaluwarsa, mengelola impor staging, dan memelihara sesi.

**Konfigurasi di Menu Cron Jobs hPanel Hostinger:**
- **Waktu Interval:** Setiap Menit (`* * * * *`)
- **Perintah (Command):**
  ```bash
  * * * * * cd /home/u123456789/domains/ecobank026.my.id/public_html && /opt/alt/php84/usr/bin/php artisan schedule:run >> /dev/null 2>&1
  ```
  *(Sesuaikan `/home/u123456789/` dengan username path akun Hostinger Anda).*

### 6.2. Queue Worker (Antrean)
- **Konfigurasi Default:** Menggunakan driver database (`QUEUE_CONNECTION=database`).
- Pada shared hosting tanpa daemon supervisor, antrean dapat diproses langsung secara sinkron (`sync`) atau dipicu berkala melalui scheduler.

---

## 7. Kebutuhan CI/CD & Alur Deployment (GitHub Actions)

Aplikasi telah dilengkapi alur otomatisasi deployment di `.github/workflows/deploy.yml` yang terhubung ke Hostinger.

### 7.1. Akses SSH yang Dibutuhkan di Hostinger:
1. **Port SSH:** Port non-standar Hostinger (Port `65002`).
2. **Kredensial SSH (disimpan di GitHub Repository Secrets):**
   - `HOSTINGER_HOST`: IP atau host server Hostinger.
   - `HOSTINGER_USERNAME`: Username SSH hPanel.
   - `HOSTINGER_SSH_KEY`: Private Key SSH (RSA / Ed25519).

### 7.2. Script Deployment Otomatis di Server:
```bash
# 1. Masuk ke direktori web
cd domains/ecobank026.my.id/public_html

# 2. Ambil perubahan kode dari GitHub
git pull origin main

# 3. Jalankan migrasi database baru jika ada
/opt/alt/php84/usr/bin/php artisan migrate --force

# 4. Bersihkan dan perbarui cache optimasi Laravel
/opt/alt/php84/usr/bin/php artisan optimize:clear
/opt/alt/php84/usr/bin/php artisan config:cache
/opt/alt/php84/usr/bin/php artisan route:cache
/opt/alt/php84/usr/bin/php artisan view:cache

# 5. Pastikan symlink storage terpasang
/opt/alt/php84/usr/bin/php artisan storage:link
```

---

## 8. Ringkasan Checklist Kesiapan Hosting (Readiness Checklist)

Sebelum merilis sistem ke pengguna akhir, pastikan checklist berikut terpenuhi:

- [ ] **PHP 8.3 atau 8.4** aktif dengan ekstensi `pdo_mysql`, `zip`, `gd`, `mbstring`, `fileinfo`, `bcmath`.
- [ ] `memory_limit` disetel minimal **256M** (disarankan **512M**).
- [ ] Document Root domain mengarah ke folder **`/public`**.
- [ ] Sertifikat **SSL/HTTPS aktif** dan rute otomatis me-redirect HTTP ke HTTPS.
- [ ] File `.env` produksi disetel:
  - `APP_ENV=production`
  - `APP_DEBUG=false`
  - `APP_URL=https://ecobank026.my.id`
  - `SESSION_SECURE_COOKIE=true`
- [ ] Kredensial MySQL menggunakan user khusus dengan password kuat (bukan `root`).
- [ ] Symlink storage publik telah dibuat (`php artisan storage:link`).
- [ ] Kompresi Gzip dan Caching Browser aktif pada web server via `.htaccess`.
- [ ] Backup berkala database dijadwalkan secara mingguan atau bulanan via hPanel.

---

*Dokumen ini disusun untuk menjamin keandalan, keamanan data nasabah, dan performa optimal aplikasi EcoBank026 di lingkungan produksi.*
