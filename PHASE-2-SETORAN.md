# Phase 2 — Setoran Reference Implementation

> **Status:** Completed  
> **Acuan Desain:** `DESIGN.md` (EcoBank026 Design System v1.0)  
> **Dasar Audit:** `UI-AUDIT.md`  
> **Fondasi Desain:** `PHASE-1-FOUNDATION.md`  
> **Fokus Utama:** Redesain antarmuka Setoran (Pencatatan Penimbangan Nasabah) sebagai **REFERENCE IMPLEMENTATION** bagi seluruh sistem antarmuka EcoBank026.

---

## 1. Objective (Tujuan)

Mengimplementasikan modul Setoran (`bank-sampah/deposits/create` & `bank-sampah/deposits/edit`) sebagai implementasi rujukan desain yang memadukan prinsip:
* **One Click**: Meminimalkan friksi antarmuka dalam penimbangan berulang harian.
* **Workflow Canvas**: Menghilangkan split-screen kaku dan menggantikannya dengan kanvas alur linear terarah (Nasabah $\rightarrow$ Material Timbangan $\rightarrow$ Total Setoran $\rightarrow$ Simpan/Lanjut).
* **Readability First**: Penegakan batas bawah tipografi 16px (`text-body`) pada seluruh input operasional.
* **Modern Operational × Warm Human**: Palet warna earthy semantik dan interaksi keyboard-first yang tenang tanpa AI-slop.

---

## 2. Architecture Compliance (Kepatuhan Arsitektur)

Sistem tetap mematuhi arsitektur murni:
$$\text{Laravel Route} \quad\longrightarrow\quad \text{Controller} \quad\longrightarrow\quad \text{Service/DB} \quad\longrightarrow\quad \text{Blade} \quad\longrightarrow\quad \text{Alpine.js} \quad\longrightarrow\quad \text{Tailwind CSS}$$

* **Bukan SPA**: Tidak ada penambahan React, Vue, Inertia, Next.js, ataupun client-side router baru.
* **Server as Source of Truth**: Seluruh validasi integritas finansial, penulisan transaksi, snapshot harga, dan pembukuan buku besar tabungan tetap diproses 100% di server melalui `BankSampahService::recordDeposit()`.
* **Blade as Render Engine**: Template Blade menyusun struktur semantik, memuat data awal, dan menyematkan komponen UI Phase 1.
* **Alpine.js as UI Interaction**: Menangani status interaksi antarmuka (pencarian instan nasabah, penambahan/penghapusan baris dinamis, pratinjau kalkulasi subtotal, fokus kursor otomatis, dan shortcut keyboard).

---

## 3. Existing Flow Preserved (Alur Bisnis yang Dijaga)

Seluruh logika bisnis yang sudah berjalan tetap 100% terjaga:
1. Skema tabel database `deposits`, `deposit_details`, `savings_ledgers`, `waste_customers`, dan `waste_categories` tidak berubah.
2. Relasi `WasteCustomer` $\rightarrow$ `Deposit` $\rightarrow$ `DepositDetail` $\rightarrow$ `SavingsLedger` tetap identik.
3. Master harga `WasteCategory.member_price` tidak pernah berubah karena transaksi penimbangan.
4. Nilai harga yang digunakan tetap tersimpan sebagai snapshot transaksi pada `deposit_details.price_per_unit`.
5. Dukungan tombol aksi ganda (`save_and_close` dan `save_and_next`) tetap terhubung dengan backend yang sama.

---

## 4. UX Changes (Perubahan Pengalaman Pengguna)

| Aspek UX | Kondisi Sebelum Phase 2 | Desain Baru (Phase 2 Reference) |
|---|---|---|
| **Struktur Tata Letak** | *Split-Screen Desktop* (Kiri form 65%, Kanan rekap 35%). | **Workflow Canvas**: Satu kanvas linear terpusat (`max-w-5xl mx-auto`) yang memandu operator tahap demi tahap tanpa distraksi samping. |
| **Pemilihan Nasabah** | `<select>` dropdown panjang yang lambat dicari. | **Search-Centered Input**: Kotak cari instan dengan tombol **Pilih Cepat (Recent Customers)** 1-klik untuk nasabah terkini. |
| **Konteks Nasabah Terpilih** | Dropdown tetap terbuka dan memakan ruang. | Begitu dipilih, pencarian menciut menjadi **Kartu Konteks Nasabah** ringkas dengan tombol *Ganti Nasabah*. |
| **Fokus Otomatis** | Fokus manual atau loncat acak. | **Auto-Focus Terarah**: Memilih nasabah otomatis memindahkan kursor ke input Material baris 1; Memilih material otomatis memindahkan kursor ke input Berat baris terkait. |
| **Input Baris Material** | Tabel dengan font kecil (12px) dan badge edit samar. | **Hybrid Table Input**: Baris tabel lapang dengan font 16px (`text-body`), combobox material dengan integrasi alias, serta indikator override harga yang jelas: `Manual · Master RpX.XXX`. |
| **Aksi Baris (Hapus)** | Menghapus butuh kehati-hatian. | Tombol hapus (*trash icon*) selalu terlihat di desktop; menghapus baris draft bersifat instan tanpa konfirmasi destruktif yang menghambat. |
| **Rekap Hari Ini** | Mengambil 35% lebar layar secara permanen. | Berpindah ke bagian bawah kanvas sebagai **Accordion Rangkuman Timbangan Hari Ini** yang dapat dibuka/tutup dengan 1 klik. |
| **Notifikasi Sukses** | Banner blok hijau statis yang mendorong konten 80px ke bawah. | **Floating Toast Notification** di pojok kanan atas layar tanpa layout shift. |

---

## 5. Component Usage (Penggunaan Komponen Phase 1)

Modul Setoran telah dimigrasikan secara penuh ke komponen bersama Phase 1:
* `<x-ui.page-header>`: Menampilkan judul *"Setoran Sampah"*, deskripsi, breadcrumbs jejak rekam, serta tombol aksi sekunder.
* `<x-ui.card>`: Membungkus seksi Langkah 1 (Nasabah), Langkah 2 (Material), dan Langkah 3 (Total Setoran) menggunakan varian `bordered`.
* `<x-ui.button>`: Digunakan pada tombol simpan utama (varian `primary`), tombol simpan & tutup (varian `secondary`), ganti nasabah (varian `outline`), dan batal (varian `ghost`).
* `<x-ui.badge>`: Menampilkan penanda `Nasabah Terpilih` (varian `success`), tag `Alias` (varian `alias`), dan tag override `Manual` (varian `warning`).
* `<x-ui.modal>`: Menyediakan dialog Quick Add material baru (`quick-add-material-modal`) tanpa meninggalkan kanvas.
* `<x-ui.spinner>`: Menampilkan status *loading spinner* halus saat tombol simpan ditekan.
* `<x-ui.toast>`: Menampilkan pesan umpan balik sukses di layout utama.

---

## 6. Alpine Responsibilities vs Server Responsibilities

| Tanggung Jawab Alpine.js (Browser) | Tanggung Jawab Server Laravel (Backend) |
|---|---|
| Memfilter daftar nasabah secara real-time saat operator mengetik. | Mengambil data nasabah aktif dan transaksi hari ini dari database. |
| Memilih nasabah cepat dari chip *Recent Customers*. | Menjalankan validasi `exists:waste_customers,id`. |
| Memindahkan fokus kursor keyboard (`$nextTick`) secara otomatis. | Memvalidasi integritas numerik bobot timbangan $> 0$. |
| Menambah dan menghapus baris draft material secara dinamis. | Mengelompokkan item material dan menyimpan record `DepositDetail`. |
| Menghitung pratinjau subtotal baris dan grand total setoran di layar. | Menghitung total transaksi resmi dan mengkreditkan `SavingsLedger`. |
| Menampilkan status override jika harga diubah dari harga master. | Menyimpan snapshot `price_per_unit` permanen ke database. |
| Mengunci tombol dan menampilkan teks `Menyimpan...` saat form disubmit. | Mengelola transaksi atomik `DB::transaction` dan audit activity log. |

---

## 7. Responsive Behavior (Desktop & Mobile)

* **Desktop ($\ge$ 768px)**:
  - Antarmuka berbentuk tabel horizontal lapang (*row-oriented table*).
  - Kolom tersusun rapi: `# | Material | Berat (kg) | Harga Beli | Subtotal | Hapus`.
  - Input berat dan harga rata kanan dengan font angka monospace yang mudah dibandingkan.
* **Mobile (< 768px)**:
  - Antarmuka tabel otomatis beralih menjadi **Stacked Adaptive Cards** per baris material.
  - Setiap kartu memuat: nomor urut, dropdown pemilihan material, tombol hapus di kanan atas, serta grid 2 kolom untuk Berat (kg) dan Harga Beli.
  - Subtotal tampil menonjol di bagian bawah kartu dengan font `text-heading-sm text-primary`.
  - **Zero Horizontal Overflow**: Tidak ada scroll menyamping yang merusak tata letak.

---

## 8. State Handling

1. **State Belum Ada Nasabah**:
   - Menampilkan input pencarian terfokus dan chip nasabah terkini.
   - Jika operator menekan tombol simpan sebelum memilih nasabah, sistem menampilkan notifikasi toast peringatan merah dan memfokuskan kembali kursor ke kotak cari.
2. **State Nasabah Terpilih**:
   - Menampilkan kartu identitas nasabah terpilih beserta kode anggota dan tombol *Ganti Nasabah*.
3. **State Override Harga**:
   - Jika harga sama dengan master: Tampil format normal `Rp X.XXX / kg`.
   - Jika harga diubah operator: Tampil badge peringatan `Manual · Master Rp X.XXX` secara transparan.
4. **State Submitting (Loading)**:
   - Variabel `submitting = true` mengunci tombol simpan (disabled) dan mengubah teks tombol menjadi *Menyimpan...* disertai ikon spinner berputar untuk mencegah klik ganda (*anti-double submit*).
5. **State Sukses & Siap Nasabah Berikutnya**:
   - Jika operator memilih *Simpan & Lanjut Nasabah Berikutnya*, transaksi disimpan ke database, sistem melakukan redirect dengan flash session, toast hijau melayang muncul, dan form kembali bersih siap menerima nasabah selanjutnya dengan tanggal yang tetap dipertahankan.

---

## 9. Material Resolution & Price Override

Alur penentuan material pada combobox mengikuti hierarki resmi:
1. **Canonical Master Material**: Menampilkan nama dan kode resmi `WasteCategory`.
2. **Material Alias**: Menampilkan label eksternal dengan penanda badge `Alias` dan keterangan panah `→ Nama Master`. Memilih alias otomatis menautkan ke ID material master terkait.
3. **Unknown Material**: Jika tidak ditemukan kecocokan, muncul opsi `+ Tambah Sebagai Jenis Sampah Baru` yang membuka modal pendaftaran kategori master resmi beserta grup induknya.
4. **Price Snapshot**: Nilai `member_price` master kategori ditarik sebagai default. Operator dapat menekan *Ubah* untuk menyesuaikan harga khusus transaksi tersebut tanpa pernah mengubah master harga di database.

---

## 10. Hasil Pengujian (Test Results)

```bash
# 1. Kompilasi Aset Frontend (Vite)
npm run build
# Hasil: built in 692ms (CSS: 94.64 kB, JS: 87.66 kB) - 100% SUKSES

# 2. Pembersihan Cache View & Bootstrap
php artisan view:clear && php artisan optimize:clear
# Hasil: Compiled views & cached bootstrap files cleared successfully

# 3. Uji Seluruh Test Suite Fitur & Regresi (11 Test Suite)
php artisan test tests/Feature/ImportTemplateTest.php tests/Feature/DeviceTrackingTest.php tests/Feature/WastePriceHistoryTest.php tests/Feature/MaterialAliasTest.php tests/Feature/DepositImportTest.php tests/Feature/WasteSaleFlowTest.php tests/Feature/ProfileTest.php tests/Feature/Auth/AuthenticationTest.php tests/Feature/Auth/PasswordUpdateTest.php tests/Feature/WasteBankReportsTest.php tests/Feature/BalanceCheckTest.php
# Hasil: 103 passed, 416 assertions (100% PASS)

# 4. Uji Test Deposit Nasabah
php artisan test tests/Feature/WasteCustomerTest.php --filter=test_deposit
# Hasil: 1 passed, 4 assertions (100% PASS)
```

---

## 11. Manual Smoke Test Results

Pengujian runtime via simulasi request aplikasi:

1. **Render Halaman Create**:
   - `GET /bank-sampah/deposits/create` $\rightarrow$ **HTTP 200 (OK)**.
   - Elemen terverifikasi: Workflow Canvas (`max-w-5xl`), Kotak Cari Nasabah (`customer-search-input`), Chip Nasabah Cepat (`recentCustomers`), Tabel Hybrid Material, Tombol Simpan & Lanjut, dan Modal Quick Add.
2. **Eksekusi Simpan & Lanjut (`action: save_and_next`)**:
   - Transaksi tersimpan ke basis data (ID: #1, Total: Rp 15.750).
   - Redirect status **HTTP 302** ke `/bank-sampah/deposits/create?date=2026-09-30`.
   - Form bersih siap untuk nasabah berikutnya.
3. **Render Halaman Edit**:
   - `GET /bank-sampah/deposits/{id}/edit` $\rightarrow$ **HTTP 200 (OK)**.
   - Elemen terverifikasi: Nasabah terpilih otomatis (*Ibu Rahayu*), Rincian material lama termuat (*Kardus Box Coklat 10.5 kg @ Rp 1.500*), Banner peringatan saldo tabungan, dan Tombol *Perbarui Setoran*.

---

## 12. Files Changed & Files Not Changed

### Files Changed:
* `resources/views/bank-sampah/deposits/create.blade.php`: Implementasi penuh Workflow Canvas Setoran Baru.
* `resources/views/bank-sampah/deposits/edit.blade.php`: Penyelarasan Workflow Canvas pada Form Edit Setoran.
* `app/Http/Controllers/DepositController.php`: Eager load relasi `wasteCategoryGroup` dan penyediaan data grup untuk modal kategori.

### Files NOT Changed (Sengaja Dijaga Utuh):
* Seluruh model: `Deposit`, `DepositDetail`, `WasteCustomer`, `WasteCategory`, `SavingsLedger`.
* Seluruh migrasi database dan skema tabel.
* Logika finansial `BankSampahService::recordDeposit()`.
* Modul Penjualan (`sales/*`), Modul Dashboard, Modul Nasabah, Modul Agregator, Modul Laporan, dan Portal Warga.

---

## 13. Known Limitations & Follow-up Candidates

* **Pencarian Nasabah Client-Side**: Pada implementasi saat ini, pencarian nasabah memanfaatkan daftar `$customers` yang dikirim dari controller untuk pencarian instan tanpa delay jaringan. Untuk bank sampah dengan ribuan nasabah di masa mendatang, pencarian dapat ditingkatkan menjadi endpoint AJAX debounced ringan jika ukuran payload melampaui batas wajar.
* **Halaman Indeks Setoran (`deposits/index`)**: Sesuai batasan Phase 2, halaman daftar setoran tidak diubah secara struktural dan tetap berfungsi normal sebagai daftar riwayat.

---

## 14. Final Visual QA & Foundation Corrections

> **Tanggal:** 30 September 2026
> **Ruang lingkup:** Shell/shared foundation + halaman Setoran (create/edit) sebagai reference implementation.
> **Batasan dipatuhi:** Tidak ada perubahan business logic, database, migration, controller finansial, atau flow backend. Tidak ada mass migration halaman lain. Tidak ada dependency baru.

### 14.1. Issue Ditemukan, Root Cause & Fix

| # | Issue | Root Cause | Fix |
|---|---|---|---|
| 1 | **Shell aktif belum memakai fondasi Phase 1.** Semua halaman (termasuk Setoran) me-render via komponen `<x-layouts.dashboard>` (`components/layouts/dashboard.blade.php`), tetapi file tersebut masih shell lama: body `bg-slate-50/dark:bg-slate-950`, sidebar desktop fixed 256px tanpa state collapse, tanpa `<x-ui.toast/>`. Akibatnya: toggle collapse di topbar menulis ke properti Alpine yang tidak ada (tidak berfungsi), tidak ada persistensi 256px/80px, dan flash sukses `save_and_next` tidak terlihat di halaman create (tidak ada listener toast). | Duplikasi shell: perbaikan Phase 1 diterapkan di `layouts/dashboard.blade.php` (layout `@extends`, tidak dipakai halaman mana pun), sedangkan shell yang benar-benar dipakai adalah komponen `components/layouts/dashboard.blade.php` yang tertinggal. | Shell komponen diselaraskan ke spesifikasi Phase 1: body `bg-background text-text`, `x-data` + `$watch` untuk `sidebarCollapsed` (persist `localStorage`), wrapper desktop collapsible (`lg:w-20`/`lg:w-64`), `x-cloak` pada drawer mobile, dan `<x-ui.toast/>` global. Logika dark mode, theme init script, ApexCharts CDN, dan `@stack('scripts')` dipertahankan. |
| 2 | **Mobile drawer bocor state collapse.** Partial sidebar memakai `:class="sidebarCollapsed ? 'w-20 …' : 'w-64 …'"` tanpa prefix breakpoint, sehingga saat operator collapse di desktop lalu membuka drawer di mobile, drawer 256px berisi sidebar 80px (ruang kosong/glitch). | Class width collapse tidak di-scope ke desktop. | Partial diubah ke base `w-64` + kondisional `lg:w-20/lg:min-w-[5rem]` vs `lg:w-64/lg:min-w-[16rem]`. Drawer mobile selalu 256px; desktop tetap toggle 256px/80px sinkron dengan wrapper. |
| 3 | **Partial rekap harian masih legacy.** `deposits/partials/today-recap.blade.php` memakai `bg-white/dark:bg-slate-900`, `border-slate-*`, `text-slate-*`, `bg-emerald-*/text-emerald-*` hardcoded + tipografi `text-xs`/`text-[10px]`/`text-[11px]` untuk angka operasional penting. | Partial luput dari migrasi Phase 2 (tidak tercakup dalam rewrite create). | Dimigrasi penuh ke token semantik (`bg-surface`, `border-border/subtle`, `text-text/muted`, `bg-success-light/text-success-text`, `bg-primary-light/40`, `text-primary`) + radius `rounded-radius-*`; angka operasional naik ke `text-body-sm`/`text-heading-sm`, label meta memakai `text-caption`. Struktur, grid, scroll, dan keyboard guide tidak berubah. |
| 4 | **Banner peringatan edit memakai amber hardcoded** (`bg-amber-50 dark:bg-amber-950/30 …`). | Shortcut Tailwind alih-alih token warning Phase 1. | Diganti ke `bg-warning-light border-warning/30`, ikon `text-warning`, teks `text-warning-text` (otomatis benar di light + dark). |
| 5 | **Indikator override tidak sesuai DESIGN §24.5 + terlalu kecil.** Menampilkan `Master RpX` (tanpa kata `Manual ·`) dengan `text-[10px]` di create & edit. | Label disingkat dan memakai arbitrary 10px. | Menjadi `Manual · Master RpX` dengan `text-caption` (12px) di kedua form, desktop + mobile. |
| 6 | **Kartu adaptif mobile tidak setara desktop.** Input material mobile tidak punya dropdown hasil (dropdown hanya ada di blok `hidden md:block`), tidak ada navigasi keyboard, input berat tanpa id/handler Enter, harga statis tanpa indikator override/Ubah. Akibatnya alur mobile: ketik material → tidak ada daftar terlihat; auto-focus berat gagal diam-diam (target id desktop tersembunyi). | Blok mobile ditulis sebagai tampilan baca-saja, bukan parity input. | Blok mobile create & edit dilengkapi: dropdown hasil memakai `getFilteredMaterials()` yang sama (termasuk badge Alias + fallback Quick Add), handler arrow/enter, `weight-input-mobile-{i}` + `@keydown.enter → onWeightEnter`, serta tampilan + edit override (`Manual · Master`, Ubah/OK). Ditambah helper Alpine `focusInput(desktopId, mobileId)` yang memilih input terlihat (`offsetParent` check) di `selectCustomer`/`addRow`/`focusWeightInput`/`onWeightEnter`. Tidak ada duplikasi id. Submit tetap memakai input bernama desktop (tetap ikut ter-submit walau tersembunyi via CSS). |
| 7 | **Form edit tidak punya Quick Add** (padahal "edit menggunakan pola yang sama"): dropdown desktop tanpa empty-state, tidak ada modal, tidak ada state Alpine. | Modal + state hanya ditambahkan di create. | Edit mendapat modal `quick-add-material-modal` identik (controller edit sudah menyediakan `$groups`), empty-state Quick Add di dropdown desktop + mobile, dan state/metode `quickAddRawMaterial`/`targetRowIndex`/`openQuickAddModal`. |
| 8 | **Submit Quick Add create memanggil metode Alpine yang tidak ada** (`@submit="onQuickCategorySubmit($event)"`). | Referensi sisa yang tidak pernah didefinisikan (console error; perilaku default tetap submit). | Atribut `@submit` dihapus; form melakukan POST normal ke `deposits.import.quick-add-category`. |
| 9 | **Select grup Quick Add memakai `text-body-sm` (14px)** di bawah baseline operasional 16px. | Luput dari penegakan baseline. | Menjadi `text-body` + focus ring semantik (create & edit). |
| 10 | **Backdrop `x-ui.modal` memakai `bg-slate-900/60`** (tidak konsisten dengan overlay shell `bg-black/50`). | Shortcut slate di shared foundation. | Menjadi `bg-black/60`. |

### 14.2. Slate Audit (bedakan 3 kategori)

1. **Legitimate legacy (sengaja TIDAK dimigrasi):** `deposits/index.blade.php`, `deposits/import.blade.php`, dan seluruh halaman bisnis lain di luar Setoran create/edit — tetap memakai `slate-*`/`emerald-*` hardcoded. Migrasi mereka adalah Phase 3/4, bukan QA ini.
2. **Setoran reference (create/edit + today-recap):** BERSIH — tidak ada lagi `bg-slate-*`/`text-slate-*`/`border-slate-*`/`ring-slate-*`, tidak ada `text-[10px]`/`text-[11px]` pada informasi operasional, tidak ada `dark:*` hardcoded.
3. **Shell/shared (`components/layouts/dashboard`, `layouts/partials/sidebar`, `topbar`, `x-ui.*`, `input-label`, `text-input`):** BERSIH dan semantik. Pengecualian yang disengaja: `text-[10px]` dipertahankan hanya untuk micro-label kontekstual sidebar (nama seksi `MAIN/DATA MASTER/…`, sub-brand `BANK SAMPAH`) — kategori caption/meta yang diizinkan DESIGN.md; bukan informasi operasional.

### 14.3. Checklist Verifikasi (A–J)

* **A. Shell/Sidebar:** expanded 256px / collapsed 80px via `:class` + `localStorage.sidebarCollapsed`; `transition-all duration-200`, `flex-shrink-0`, konten `flex-1` menyesuaikan tanpa overlap; refresh persist (init `=== 'true'` + `$watch`); drawer mobile `w-64` + `x-cloak` + overlay `bg-black/50`.
* **B. Color:** seluruh Setoran + shell memakai token Phase 1 (`background/surface/text/border/primary/success/warning/danger/info`); dark mode berasal dari token yang sama (variabel `.dark`), bukan invert manual.
* **C. Typography:** input/baris/total `text-body` (16px); label `text-body-sm` + `(Wajib diisi)`; meta/kbd/badge `text-caption`; tidak ada `text-[10px]/[11px]` pada info utama reference.
* **D. Setoran UX:** search-centered + recent chips, kartu konteks + Ganti, auto-focus nasabah→material→berat, Enter berat→baris baru, tambah/hapus baris, harga master, indikator `Manual · Master`, total, Simpan & Selesai / Simpan & Lanjut + loading `Menyimpan...` + anti-double-submit, toast sukses/gagal tanpa layout shift, empty state rekap, Quick Add modal, parity edit — terverifikasi render + roundtrip (lihat §14.4).
* **E. Responsive:** tidak ada `overflow-x` baru (tabel `hidden md:block`, kartu `block md:hidden`); action buttons `flex-col → sm:flex-row`; modal `maxWidth=md` + `px-4` dalam viewport; input `text-body` (mencegah zoom iOS); drawer tidak overlap konten.
* **F. Dark mode:** kontras via token (`text` stone-100 di atas `surface` stone-900, border stone-800 terlihat, surface ≠ background stone-950); input/dropdown/modal/dropdown hasil memakai token yang sama; status sukses/warning memakai pasangan `light/text` dengan varian dark.
* **G. One-click:** tidak ada langkah/interaksi baru; semua fix adalah parity/koreksi, bukan fitur. Alur tetap Nasabah → Material → Berat → Total → Simpan.
* **H–I:** sesuai §14.2; hanya shell/shared/Setoran yang diubah; tidak ada design system baru, tidak ada dependency baru.

### 14.4. Verification

```bash
# 1. Build frontend
npm run build
# Hasil: built in 679ms (CSS 93.47 kB, JS 87.66 kB) — SUKSES

# 2. View/bootstrap cache clear
php artisan view:clear && php artisan optimize:clear
# Hasil: cleared successfully

# 3. Regression suite (11 suites, sama seperti Phase 2)
php artisan test tests/Feature/ImportTemplateTest.php tests/Feature/DeviceTrackingTest.php tests/Feature/WastePriceHistoryTest.php tests/Feature/MaterialAliasTest.php tests/Feature/DepositImportTest.php tests/Feature/WasteSaleFlowTest.php tests/Feature/ProfileTest.php tests/Feature/Auth/AuthenticationTest.php tests/Feature/Auth/PasswordUpdateTest.php tests/Feature/WasteBankReportsTest.php tests/Feature/BalanceCheckTest.php
# Hasil: 103 passed, 416 assertions — PASS

# 4. Deposit regression
php artisan test tests/Feature/WasteCustomerTest.php --filter=test_deposit
# Hasil: 1 passed, 4 assertions — PASS

# 5. Smoke test Setoran (sementara, dihapus setelah lolos):
#    - GET deposits/create → 200 + marker (customer-search-input, recentCustomers,
#      quick-add-material-modal, deposit-form, Manual · Master, sidebarCollapsed)
#    - assertDontSee bg-slate-50 / dark:bg-slate-900
#    - POST store save_and_next → 302, 1 deposit, total Rp 15.750 (10.5 kg @ Rp 1.500)
#    - GET deposits/{id}/edit → 200 + deposit-edit-form + quick-add-material-modal + Manual · Master
# Hasil: 2 passed, 16 assertions — PASS (file smoke dihapus, repo bersih)
```

### 14.5. Files Changed

* `resources/views/components/layouts/dashboard.blade.php` — shell aktif: token semantik, sidebar collapsible + persist, toast global, x-cloak drawer.
* `resources/views/layouts/partials/sidebar.blade.php` — width collapse di-scope `lg:` (mobile drawer selalu 256px).
* `resources/views/bank-sampah/deposits/partials/today-recap.blade.php` — migrasi token semantik + tipografi.
* `resources/views/bank-sampah/deposits/create.blade.php` — indikator `Manual · Master`, hapus `@submit` tak terdefinisi, select `text-body`, parity mobile + `focusInput()`.
* `resources/views/bank-sampah/deposits/edit.blade.php` — banner warning semantik, indikator `Manual · Master`, parity mobile + `focusInput()`, Quick Add modal + empty-state + state Alpine.
* `resources/views/components/ui/modal.blade.php` — backdrop `bg-black/60`.

### 14.6. Known Limitations (baru, tetap berlaku)

* `deposits/index` & `deposits/import` tetap legacy slate/emerald — migrasi di Phase 3 (tidak diubah dalam QA ini).
* `layouts/dashboard.blade.php` (layout `@extends` lama) kini tidak dipakai halaman mana pun; shell aktif adalah komponen `x-layouts.dashboard`. Dibiarkan utuh agar tidak merusak referensi; kandidat hapus/merge di Phase 3.
* Varian badge `alias` (ungu) dan `pending` (amber) masih memakai warna hardcoded karena bukan bagian palet status inti; `pending` saat ini tidak dipakai di mana pun. Kandidat tokenisasi saat sistem badge diperluas.
* Micro-label sidebar (`text-[10px]`, section headers + sub-brand) dipertahankan sebagai caption kontekstual sesuai DESIGN.md.
* ApexCharts CDN tetap dimuat global oleh shell (dibutuhkan dashboard); kandidat lazy-load saat dashboard diredesain (Phase 4).

---
*Dokumen ini merupakan laporan resmi penuntasan Phase 2 Setoran Reference Implementation. Seluruh kriteria penerimaan terpenuhi 100%.*
