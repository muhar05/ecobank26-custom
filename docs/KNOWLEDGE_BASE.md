# KNOWLEDGE BASE: ECOBANK026

Dokumen ini berfungsi sebagai basis pengetahuan teknis (*technical knowledge base*) komprehensif untuk sistem **EcoBank026**. Dokumen ini dirancang secara khusus agar AI lain dapat langsung memahami arsitektur, skema database, aturan bisnis, dan alur kerja (*workflows*) sistem secara mendalam tanpa perlu memindai seluruh *source code* aplikasi dari awal.

---

## 1. GAMBARAN UMUM SISTEM (SYSTEM OVERVIEW)

**EcoBank026** adalah aplikasi berbasis web yang menggabungkan dua domain fungsional kemasyarakatan di tingkat RT/RW:
1. **Sistem Kas & Tagihan Lingkungan**: Digitalisasi iuran berkala warga, pengelolaan kas, otomatisasi pembuatan tagihan bulanan per Kartu Keluarga (KK), dan pelaporan transparansi kas.
2. **Sistem Bank Sampah & Tabungan**: Pemberdayaan ekonomi warga melalui tabungan sampah terpilah, penjualan sampah terakumulasi ke pengepul skala besar menggunakan model *dual-pricing* untuk memanen margin laba, serta audit konsistensi saldo otomatis.

### Spesifikasi Teknis Inti
- **Framework & Runtime**: Laravel v13.8 (PHP >= 8.3)
- **Database**: Relasional (MySQL)
- **Front-end**: Blade Templates, Tailwind CSS, Vite.js
- **Sistem Autentikasi**: Menggunakan nomor telepon (`phone`) sebagai identitas utama login (bukan email). Sebelum validasi kredensial, nomor telepon akan di-normalisasi secara konsisten (menghapus karakter non-numerik, mengubah awalan `620` atau `62` menjadi `0`).
- **Pola Desain**: MVC (Model-View-Controller) dikombinasikan dengan **Service Layer Pattern** untuk menampung seluruh operasi transaksional database yang kompleks guna menjamin isolasi transaksi (*Database Transactions*).

---

## 2. ROLE PENGGUNA (USER ROLES & RBAC)

Sistem ini menerapkan pembatasan hak akses berbasis peran menggunakan package `spatie/laravel-permission` dengan 6 peran terdefinisi:

1. **Admin RW (`admin_rw`)**
   - Otoritas: Akses tanpa batas (*superuser*).
   - Cakupan: Mengelola pengaturan aplikasi global, log audit jejak trail seluruh aktivitas, data kependudukan lintas RT, keuangan kas global, serta operasional penuh bank sampah.
2. **Admin RT (`admin_rt`)**
   - Otoritas: Terbatas pada Rukun Tetangga yang diwakili oleh kolom `rt_id` pada tabel `users`.
   - Cakupan: Mengelola data warga, KK, iuran kas wajib/sukarela RT sendiri, serta melihat laporan keuangan RT. Tidak memiliki hak menulis pada transaksi inti bank sampah (setoran, penarikan, penjualan).
3. **Bendahara RW / RT (`bendahara_rw` / `bendahara`)**
   - Otoritas: Manajer keuangan kas warga.
   - Cakupan: Mengelola kategori kas, mencatat iuran warga, menyetujui pembayaran tagihan iuran bulanan, mencatat pengeluaran kas, serta mengekspor rekapitulasi laporan tahunan kas warga.
4. **Admin Bank Sampah (`admin_bank_sampah`)**
   - Otoritas: Petugas teknis operasional bank sampah.
   - Cakupan: Mengelola profil nasabah (`WasteCustomer`), memperbarui daftar harga sampah pengepul/nasabah, mencatat transaksi setoran sampah (`deposits`), penarikan saldo tabungan (`withdrawals`), biaya operasional bank sampah (`waste_bank_expenses`), dan penjualan sampah massal ke pengepul (`sales`).
5. **Warga (`warga`)**
   - Otoritas: Anggota masyarakat terdaftar.
   - Cakupan: Hanya memiliki hak akses baca (*read-only*) ke portal mandiri untuk melihat rekap tagihan KK miliknya, total saldo tabungan sampah pribadi, riwayat mutasi tabungan, serta laporan arus kas lingkungan demi transparansi.

---

## 3. MODUL APLIKASI (APPLICATION MODULES)

Sistem dibagi menjadi 4 modul fungsional terintegrasi:

```
                      +---------------------------------------+
                      |         Manajemen Akun & Log          |
                      |   (Users, Activity Logs, Settings)    |
                      +-------------------+-------------------+
                                          |
                                          v
                      +---------------------------------------+
                      |       Kependudukan & Demografi        |
                      |          (RT, KK, Warga/Member)       |
                      +-------+-----------------------+-------+
                              |                       |
                              v                       v
            +---------------------------+   +---------------------------+
            |        Kas & Tagihan      |   |        Bank Sampah        |
            | (Fund Category, Bills,    |   |    (Customer, Deposit,    |
            |  Contributions, Expenses) |   |    Withdrawal, Sales)     |
            +---------------------------+   +---------------------------+
```

### A. Modul Kependudukan
Mengatur struktur demografi warga.
- **Entitas**: `Rt`, `Kk` (Kartu Keluarga), `Member` (Warga).
- **Fitur Kunci**: Bulk Excel import dengan manajemen baris gagal, pembuatan kredensial login otomatis untuk warga, dan pengelompokan umur demografis otomatis (balita, anak, remaja, dewasa, lansia) untuk keperluan statistik.

### B. Modul Kas & Tagihan Warga
Sistem penagihan keuangan kemasyarakatan.
- **Entitas**: `FundCategory`, `Bill` (Tagihan), `BillPayment`, `CommunityContribution`, `CommunityExpense`, `CommunityCashLedger`.
- **Fitur Kunci**: Otomatisasi generate iuran bulanan massal per KK berdasarkan jenis kas bertanda wajib (`is_mandatory = true`), pelacakan tunggakan iuran, dan pembukuan kas otomatis (*cash ledger*).

### C. Modul Bank Sampah
Sistem pemberdayaan ekonomi sirkular.
- **Entitas**: `WasteCustomer`, `Collector` (Pengepul), `WasteCategoryGroup`, `WasteCategory`, `WastePrice`, `Deposit`, `DepositDetail`, `Withdrawal`, `SavingsLedger`, `Sale`, `SaleDetail`, `WasteBankCashLedger`, `WasteBankExpense`.
- **Fitur Kunci**: Manajemen katalog harga jual-beli sampah terpilah, buku tabungan sampah digital nasabah, audit mandiri konsistensi saldo otomatis, dan kalkulasi profit margin penjualan sampah ke pengepul besar.

### D. Modul Audit & Keamanan
Infrastruktur kestabilan dan akuntabilitas sistem.
- **Entitas**: `ActivityLog`, `AppSetting`, `ImportHistory`.
- **Fitur Kunci**: Log audit *immutable* (tidak bisa dimodifikasi/dihapus) yang mengikat IP, user agent, data sebelum-sesudah tindakan, serta konfigurasi parameter global yang tersimpan dalam database dinamis.

---

## 4. STRUKTUR DATABASE & RELASI (DATABASE STRUCTURE)

Berikut adalah definisi struktur fisik tabel kustom dan relasi antar entitas yang benar-benar diimplementasikan dalam database.

### A. Skema Tabel Kependudukan

#### 1. Tabel `rts`
- **Tujuan**: Mengelompokkan warga berdasarkan wilayah RT.
- **Skema**:
  - `id` (bigint, Primary Key, Auto Increment)
  - `rt_number` (varchar, Unique) - Nomor RT (misal: '001')
  - `description` (text, Nullable) - Keterangan RT
  - `created_at` / `updated_at` (timestamps)

#### 2. Tabel `kks`
- **Tujuan**: Data Kartu Keluarga penanggung jawab iuran wajib.
- **Skema**:
  - `id` (bigint, Primary Key, Auto Increment)
  - `rt_id` (bigint, Foreign Key to `rts.id`)
  - `kk_number` (varchar, Unique) - 16 digit nomor KK
  - `family_head` (varchar) - Nama Kepala Keluarga
  - `address` (text) - Alamat rumah domisili
  - `phone` (varchar) - Nomor telepon perwakilan KK
  - `status` (enum: 'active', 'kontrak', 'pindah', 'kosong')
  - `created_at` / `updated_at` (timestamps)

#### 3. Tabel `members`
- **Tujuan**: Anggota warga di dalam KK. Mendukung soft deletes.
- **Skema**:
  - `id` (bigint, Primary Key, Auto Increment)
  - `user_id` (bigint, Foreign Key to `users.id`, Nullable) - Relasi ke akun login
  - `kk_id` (bigint, Foreign Key to `kks.id`) - Pengikat kelompok KK
  - `member_code` (varchar, Unique) - Kode registrasi warga (misal: 'WRG026')
  - `name` (varchar) - Nama lengkap
  - `phone` (varchar, Nullable) - Nomor telepon pribadi
  - `birth_date` (date, Nullable) - Tanggal lahir warga
  - `gender` (enum: 'L', 'P')
  - `address` (text, Nullable)
  - `relationship` (varchar) - Hubungan keluarga (misal: 'Kepala Keluarga', 'Istri', 'Anak')
  - `deleted_at` (timestamp, Nullable) - Untuk Soft Deletes
  - `created_at` / `updated_at` (timestamps)

#### 4. Tabel `users`
- **Tujuan**: Menyimpan kredensial otentikasi login.
- **Skema**:
  - `id` (bigint, Primary Key, Auto Increment)
  - `rt_id` (bigint, Foreign Key to `rts.id`, Nullable) - Penentu scope wilayah administrasi
  - `name` (varchar) - Nama user
  - `email` (varchar, Nullable)
  - `phone` (varchar, Unique) - Kontak login utama
  - `password` (varchar) - Sandi ter-hash
  - `remember_token` (varchar, Nullable)
  - `created_at` / `updated_at` (timestamps)

---

### B. Skema Tabel Keuangan & Kas Warga

#### 5. Tabel `fund_categories`
- **Tujuan**: Katalog jenis iuran warga.
- **Skema**:
  - `id` (bigint, Primary Key, Auto Increment)
  - `rt_id` (bigint, Foreign Key to `rts.id`, Nullable) - NULL berarti iuran berlaku global tingkat RW
  - `name` (varchar) - Nama jenis iuran (misal: 'Iuran Kebersihan')
  - `description` (text, Nullable)
  - `target_amount` (decimal(15,2), Nullable) - Target dana terkumpul
  - `is_active` (boolean, default: true)
  - `is_mandatory` (boolean, default: false) - Menandai apakah diterbitkan tagihan berkala otomatis
  - `monthly_amount` (decimal(15,2), default: 0.00) - Nominal iuran berkala wajib
  - `created_at` / `updated_at` (timestamps)

#### 6. Tabel `community_contributions`
- **Tujuan**: Catatan setoran iuran masuk dari warga.
- **Skema**:
  - `id` (bigint, Primary Key, Auto Increment)
  - `fund_category_id` (bigint, Foreign Key to `fund_categories.id`)
  - `rt_id` (bigint, Foreign Key to `rts.id`, Nullable) - Penanda scope transaksi RT
  - `member_id` (bigint, Foreign Key to `members.id`, Nullable) - Warga yang membayar iuran
  - `member_name` (varchar) - Nama pembayar (fallback jika `member_id` kosong)
  - `amount` (decimal(15,2)) - Besaran iuran masuk
  - `date` (date) - Tanggal transaksi iuran masuk
  - `description` (text, Nullable)
  - `recorded_by` (bigint, Foreign Key to `users.id`) - Admin pencatat
  - `created_at` / `updated_at` (timestamps)

#### 7. Tabel `community_expenses`
- **Tujuan**: Catatan pengeluaran kas lingkungan.
- **Skema**:
  - `id` (bigint, Primary Key, Auto Increment)
  - `fund_category_id` (bigint, Foreign Key to `fund_categories.id`)
  - `rt_id` (bigint, Foreign Key to `rts.id`, Nullable) - Penanda scope transaksi RT
  - `amount` (decimal(15,2)) - Besaran pengeluaran
  - `date` (date) - Tanggal transaksi keluar
  - `description` (text) - Tujuan alokasi dana keluar
  - `recorded_by` (bigint, Foreign Key to `users.id`) - Admin pencatat
  - `created_at` / `updated_at` (timestamps)

#### 8. Tabel `community_cash_ledgers`
- **Tujuan**: Pembukuan ganda untuk menghitung saldo berjalan kas warga per kategori dana secara konsisten.
- **Skema**:
  - `id` (bigint, Primary Key, Auto Increment)
  - `fund_category_id` (bigint, Foreign Key to `fund_categories.id`)
  - `type` (enum: 'in', 'out') - Arah arus kas
  - `amount` (decimal(15,2)) - Nominal mutasi
  - `balance` (decimal(15,2)) - Saldo kas berjalan hasil akumulasi pasca transaksi
  - `reference_type` (enum: 'contribution', 'expense') - Rujukan model asal iuran/pengeluaran
  - `reference_id` (bigint) - ID record dari tabel `community_contributions` atau `community_expenses`
  - `date` (date)
  - `description` (varchar)
  - `created_at` / `updated_at` (timestamps)

#### 9. Tabel `bills`
- **Tujuan**: Record tagihan iuran wajib KK per bulan.
- **Skema**:
  - `id` (bigint, Primary Key, Auto Increment)
  - `kk_id` (bigint, Foreign Key to `kks.id`)
  - `fund_category_id` (bigint, Foreign Key to `fund_categories.id`)
  - `bill_code` (varchar, Unique) - Nomor tagihan sistem (format: 'BILL-[PERIOD]-[RT]-[INC]')
  - `amount` (decimal(15,2)) - Nominal tagihan awal
  - `due_date` (date) - Tanggal batas jatuh tempo bayar
  - `month` (integer) - Periode bulan tagihan (1-12)
  - `year` (integer) - Periode tahun tagihan (YYYY)
  - `status` (enum: 'unpaid', 'partially_paid', 'paid')
  - `created_at` / `updated_at` (timestamps)

#### 10. Tabel `bill_payments`
- **Tujuan**: Riwayat pembayaran/pelunasan cicilan tagihan bulanan.
- **Skema**:
  - `id` (bigint, Primary Key, Auto Increment)
  - `bill_id` (bigint, Foreign Key to `bills.id`)
  - `community_contribution_id` (bigint, Foreign Key to `community_contributions.id`) - Penghubung ke jurnal kas iuran masuk
  - `receipt_number` (varchar, Unique) - Kode kuitansi resmi (format: 'RCPT-[PERIOD]-[INC]')
  - `amount_paid` (decimal(15,2)) - Nominal yang dibayarkan
  - `payment_method` (varchar) - Metode ('cash', 'transfer', 'qris')
  - `paid_at` (datetime) - Waktu transaksi bayar
  - `created_at` / `updated_at` (timestamps)

---

### C. Skema Tabel Bank Sampah

#### 11. Tabel `collectors`
- **Tujuan**: Menyimpan katalog mitra pengepul tempat menjual tumpukan sampah.
- **Skema**:
  - `id` (bigint, Primary Key, Auto Increment)
  - `name` (varchar) - Nama pengepul/perusahaan
  - `phone` (varchar, Nullable)
  - `address` (text, Nullable)
  - `deleted_at` (timestamp, Nullable) - Untuk Soft Deletes
  - `created_at` / `updated_at` (timestamps)

#### 12. Tabel `waste_category_groups`
- **Tujuan**: Induk klasifikasi sampah.
- **Skema**:
  - `id` (bigint, Primary Key, Auto Increment)
  - `code` (varchar, Unique) - Kode klasifikasi (misal: 'PLS', 'LOG')
  - `name` (varchar) - Nama kelompok sampah
  - `description` (text, Nullable)
  - `is_active` (boolean, default: true)
  - `created_at` / `updated_at` (timestamps)

#### 13. Tabel `waste_categories`
- **Tujuan**: Katalog jenis sampah siap tabung.
- **Skema**:
  - `id` (bigint, Primary Key, Auto Increment)
  - `waste_category_group_id` (bigint, Foreign Key to `waste_category_groups.id`, Nullable)
  - `code` (varchar, Unique) - Kode item (misal: 'PLS.01', 'KRT.02')
  - `name` (varchar) - Nama jenis sampah (misal: 'Gelas Plastik Bersih')
  - `unit` (varchar) - Satuan ukur (misal: 'kg', 'pcs')
  - `category_group` (varchar, Nullable) - Legacy group string
  - `created_at` / `updated_at` (timestamps)

#### 14. Tabel `waste_prices`
- **Tujuan**: Katalog harga dual-pricing sampah terikat per mitra pengepul.
- **Skema**:
  - `id` (bigint, Primary Key, Auto Increment)
  - `waste_category_id` (bigint, Foreign Key to `waste_categories.id`)
  - `collector_id` (bigint, Foreign Key to `collectors.id`)
  - `price_per_unit` (decimal(15,2), default: 0.00) - Legacy price
  - `member_price` (decimal(15,2)) - Harga beli yang diberikan kepada nasabah
  - `collector_price` (decimal(15,2)) - Harga jual yang didapatkan dari pengepul besar
  - `created_at` / `updated_at` (timestamps)

#### 15. Tabel `waste_customers`
- **Tujuan**: Profil nasabah bank sampah resmi.
- **Skema**:
  - `id` (bigint, Primary Key, Auto Increment)
  - `user_id` (bigint, Foreign Key to `users.id`, Nullable) - Akun masuk nasabah
  - `member_id` (bigint, Foreign Key to `members.id`, Nullable) - Menghubungkan nasabah ke data warga
  - `customer_code` (varchar, Unique) - Kode nasabah otomatis (format: 'NSB-[PERIOD]-[INC]')
  - `name` (varchar) - Nama lengkap nasabah
  - `phone` (varchar) - Nomor kontak nasabah
  - `address` (text) - Alamat tempat tinggal nasabah
  - `status` (varchar) - Status nasabah ('active', 'inactive')
  - `joined_at` (datetime) - Tanggal daftar pertama kali
  - `created_at` / `updated_at` (timestamps)

#### 16. Tabel `deposits`
- **Tujuan**: Transaksi setoran sampah nasabah ke bank sampah.
- **Skema**:
  - `id` (bigint, Primary Key, Auto Increment)
  - `member_id` (bigint, Foreign Key to `members.id`, Nullable) - Legacy member link
  - `waste_customer_id` (bigint, Foreign Key to `waste_customers.id`, Nullable) - Target utama nasabah penyetor
  - `collector_id` (bigint, Foreign Key to `collectors.id`) - Sponsor harga beli pada transaksi
  - `date` (date) - Tanggal transaksi
  - `total_amount` (decimal(15,2)) - Total rupiah tabungan hasil penyetoran
  - `notes` (text, Nullable)
  - `created_at` / `updated_at` (timestamps)

#### 17. Tabel `deposit_details`
- **Tujuan**: Detail jenis sampah dan berat yang disetorkan.
- **Skema**:
  - `id` (bigint, Primary Key, Auto Increment)
  - `deposit_id` (bigint, Foreign Key to `deposits.id`)
  - `waste_category_id` (bigint, Foreign Key to `waste_categories.id`)
  - `weight` (decimal(8,2)) - Berat/kuantitas sampah
  - `price_per_unit` (decimal(15,2)) - Disalin langsung dari `waste_prices.member_price`
  - `subtotal` (decimal(15,2)) - Total rupiah baris (`weight * price_per_unit`)
  - `created_at` / `updated_at` (timestamps)

#### 18. Tabel `withdrawals`
- **Tujuan**: Penarikan tunai saldo tabungan sampah nasabah.
- **Skema**:
  - `id` (bigint, Primary Key, Auto Increment)
  - `member_id` (bigint, Foreign Key to `members.id`, Nullable)
  - `waste_customer_id` (bigint, Foreign Key to `waste_customers.id`, Nullable)
  - `amount` (decimal(15,2)) - Nominal rupiah yang ditarik tunai
  - `date` (date) - Tanggal transaksi penarikan
  - `notes` (text, Nullable)
  - `created_at` / `updated_at` (timestamps)

#### 19. Tabel `savings_ledgers`
- **Tujuan**: Buku besar tabungan nasabah. Mencatat mutasi credit (setoran) dan debit (penarikan saldo).
- **Skema**:
  - `id` (bigint, Primary Key, Auto Increment)
  - `member_id` (bigint, Foreign Key to `members.id`, Nullable)
  - `waste_customer_id` (bigint, Foreign Key to `waste_customers.id`, Nullable)
  - `type` (enum: 'credit', 'debit') - Penambah/pengurang saldo tabungan
  - `amount` (decimal(15,2)) - Nominal mutasi
  - `description` (varchar) - Alasan/keterangan mutasi saldo
  - `reference_type` (varchar) - Nama model pembuat mutasi ('App\Models\Deposit', 'App\Models\Withdrawal')
  - `reference_id` (bigint) - ID instansi model pembuat mutasi
  - `created_at` / `updated_at` (timestamps)

#### 20. Tabel `sales`
- **Tujuan**: Transaksi penjualan tumpukan sampah terpilah di gudang ke pengepul skala besar.
- **Skema**:
  - `id` (bigint, Primary Key, Auto Increment)
  - `collector_id` (bigint, Foreign Key to `collectors.id`) - Pengepul pembeli
  - `date` (date) - Tanggal transaksi penjualan
  - `total_amount` (decimal(15,2)) - Total nominal yang didapatkan dari pengepul
  - `notes` (text, Nullable)
  - `created_at` / `updated_at` (timestamps)

#### 21. Tabel `sale_details`
- **Tujuan**: Rincian jenis dan berat sampah yang dijual ke pengepul.
- **Skema**:
  - `id` (bigint, Primary Key, Auto Increment)
  - `sale_id` (bigint, Foreign Key to `sales.id`)
  - `waste_category_id` (bigint, Foreign Key to `waste_categories.id`)
  - `weight` (decimal(8,2)) - Total berat sampah yang dijual
  - `price_per_unit` (decimal(15,2)) - Disalin langsung dari `waste_prices.collector_price`
  - `subtotal` (decimal(15,2)) - Nilai jual baris (`weight * price_per_unit`)
  - `created_at` / `updated_at` (timestamps)

#### 22. Tabel `waste_bank_cash_ledgers`
- **Tujuan**: Buku besar rekap kas operasional nyata bank sampah. Menyimpan margin keuntungan penjualan sampah sebagai pemasukan, dan biaya operasional sebagai pengeluaran.
- **Skema**:
  - `id` (bigint, Primary Key, Auto Increment)
  - `type` (enum: 'in', 'out') - Arah arus kas nyata bank sampah
  - `amount` (decimal(15,2)) - Besaran mutasi kas
  - `balance` (decimal(15,2)) - Saldo kas operasional nyata berjalan
  - `reference_type` (varchar, Nullable) - Sumber mutasi (Sale atau WasteBankExpense)
  - `reference_id` (bigint, Nullable)
  - `date` (date)
  - `description` (varchar)
  - `created_at` / `updated_at` (timestamps)

#### 23. Tabel `waste_bank_expenses`
- **Tujuan**: Pengeluaran operasional bank sampah.
- **Skema**:
  - `id` (bigint, Primary Key, Auto Increment)
  - `expense_code` (varchar, Unique) - Kode pengeluaran resmi (format: 'EXP-[PERIOD]-[INC]')
  - `amount` (decimal(15,2)) - Nominal biaya keluar
  - `description` (text) - Deskripsi operasional (seperti beli timbangan)
  - `expense_date` (date)
  - `recorded_by` (bigint, Foreign Key to `users.id`) - Petugas pencatat
  - `proof_path` (varchar, Nullable) - Path file unggahan nota fisik
  - `created_at` / `updated_at` (timestamps)

---

### D. Skema Tabel Keamanan & Penunjang

#### 24. Tabel `activity_logs`
- **Tujuan**: Jejak audit (*audit trail*) sistem yang bersifat *immutable* (tidak bisa di-update/delete).
- **Skema**:
  - `id` (bigint, Primary Key, Auto Increment)
  - `user_id` (bigint, Foreign Key to `users.id`, Nullable) - Aktor yang melakukan aksi (NULL untuk sistem)
  - `severity` (enum: 'info', 'warning', 'critical')
  - `event_type` (varchar) - Jenis aktivitas (misal: 'deposit.create', 'bill.payment')
  - `description` (text) - Narasi aktivitas
  - `ip_address` (varchar, Nullable)
  - `user_agent` (varchar, Nullable)
  - `correlation_id` (varchar, Nullable) - UUID pengait rangkaian transaksi
  - `payload` (json, Nullable) - Payload log perubahan data (before vs after)
  - `created_at` (datetime) - Immutable, no updated_at.

#### 25. Tabel `app_settings`
- **Tujuan**: Pengaturan parameter aplikasi dinamis di database dengan mekanisme cache invalidation.
- **Skema**:
  - `id` (bigint, Primary Key, Auto Increment)
  - `key` (varchar, Unique) - Kunci setelan (misal: 'billing.default_due_days')
  - `value` (text, Nullable) - Nilai setelan
  - `type` (varchar) - Tipe data untuk casting ('string', 'int', 'boolean', 'float')
  - `setting_group` (varchar, Nullable)
  - `created_at` / `updated_at` (timestamps)

#### 26. Tabel `import_histories`
- **Tujuan**: Log riwayat import data warga/katalog sampah dari file Excel.
- **Skema**:
  - `id` (bigint, Primary Key, Auto Increment)
  - `filename` (varchar) - Nama file terunggah
  - `import_type` (varchar) - Objek ('member', 'waste_price')
  - `user_id` (bigint, Foreign Key to `users.id`) - Admin pengunggah
  - `total_rows` / `total_success` / `total_failed` / `total_skipped` / `total_duplicates` (integer)
  - `created_at` / `updated_at` (timestamps)

---

## 5. ATURAN BISNIS & LOGIKA VALIDASI (BUSINESS RULES)

### A. Modul Kependudukan & Scoping RT
- **Normalisasi Telepon**: Input telepon wajib di-normalisasi menggunakan ekspresi regular `[^0-9]` untuk membuang karakter non-angka, lalu awalan `620` atau `62` dikonversi konsisten menjadi `0`.
- **RT Scoping**: `RtScopeService` mempartisi data secara ketat.
  - Otoritas RW (`admin_rw`, `bendahara_rw`) dibiarkan mengakses seluruh data tanpa klausa filter.
  - Otoritas RT (`admin_rt`) dipaksa menggunakan filter `where('rt_id', auth()->user()->rt_id)` untuk tabel fisik langsung (`kks`, `fund_categories`, `community_contributions`, `community_expenses`), atau `whereHas('kk', fn($q) => $q->where('rt_id', ...))` untuk data warga (`members`) dan tagihan (`bills`).
  - Jika admin RT belum memiliki `rt_id` terdaftar (bernilai NULL), sistem secara sengaja menyaring data menggunakan kondisi mustahil (`whereRaw('1 = 0')`) agar halaman tersaji kosong tanpa meloloskan data milik RT lain.

---

### B. Modul Keuangan & Tagihan Warga
- **Validasi Unik Tagihan**: Proses generate tagihan memeriksa database iuran bulanan untuk periode terpilih. Kombinasi `kk_id`, `fund_category_id`, `month`, dan `year` bersifat unik. Tagihan ganda untuk satu KK pada periode yang sama akan diabaikan oleh sistem.
- **Validasi Nominal Bayar**: Pembayaran tagihan (`bills.pay`) diverifikasi ketat terhadap outstanding balance:
  - `outstanding_balance = amount - sum(bill_payments.amount_paid)`.
  - Nominal pembayaran tidak boleh melampaui sisa saldo tagihan berjalan. Upaya membayar melebihi sisa tagihan akan langsung ditolak sistem sebelum transaksi masuk database.
- **Alokasi Pembayaran**: Sistem mendukung pencatatan pembayaran sebagian (*partial payment*). Status tagihan diperbarui otomatis:
  - `paid` jika total bayar terakumulasi >= nominal awal tagihan.
  - `partially_paid` jika nominal iuran masuk > 0 namun belum sepenuhnya melunasi nilai tagihan awal.
- **Kas Tidak Boleh Negatif**: Pengeluaran kas warga (`expenses`) wajib memotong saldo berjalan kas kategori tersebut. Jika nilai pengeluaran > saldo kas terhitung, sistem melempar `InsufficientBalanceException` dan membatalkan seluruh perubahan menggunakan Rollback.

---

### C. Modul Bank Sampah

#### 1. Nasabah Bank Sampah
- Record nasabah (`WasteCustomer`) yang memiliki data keuangan historis di tabel setoran (`deposits`), penarikan (`withdrawals`), atau buku tabungan (`savings_ledgers`) **dilarang keras untuk dihapus secara fisik** dari sistem guna menjamin keandalan proses audit. Admin diarahkan untuk mematikan status nasabah menjadi `inactive`.

#### 2. Transaksi Setoran Sampah (`deposits`)
- Subtotal setoran dihitung dinamis per item sampah: `weight * member_price`. Total nilai setoran langsung ditambahkan ke saldo tabungan nasabah.
- Transaksi otomatis menambahkan mutasi masuk (`credit`) di tabel `savings_ledgers`.

#### 3. Transaksi Penarikan Saldo Tabungan (`withdrawals`)
- **Syarat Minimum Setoran**: Nasabah bank sampah wajib memiliki **minimal 2 kali transaksi setoran sampah (`deposits`)** di masa lalu sebelum diizinkan mencairkan tabungan pertama kali. Jika kurang dari 2 kali setor, sistem memblokir pencairan saldo dan melempar `MinimumDepositException`.
- **Validasi Batas Saldo**: Nominal pencairan dana tabungan tidak boleh melebihi saldo tabungan nasabah. Jika tidak cukup, sistem melempar `InsufficientBalanceException`.
- Transaksi otomatis menambahkan mutasi keluar (`debit`) di tabel `savings_ledgers`.

#### 4. Transaksi Penjualan Sampah & Keuntungan Margin (`sales`)
- **Perhitungan Margin Dual-Pricing**: Bank Sampah membeli sampah dari warga menggunakan katalog `member_price` dan menjual akumulasi sampah ke pengepul besar menggunakan katalog `collector_price`.
- **Aturan Bisnis Margin**:
  - Keuntungan margin dihitung otomatis: `Margin = (collector_price - member_price) * weight`.
  - Total keuntungan margin dari seluruh item yang dijual dalam satu transaksi wajib bernilai positif (>= 0). Jika margin bernilai negatif (kerugian operasional), transaksi langsung dibatalkan sistem via `InvalidArgumentException`.
  - Nilai keuntungan margin bersih ini secara otomatis ditransfer ke kas operasional nyata bank sampah (`waste_bank_cash_ledgers`) sebagai mutasi masuk (`type: in`).
  - Biaya operasional bank sampah (seperti sewa gedung, timbangan) dicatat di tabel `waste_bank_expenses`, yang secara otomatis didebit (`type: out`) dari saldo kas operasional bank sampah.

#### 5. Sistem Audit Konsistensi Saldo
- Mesin audit (`BankSampahAuditService`) memeriksa kesehatan data secara real-time.
- Nilai kesehatan database (*Health Score*) dihitung dari skala 100 dengan skema pemotongan bobot penalti sebagai berikut:
  - Anomali ketidakcocokan saldo tabungan terakumulasi vs mutasi buku besar: penalti -20 poin.
  - Duplikasi pencatatan buku besar tabungan nasabah: penalti -15 poin.
  - Saldo rekening tabungan bernilai negatif (< 0): penalti -15 poin.
  - Transaksi yatim (orphan transactions/ledgers): penalti -10 poin.
  - Adanya transaksi lama unmapped (tanpa `waste_customer_id` terisi): penalti -2 poin.

---

## 6. ALUR FITUR UTAMA (CORE WORKFLOWS)

### A. Alur Kerja: Otomatisasi Generate Tagihan Warga
1. **Pemicu**: Admin mengklik tombol "Generate Tagihan" di form iuran dengan menentukan parameter Bulan (1-12) dan Tahun (YYYY).
2. **Pengumpulan Data**:
   - Sistem memanggil `BillService::generateMonthlyBills(month, year)`.
   - Mengambil seluruh KK yang berstatus aktif atau kontrak (`Kk::activeOrContract()`).
   - Mengambil seluruh kategori iuran yang berstatus wajib (`is_mandatory = true` dan `monthly_amount > 0`).
3. **Penyaringan Keunikan**:
   - Loop KK dan Kategori Kas:
     - Melakukan pencarian ke tabel `bills` untuk memeriksa apakah telah terbit record tagihan dengan kombinasi `kk_id`, `fund_category_id`, `month`, dan `year` tersebut.
     - Jika sudah ada, lewati (*skip*).
4. **Pembuatan Tagihan**:
   - Jika belum ada, sistem menghitung jumlah total tagihan di sistem untuk bulan & tahun terpilih guna menghasilkan nomor increment berurutan.
   - Menyusun nomor tagihan unik (`bill_code`) menggunakan rumus: `[PREFIX]-[YYYYMM]-RT[RT_NUMBER]-[INCREMENT]`.
   - Membuat record baru di tabel `bills` dengan status awal `unpaid`.
5. **Kembalian**: Mengembalikan total jumlah baris tagihan baru yang berhasil diterbitkan ke admin.

---

### B. Alur Kerja: Pembayaran Tagihan Warga
1. **Pemicu**: Admin menginput nominal pembayaran, metode bayar, dan tanggal pada tagihan yang dipilih.
2. **Validasi Awal**:
   - Sistem menghitung outstanding balance tagihan (`amount - sum(amount_paid)`).
   - Memastikan nominal pembayaran tidak melampaui sisa tagihan. Jika ya, batalkan transaksi dan beri peringatan ke admin.
3. **Penyusunan Kuitansi & Transaksi Kas**:
   - Sistem memanggil `BillService::payBill(bill_id, data)` dalam satu *database transaction block*.
   - Menerbitkan nomor kuitansi otomatis (`receipt_number`) dengan format: `[PREFIX]-[YYYYMM]-[INCREMENT]`.
   - Menghubungkan transaksi dengan warga penanggung jawab utama KK (mengambil data warga pertama yang terikat pada KK tersebut).
   - Memanggil `CommunityCashService::recordContribution()` untuk mencatat mutasi masuk kas iuran warga di tabel `community_contributions` dan menerbitkan buku besar kas di `community_cash_ledgers`.
4. **Pembaruan Status Tagihan**:
   - Menyimpan record detail pembayaran ke tabel `bill_payments`.
   - Menghitung ulang total bayar tagihan. Jika total bayar terkumpul >= nominal awal tagihan, set status tagihan menjadi `paid`, jika belum set status menjadi `partially_paid`.
5. **Audit trail**: Menuliskan log transaksi secara otomatis di tabel `activity_logs` dengan event `bill.payment` dan status `info`.

---

### C. Alur Kerja: Transaksi Pencairan Tabungan Sampah (Withdrawal)
1. **Pemicu**: Petugas bank sampah memasukkan nasabah, nominal penarikan, tanggal, dan catatan pengajuan pencairan tabungan.
2. **Validasi Kelayakan**:
   - Sistem memanggil `BankSampahService::recordWithdrawal(data)` di dalam *database transaction block*.
   - Menghitung jumlah seluruh riwayat transaksi setoran sampah (`deposits`) nasabah tersebut.
   - Jika total setoran nasabah di masa lalu **kurang dari 2 kali**, sistem menghentikan proses, melempar `MinimumDepositException`, dan membatalkan pencairan saldo.
3. **Validasi Ketersediaan Saldo**:
   - Menghitung total saldo tabungan berjalan nasabah (`sum(credit) - sum(debit)` di tabel `savings_ledgers`).
   - Jika nominal penarikan > saldo tabungan berjalan, sistem memblokir pencairan, melempar `InsufficientBalanceException`, dan membatalkan proses.
4. **Eksekusi Penarikan**:
   - Membuat record baru di tabel `withdrawals`.
   - Membuat record mutasi keluar baru (`type: debit`) di tabel buku tabungan nasabah (`savings_ledgers`), mereferensikan model `Withdrawal` yang baru saja dibuat.
5. **Audit Trail**: Menuliskan log aktivitas pencairan saldo nasabah ke tabel `activity_logs` dengan tipe event `withdrawal.create`.

---

### D. Alur Kerja: Penjualan Sampah Ke Pengepul (Sale)
1. **Pemicu**: Petugas menginput mitra pengepul, tanggal penjualan, dan daftar detail sampah beserta beratnya yang siap dikeluarkan dari gudang.
2. **Validasi Item**:
   - Menyaring data detail yang memiliki baris kategori sampah dan berat yang valid. Jika kosong, batalkan transaksi.
3. **Kalkulasi Margin & Dual-Pricing**:
   - Sistem memanggil `BankSampahService::recordSale(data)` dalam *database transaction block*.
   - Mengambil katalog harga dual-pricing sampah (`waste_prices`) yang disepakati dengan pengepul terpilih.
   - Menghitung nilai jual ke pengepul (`weight * collector_price`) untuk dimasukkan ke subtotal penjualan.
   - Menghitung margin keuntungan bank sampah per baris: `margin = (collector_price - member_price) * weight`.
4. **Verifikasi Aturan Margin**:
   - Akumulasi total margin dari seluruh baris sampah dihitung.
   - Jika akumulasi total margin bernilai negatif (keuntungan bank sampah kurang dari nol), sistem menolak transaksi penjualan, melempar `InvalidArgumentException` dan membatalkan seluruh operasi.
5. **Pencatatan Transaksi & Kas Operasional**:
   - Membuat record penjualan di tabel `sales` dan detail di `sale_details`.
   - Jika total margin positif (> 0), sistem secara otomatis menerbitkan record mutasi masuk (`type: in`) di buku besar kas operasional bank sampah (`waste_bank_cash_ledgers`), mereferensikan model `Sale` tersebut untuk menambahkan saldo kas operasional riil.
