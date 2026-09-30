# EcoBank026 UI/UX Audit Report

> **Baseline Source of Truth:** `DESIGN.md` (EcoBank026 Design System v1.0)  
> **Core Direction:** *Modern Operational × Warm Human* — **UX Motto:** *One Click*  
> **Audit Date:** 30 September 2026  
> **Scope:** Seluruh antarmuka pengguna (Views, Layouts, Components, Navigation, Design Tokens, States, Responsiveness, Anti-Slop Compliance).  
> **Mode:** Read-Only Audit & Inventory (Zero Code / Schema Mutation).

---

## 1. Executive Summary

Audit menyeluruh telah dilakukan terhadap seluruh antarmuka web EcoBank026 (102 berkas Blade, 28 rute tampilan, 31 komponen/parsial) dengan membandingkannya terhadap acuan desain resmi di `DESIGN.md`.

### Temuan Utama:
1. **Divergensi Visual & Fragmentasi Bahasa Desain**:
   Aplikasi saat ini terpecah ke dalam 4 gaya visual yang bertolak belakang:
   * **Modul Operasional Bank Sampah Baru (Setoran, Impor Setoran, Riwayat Harga)**: Berfungsi sangat baik secara alur kerja (*keyboard-ready*, kalkulasi otomatis, *zero nested modal*), namun tipografinya terlalu padat (`text-xs` 12px dan `text-[10px]` mendominasi), serta palet warnanya murni mengandalkan Tailwind *emerald/slate* default tanpa sentuhan palet *earthy* (*warm cream, charcoal, brown*).
   * **Dashboard Admin Bank Sampah**: Mengalami sindrom **AI-Slop & Generic Admin Template** yang parah: sudut kartu melengkung ekstrem (`rounded-[2rem]`), gelembung *gradient blur* dekoratif, animasi *staggered entrance delay* buatan (`delay-[200ms]` s/d `delay-[1000ms]`), serta grafik ApexCharts dekoratif yang secara finansial tidak logis (menjumlahkan Saldo Nasabah + Kas + Penjualan sebagai "Total Aset"). Tombol aksi cepat (*Quick Actions*) justru terkubur di bagian paling bawah layar.
   * **Portal Warga (Cek Saldo & Tabungan Warga)**: Menggunakan skala tipografi raksasa (`text-5xl`), radius kartu bengkak (`rounded-3xl`), dan bayangan tebal (`shadow-lg`), terisolasi dari gaya modul admin.
   * **Modul Breeze/Auth & Pengaturan RW**: Masih menggunakan komponen warisan Breeze default (`rounded-md`, tombol hitam pekat, `text-xs uppercase tracking-widest`).

2. **Tipografi & Keterbacaan (*Readability First Violation*)**:
   `DESIGN.md` mewajibkan batas bawah (*baseline*) body text **16px+ (`text-base`)** dengan hierarki moderat. Pada kenyataannya, hampir seluruh tabel operasional, badge, teks bantuan, dan input form menggunakan `text-xs` (12px), `text-sm` (14px), bahkan `text-[10px]`/`text-[11px]`. Teks penanda wajib (`Wajib diisi`) hampir di semua form masih disederhanakan menjadi tanda bintang merah (`*`).

3. **Komponen Hantu (*Dead/Unused Component Slop*)**:
   Ditemukan 9 komponen di `resources/views/components/` (`modern-summary-card`, `modern-table-toolbar`, `modern-badge`, `modern-data-table`, `modern-page-header`, `modern-actions`, `skeleton-card`, `skeleton-line`, `skeleton-table`) yang dibuat dengan pola generic admin dashboard dan **TIDAK PERNAH DIGUNAKAN DI HALAMAN MANAPUN**.

4. **Ketiadaan Komponen Kritis yang Diwajibkan `DESIGN.md`**:
   * **Sidebar Desktop Tidak Collapsible**: Sidebar berukuran kaku 16rem (256px) tanpa tombol *collapse/expand*.
   * **Header/Topbar Statis**: Topbar tidak memiliki *breadcrumbs*, tidak memiliki *contextual primary action*, dan tidak memiliki pencarian hibrida.
   * **Feedback Toast Mengambang Tidak Ada**: Notifikasi sukses masih berupa banner blok statis (`session('success')`) yang mendorong konten halaman ke bawah.

---

## 2. Page Inventory

Berikut adalah inventarisasi lengkap seluruh 28 halaman/tampilan unik di EcoBank026:

| No | Halaman (Page) | Route Name | Tujuan / Kegunaan (Purpose) | Klasifikasi | Temuan Utama (Main Finding) |
|:---:|---|---|---|:---:|---|
| 1 | **Dashboard Bank Sampah** | `bank-sampah.dashboard` | Ringkasan operasional & navigasi cepat admin | 🔴 REDESIGN | AI-Slop: `rounded-[2rem]`, gradient blur, delay animasi bertingkat, chart dekoratif, menu cepat terkubur di bawah. |
| 2 | **Setoran Sampah (Create)** | `bank-sampah.deposits.create` | Pencatatan penimbangan nasabah aktif (Reference Workflow) | 🟡 REFINE | Alur operasional keyboard-first & split screen sangat solid, tetapi teks terlalu kecil (`text-xs`), input nasabah belum search-first dengan recent list. |
| 3 | **Setoran Sampah (List)** | `bank-sampah.deposits.index` | Riwayat penimbangan sampah nasabah | 🟡 REFINE | Struktur tabel informatif & hirarki nama jelas, tetapi filter tanggal belum sticky dan pagination default. |
| 4 | **Setoran Sampah (Edit)** | `bank-sampah.deposits.edit` | Perbaikan transaksi penimbangan yang keliru | 🟡 REFINE | Konsisten dengan create, namun banner peringatan saldo masih monoton. |
| 5 | **Import Setoran (Wizard)** | `bank-sampah.deposits.import` | Wizard impor massal catatan timbangan Excel | 🟡 REFINE | Sangat fungsional (auto-match, bulk add, alias resolver), namun tabel pratinjau sangat padat pada layar laptop standar. |
| 6 | **Nasabah (List)** | `bank-sampah.customers.index` | Daftar master nasabah aktif/nonaktif | 🟡 REFINE | Masih menggunakan avatar bulat generik dan badge status tanpa ikon. Tombol aksi terlalu kecil. |
| 7 | **Nasabah (Create)** | `bank-sampah.customers.create` | Registrasi profil nasabah bank sampah baru | 🟡 REFINE | Form bersih menggunakan `form-card`, namun label required hanya `*` dan font input kecil (14px). |
| 8 | **Nasabah (Edit)** | `bank-sampah.customers.edit` | Pembaruan data kontak/alamat nasabah | 🟡 REFINE | Mirip dengan form create; perlu penyesuaian token input dan label eksplisit. |
| 9 | **Nasabah (Detail/Show)** | `bank-sampah.customers.show` | Profil nasabah, koneksi akun, dan ringkasan tabungan | 🟡 REFINE | Kartu ringkasan finansial cukup baik, namun kartu profil kiri terlalu vertikal dan tombol aksi minim. |
| 10 | **Penjualan (List)** | `bank-sampah.sales.index` | Riwayat transaksi penjualan sampah ke agregator | 🟡 REFINE | Kolom margin kas belum menonjol; tabel desktop belum adaptif di mobile. |
| 11 | **Penjualan (Create)** | `bank-sampah.sales.create` | Muat timbangan truk penjualan ke agregator | 🔴 REDESIGN | Masih menggunakan **popup modal checkbox** untuk pemilihan material manual (berlawanan dengan prinsip One Click & anti-modal). |
| 12 | **Penjualan (Edit)** | `bank-sampah.sales.edit` | Perbaikan data muat timbangan penjualan | 🔴 REDESIGN | Mengulang pola modal create penjualan yang kaku; kalkulasi sisa stok rawan membingungkan. |
| 13 | **Agregator / Collector (List)** | `bank-sampah.collectors.index` | Daftar pengepul / mitra pembeli bank sampah | 🟡 REFINE | Sederhana dan fungsional, tetapi tombol WhatsApp dan kontak tidak konsisten styling-nya. |
| 14 | **Agregator (Create/Edit)** | `bank-sampah.collectors.create|edit` | Pendaftaran & pembaruan kontak agregator | 🟢 KEEP | Form standar 3 field (nama, telepon, alamat) yang bersih dan tidak berbelit-belit. |
| 15 | **Harga Sampah (List)** | `bank-sampah.waste-prices.index` | Katalog harga penawaran beli agregator per kategori | 🟡 REFINE | Memuat kartu "Grup Terbanyak" yang tidak esensial; margin chip bagus tapi teks sub-info riwayat terlalu kecil (10px). |
| 16 | **Harga Sampah (Create/Edit)** | `bank-sampah.waste-prices.create|edit` | Penetapan harga beli agregator per jenis material | 🟢 KEEP | Form ringkas 3 input menggunakan `x-rupiah-input`. |
| 17 | **Harga Sampah (Import)** | `bank-sampah.waste-prices.import` | Impor berkas penawaran harga agregator | 🟡 REFINE | Layout 3 seksi terlalu memanjang ke bawah; belum ada pratinjau tabel interaktif seperti impor setoran. |
| 18 | **Harga Sampah (Riwayat)** | `bank-sampah.waste-prices.history` | Audit log perubahan harga agregator | 🟢 KEEP | Tabel kronologis sangat bersih, audit perangkat jelas, filter agregator/material bekerja presisi. |
| 19 | **Kategori / Material (List)** | `bank-sampah.waste-categories.index` | Master jenis sampah & harga beli nasabah | 🟡 REFINE | Header kartu memuat statistik vanity ("Grup Terbanyak: Plastik"); belum menampilkan daftar alias yang terhubung. |
| 20 | **Kategori (Create/Edit)** | `bank-sampah.waste-categories.create|edit` | Tambah/ubah nama material resmi & harga beli | 🟢 KEEP | Form terstruktur dengan pemilihan grup induk dan generate kode otomatis. |
| 21 | **Grup Kategori (Index/CRUD)** | `bank-sampah.waste-category-groups.*` | Master kelompok sampah induk (Plastik, Kertas, dll.) | 🟢 KEEP | Tabel sederhana dengan toggle aktif/nonaktif yang bersih. |
| 22 | **Perangkat Saya (Device Index)** | `bank-sampah.devices.index` | Daftar browser/perangkat admin & cabut akses | 🟢 KEEP | Mengikuti prinsip audit context tanpa over-engineering; kartu perangkat jelas dan mudah dipahami. |
| 23 | **Beri Nama Perangkat (Device Name)** | `bank-sampah.devices.name` | Prompt penamaan perangkat baru saat login | 🟢 KEEP | Layar fokus satu tugas terisolasi (*one click / single purpose*). |
| 24 | **Laporan Kas & Transaksi** | `bank-sampah.reports.*` | Laporan Setoran, Penjualan, Tabungan, Arus Kas | 🟡 REFINE | Filter 5 kolom terlalu padat; ada typo syntax HTML pada kolom subtotal; format cetak perlu perapihan grid A4. |
| 25 | **Saldo Nasabah (Rekap Tabungan)** | `bank-sampah.savings.index` | Buku tabungan kolektif nasabah | 🟡 REFINE | Menampilkan baris loading Alpine custom yang tidak standar; avatar nasabah masih huruf generic. |
| 26 | **Pengeluaran Operasional** | `bank-sampah.expenses.index|create` | Pencatatan biaya operasional bank sampah | 🟡 REFINE | Memuat 4 kartu metrik berwarna-warni yang agak bising (*blue, emerald, rose, slate*). |
| 27 | **Portal Tabungan Warga** | `warga.savings.*` | Halaman pengecekan saldo & mutasi khusus warga | 🔴 REDESIGN | Terlalu besar (`rounded-3xl`, `text-5xl font-extrabold`, card bengkak). Sangat terisolasi dari bahasa desain utama. |
| 28 | **Cek Saldo Publik & Landing** | `cek-saldo.index` & `welcome` | Halaman publik tanpa login untuk cek saldo & info | 🟡 REFINE | Memuat animasi CSS `@keyframes float` yang tidak esensial dan gradien dekoratif berlebihan. |

---

## 3. Page-by-Page Audit (Audit Terperinci per Halaman)

---

### 3.1. Dashboard Bank Sampah (`dashboard/admin-bank-sampah.blade.php`)
* **Location:** `resources/views/dashboard/admin-bank-sampah.blade.php`
* **Route:** `GET /bank-sampah/dashboard` (`bank-sampah.dashboard`)
* **Purpose:** Pusat kendali operasional harian administrator bank sampah.
* **Primary Action:** Tombol *"Setoran Baru"* (hijau) & *"Jual ke Agregator"* di bagian hero header.
* **Current Pattern:** Dashboard bergaya analitik modern AI-generik dengan grafik ganda dan kartu bertingkat.
* **DESIGN.md Alignment:** **BURUK (Bertentangan dengan Seksi 19, 20, dan 31).**
* **Classification:** 🔴 **REDESIGN**
* **Findings:**
  1. *Anti-Slop Violation (Seksi 31)*: Memuat elemen dekoratif murni berupa lingkaran blur gradien:  
     `<div class="absolute right-0 top-0 w-64 h-64 bg-gradient-to-br from-blue-500/10 to-emerald-500/0 rounded-full blur-3xl ...">` (Baris 6).
  2. *Excessive Rounded Corners (Seksi 4.1)*: Menggunakan `rounded-[2rem]` (32px) pada hero dan kartu container (Baris 5, 93, 97, 106, 136).
  3. *Unnecessary Cascading Motion (Seksi 21)*: Setiap kartu memiliki penundaan animasi masuk buatan: `delay-[200ms]`, `delay-[300ms]`, ..., `delay-[1000ms]`, memperlambat interaksi operator.
  4. *Decorative / Irrational Charts (Seksi 20)*: ApexCharts donut *"Total Aset"* menjumlahkan Saldo Nasabah + Kas Operasional + Penjualan, yang secara akuntansi tidak memiliki makna operasional. Bar chart "Setoran vs Penarikan" hanya berisi 2 batang statis.
  5. *Action-First Inversion (Seksi 19)*: Menu Cepat (*Quick Actions*) yang merupakan kebutuhan kerja harian diletakkan di paling bawah setelah grafik (Baris 167–195).

---

### 3.2. Setoran Sampah Create (`bank-sampah/deposits/create.blade.php`)
* **Location:** `resources/views/bank-sampah/deposits/create.blade.php`
* **Route:** `GET /bank-sampah/deposits/create` (`bank-sampah.deposits.create`)
* **Purpose:** Entri timbangan multi-material nasabah aktif (Reference Workflow).
* **Primary Action:** *"Simpan & Lanjut Nasabah Berikutnya"* (Ctrl+Shift+Enter) & *"Simpan & Selesai"* (Ctrl+Enter).
* **Current Pattern:** Split-screen layout (Form kiri 65%, Rekap Hari Ini kanan 35%), inline combobox material, zero modal.
* **DESIGN.md Alignment:** **SANGAT BAIK (Menjadi rujukan Seksi 24), tetapi butuh perbaikan tipografi & token.**
* **Classification:** 🟡 **REFINE**
* **Findings:**
  1. *Pencarian Nasabah (Seksi 24.1)*: Masih berupa `<select>` HTML standar (Baris 67). DESIGN.md menginginkan *search-centered input* dengan *recent customers list*.
  2. *Tipografi Di Bawah Standar (Seksi 5.2)*: Seluruh input tabel dan teks baris menggunakan `text-xs` (12px) dan `text-sm` (14px). Belum mencapai baseline 16px (`text-base`).
  3. *Required Field Label (Seksi 5.5)*: Masih menggunakan `<span class="text-rose-500">*</span>` dan belum bertuliskan `Wajib diisi`.
  4. *Visual Override Label (Seksi 24.5)*: Saat harga diubah manual, hanya muncul badge kecil `[Edit]`, belum menampilkan pembanding resmi `Manual · Master RpX.XXX`.

---

### 3.3. Setoran Sampah Index (`bank-sampah/deposits/index.blade.php`)
* **Location:** `resources/views/bank-sampah/deposits/index.blade.php`
* **Route:** `GET /bank-sampah/deposits` (`bank-sampah.deposits.index`)
* **Purpose:** Daftar riwayat transaksi setoran nasabah.
* **Primary Action:** *"Catat Setoran Baru"* & *"Import Excel"*.
* **Current Pattern:** Tabel operasional padat dengan expandable drawer rincian material per baris.
* **DESIGN.md Alignment:** **BAIK (Sesuai Seksi 9 & 18).**
* **Classification:** 🟡 **REFINE**
* **Findings:**
  1. *Ukuran Teks (Seksi 5.2)*: Kolom tanggal dan ID menggunakan `text-xs` (12px) dan `text-[11px]`.
  2. *Toolbar Filter*: Filter tanggal dan search bar berada di dalam card tabel, belum terintegrasi sebagai header kontekstual halaman.
  3. *Badge Konsistensi*: Badge jumlah material masih berupa teks biasa, belum menggunakan sistem badge berikon.

---

### 3.4. Penjualan Create & Edit (`bank-sampah/sales/create.blade.php` & `edit.blade.php`)
* **Location:** `resources/views/bank-sampah/sales/create.blade.php`
* **Route:** `GET /bank-sampah/sales/create` (`bank-sampah.sales.create`)
* **Purpose:** Pencatatan muat truk sampah terpilah untuk dijual ke agregator.
* **Primary Action:** *"Simpan Penjualan"* & *"Tarik Rekap Setoran Hari Ini"*.
* **Current Pattern:** Form bertingkat dengan tombol modal popup checkbox untuk pemilihan kategori manual.
* **DESIGN.md Alignment:** **BURUK (Melanggar Seksi 11 Anti-Nested Modal & Seksi 2.2 Contextual Simplicity).**
* **Classification:** 🔴 **REDESIGN**
* **Findings:**
  1. *Modal Popup Checkbox (Seksi 11)*: Masih menggunakan tombol *"Pilih Manual"* yang memicu modal popup berisi daftar checkbox kategori (Baris 57, 180–233). Sangat lambat dan bertolak belakang dengan kemudahan inline grid Setoran.
  2. *Komunikasi Margin & Stok*: Kolom "Sisa Tersedia" dan "Margin Kas" terpisah jauh dari input berat jual.
  3. *Validasi Anti-Over*: Belum ada feedback visual real-time di antarmuka jika operator mengetik berat jual melebihi sisa fisik di gudang.

---

### 3.5. Import Setoran Wizard (`bank-sampah/deposits/import.blade.php`)
* **Location:** `resources/views/bank-sampah/deposits/import.blade.php`
* **Route:** `GET /bank-sampah/deposits/import` (`bank-sampah.deposits.import`)
* **Purpose:** Wizard impor massal catatan timbangan Excel operasional lapangan.
* **Primary Action:** *"Import Data Sekarang"* (pada Step 2).
* **Current Pattern:** Wizard 3 langkah (Upload $\rightarrow$ Preview & Mapping $\rightarrow$ Result).
* **DESIGN.md Alignment:** **BAIK SECARA ALUR, MEMERLUKAN PERAPIHAN DENSITAS.**
* **Classification:** 🟡 **REFINE**
* **Findings:**
  1. *Densitas Tabel Pratinjau (Seksi 2.3)*: Memuat 10 kolom dalam satu tabel (`Status`, `Nasabah`, `Material`, `Berat`, `Harga Excel`, `Harga Digunakan`, `Total Excel`, `Total Sistem`, `Keterangan`). Pada resolusi 1366x768 (laptop standar balai RW), kolom keterangan sering terhimpit.
  2. *Status Badge (Seksi 12)*: Badge status (`VALID`, `ALIAS`, `WARNING`, `MAPPING`, `ERROR`) hanya berupa teks dan warna, belum memadukan ikon representatif sesuai aturan `Badge + Icon + Text`.
  3. *Palet Warna*: Masih menggunakan variasi warna Tailwind murni (*purple, blue, amber, emerald, rose*).

---

### 3.6. Data Nasabah Index & Show (`bank-sampah/customers/index.blade.php` & `show.blade.php`)
* **Location:** `resources/views/bank-sampah/customers/index.blade.php`
* **Route:** `GET /bank-sampah/customers` (`bank-sampah.customers.index`)
* **Purpose:** Pengelolaan direktori nasabah bank sampah.
* **Primary Action:** *"Tambah Nasabah"*.
* **Current Pattern:** Tabel standar dengan avatar generic dan link aksi Edit/Hapus.
* **DESIGN.md Alignment:** **CUKUP, PERLU SENTUHAN WARM HUMAN.**
* **Classification:** 🟡 **REFINE**
* **Findings:**
  1. *Avatar Generic*: Kolom nasabah memuat ikon user statis di dalam bulatan biru muda (Baris 79–81). Tidak informatif. Seharusnya inisial nama atau penanda visual bermakna.
  2. *Empty State (Seksi 16)*: Saat data nasabah kosong, hanya muncul ikon dan teks *"Belum ada data nasabah Bank Sampah yang ditambahkan."*, tanpa tombol aksi `[+ Tambah Nasabah Sekarang]`.
  3. *Halaman Detail (Show)*: Kartu kiri menampilkan foto avatar placeholder SVG raksasa (w-20 h-20), membuang ruang horizontal yang berharga.

---

### 3.7. Agregator / Pengepul (`bank-sampah/collectors/index.blade.php`)
* **Location:** `resources/views/bank-sampah/collectors/index.blade.php`
* **Route:** `GET /bank-sampah/collectors` (`bank-sampah.collectors.index`)
* **Purpose:** Daftar mitra pembeli/pengepul sampah.
* **Primary Action:** *"Tambah Agregator"*.
* **Current Pattern:** Tabel 3 kolom (Nama/Kontak, Alamat, Aksi).
* **DESIGN.md Alignment:** **CUKUP.**
* **Classification:** 🟡 **REFINE**
* **Findings:**
  1. *WhatsApp Pill Link*: Tombol WhatsApp menggunakan badge hijau terang `bg-emerald-100 text-emerald-700` yang terlalu mencolok dan menyerupai badge status sukses.
  2. *Empty State*: Tidak memiliki tombol Call-to-Action langsung di dalam kotak kosong.

---

### 3.8. Harga Sampah Agregator (`bank-sampah/waste-prices/index.blade.php` & `history.blade.php`)
* **Location:** `resources/views/bank-sampah/waste-prices/index.blade.php`
* **Route:** `GET /bank-sampah/waste-prices` (`bank-sampah.waste-prices.index`)
* **Purpose:** Katalog harga beli agregator dan pantauan margin bank sampah.
* **Primary Action:** *"Tambah Harga"*, *"Import Harga"*, *"Riwayat Harga"*.
* **Current Pattern:** Header metrik grup, toolbar filter grup kategori, tabel harga dengan margin pill.
* **DESIGN.md Alignment:** **BAIK.**
* **Classification:** 🟡 **REFINE**
* **Findings:**
  1. *Metrik Tidak Relevan (Seksi 31)*: Kartu "Grup Terbanyak" di baris header tidak memiliki nilai aksi operasional.
  2. *Halaman Riwayat (`history.blade.php`)*: Sangat baik secara fungsi audit, namun font perubahan harga (`old -> new`) masih kecil (11px).

---

### 3.9. Perangkat Saya & Audit Context (`bank-sampah/devices/index.blade.php` & `name.blade.php`)
* **Location:** `resources/views/bank-sampah/devices/index.blade.php`
* **Route:** `GET /bank-sampah/devices` (`bank-sampah.devices.index`)
* **Purpose:** Pengelolaan perangkat browser admin & audit konteks penamaan perangkat.
* **Primary Action:** *"Cabut Akses"* (pada index) & *"Simpan Perangkat"* (pada name).
* **Current Pattern:** Grid kartu perangkat dengan indikator aktif live pulse.
* **DESIGN.md Alignment:** **SANGAT BAIK (Memenuhi Seksi 4, 17, dan Anti-Slop).**
* **Classification:** 🟢 **KEEP**
* **Findings:**
  - Desain sangat fokus pada tugas, tidak ada animasi berlebihan, status pencabutan jelas dan dilindungi konfirmasi browser standar.
  - Perlu sedikit penyesuaian warna ke palet earthy pada fase token polish.

---

### 3.10. Portal Warga — Saldo & Tabungan (`warga/savings/index.blade.php` & `history.blade.php`)
* **Location:** `resources/views/warga/savings/index.blade.php`
* **Route:** `GET /warga/savings` (`warga.savings`)
* **Purpose:** Antarmuka nasabah warga untuk mengecek saldo tabungan dan mutasi setoran.
* **Primary Action:** *"Lihat Semua Riwayat"*.
* **Current Pattern:** Hero card raksasa hijau tua, kartu metrik 3 kolom, riwayat transaksi list.
* **DESIGN.md Alignment:** **BURUK (Melanggar Seksi 4.1, 5.2, dan 22.2).**
* **Classification:** 🔴 **REDESIGN**
* **Findings:**
  1. *Disproporsi Visual*: Saldo ditampilkan dengan font `text-5xl font-extrabold` di dalam kartu hijau masif `bg-emerald-600 rounded-3xl p-8 shadow-lg`. Terlalu mencolok dan menghabiskan lebih dari setengah layar ponsel.
  2. *Elemen Dekoratif Berlebihan*: Ikon pada empty state dibungkus ring ganda `ring-8 ring-rose-50/50`.
  3. *Inkonsistensi Komponen*: Menggunakan komponen dan struktur kelas yang sama sekali berbeda dari modul Bank Sampah lainnya.

---

### 3.11. Cek Saldo Publik & Landing Page (`cek-saldo/index.blade.php` & `welcome.blade.php`)
* **Location:** `resources/views/cek-saldo/index.blade.php`
* **Route:** `GET /cek-saldo` (`cek-saldo.index`)
* **Purpose:** Pengecekan saldo tanpa login bagi warga menggunakan No. HP + Kode Nasabah.
* **Primary Action:** *"Periksa Saldo"*.
* **Current Pattern:** Halaman publik tunggal dengan kartu form di tengah dan backdrop blur.
* **DESIGN.md Alignment:** **CUKUP, PERLU PEMBERSIHAN DEKORATIF.**
* **Classification:** 🟡 **REFINE**
* **Findings:**
  1. *Gradien Dekoratif (Seksi 31)*: `bg-gradient-to-br from-emerald-50 via-white to-green-50`.
  2. *Animasi Melayang*: Terdapat inline CSS `@keyframes float` yang membuat elemen bergerak naik-turun tanpa fungsi operasional.
  3. *Tampilan Hasil*: Kartu hasil pengecekan saldo cukup bersih, namun perlu diselaraskan dengan token visual baru.

---

### 3.12. Modul Autentikasi (`resources/views/auth/*`)
* **Location:** `resources/views/auth/login.blade.php`, `forgot-password.blade.php`, `reset-password.blade.php`
* **Route:** `GET /login`, `GET /forgot-password`
* **Purpose:** Layar masuk akun pengurus/admin.
* **Primary Action:** *"Masuk"*.
* **Current Pattern:** Kartu tengah melayang dengan `backdrop-blur-sm`, `rounded-3xl`, dan gradien latar belakang.
* **DESIGN.md Alignment:** **CUKUP.**
* **Classification:** 🟡 **REFINE**
* **Findings:**
  - Tombol masuk memiliki efek hover `-translate-y-0.5 hover:shadow-lg` yang sedikit berlebihan.
  - Kartu menggunakan `rounded-3xl` (24px) yang terlalu bulat dibanding standar soft rounded (`rounded-xl`/`rounded-2xl`).

---

### 3.13. Laporan Bank Sampah (`resources/views/bank-sampah/reports/*`)
* **Location:** `resources/views/bank-sampah/reports/*.blade.php` (8 file)
* **Route:** `/bank-sampah/reports/deposits`, `/sales`, `/savings-journal`, `/cashflow`
* **Purpose:** Pelaporan agregasi setoran, penjualan, arus kas, dan jurnal tabungan nasabah.
* **Primary Action:** *"Filter Laporan"*, *"Cetak / Simpan PDF"*, *"Ekspor Excel"*.
* **Current Pattern:** Grid filter 5 kolom, kartu ringkasan 4 metrik, tabel data, tampilan cetak print preview terpisah.
* **DESIGN.md Alignment:** **CUKUP.**
* **Classification:** 🟡 **REFINE**
* **Findings:**
  1. *Typo Syntax HTML*: Pada `deposits.blade.php` baris 95–97 terdapat kesalahan penulisan class:  
     `<th class="px-6 py-4 text-right class text-xs font-semibold ...">`.
  2. *Grid Filter 5 Kolom*: Pada layar tablet/laptop 13-inci, filter 5 kolom menjadi sangat sempit sehingga tanggal terpotong.
  3. *Format Print (A4)*: Halaman print preview menggunakan tabel HTML murni yang fungsional, tetapi padding dan margin belum terkalibrasi presisi untuk lembar A4 portrait/landscape.

---

## 4. Component Audit

Inventarisasi seluruh komponen Blade yang ada di `resources/views/components/`:

| No | Component Name | File Location | Real Usage in Codebase | Classification | Finding & Analysis |
|:---:|---|---|---|:---:|---|
| 1 | `application-logo` | `components/application-logo.blade.php` | Auth views (`layouts/guest`) | 🟢 KEEP | Logo SVG sederhana, tidak bermasalah. |
| 2 | `auth-session-status` | `components/auth-session-status.blade.php` | Auth login/password | 🟢 KEEP | Menampilkan alert status sesi hijau standar. |
| 3 | `confirm-delete-modal` | `components/confirm-delete-modal.blade.php` | Hampir di seluruh tabel index CRUD | 🟢 KEEP | Sangat baik: menggunakan modal bahaya merah, teks peringatan dampak saldo, escape listener, dan tombol spinner saat delete. |
| 4 | `danger-button` | `components/danger-button.blade.php` | Profile delete form | 🟢 KEEP | Tombol merah standar Breeze. |
| 5 | `dropdown` & `dropdown-link` | `components/dropdown.blade.php` | Topbar user menu | 🟢 KEEP | Komponen dropdown Alpine standar. |
| 6 | `field-group` | `components/field-group.blade.php` | Categories, prices, expenses forms | 🟡 REFINE | Membantu merapikan form, namun penanda required masih menggunakan `<span class="text-rose-500">*</span>`, belum bertuliskan `(Wajib diisi)`. |
| 7 | `form-card` | `components/form-card.blade.php` | Categories, prices, sales create/edit | 🟡 REFINE | Wadah form putih dengan border halus, tetapi wrapper luarnya memiliki padding arbitrary `py-8 px-4`. |
| 8 | `form-section` | `components/form-section.blade.php` | Prices & import views | 🟢 KEEP | Pemisah seksi form bertingkat yang rapi. |
| 9 | `form-actions` | `components/form-actions.blade.php` | Categories, prices create/edit | 🟢 KEEP | Memiliki tombol Batal dan Simpan dengan spinner submit bawaan. |
| 10 | `input-error` | `components/input-error.blade.php` | Form fields | 🟢 KEEP | Menampilkan teks error merah di bawah input. |
| 11 | `input-label` | `components/input-label.blade.php` | Form fields | 🟡 REFINE | Label di atas input (bagus), namun belum mendukung teks eksplisit "Wajib diisi". |
| 12 | `modal` | `components/modal.blade.php` | Generic dialog | 🟡 REFINE | Komponen modal Breeze default, perlu integrasi ukuran adaptif (sm, md, lg). |
| 13 | `primary-button` | `components/primary-button.blade.php` | Auth & profile forms | 🟡 REFINE | Masih menggunakan styling hitam Breeze default (`bg-gray-800`), bukan warna primer brand EcoBank. |
| 14 | `secondary-button` | `components/secondary-button.blade.php` | Profile forms | 🟢 KEEP | Tombol abu-abu outline standar. |
| 15 | `rupiah-input` | `components/rupiah-input.blade.php` | Form kategori, harga, pengeluaran | 🟢 KEEP | Komponen interaktif format mata uang Rupiah otomatis via Alpine.js. Sangat berguna. |
| 16 | `sidebar-link` | `components/sidebar-link.blade.php` | `layouts/partials/sidebar` | 🟡 REFINE | Tautan navigasi sidebar, namun status aktifnya masih berupa fill hijau muda, belum terintegrasi dengan penanda garis aksen. |
| 17 | `table-toolbar` | `components/table-toolbar.blade.php` | Index categories, collectors, sales | 🟡 REFINE | Toolbar pencarian dasar satu input dengan tombol cari spinner. Tidak mendukung filter tanggal. |
| 18 | `text-input` | `components/text-input.blade.php` | Form auth | 🟢 KEEP | Input text standar Breeze. |
| 19 | `modern-summary-card` | `components/modern-summary-card.blade.php` | **0 Panggilan (Unused)** | 🔴 REDESIGN | Dead code boilerplate. Dibuat dengan varian warna-warni yang tidak dipakai oleh halaman mana pun. |
| 20 | `modern-table-toolbar` | `components/modern-table-toolbar.blade.php` | **0 Panggilan (Unused)** | 🔴 REDESIGN | Dead code boilerplate. Tidak pernah di-render. |
| 21 | `modern-badge` | `components/modern-badge.blade.php` | **0 Panggilan (Unused)** | 🔴 REDESIGN | Dead code boilerplate. |
| 22 | `modern-data-table` | `components/modern-data-table.blade.php` | **0 Panggilan (Unused)** | 🔴 REDESIGN | Dead code boilerplate. |
| 23 | `modern-page-header` | `components/modern-page-header.blade.php` | **0 Panggilan (Unused)** | 🔴 REDESIGN | Dead code boilerplate. |
| 24 | `modern-actions` | `components/modern-actions.blade.php` | **0 Panggilan (Unused)** | 🔴 REDESIGN | Dead code boilerplate. |
| 25 | `skeleton-card` | `components/skeleton-card.blade.php` | **0 Panggilan (Unused)** | 🟡 REFINE | Komponen skeleton yang valid, tetapi tidak pernah dipanggil di view mana pun. Harus diintegrasikan saat loading. |
| 26 | `skeleton-line` | `components/skeleton-line.blade.php` | **0 Panggilan (Unused)** | 🟡 REFINE | Belum terintegrasi. |
| 27 | `skeleton-table` | `components/skeleton-table.blade.php` | **0 Panggilan (Unused)** | 🟡 REFINE | Belum terintegrasi di tabel operasional. |
| 28 | `theme-reference` | `components/ui/theme-reference.blade.php` | **0 Panggilan (Unused)** | 🔴 REDESIGN | Berkas referensi tema terisolasi, tidak terpakai di runtime. |
| 29 | **Toast Component** | *(Belum ada file)* | Dibutuhkan di seluruh aplikasi | ⚪ MISSING | Tidak ada komponen toast mengambang untuk feedback simpan/mutasi instan. |
| 30 | **Breadcrumb Component**| *(Belum ada file)* | Dibutuhkan di header topbar | ⚪ MISSING | Belum ada navigasi breadcrumb hirarkis pada header. |
| 31 | **Searchable Customer**| *(Inline di form)* | Setoran Create & Filter Laporan | ⚪ MISSING | Dropdown pencarian nasabah cerdas dengan histori penimbang terbaru belum dijadikan komponen reusable. |

---

## 5. Design Token Audit

Pemeriksaan token visual aktual pada kode program dan konfigurasi Tailwind (`tailwind.config.js` & `resources/css/app.css`):

### 5.1. Colors (Warna)
* **Status Aktual**: Proyek saat ini murni menggunakan default Tailwind CSS palette tanpa ada kustomisasi tema earthy pada `tailwind.config.js`:
  ```javascript
  // tailwind.config.js saat ini:
  theme: {
      extend: {
          fontFamily: { sans: ['Figtree', ...defaultTheme.fontFamily.sans] },
      },
  }
  ```
* **Nilai Aktual yang Ditemukan di Kode**:
  - **Primary Action**: `bg-emerald-600` (`#059669`), `bg-emerald-700` (`#047857`), hover `bg-emerald-800` (`#065f46`).
  - **Surface / Background**: `bg-slate-50` (`#f8fafc`), `bg-white` (`#ffffff`), dark mode: `dark:bg-slate-900` (`#0f172a`), `dark:bg-slate-950` (`#020617`).
  - **Border**: `border-slate-200` (`#e2e8f0`), `border-slate-300` (`#cbd5e1`), dark: `dark:border-slate-700` (`#334155`), `dark:border-slate-800` (`#1e293b`).
  - **Text**: `text-slate-900` (`#0f172a`), `text-slate-800` (`#1e293b`), muted: `text-slate-500` (`#64748b`), `text-slate-400` (`#94a3b8`).
  - **Accent / Status Acak**: Banyak menggunakan `bg-blue-50`, `text-blue-600`, `bg-indigo-50`, `text-indigo-600`, `bg-purple-100`, `text-purple-700` secara tidak seragam.
* **Kesesuaian dengan `DESIGN.md` (Seksi 3.2)**:
  **PARTIAL / BELUM SELARAS.**  
  `DESIGN.md` secara eksplisit mewajibkan palet *earthy* (*warm green, cream/beige, dark brown/charcoal*). Saat ini palet aplikasi adalah *cold tech green + cool slate gray*. Belum ada token warna cream/beige atau charcoal.

### 5.2. Typography (Tipografi)
* **Font Family**: Menggunakan `Figtree, sans-serif` via Bunny Fonts. Sesuai dengan prinsip *Modern + Friendly*.
* **Font Sizes (Ukuran Aktual)**:
  - Heading 1 & 2: `text-2xl` (24px), `text-xl` (20px), `font-bold`.
  - Section title: `text-base` (16px), `text-sm` (14px), `font-semibold`.
  - Body text & Table cell: `text-sm` (14px) dan `text-xs` (12px).
  - Metadata, badges, & chip labels: `text-[10px]` dan `text-[11px]`.
  - Warga savings balance: `text-5xl` (48px).
* **Kesesuaian dengan `DESIGN.md` (Seksi 2.4 & 5.2)**:
  **TIDAK SESUAI (Pelanggaran Kritis Baseline Body 16px+)**.  
  Hampir 80% teks operasional aplikasi berada di angka 12px dan 14px. Metadata penting sering menggunakan ukuran mikroskopik `text-[10px]`.

### 5.3. Shape & Border Radius
* **Nilai Aktual yang Ditemukan**:
  - `rounded-md` (6px) $\rightarrow$ Pada tombol Breeze warisan.
  - `rounded-lg` (8px) $\rightarrow$ Pada input form dan tombol sekunder.
  - `rounded-xl` (12px) $\rightarrow$ Pada kartu metrik, input modern, dan form action.
  - `rounded-2xl` (16px) $\rightarrow$ Pada wadah tabel dan form card.
  - `rounded-3xl` (24px) $\rightarrow$ Pada kartu portal warga, kartu login, kartu profil hero.
  - `rounded-[2rem]` (32px) $\rightarrow$ Pada container dashboard hero, chart cards, audit logs hero.
  - `rounded-full` (9999px) $\rightarrow$ Pada badge status, avatar nasabah, progress bar.
* **Kesesuaian dengan `DESIGN.md` (Seksi 4.1)**:
  **TIDAK KONSISTEN.** Rentang radius melompat liar dari 6px hingga 32px. `DESIGN.md` meminta bentuk *slightly rounded / soft shapes* (umumnya 8px–12px/16px) dan melarang radius bengkak `rounded-[2rem]` atau `rounded-3xl`.

### 5.4. Shadows & Depth
* **Nilai Aktual yang Ditemukan**:
  - `shadow-xs`, `shadow-sm`, `shadow` (halus, bagus).
  - `shadow-md`, `shadow-lg`, `shadow-xl` (pada portal warga, auth login, dan hover card dashboard).
  - Shadow berwarna dekoratif: `shadow-emerald-600/20`, `shadow-blue-500/5` pada dashboard.
* **Kesesuaian dengan `DESIGN.md` (Seksi 4.2)**:
  Sebagian besar tabel operasional sudah mematuhi bayangan halus (`shadow-sm` + `border-slate-200`), namun halaman dashboard dan portal warga menggunakan elevasi dan bayangan berwarna yang berlebihan.

### 5.5. Breakpoints & Spacing
* **Breakpoints**: Menggunakan standar Tailwind: `sm` (640px), `md` (768px), `lg` (1024px), `xl` (1280px).
* **Spacing**:
  - Padding halaman: `p-4 sm:p-6` (cukup baik).
  - Jarak antar seksi: `space-y-6 sm:space-y-8`.
  - Padding tabel: bervariasi antara `py-2 px-2.5` (sangat padat di impor) hingga `py-4 px-6` (sangat longgar di customers index). Belum ada token spacing kontekstual terstandarisasi.

### 5.6. Motion & Animations
* **Nilai Aktual yang Ditemukan**:
  - `transition-colors duration-300`, `transition duration-150` (halus dan baik).
  - `@keyframes float` (pada landing page welcome - dekoratif).
  - `delay-[200ms]` s/d `delay-[1000ms]` (pada dashboard - gerakan artifisial yang tidak fungsional).

---

## 6. Responsive Audit (Desktop, Tablet, Mobile)

| Area / Halaman | Desktop ($\ge$ 1024px) | Tablet (768px – 1023px) | Mobile (< 768px) | Temuan & Potensi Masalah |
|---|---|---|---|---|
| **Layout Global** | Sidebar kiri kaku 16rem, konten utama scrollable. | Konten menyempit, sidebar masih terpasang. | Sidebar disembunyikan di balik tombol hamburger. | Sidebar desktop tidak bisa di-collapse untuk memberi ruang kerja tabel lebar. |
| **Setoran Create** | Split-screen (Kiri 65%, Kanan 35%). Sangat nyaman. | Panel kanan turun ke bawah form. Masih dapat dioperasikan. | Stacked vertikal. Tabel timbangan bisa di-scroll horizontal. | Pada mobile, input tabel timbangan membutuhkan zoom layar jika font tetap 12px. Perlu adaptive card input di mobile. |
| **Setoran Index** | Tabel 6 kolom penuh dengan expandable drawer. | Tabel penuh muat tanpa masalah. | Beralih ke **Mobile Card List** yang rapi. | Salah satu implementasi mobile terbaik di aplikasi saat ini. Tidak ada overflow horizontal. |
| **Penjualan Create** | Form 2 kolom + tabel muat truk. | Cukup padat. | Form bertumpuk, tombol "Tarik Rekap" dan "Pilih Manual" berdesakan di layar kecil. Modal manual sulit di-scroll di ponsel. |
| **Import Setoran** | Wizard 3 kolom kartu ringkasan + tabel pratinjau 10 kolom. | Tabel pratinjau mengalami overflow horizontal parah (10 kolom). | Di ponsel, tabel pratinjau hampir tidak bisa dibaca tanpa menggeser layar jauh ke kanan. |
| **Daftar Nasabah** | Tabel 5 kolom longgar (`px-6 py-4`). | Tabel mulai memakan lebar layar. | Mengandalkan scroll horizontal mentah. Kolom status dan aksi terdorong ke luar layar. |
| **Laporan Bank Sampah** | Filter 5 kolom sejajar. | Filter terpotong menjadi 2-3 baris yang canggung. | Filter bertumpuk menjadi 5 kotak input vertikal yang memakan seluruh viewport ponsel sebelum data terlihat. |
| **Portal Tabungan Warga** | Kartu saldo besar di tengah. | Cukup proporsional. | Kartu saldo `text-5xl` memakan seluruh layar pertama (*above the fold*). |

---

## 7. State Audit Matrix

Pemeriksaan kelengkapan state antarmuka pada halaman-halaman utama:

| Halaman | Default State | Loading State | Empty State | Error State | Success State | Validation State | Destructive Confirmation |
|---|:---:|:---:|:---:|:---:|:---:|:---:|
| **Dashboard** | Present | Partial (Animasi delay) | Missing | Missing | Missing | Missing | N/A |
| **Setoran (Index)** | Present | Missing | Present (Dengan CTA) | Present (Alert) | Present (Alert) | N/A | Present (Modal) |
| **Setoran (Create)** | Present | Missing | Present (Batas kosong) | Present (Banner) | Present (Alert) | Present (Inline) | N/A |
| **Setoran (Import)** | Present | Partial (Disabled button) | Present (Baris 0) | Present (Alert/Row) | Present (Hasil Step 3) | Present (Badge per row)| Present (Batal sesi) |
| **Nasabah (Index)** | Present | Missing | Partial (Tanpa CTA) | Present (Alert) | Present (Alert) | N/A | Present (Modal) |
| **Nasabah (Create)** | Present | Partial (Submit spinner) | N/A | Present (Inline) | Present (Alert) | Present (Inline) | N/A |
| **Nasabah (Show)** | Present | Missing | N/A | N/A | N/A | N/A | N/A |
| **Penjualan (Index)** | Present | Missing | Partial (Tanpa CTA) | Present (Alert) | Present (Alert) | N/A | Present (Modal) |
| **Penjualan (Create)** | Present | Partial ("Menghitung...") | Partial (Teks dashed) | Present (Alert list) | Present (Alert) | Present (Inline) | N/A |
| **Harga Sampah (Index)**| Present | Missing | Partial (Tanpa CTA) | Present (Alert) | Present (Alert) | N/A | Present (Modal) |
| **Harga (Riwayat)** | Present | Missing | Partial (Teks datar) | N/A | N/A | N/A | N/A |
| **Kategori Sampah** | Present | Missing | Partial (Tanpa CTA) | Present (Alert) | Present (Alert) | N/A | Present (Modal) |
| **Perangkat Saya** | Present | Missing | Present | N/A | Present (Alert) | N/A | Present (Browser Confirm)|
| **Laporan Setoran** | Present | Missing | Present (Teks) | Present (Alert) | N/A | Present (Date range) | N/A |

*Catatan Definisi:*  
* **Present**: Tersedia dan berfungsi dengan jelas.  
* **Partial**: Ada sebagian, tetapi belum memenuhi standar DESIGN.md (misal: empty state tanpa tombol CTA, atau loading hanya mengandalkan freeze layar).  
* **Missing**: Belum diimplementasikan sama sekali.

---

## 8. Setoran Reference Workflow Audit (Audit Alur Kerja Rujukan)

Seksi 24 dari `DESIGN.md` mendefinisikan modul Setoran sebagai implementasi rujukan (*reference workflow*). Berikut hasil komparasi detail alur implementasi saat ini terhadap target desain:

```text
Target Alur DESIGN.md                        Status Implementasi Saat Ini & Catatan Audit
──────────────────────────────────────────────────────────────────────────────────────────────────
1. Cari Nasabah                             -> 🟡 PARTIAL: Masih berupa <select> dropdown panjang.
                                               Belum berbasis pencarian instan dengan daftar nasabah terbaru.
2. Pilih Nasabah                            -> 🟢 MATCHED: Memilih nasabah mentrigger event Alpine.
3. Auto-focus Material                      -> 🟢 MATCHED: Kursor otomatis melompat ke baris material pertama.
4. Pilih Material                           -> 🟢 MATCHED: Combobox inline interaktif, navigasi keyboard (Enter/Arrow).
5. Masukkan Berat                           -> 🟢 MATCHED: Input angka desimal, auto-focus saat material terpilih.
6. Harga Master otomatis                    -> 🟢 MATCHED: Langsung terisi dari member_price tanpa klik mouse.
7. Optional manual override                 -> 🟡 PARTIAL: Override tersedia via link "Ubah", tetapi belum
                                               menampilkan teks pembanding "Manual · Master RpX.XXX".
8. Total otomatis                           -> 🟢 MATCHED: Subtotal baris dan grand total bawah terhitung instan.
9. Tambah Material (Enter)                  -> 🟢 MATCHED: Tekan Enter di berat langsung membuka baris baru di bawahnya.
10. Simpan & Lanjut Nasabah                 -> 🟢 MATCHED: Tombol Simpan & Lanjut (Ctrl+Shift+Enter) menyimpan data,
                                               mereset form, dan memperbarui rekap hari ini secara live.
11. Success Feedback                        -> 🟡 PARTIAL: Masih berupa banner blok hijau statis di atas form,
                                               belum menggunakan floating Toast yang tidak menggeser layout.
```

---

## 9. Material & Alias UI Audit

Hasil audit terhadap antarmuka pengelolaan material canonical dan alias:

1. **Prinsip Canonical ID Terjaga**:  
   Seluruh tabel transaksi (`deposit_details`, `sale_details`, `waste_prices`) murni mengikat pada `waste_category_id`. Tidak ada tabel transaksi yang menyimpan string mentah external.
2. **Kamus Alias Scoped Bekerja Sempurna di Backend**:  
   Tabel `material_aliases` memisahkan scope `BANK_SAMPAH` dan `AGGREGATOR` (`source_id = collectors.id`). Isolasi antar pengepul terbukti aman dan lulus pengujian 100%.
3. **Kekurangan pada UI Master Kategori (`waste-categories/index.blade.php`)**:  
   Halaman index kategori saat ini **belum menampilkan daftar alias yang terhubung** ke suatu kategori master. Operator tidak bisa melihat label apa saja yang sudah dipetakan ke *Kardus Bersih* kecuali membuka database atau masuk ke alur impor.
4. **Kekurangan pada UI Impor Harga Agregator (`waste-prices/import.blade.php`)**:  
   Meskipun backend parser sudah mendukung alias, tampilan impor harga agregator masih berupa form unduh/unggah berkas sederhana. Belum mengadopsi tabel pratinjau interaktif seperti yang dimiliki oleh Impor Setoran (`deposits/import.blade.php`).

---

## 10. Anti-Slop Audit (Pola Generik & Artifisial yang Terdeteksi)

Mengacu pada panduan Seksi 31 `DESIGN.md`, berikut adalah temuan konkret pola yang tidak sesuai dengan prinsip fungsional:

1. **Gradient Blur Circles (Latar Belakang Halusinatif)**:
   * `resources/views/dashboard/admin-bank-sampah.blade.php:6`:
     `<div class="absolute right-0 top-0 w-64 h-64 bg-gradient-to-br from-blue-500/10 to-emerald-500/0 rounded-full blur-3xl ...">`
   * `resources/views/admin/audit-logs.blade.php:6`:
     `<div class="absolute right-0 top-0 w-64 h-64 bg-gradient-to-br from-emerald-500/10 to-transparent rounded-full blur-3xl ...">`
   * `resources/views/welcome.blade.php:80`:
     `<div class="absolute right-0 top-0 w-96 h-96 bg-emerald-400/20 rounded-full blur-3xl ...">`
   * *Masalah*: Elemen dekoratif murni tanpa nilai informasi, menyebabkan render lag pada perangkat berdaya rendah.

2. **Corner Radius Raksasa (`rounded-[2rem]` & `rounded-3xl`)**:
   * Tersebar di `dashboard/admin-bank-sampah.blade.php`, `admin/audit-logs.blade.php`, dan `warga/savings/*.blade.php`.
   * *Masalah*: Sudut 32px memakan area sudut konten dan memberikan kesan main-main (*overly playful*), bertentangan dengan karakter *Modern Operational*.

3. **Cascading Animation Delay Spam**:
   * `dashboard/admin-bank-sampah.blade.php`:
     `delay-[200ms]`, `delay-[300ms]`, `delay-[400ms]`, `delay-[500ms]`, `delay-[600ms]`, `delay-[700ms]`, `delay-[800ms]`, `delay-[900ms]`, `delay-[1000ms]`.
   * *Masalah*: Memaksa operator menunggu 1 detik penuh hanya untuk melihat seluruh kartu dashboard muncul secara bertahap.

4. **Metrik Vanity / Tanpa Nilai Keputusan**:
   * `waste-categories/index.blade.php:51`: Kartu *"Grup Terbanyak: Plastik"*.
   * *Masalah*: Tidak ada tindakan atau keputusan yang bisa diambil operator dari mengetahui bahwa kelompok plastik memiliki item terbanyak.

5. **Komponen Boilerplate yang Tidak Terpakai**:
   * 9 file komponen di `resources/views/components/` dibuat hanya sebagai pajangan template dan tidak pernah diintegrasikan ke halaman nyata.

---

## 11. Cross-Cutting Issues (Isu Lintas Halaman)

1. **Tidak Adanya Sistem Token Earthy Terpusat**:
   Tidak ada konfigurasi warna *warm cream, beige, charcoal brown* di Tailwind. Seluruh halaman menggunakan paduan default `emerald` dan `slate` yang dingin.
2. **Ketiadaan Floating Toast Notification**:
   Setiap aksi berhasil (simpan, perbarui, hapus, impor) menampilkan banner kotak hijau statis di atas konten yang mendorong layout ke bawah sebesar 60–80px.
3. **Label Required Mengandalkan Bintang (`*`)**:
   Hampir 100% form menggunakan `<span class="text-rose-500">*</span>` dan tidak mematuhi aturan teks eksplisit `Wajib diisi`.
4. **Header Topbar Statis**:
   Topbar di seluruh modul admin hanya menampilkan judul teks polos tanpa breadcrumb, tanpa pencarian kontekstual, dan tanpa tombol aksi primer.
5. **Desktop Sidebar Fixed (Tidak Bisa Di-collapse)**:
   Lebar 256px memotong ruang pandang tabel data operasional pada layar laptop balai RW/kelurahan yang beresolusi 1280–1366px.

---

## 12. Keep / Refine / Redesign / Missing Summary

### Ringkasan Tabel Evaluasi:

| Area / Halaman | Klasifikasi | Bukti Faktual & Pertimbangan |
|---|:---:|---|
| **Setoran Create (`deposits/create`)** | 🟡 REFINE | Alur kerja keyboard-first & split screen sangat baik; perlu perbaikan tipografi baseline 16px, search-first customer, dan toast. |
| **Setoran Index (`deposits/index`)** | 🟡 REFINE | Hierarki visual nama nasabah dan drawer rincian sangat baik; perlu perapihan toolbar filter dan token visual. |
| **Setoran Import (`deposits/import`)** | 🟡 REFINE | Wizard & resolver pemetaan sangat fungsional; perlu perapihan densitas tabel pratinjau dan status badge ikonik. |
| **Dashboard (`dashboard/admin-bank-sampah`)** | 🔴 REDESIGN | Melanggar aturan Anti-Slop (radius 32px, gradient blur, animation delay spam, grafik dekoratif, quick actions di bawah). |
| **Penjualan Create/Edit (`sales/create|edit`)** | 🔴 REDESIGN | Masih mengandalkan modal popup checkbox multi-klik yang kaku untuk memilih material secara manual. |
| **Portal Tabungan Warga (`warga/savings/*`)** | 🔴 REDESIGN | Tipografi raksasa (text-5xl), kartu 3xl bengkak, bayangan tebal, terisolasi dari bahasa desain aplikasi. |
| **Data Nasabah (`customers/*`)** | 🟡 REFINE | Struktur CRUD lengkap; perlu pembersihan avatar generik, penambahan CTA pada empty state, dan perbaikan detail page. |
| **Agregator (`collectors/*`)** | 🟡 REFINE | Fungsional; perlu perbaikan styling tautan WhatsApp dan penambahan empty state CTA. |
| **Harga Sampah (`waste-prices/index`)** | 🟡 REFINE | Perlu menghapus metrik vanity ("Grup Terbanyak") dan menyelaraskan sub-teks audit perangkat. |
| **Harga Riwayat (`waste-prices/history`)** | 🟢 KEEP | Tabel audit log kronologis sangat bersih, filter bekerja presisi, struktur data immutable. |
| **Perangkat Saya (`devices/*`)** | 🟢 KEEP | Mengikuti prinsip audit context tanpa over-engineering; kartu perangkat jelas dan mudah dipahami. |
| **Kategori & Grup (`waste-categories/*`)** | 🟡 REFINE | Master canonical stabil; perlu visualisasi alias yang terhubung dan penghapusan kartu vanity. |
| **Pengeluaran (`expenses/*`)** | 🟡 REFINE | Terlalu banyak variasi warna kartu (4 warna berbeda); perlu penyelarasan palet netral. |
| **Laporan (`reports/*`)** | 🟡 REFINE | Filter 5 kolom terlalu padat di layar sedang; terdapat typo class HTML pada kolom subtotal; perlu kalibrasi print preview A4. |
| **Auth / Login (`auth/login`)** | 🟡 REFINE | Sederhana dan terpusat; perlu pengurangan radius kartu dari 3xl menjadi xl/2xl dan pembersihan efek hover berlebih. |
| **Floating Toast System** | ⚪ MISSING | Sistem umpan balik mengambang (toast) yang tidak mendorong layout belum ada di seluruh aplikasi. |
| **Collapsible Sidebar** | ⚪ MISSING | Fitur menciutkan/melebarkan sidebar desktop belum tersedia di layout utama. |
| **Contextual Topbar & Breadcrumbs** | ⚪ MISSING | Navigasi jejak rekam (breadcrumbs) dan aksi primer pada header topbar belum diimplementasikan. |
| **Earthy Design Tokens** | ⚪ MISSING | Konfigurasi token warna *earthy* (*warm green, cream, charcoal*) belum didefinisikan di `tailwind.config.js`. |

---

## 13. Recommended Redesign Sequence (Urutan Rekomendasi Redesain)

Berdasarkan dependensi arsitektur dan prinsip yang ditetapkan pada Seksi 33 `DESIGN.md`:

```text
TAHAP 1: PONDASI SISTEM DESAIN (FOUNDATION)
├── 1.1 Konfigurasi Token Desain di tailwind.config.js & app.css (Earthy palette, spacing, soft radius).
├── 1.2 Standarisasi Tipografi Baseline (Body text 16px+, moderate heading jumps).
├── 1.3 Pembuatan Komponen Inti Baru:
│       ├── Floating Toast System (Feedback tanpa layout shift)
│       ├── Contextual Header & Breadcrumb
│       └── Status Badge System (Badge + Icon + Text)
├── 1.4 Pembersihan Komponen Hantu (Hapus 9 komponen modern-* dan theme-reference yang tidak terpakai).
└── 1.5 Perbaikan Shell Layout (Sidebar desktop collapsible + topbar kontekstual).

TAHAP 2: VALIDASI PADA WORKFLOW RUJUKAN (SETORAN)
├── 2.1 Refine Setoran Create (Input nasabah search-centered + recent list, baseline 16px, label "Wajib diisi").
├── 2.2 Refine Setoran Index (Header kontekstual, status badge berikon, filter sticky).
└── 2.3 Refine Import Setoran Wizard (Optimasi densitas tabel pratinjau 10-kolom di resolusi 1366px).

TAHAP 3: REDESIGN & REFINE HALAMAN OPERASIONAL
├── 3.1 Redesign Penjualan Create/Edit (Hapus modal checkbox -> ganti inline row seperti Setoran).
├── 3.2 Refine Data Nasabah (Index, Create/Edit, Show: hapus avatar generik, rapikan kartu detail).
├── 3.3 Refine Harga Sampah & Kategori (Hapus kartu vanity "Grup Terbanyak", tampilkan connected aliases).
├── 3.4 Refine Laporan Bank Sampah (Kompakkan filter bar, perbaiki typo HTML, kalibrasi print preview A4).
└── 3.5 Refine Pengeluaran Operasional (Sederhanakan palet warna metrik).

TAHAP 4: OVERVIEW & PUBLIK (DASHBOARD & WARGA)
├── 4.1 Redesign Dashboard Bank Sampah (Action-first: Quick Actions di atas, hapus delay animasi, hapus chart dekoratif, hapus blur gradient).
├── 4.2 Redesign Portal Tabungan Warga (Proporsional, hapus card bengkak text-5xl, selaraskan dengan visual language baru).
└── 4.3 Refine Cek Saldo Publik & Login (Hapus animasi float, selaraskan radius ke soft/rounded-xl).

TAHAP 5: POLISH RESPONSIF & AKSESIBILITAS
├── 5.1 Pengujian Adaptive Mobile pada seluruh tabel (verifikasi stacked card di layar < 768px).
├── 5.2 Verifikasi Dark Mode di seluruh palet earthy baru.
└── 5.3 Audit Kontras Aksesibilitas & Anti-Slop Check.
```

---

## 14. Bukti Kode Faktual (Code Evidence)

1. **Typo Class HTML pada Laporan Setoran**:
   * *File*: `resources/views/bank-sampah/reports/deposits.blade.php:95–97`
   * *Kode*:
     ```blade
     <th class="px-6 py-4 text-right class text-xs font-semibold text-slate-500 uppercase">Berat</th>
     <th class="px-6 py-4 text-right class text-xs font-semibold text-slate-500 uppercase">Harga</th>
     <th class="px-6 py-4 text-right class text-xs font-semibold text-slate-500 uppercase">Subtotal</th>
     ```

2. **AI-Slop Blur Gradient & Excessive Radius pada Dashboard**:
   * *File*: `resources/views/dashboard/admin-bank-sampah.blade.php:5–6`
   * *Kode*:
     ```blade
     <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-700 relative overflow-hidden bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-200/60 dark:border-slate-700/60 p-6 sm:p-10 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6">
         <div class="absolute right-0 top-0 w-64 h-64 bg-gradient-to-br from-blue-500/10 to-emerald-500/0 rounded-full blur-3xl -translate-y-1/2 translate-x-1/3"></div>
     ```

3. **Chart Donut yang Tidak Rasional Secara Finansial**:
   * *File*: `resources/views/dashboard/admin-bank-sampah.blade.php:233–242`
   * *Kode*:
     ```javascript
     series: [{{ $totalSavings }}, {{ $wasteBankCashBalance }}, {{ $totalSales }}],
     labels: ['Saldo Nasabah', 'Kas Operasional', 'Penjualan'],
     // ...
     total: { show: true, label: 'Total Aset', formatter: function (w) { return 'Rp ' + ...reduce((a, b) => a + b, 0) } }
     ```

4. **Penggunaan Modal Checkbox Kuno pada Penjualan**:
   * *File*: `resources/views/bank-sampah/sales/create.blade.php:57–60`
   * *Kode*:
     ```blade
     <button type="button" @click="modalOpen = true" class="inline-flex items-center gap-1.5 bg-emerald-700 dark:bg-emerald-500 text-white px-3 py-1.5 rounded-lg text-xs font-medium hover:bg-emerald-800 ...">
         <svg class="w-3.5 h-3.5" ...><path d="M12 4v16m8-8H4"/></svg>
         Pilih Manual
     </button>
     ```

5. **Banner Alert Penolak Konten (Ketiadaan Toast)**:
   * *File*: `resources/views/bank-sampah/deposits/index.blade.php:16–21`
   * *Kode*:
     ```blade
     @if(session('success'))
         <div class="p-4 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 rounded-xl flex items-center gap-3 shadow-xs">
             <svg ...></svg>
             <div class="text-sm font-medium">{{ session('success') }}</div>
         </div>
     @endif
     ```

---
*Laporan audit ini disusun sebagai dokumen dasar objektif sebelum memulai pengerjaan Tahap 1 Redesain Antarmuka EcoBank026.*
