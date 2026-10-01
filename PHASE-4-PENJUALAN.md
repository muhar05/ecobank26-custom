# Phase 4 — Penjualan / Sisa Setoran Siap Dijual Reference Implementation

> **Status:** Completed  
> **Acuan Desain:** `DESIGN.md` · `UI-AUDIT.md` · `PHASE-1-FOUNDATION.md` · `PHASE-2-SETORAN.md` · `PHASE-3-DASHBOARD.md` · `NAVIGATION-IA.md`  
> **Fokus Utama:** Redesain modul **Penjualan ke Agregator** (Index/List, Create, Edit) sebagai implementasi rujukan (*reference implementation*) alur muat timbangan sampah terpilah dan pembukuan **Margin Bank Sampah**.  
> **Batasan Dipatuhi:** Zero database migration, zero schema changes, zero financial formula mutation, zero permission changes.

---

## 1. Objective (Tujuan)

Mentransformasikan modul Penjualan agar operator bank sampah dapat menyelesaikan proses:
$$\text{Sisa Setoran Siap Dijual} \longrightarrow \text{Pilih Agregator} \longrightarrow \text{Isi Berat Jual} \longrightarrow \text{Preview Margin Kas} \longrightarrow \text{Konfirmasi Penjualan}$$
secara cepat, intuitif, bebas popup modal yang lambat, dan terlindungi dari kesalahan jual (anti-double sale & validasi rugi harga).

---

## 2. Existing Flow Audited (Hasil Audit Alur Kerja Lama)

1. **Masalah pada Index Penjualan Lama (`sales/index.blade.php`):**
   - Halaman indeks lama hanya menampilkan riwayat transaksi penjualan masa lalu; **tidak menampilkan apa yang siap dijual hari ini**.
   - Margin bank sampah terkubur di bawah kolom total dengan font mikro `text-[10px]`.
   - Kolom tabel padat dengan pewarnaan slate hardcoded (`bg-slate-900`, `text-slate-*`).
   - Tampilan ponsel (< 768px) mengalami *horizontal overflow* karena tabel desktop dipaksakan.
2. **Masalah pada Form Create & Edit Lama (`sales/create.blade.php` & `edit.blade.php`):**
   - Dinyatakan **🔴 REDESIGN** pada `UI-AUDIT.md`.
   - Menggunakan **popup modal checkbox 80vh** (`modalOpen = true`) untuk memilih kategori sampah manual (bertolak belakang dengan prinsip One Click).
   - Terdapat artefak kode duplikat yang terpotong di baris 164–185 di luar template Alpine.
   - Peringatan kelebihan berat dan harga belum tersedia tidak terintegrasi secara visual di dekat field input.
3. **Logika Bisnis yang Diaudit (`BankSampahService` & `WasteSaleFlowTest`):**
   - Penjualan ke agregator mengikat pada tanggal yang sama dengan setoran nasabah (`whereDate('date', $date)`).
   - `remaining_weight = total_deposited_on_date - total_sold_on_date` per kategori sampah.
   - Penjualan ditolak jika `weight > remaining_weight` (Anti-Double-Sale).
   - Penjualan ditolak jika `collector_price < member_price` (Proteksi Rugi).
   - Snapshot permanen tersimpan pada `sale_details` (`price_per_unit`, `member_price`, `subtotal`, `margin`).
   - Kas Bank Sampah menerima pencatatan kas masuk sebesar `totalMargin = sum(margin)`.

---

## 3. UI Changes (Perubahan Antarmuka)

* **Terminologi Resmi Dipatuhi:**
  - `"Penjualan"`
  - `"Sisa Setoran Siap Dijual"`
  - `"Agregator"`
  - `"Margin Bank Sampah"`
  - Menghindari istilah inventaris pabrik seperti *"Sisa di Gudang"* atau *"Margin Bersih"*.
* **Halaman Indeks (`sales/index.blade.php`):**
  - **Seksi 1 (Prioritas Utama): Sisa Setoran Siap Dijual:**
    - Menampilkan tabel operasional langsung di bagian atas: Kategori Sampah, Total Setoran, Sudah Terjual, Sisa Siap Dijual (angka hijau tebal), Harga Beli Nasabah, dan tombol aksi `Jual ke Agregator →`.
    - Dilengkapi pemilih tanggal setoran terpadu.
    - Honest empty state jika tidak ada setoran: *"Saat ini belum ada setoran yang siap dijual pada tanggal ini."* disertai CTA `+ Catat Setoran Baru`.
  - **Seksi 2: Riwayat Transaksi Penjualan:**
    - Tabel rapi memuat Tanggal, Agregator, Nilai Penjualan, dan badge hijau terang `Margin Bank Sampah` (`+Rp 18.000`).
    - Accordion drawer per baris (`Lihat Rincian`): Menampilkan rincian muatan material (berat, harga beli nasabah, harga agregator, subtotal, margin).
  - **Mobile Adaptive Cards:**
    - Pada layar ponsel (< 768px), kedua seksi otomatis bertransisi menjadi kartu adaptif bertumpuk tanpa ada scroll menyamping.
* **Halaman Form Create & Edit (`sales/create.blade.php` & `edit.blade.php`):**
  - **Zero Popup Modal:** Menghilangkan modal checkbox 80vh dan menggantikannya dengan kanvas operasional linear terarah.
  - **Auto-Populate Sisa Setoran:** Tombol `Tarik Sisa Setoran` otomatis mengambil data sisa timbangan hari itu via endpoint `/bank-sampah/sales/daily-recap`.
  - **Inline Baris Timbangan:** Tombol `+ Tambah Baris` memungkinkan operator menambahkan kategori manual langsung pada baris tabel/kartu.
  - **Input Validasi Real-Time Dekat Sumber:** Jika operator memasukkan berat melebihi sisa (misal 35 kg padahal sisa 20 kg), kotak input berubah merah disertai teks peringatan langsung: `Maks sisa: 20 kg!` dan tombol konfirmasi otomatis terkunci.
  - **Kartu Ringkasan Finansial:** Menampilkan 4 metrik jelas (Total Material, Total Berat Muatan, Nilai Penjualan Agregator, dan **Margin Kas Bank Sampah** dengan ukuran font `text-heading-xl` hijau tebal).

---

## 4. UX Decisions (Keputusan Desain Pengalaman Pengguna)

1. **One Click dari Sisa Setoran:** Mengklik `Jual ke Agregator →` pada baris sisa setoran di halaman indeks langsung membuka form penjualan dengan parameter tanggal dan kategori yang sudah terisi otomatis.
2. **Positive Financial Cue:** Margin Bank Sampah diberi perlakuan visual khusus dengan badge semantik `bg-success-light text-success-text` berangka monospace untuk memudahkan kasir membedakan omzet kotor vs laba kas bank sampah.
3. **Negative Margin Warning:** Jika harga penawaran agregator lebih rendah dari harga beli nasabah, sistem menampilkan peringatan `⚠️ Rugi! < Beli` dan mengunci tombol konfirmasi untuk melindungi kas desa/bank sampah.

---

## 5. Business Rules Preserved (Kepatuhan Aturan Bisnis)

Seluruh logika bisnis 100% terjaga dan terverifikasi melalui test suite:
* Perhitungan margin: `margin = (collector_price - member_price) * weight`.
* Aturan Anti-Double-Sale: Penjualan $> \text{remaining\_weight}$ ditolak di frontend maupun backend.
* Aturan Proteksi Rugi: Penjualan dengan $\text{collector\_price} < \text{member\_price}$ ditolak.
* Pembukuan Kas: `WasteBankCashLedger` bertambah sebesar margin transaksi penjualan secara atomik di dalam `DB::transaction`.
* Snapshot Harga: Nilai harga dan margin tersimpan permanen pada `sale_details` dan tidak berubah jika master harga di kemudian hari diperbarui.

---

## 6. Responsive Behavior (Perilaku Responsif)

| Viewport | Index Penjualan | Form Create & Edit Penjualan |
|---|---|---|
| **Desktop ($\ge$ 1024px)** | Dua tabel operasional horizontal lapang dengan accordion drawer rincian material per baris. | Kanvas linear terpusat `max-w-5xl`, tabel input baris horizontal dengan kalkulasi subtotal dan margin real-time. |
| **Tablet (768px – 1023px)** | Tabel memuat penuh dengan padding proporsional, drawer rincian tetap rapi. | Kolom Agregator dan Tanggal berdampingan, tabel timbangan nyaman dioperasikan. |
| **Mobile (< 768px)** | Tabel Sisa Setoran dan Riwayat berganti menjadi **Stacked Adaptive Cards**, tombol aksi lebar penuh (*thumb-friendly*), **zero horizontal scroll**. | Form bertumpuk vertikal, kartu material dengan input angka besar, ringkasan margin jelas di bagian bawah. |

---

## 7. SPA Behavior (`X-Eco-Nav: 1`)

Modul Penjualan (`/bank-sampah/sales*`) telah didaftarkan ke `spa-nav.js`:
- Navigasi bolak-balik:
  $$\text{Dashboard} \longleftrightarrow \text{Penjualan} \longleftrightarrow \text{Catat Penjualan} \longleftrightarrow \text{Edit Penjualan} \longleftrightarrow \text{Setoran / Nasabah}$$
  berjalan instan tanpa full reload dan tanpa kedipan layar putih.
- Shell layout (sidebar dan topbar) persisten dan menu aktif berpindah secara mulus.
- Terverifikasi menghasilkan **0 console error**.

---

## 8. Testing (Hasil Pengujian Otomatis)

```bash
# 1. Kompilasi Aset Frontend (Vite)
npm run build
# Hasil: built in 706ms (CSS: 93.52 kB, JS: 93.52 kB) - 0 errors, 0 warnings

# 2. Pembersihan Cache View & Optimize
php artisan view:clear && php artisan optimize:clear
# Hasil: Compiled views & cached bootstrap files cleared successfully

# 3. Test Khusus Modul Penjualan (SaleReferenceTest & WasteSaleFlowTest)
php artisan test tests/Feature/SaleReferenceTest.php tests/Feature/WasteSaleFlowTest.php
# Hasil: 13 passed (102 assertions) - 100% PASS

# 4. Seluruh Regression Suite Aplikasi (16 Test Suites)
php artisan test tests/Feature/SaleReferenceTest.php tests/Feature/WasteSaleFlowTest.php tests/Feature/CustomerReferenceTest.php tests/Feature/NavigationIaTest.php tests/Feature/DashboardReferenceTest.php tests/Feature/SpaNavigationTest.php tests/Feature/ImportTemplateTest.php tests/Feature/DeviceTrackingTest.php tests/Feature/WastePriceHistoryTest.php tests/Feature/MaterialAliasTest.php tests/Feature/DepositImportTest.php tests/Feature/ProfileTest.php tests/Feature/Auth/AuthenticationTest.php tests/Feature/Auth/PasswordUpdateTest.php tests/Feature/WasteBankReportsTest.php tests/Feature/BalanceCheckTest.php
# Hasil: 127 passed, 626 assertions (100% PASS)

# 5. Uji Regresi Transaksi Setoran
php artisan test tests/Feature/WasteCustomerTest.php --filter=test_deposit
# Hasil: 1 passed, 4 assertions (100% PASS)
```

---

## 9. Browser Smoke Result (Playwright / Chromium Nyata)

Skenario browser smoke yang berhasil diuji:
1. Navigasi SPA dari Dashboard ke Penjualan: **OK**.
2. Render tabel Sisa Setoran Siap Dijual pada tanggal aktif: **OK**.
3. Klik tombol `Jual ke Agregator` langsung mengisi parameter form: **OK**.
4. Pemilihan Agregator & klik `Tarik Sisa Setoran`: **OK**.
5. Uji validasi melebihi sisa (input 35 kg dari sisa 20 kg): **Peringatan merah muncul seketika & tombol terkunci OK**.
6. Input berat valid (10 kg): **Subtotal Rp 26.000 & Margin Rp 6.000 terhitung otomatis OK**.
7. Submit Konfirmasi Penjualan: **Tersimpan ke database & redirect ke indeks OK**.
8. Sisa Setoran ter-update menjadi 10 kg & Riwayat Penjualan menampilkan transaksi baru: **OK**.
9. Buka accordion drawer `Lihat Rincian` pada tabel riwayat: **OK**.
10. Buka form Edit Penjualan (pre-filled details): **OK**.
11. Batal dan kembali ke indeks via SPA: **OK**.
12. Tampilan Dark Mode (charcoal stone): **OK**.
13. Tampilan ponsel (390x844 adaptive cards): **OK**.
14. **Console Errors:** **0 errors**.

---

## 10. Known Limitations (Limitasi yang Diketahui)

1. **Pengelompokan Tanggal Penjualan:** Sesuai arsitektur backend `BankSampahService`, penjualan sampah pada tanggal X hanya dapat menjual sisa timbangan yang disetor pada tanggal X yang sama. Sisa setoran dari tanggal yang berbeda dapat dilihat dengan memilih tanggal pada pemilih tanggal terpadu.
2. **Penghapusan Penjualan:** Penghapusan transaksi penjualan akan menghapus catatan jurnal kas terkait dan menghitung ulang saldo kas operasional (`recalculateWasteBankBalances()`).

---

## 11. Files Changed (Berkas yang Berubah)

* `app/Http/Controllers/SaleController.php` — Menambahkan kalkulasi data `readyToSell` dan `$selectedDate` pada method `index()` serta eager-load `details.wasteCategory`.
* `resources/views/bank-sampah/sales/index.blade.php` — Redesain penuh: Tabel Sisa Setoran Siap Dijual, Pemilih Tanggal, Tabel Riwayat Penjualan dengan Accordion Drawer, dan Mobile Adaptive Cards.
* `resources/views/bank-sampah/sales/create.blade.php` — Redesain penuh: Kanvas operasional linear, auto-populate sisa setoran, inline row management (zero popup modal), kalkulasi margin real-time, dan validasi anti-over.
* `resources/views/bank-sampah/sales/edit.blade.php` — Redesain penuh: Penyelarasan kanvas operasional pada form edit dan pemuatan rincian lama yang aman.
* `resources/js/spa-nav.js` — Penambahan rute `/bank-sampah/sales*` ke dalam cakupan navigasi instan SPA.
* `tests/Feature/SaleReferenceTest.php` — Automated test suite baru untuk modul Penjualan (5 test cases, 45 assertions).

---
*Dokumen ini merupakan laporan resmi penuntasan Phase 4 — Penjualan / Sisa Setoran Siap Dijual Reference Implementation EcoBank026.*
