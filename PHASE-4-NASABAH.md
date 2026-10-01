# Phase 4 — Data Nasabah Reference Implementation

> **Status:** Completed  
> **Acuan Desain:** `DESIGN.md` · `UI-AUDIT.md` · `PHASE-1-FOUNDATION.md` · `PHASE-2-SETORAN.md` · `PHASE-3-DASHBOARD.md` · `NAVIGATION-IA.md`  
> **Fokus Utama:** Mentransformasikan modul **Data Nasabah** (Index/List, Show/Detail, Create, Edit) menjadi **REFERENCE IMPLEMENTATION** ketiga bagi halaman operasional direktori, formulir CRUD, dan navigasi kontekstual *Connect The Dots*.  
> **Batasan Dipatuhi:** Zero database migration, zero schema changes, zero route URL changes, zero business logic mutations.

---

## 1. UX Direction (Arah Pengalaman Pengguna)

Modul Data Nasabah ditransformasikan dari tampilan CRUD admin generik menjadi **Direktori Operasional Cepat & Humanis**:
* **Operational Directory vs Admin Generic:**
  - Menghilangkan kesan tabel bootstrap kaku yang penuh badge tanpa arti.
  - Memposisikan **Nama Nasabah** sebagai jangkar visual utama (*visual anchor*) dengan avatar inisial berwarna brand, kode anggota berkarakter monospaced bersih, dan nomor kontak yang mudah dipindai.
* **One Click Access:**
  - Tombol aksi primer `+ Nasabah Baru` berada langsung di header halaman.
  - Setiap baris memiliki aksi cepat `Detail`, `Edit`, dan `Hapus` (dilindungi konfirmasi bahaya).
* **Connect The Dots (Halaman Detail):**
  - Halaman profil nasabah tidak lagi menjadi jalan buntu (*dead end*).
  - Operator dapat langsung mengeksekusi transaksi harian untuk nasabah tersebut (`+ Catat Setoran`, `Tarik Saldo`, `Buku Tabungan`) dengan sekali klik tanpa harus kembali ke sidebar.
* **Readability First:**
  - Seluruh teks input, nama nasabah, nomor telepon, dan status mematuhi batas bawah tipografi 16px (`text-body`) atau label proporsional 14px (`text-body-sm`).
  - Menghapus penggunaan teks mikro `text-[10px]` dan `text-[11px]` pada konten operasional utama.

---

## 2. Information Hierarchy (Hierarki Informasi)

```text
A. INDEX / LIST
   ├── Page Header (+ CTA Prominen: "+ Nasabah Baru")
   ├── Filter & Search Bar (Nama, Kode NSB-..., Telepon, Alamat + Filter Status Aktif/Nonaktif)
   ├── Main Data Display:
   │   ├── Desktop: Dense Operational Table (Nasabah | Kontak & Alamat | Status & Akun | Aksi)
   │   └── Mobile: Adaptive Stacked Cards (Zero Horizontal Overflow)
   └── Honest Empty States (Pencarian Nihil vs Database Baru)

B. DETAIL / SHOW (CONNECT THE DOTS)
   ├── Customer Identity Banner (Avatar Inisial, Nama, Kode, Status, Label Portal)
   ├── Action-First Transaksi Langsung:
   │   ├── [+ Catat Setoran] (Membuka alur timbangan dengan nasabah terpilih otomatis)
   │   ├── [Tarik Saldo] (Pencairan tabungan tunai nasabah)
   │   └── [Buku Tabungan →] (Tinjauan mutasi kredit/debit lengkap)
   ├── Financial Overview KPIs (3 Kartu Ringkasan):
   │   ├── Saldo Tabungan Aktif (Nominal Rupiah menonjol)
   │   ├── Frekuensi Setoran (Akumulasi kali penimbangan)
   │   └── Frekuensi Penarikan (Akumulasi kali pencairan)
   ├── Profile & Contact Details (No. HP, Alamat lengkap, Tanggal bergabung)
   ├── Portal Warga Integration Status (Detail akun login warga jika terhubung)
   └── Danger Zone: Hapus Nasabah (Dilindungi jika ada riwayat keuangan)

C. CREATE & EDIT FORMS
   ├── Setoran Form Language (Label di atas, penanda eksplisit "Wajib diisi")
   ├── Hybrid Input (Padding lapang, focus ring semantik, border halus)
   └── Action Buttons (Batal vs Simpan/Perbarui)
```

---

## 3. Index Design (Desain Halaman Indeks)

* **Toolbar Pencarian & Filter:**
  - Terbungkus kartu semantik `bg-surface border border-border rounded-radius-lg p-4 sm:p-5`.
  - Input pencarian responsif mendukung pencarian nama, kode (`NSB-...`), no. HP, dan alamat.
  - Dropdown filter status (`Semua Status`, `Aktif`, `Nonaktif`) dan tombol `Filter`.
  - Tautan kontekstual `Reset` otomatis muncul jika filter/pencarian sedang aktif.
* **Desktop Table (`hidden md:block`):**
  - Wadah tabel borderless di dalam `<x-ui.card variant="bordered" class="p-0 overflow-hidden">`.
  - Baris tabel lapang (`py-3.5 px-5`) dengan efek hover lembut `hover:bg-surface-muted/50`.
  - Kolom terstruktur:
    1. *Nasabah:* Avatar inisial (`w-9 h-9 bg-primary-light text-primary`), Nama nasabah tebal, Kode `NSB-XXXXXX` monospace.
    2. *Kontak & Alamat:* Nomor HP monospace, teks alamat ringkas dengan tooltip teks lengkap.
    3. *Status & Akun:* Badge semantik (`✓ Aktif` hijau / `Nonaktif` abu-abu) dan indikator akun portal.
    4. *Aksi:* Tombol teks `Detail` (hijau), `Edit` (netral), dan `Hapus` (merah).
* **Mobile Adaptive Cards (`block md:hidden`):**
  - Tabel desktop diubah menjadi kartu adaptif bertumpuk (`p-4 bg-surface border border-border rounded-radius-md space-y-3`).
  - Baris atas: Avatar + Nama + Kode berdampingan dengan badge status.
  - Baris tengah: Grid 2 kolom untuk info Kontak dan Akun Portal, serta baris alamat.
  - Baris bawah: Tiga tombol aksi sentuh (*thumb-friendly*): `Detail` (secondary), `Edit` (outline), `Hapus` (ghost danger).
  - **Zero Horizontal Scrolling:** Tidak ada lagi scroll menyamping yang merusak layout layar ponsel.
* **Honest Empty States:**
  - **Kasus A (Pencarian Nihil):** Menampilkan ilustrasi pencarian, judul *"Nasabah Tidak Ditemukan"*, penjelasan ramah, tombol *"Reset Filter"*, dan tombol *"+ Tambah Nasabah Baru"*.
  - **Kasus B (Database Kosong):** Menampilkan ilustrasi pengguna, judul *"Belum Ada Data Nasabah"*, penjelasan memulai operasional, dan tombol CTA prominen *"+ Tambah Nasabah Pertama"*.

---

## 4. Detail Design (Desain Halaman Rincian / Show)

Halaman detail berfungsi sebagai **pusat kendali operasional seorang nasabah**:
1. **Header & Identitas:**
   - Menampilkan breadcrumbs jejak navigasi `Dashboard → Nasabah → Kode Nasabah`.
   - Kartu identitas utama memuat avatar inisial besar 56px (`w-14 h-14 bg-primary-light text-primary font-bold text-heading-lg`), nama nasabah, kode anggota, badge status, serta status koneksi akun warga.
2. **Connect The Dots Actions (Aksi Cepat Transaksi):**
   - Tombol primer `+ Catat Setoran` mengarahkan operator ke rute `bank-sampah.deposits.create?customer_id={{ $customer->id }}` sehingga form penimbangan langsung memilih nasabah terkait tanpa perlu mengetik ulang (*One Click flow*).
   - Tombol sekunder `Tarik Saldo` mengarahkan ke form penarikan kas nasabah.
   - Tombol `Buku Tabungan →` mengarahkan ke mutasi tabungan kolektif.
3. **Financial KPIs:**
   - 3 kartu ringkasan keuangan (`Saldo Tabungan Aktif`, `Frekuensi Setoran`, `Frekuensi Penarikan`) dengan font angka monospace tebal dan warna brand primer.
4. **Informasi Kontak & Akun Portal:**
   - Kotak informasi nomor telepon, alamat lengkap domisili, dan tanggal bergabung.
   - Kotak integrasi portal warga menampilkan status akun terhubung atau status nasabah langsung.
5. **Proteksi Integritas Data:**
   - Tombol hapus diletakkan di bagian paling bawah dengan penjelasan bahwa nasabah yang telah memiliki riwayat setoran/tabungan tidak dapat dihapus demi kepatuhan audit pembukuan.

---

## 5. Create & Edit Design (Desain Formulir Tambah & Ubah)

Mengadopsi pola formulir rujukan Setoran (*Setoran Form Language*):
* **Struktur Bersih:** Wadah form terpusat (`max-w-3xl mx-auto`) di dalam `<x-ui.card variant="bordered">`.
* **Labeling Jelas:**
  - Seluruh label diletakkan konsisten **di atas field** menggunakan komponen `<x-input-label>`.
  - Field wajib diberi tanda teks eksplisit berwarna merah lembut: `(Wajib diisi)` sesuai aturan `DESIGN.md` §5.5 (bukan sekadar simbol asterisk `*`).
* **Input Semantik:**
  - Menggunakan komponen `<x-text-input>` dengan baseline ukuran 16px (`text-body`) untuk mencegah auto-zoom pada peramban mobile/iOS.
  - Border halus `border-border` dengan transisi warna border fokus `focus:border-primary` tanpa focus ring dekoratif yang tebal.
* **Form Edit Identity Reference:**
  - Menampilkan kotak penanda read-only `Kode Nasabah Resmi` (misal `NSB-000003`) dengan keterangan bahwa kode dihasilkan otomatis oleh sistem dan bersifat permanen.
* **Aksi Bawah:**
  - Tombol sekunder `Batal` (ghost/outline) dan tombol primer `Simpan Nasabah Baru` / `Simpan Perbarui`.

---

## 6. Contextual Navigation (Hubungan Antar Halaman)

Modul Nasabah kini terhubung langsung dengan modul operasional lain:
* **Nasabah Detail $\longrightarrow$ Setoran Baru:** Tautan `+ Catat Setoran` menyertakan query parameter `customer_id` untuk memilih nasabah instan di form timbangan.
* **Nasabah Detail $\longrightarrow$ Penarikan Saldo:** Tautan `Tarik Saldo` menuju form pencairan tunai.
* **Nasabah Detail $\longrightarrow$ Buku Tabungan:** Tautan `Buku Tabungan →` membuka rekapitulasi mutasi tabungan.
* **Setoran Index $\longrightarrow$ Data Nasabah:** Tautan langsung `Data Nasabah →` di header setoran.
* **Dashboard $\longrightarrow$ Data Nasabah:** KPI Nasabah Terdaftar menghubungkan langsung ke direktori nasabah.

---

## 7. Responsive Behavior (Perilaku Responsif)

| Viewport | Index / List | Detail / Show | Create / Edit |
|---|---|---|---|
| **Desktop ($\ge$ 1024px)** | Dense operational table 4 kolom, toolbar filter 1 baris, actions sejajar kanan. | Header identitas horizontal, 3 kartu KPI keuangan berdampingan, 2 kolom info profil. | Form terpusat `max-w-3xl`, padding lapang `p-8`, tombol batal & simpan di kanan bawah. |
| **Tablet (768px – 1023px)** | Tabel desktop muat penuh tanpa scroll, toolbar filter responsif. | 3 kartu KPI menyesuaikan proporsi, aksi transaksi tetap sejajar di kanan identitas. | Form proporsional, input nyaman dioperasikan. |
| **Mobile (< 768px)** | Beralih ke **Stacked Adaptive Cards** per nasabah, tombol aksi lebar penuh, zero horizontal overflow. | Header identitas bertumpuk vertikal, 3 kartu KPI menumpuk 1 kolom, tombol aksi sentuh penuh. | Field form menumpuk vertikal, tombol batal dan simpan tersusun rapi tanpa desak-desakan. |

---

## 8. Dark Mode (Penyelarasan Tema Gelap)

* **Kontras & Keterbacaan:**
  - Seluruh kartu dan kontainer tabel menggunakan `bg-surface` (`#1c1917` Stone-900 di dark mode).
  - Latar belakang halaman menggunakan `bg-background` (`#0c0a09` Stone-950).
  - Garis batas menggunakan `border-border` (`#292524` Stone-800) yang kontras dan jelas memisahkan baris/kartu.
  - Teks nasabah dan nilai uang menggunakan `text-text` (`#f5f5f4` Stone-100) dan teks sekunder `text-text-muted` (`#a8a29e` Stone-400).
* **Komponen & Feedback:**
  - Badge status aktif menggunakan pasangan semantik `bg-success-light` dan `text-success-text`.
  - Modal konfirmasi hapus ter-render dengan backdrop gelap transparan `bg-black/60 backdrop-blur-xs` dan kontainer permukaan Stone-900.
  - Toast feedback sukses/gagal tampil kontras tanpa silau.

---

## 9. SPA Compatibility (Kompabilitas Navigasi SPA)

* Terintegrasi penuh dengan engine navigasi SPA `spa-nav.js`:
  - Rute `/bank-sampah/customers` beserta sub-rutenya (`/create`, `/{id}`, `/{id}/edit`) didaftarkan ke `isEligibleUrl()`.
  - Saat navigasi ke halaman nasabah mana pun melalui tautan internal, server merespons dengan potongan fragment `<div id="eco-spa-fragment">` tanpa reload browser.
  - Pohon DOM lama dibersihkan via `Alpine.destroyTree()` dan pohon baru didaftarkan via `Alpine.initTree()`.
  - Alur lengkap terverifikasi tanpa error console:
    $$\text{Dashboard} \longleftrightarrow \text{Data Nasabah (List)} \longleftrightarrow \text{Detail} \longleftrightarrow \text{Edit / Create} \longleftrightarrow \text{Setoran Baru} \longleftrightarrow \text{Back / Forward}$$
  - Riwayat tombol Back dan Forward peramban berpindah secara instan.

---

## 10. Components Reused & Refined

* **Reused Shared Components:**
  - `<x-layouts.dashboard>`: App shell persisten dengan collapsible sidebar, topbar, dan toast.
  - `<x-ui.page-header>`: Header halaman kontekstual dengan breadcrumbs dan tombol aksi.
  - `<x-ui.card>`: Kontainer kartu hibrida bervarian `bordered`.
  - `<x-ui.button>`: Tombol terpadu varian `primary`, `secondary`, `outline`, dan `ghost`.
  - `<x-ui.badge>`: Status badge berikon (`success` dan `neutral`).
  - `<x-input-label>`: Label di atas input dengan teks eksplisit `(Wajib diisi)`.
  - `<x-text-input>`: Hybrid text input 16px semantik.
  - `<x-input-error>`: Pesan validasi inline di bawah input.
  - `<x-ui.toast>`: Floating toast notification penangkap flash session.
* **Refined Shared Component:**
  - `<x-confirm-delete-modal>`: Dimigrasikan 100% dari styling lama berbasis hardcoded `slate-900` ke semantic tokens Phase 1 (`bg-surface`, `border-border`, `text-text`, `bg-danger`, `rounded-radius-lg`).

---

## 11. Verification & Test Evidence (Bukti Pengujian)

```bash
# 1. Kompilasi Aset Frontend (Vite)
npm run build
# Hasil: built in 738ms (CSS: 94.30 kB, JS: 93.51 kB) - 0 errors, 0 warnings

# 2. Pembersihan Cache View & Optimize
php artisan view:clear && php artisan optimize:clear
# Hasil: Compiled views & cached bootstrap files cleared successfully

# 3. Eksekusi Test Khusus Customer Reference Implementation
php artisan test tests/Feature/CustomerReferenceTest.php
# Hasil:
# ✓ customer index renders desktop table and mobile cards
# ✓ customer index empty state and search no result
# ✓ customer detail shows financial kpis and connect the dots
# ✓ customer create and validation
# ✓ customer edit and update
# ✓ customer spa fragments render cleanly
# Tests: 6 passed (60 assertions) - Duration: 357ms

# 4. Eksekusi Seluruh Regression Suite (15 Test Suites)
php artisan test tests/Feature/CustomerReferenceTest.php tests/Feature/NavigationIaTest.php tests/Feature/DashboardReferenceTest.php tests/Feature/SpaNavigationTest.php tests/Feature/ImportTemplateTest.php tests/Feature/DeviceTrackingTest.php tests/Feature/WastePriceHistoryTest.php tests/Feature/MaterialAliasTest.php tests/Feature/DepositImportTest.php tests/Feature/WasteSaleFlowTest.php tests/Feature/ProfileTest.php tests/Feature/Auth/AuthenticationTest.php tests/Feature/Auth/PasswordUpdateTest.php tests/Feature/WasteBankReportsTest.php tests/Feature/BalanceCheckTest.php
# Hasil: 122 passed, 581 assertions (100% PASS)

# 5. Uji Regresi Transaksi Setoran
php artisan test tests/Feature/WasteCustomerTest.php --filter=test_deposit
# Hasil: 1 passed, 4 assertions (100% PASS)

# 6. Browser Smoke Test (Playwright / Chromium Nyata)
# - Nasabah index desktop: OK
# - Search & Search empty state: OK
# - Create customer form + submission: OK
# - Detail customer page + financial KPIs: OK
# - Dark mode rendering: OK
# - Edit customer form: OK
# - SPA loop (Detail -> Setoran -> Back -> Detail -> Back -> Index): OK
# - Mobile adaptive cards (< 768px): OK
# Console Errors: 0
```

Tangkapan layar hasil uji visual disimpan di `docs/nasabah-reference/`:
- `nasabah-index-desktop.png` — Tampilan indeks desktop dengan tabel operasional bersih.
- `nasabah-search-empty.png` — State kosong pencarian dengan CTA reset & tambah baru.
- `nasabah-create.png` — Form tambah nasabah berstandar Setoran (label `Wajib diisi`, 16px).
- `nasabah-detail.png` — Halaman detail dengan kartu identitas, aksi transaksi, dan 3 KPI keuangan.
- `nasabah-detail-dark.png` — Halaman detail dalam mode gelap (earthy charcoal).
- `nasabah-edit.png` — Form edit profil dengan penanda kode resmi permanen.
- `nasabah-index-mobile.png` — Tampilan kartu adaptif di viewport ponsel 390px tanpa overflow horizontal.

---

## 12. Known Limitations (Limitasi yang Diketahui)

1. **Paginasi Standar 15 Baris:** Mengikuti controller existing (`paginate(15)`), antarmuka saat ini menggunakan paginasi nomor halaman Tailwind default.
2. **Koneksi Akun Portal Warga:** Form create dan edit saat ini murni mencatat data profil nasabah operasional bank sampah (`WasteCustomer`). Penautan akun login portal warga (`User`) dikelola secara terpisah melalui mekanisme sinkronisasi akun yang sudah ada.

---
*Dokumen ini merupakan laporan resmi penuntasan Phase 4 — Data Nasabah Reference Implementation EcoBank026.*
