# Phase 1 Design Foundation — EcoBank026

> **Status:** Completed  
> **Acuan Desain:** `DESIGN.md` (EcoBank026 Design System v1.0)  
> **Baseline Audit:** `UI-AUDIT.md`  
> **Fokus Utama:** Menyediakan fondasi visual, token semantik, sistem komponen inti bersama (*shared UI system*), serta perbaikan shell layout desktop/mobile tanpa melakukan redesain halaman bisnis maupun mengubah logika transaksi.

---

## 1. Implemented (Fondasi yang Dibuat)

Pada fase ini, fondasi arsitektur antarmuka telah dibangun:
1. **Core Design Tokens**:
   - Palet warna semantik *Earthy* (hijau hutan/emerald, batu hangat/charcoal, aksen oker/amber).
   - Pasangan token Light Mode dan Dark Mode otomatis via CSS Custom Properties (`hsl`/`rgb`).
   - Token tipografi dengan skala moderat dan penegakan baseline body text 16px (`text-body`).
   - Token border radius berkarakter *soft/slightly rounded* (`radius-sm` 6px, `radius-md` 10px, `radius-lg` 14px, `radius-xl` 16px) menghindari bentuk pill ekstrem atau lengkungan bengkak.
   - Token elevasi dan bayangan sangat halus (`shadow-subtle`, `shadow-card`, `shadow-popover`).
2. **Sistem Komponen Bersama (*Shared Components*)**:
   - `x-ui.button`: Komponen tombol terpadu dengan varian semantik (`primary`, `secondary`, `danger`, `ghost`, `outline`), ukuran (`sm`, `md`, `lg`), dan status *loading spinner*.
   - `x-ui.badge`: Komponen status yang mematuhi aturan wajib `Badge + Icon + Text` (tidak mengandalkan warna semata).
   - `x-ui.card`: Sistem kartu hibrida (`bordered`, `muted`, `flat`).
   - `x-ui.table`: Wadah tabel operasional padat dengan header jelas dan hover halus.
   - `x-ui.modal`: Dialog adaptif responsif (`sm`, `md`, `lg`, `xl`, `full`) dengan backdrop blur dan listener tombol escape.
   - `x-ui.toast`: Sistem notifikasi mengambang (*floating*) non-layout-shifting yang secara otomatis menangkap session flash Laravel (`success`, `error`, `warning`, `info`) maupun window event `@toast`.
   - `x-ui.spinner`: Indikator loading kontekstual SVG.
   - `x-ui.empty-state`: Komponen state kosong bermakna (*visual icon + explanation + action button*).
   - `x-ui.error-state`: Komponen state kesalahan operasional (*error icon + what happened + next action*).
   - `x-ui.page-header`: Header halaman kontekstual dengan slot breadcrumbs, judul, deskripsi, dan tombol aksi primer.
3. **Penyempurnaan App Shell**:
   - **Sidebar Desktop Collapsible**: Dapat diciutkan ke mode ringkas (5rem / 80px) atau dilebarkan (16rem / 256px) dengan state yang tersimpan di `localStorage`.
   - **Topbar Header Terintegrasi**: Memuat tombol toggle ciut/lebar desktop, dark mode switch terpadu, dan wadah floating toast global.

---

## 2. Existing Components Reused (Komponen yang Dipertahankan)

Komponen-komponen yang telah berfungsi dengan baik dan dipertahankan:
* `components/confirm-delete-modal.blade.php`: Modal konfirmasi bahaya merah dengan spinner submit bawaan.
* `components/rupiah-input.blade.php`: Input format mata uang interaktif via Alpine.js.
* `components/form-section.blade.php`: Pemisah seksi form bertingkat.
* `components/form-actions.blade.php`: Wadah aksi tombol simpan/batal pada form.
* `components/auth-session-status.blade.php`: Alert status sesi autentikasi.
* `components/input-error.blade.php`: Pesan error validasi inline di bawah input.
* `components/dropdown.blade.php` & `dropdown-link.blade.php`: Dropdown user menu.

---

## 3. Components Refined (Komponen yang Diperhalus)

* `components/primary-button.blade.php`: Diperbarui dari tombol hitam kaku Breeze (`bg-gray-800 rounded-md text-xs`) menjadi tombol berstandar brand primer (`bg-primary hover:bg-primary-hover text-white rounded-radius-md text-body-sm shadow-subtle`).
* `components/secondary-button.blade.php`: Mengadopsi token semantik `bg-surface hover:bg-surface-muted text-text border-border rounded-radius-md`.
* `components/danger-button.blade.php`: Mengadopsi token semantik `bg-danger hover:bg-danger text-white rounded-radius-md`.
* `components/text-input.blade.php`: Diperbarui ke sistem *hybrid input* dengan ukuran 16px (`text-body`), border halus `border-border`, dan fokus transisi warna border `focus:border-primary`.
* `components/input-label.blade.php`: Ditambahkan dukungan atribut `required="true"`, menampilkan teks penjelas eksplisit `(Wajib diisi)` berwarna merah lembut, bukan sekadar simbol `*`.
* `components/sidebar-link.blade.php`: Mendukung mode *collapsed* (menyembunyikan teks label dengan transisi mulus dan memusatkan ikon).

---

## 4. New Tokens (Token Desain Baru)

Didefinisikan pada `tailwind.config.js` dan `resources/css/app.css`:

### A. Palet Warna Semantik Earthy (`extend.colors`)
| Token Semantik | Kelas Tailwind | Nilai Light Mode | Nilai Dark Mode |
|---|---|---|---|
| **Primary** | `bg-primary`, `text-primary` | Forest Green (`#059669`) | Crisp Emerald (`#10b981`) |
| **Primary Hover** | `hover:bg-primary-hover` | Deep Green (`#047857`) | Emerald (`#059669`) |
| **Primary Light** | `bg-primary-light` | Soft Sage (`#ecfdf5`) | Dark Forest Tint (`rgba(6, 78, 59, 0.3)`) |
| **Secondary** | `bg-secondary` | Warm Ochre (`#d97706`) | Warm Amber (`#f59e0b`) |
| **Background** | `bg-background` | Warm Stone-50 (`#fafaf9`) | Warm Deep Charcoal Stone-950 (`#0c0a09`) |
| **Surface** | `bg-surface` | Pure White (`#ffffff`) | Charcoal Card Stone-900 (`#1c1917`) |
| **Surface Muted** | `bg-surface-muted` | Soft Stone-100 (`#f5f5f4`) | Stone-800 (`#292524`) |
| **Text** | `text-text` | Warm Dark Charcoal (`#1c1917`) | Warm Off-White Stone-100 (`#f5f5f4`) |
| **Text Muted** | `text-text-muted` | Stone-500 (`#78716c`) | Stone-400 (`#a8a29e`) |
| **Border** | `border-border` | Subtle Stone-200 (`#e7e5e4`) | Stone-800 (`#292524`) |
| **Success** | `bg-success`, `text-success` | Emerald-500 (`#10b981`) | Emerald-400 (`#34d399`) |
| **Warning** | `bg-warning`, `text-warning` | Amber-600 (`#d97706`) | Amber-400 (`#fbbf24`) |
| **Danger** | `bg-danger`, `text-danger` | Rose-600 (`#e11d48`) | Rose-400 (`#fb7185`) |
| **Info** | `bg-info`, `text-info` | Sky-500 (`#0ea5e9`) | Sky-400 (`#38bdf8`) |

### B. Tipografi (`extend.fontSize`)
* `text-body`: `1rem` (16px, line-height: 1.5rem) $\rightarrow$ Standar baseline keterbacaan.
* `text-body-sm`: `0.875rem` (14px, line-height: 1.25rem).
* `text-caption`: `0.75rem` (12px, line-height: 1rem) $\rightarrow$ Khusus teks bantuan/metadata sekunder.
* `text-heading-sm`: `1.125rem` (18px, font-weight: 600).
* `text-heading-md`: `1.25rem` (20px, font-weight: 700).
* `text-heading-lg`: `1.5rem` (24px, font-weight: 700).
* `text-heading-xl`: `1.875rem` (30px, font-weight: 800).

### C. Border Radius (`extend.borderRadius`)
* `rounded-radius-sm`: `0.375rem` (6px) $\rightarrow$ Badge, chip kecil.
* `rounded-radius-md`: `0.625rem` (10px) $\rightarrow$ Default input, tombol, popover.
* `rounded-radius-lg`: `0.875rem` (14px) $\rightarrow$ Kartu, tabel wrapper, modal.
* `rounded-radius-xl`: `1rem` (16px) $\rightarrow$ Kontainer luar maksimum.

### D. Depth & Shadows (`extend.boxShadow`)
* `shadow-subtle`: `0 1px 2px 0 rgba(0, 0, 0, 0.04), 0 1px 1px 0 rgba(0, 0, 0, 0.02)`.
* `shadow-card`: `0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px -1px rgba(0, 0, 0, 0.05)`.
* `shadow-popover`: `0 4px 6px -1px rgba(0, 0, 0, 0.07), 0 2px 4px -2px rgba(0, 0, 0, 0.05)`.

---

## 5. New Shared Components (Komponen Bersama Baru)

1. `components/ui/button.blade.php`: Komponen tombol semantik (`<x-ui.button>`).
2. `components/ui/badge.blade.php`: Komponen status berikon (`<x-ui.badge>`).
3. `components/ui/card.blade.php`: Wadah kartu hibrida (`<x-ui.card>`).
4. `components/ui/table.blade.php`: Wadah tabel operasional (`<x-ui.table>`).
5. `components/ui/modal.blade.php`: Dialog adaptif berukuran dinamis (`<x-ui.modal>`).
6. `components/ui/toast.blade.php`: Manajer notifikasi mengambang global (`<x-ui.toast>`).
7. `components/ui/spinner.blade.php`: Indikator loading berputar (`<x-ui.spinner>`).
8. `components/ui/empty-state.blade.php`: Komponen state kosong representatif (`<x-ui.empty-state>`).
9. `components/ui/error-state.blade.php`: Komponen state kesalahan operasional (`<x-ui.error-state>`).
10. `components/ui/page-header.blade.php`: Header halaman kontekstual (`<x-ui.page-header>`).

---

## 6. Toast System (Panduan Penggunaan)

Sistem toast diletakkan di `resources/views/layouts/dashboard.blade.php` sehingga otomatis aktif di seluruh halaman modul admin.

### Cara Menggunakan:
1. **Melalui Flash Session Laravel (Di Controller)**:
   ```php
   return redirect()->route('...')->with('success', 'Setoran sampah berhasil dicatat.');
   // Mendukung: with('success', ...), with('error', ...), with('warning', ...), with('info', ...)
   ```
   Toast otomatis muncul di pojok kanan atas layar secara mengambang dan hilang sendiri setelah 4 detik.
2. **Melalui JavaScript / Alpine.js**:
   ```javascript
   window.dispatchEvent(new CustomEvent('toast', {
       detail: { type: 'success', message: 'Data berhasil diperbarui.' }
   }));
   ```

---

## 7. Collapsible Sidebar (Perilaku Desktop & Mobile)

* **Desktop ($\ge$ 1024px)**:
  - Default: Mode melebar (`w-64 min-w-[16rem]`).
  - Tombol Toggle: Ikon panah ganda di sebelah kiri judul pada topbar.
  - Saat diciutkan (`sidebarCollapsed = true`):
    - Lebar menyusut menjadi `w-20 min-w-[5rem]` (80px).
    - Teks label nama menu dan sub-header seksi disembunyikan secara otomatis.
    - Ikon menu tetap berada di tengah dengan atribut `title="..."` asli sehingga tooltip native muncul saat di-hover.
    - Status tersimpan di `localStorage.sidebarCollapsed` sehingga pilihan operator bertahan saat berpindah halaman.
* **Mobile (< 1024px)**:
  - Sidebar desktop otomatis disembunyikan (`hidden lg:flex`).
  - Pengguna ponsel membuka navigasi melalui tombol hamburger yang memunculkan laci geser (*drawer overlay*) selebar 256px dengan latar belakang transparan gelap (`bg-black/50`).

---

## 8. Header Kontekstual

Komponen `<x-ui.page-header>` menyediakan slot:
* `$breadcrumbs`: Jejak tautan navigasi (contoh: *Data Master &rarr; Kategori Sampah*).
* `$title`: Judul utama halaman dalam ukuran `text-heading-lg` tebal.
* `$description`: Keterangan ringkas tujuan halaman.
* `$actions`: Tombol aksi primer/sekunder (contoh: *+ Catat Setoran Baru* atau *Export Excel*).

---

## 9. Dead Code Review (Status Komponen yang Tidak Terpakai)

Berdasarkan audit referensi kode, dilakukan tindakan sebagai berikut:
1. **`components/modern-summary-card.blade.php`** $\rightarrow$ **DIHAPUS (SAFE TO REMOVE)**.
2. **`components/modern-table-toolbar.blade.php`** $\rightarrow$ **DIHAPUS (SAFE TO REMOVE)**.
3. **`components/modern-badge.blade.php`** $\rightarrow$ **DIHAPUS (SAFE TO REMOVE)**.
4. **`components/modern-data-table.blade.php`** $\rightarrow$ **DIHAPUS (SAFE TO REMOVE)**.
5. **`components/modern-page-header.blade.php`** $\rightarrow$ **DIHAPUS (SAFE TO REMOVE)**.
6. **`components/modern-actions.blade.php`** $\rightarrow$ **DIHAPUS (SAFE TO REMOVE)**.
7. **`components/skeleton-card.blade.php`** $\rightarrow$ **DIPERTAHANKAN (KEEP FOR FOUNDATION)**.
8. **`components/skeleton-line.blade.php`** $\rightarrow$ **DIPERTAHANKAN (KEEP FOR FOUNDATION)**.
9. **`components/skeleton-table.blade.php`** $\rightarrow$ **DIPERTAHANKAN (KEEP FOR FOUNDATION)**.

---

## 10. Hasil Pengujian (Tests & Build Validation)

```bash
# 1. Kompilasi Aset Frontend (Vite)
npm run build
# Hasil: built in 705ms (CSS: 93.19 kB, JS: 87.66 kB) - 100% SUKSES

# 2. Pembersihan Cache View Laravel
php artisan view:clear && php artisan optimize:clear
# Hasil: Compiled views & cached bootstrap files cleared successfully

# 3. Uji Seluruh Test Suite Fitur & Regresi
php artisan test tests/Feature/ImportTemplateTest.php tests/Feature/DeviceTrackingTest.php tests/Feature/WastePriceHistoryTest.php tests/Feature/MaterialAliasTest.php tests/Feature/DepositImportTest.php tests/Feature/WasteSaleFlowTest.php tests/Feature/ProfileTest.php tests/Feature/Auth/AuthenticationTest.php tests/Feature/Auth/PasswordUpdateTest.php tests/Feature/WasteBankReportsTest.php tests/Feature/BalanceCheckTest.php
# Hasil: 103 passed, 416 assertions (100% PASS)

# 4. Uji Test Deposit Waste Customer
php artisan test tests/Feature/WasteCustomerTest.php --filter=test_deposit
# Hasil: 1 passed, 4 assertions (100% PASS)
```

---

## 11. Visual Smoke Test Hasil Rendering Halaman Kunci

Pengujian rendering runtime dengan device admin aktif:
* **Dashboard (`/bank-sampah/dashboard`)**: **HTTP 200 (OK)**. Layout ter-render normal tanpa ada layout break.
* **Setoran Create (`/bank-sampah/deposits/create`)**: **HTTP 200 (OK)**. Alur split-screen, grid timbangan, dan rekap hari ini berjalan utuh.
* **Nasabah List (`/bank-sampah/customers`)**: **HTTP 200 (OK)**.
* **Penjualan Create (`/bank-sampah/sales/create`)**: **HTTP 200 (OK)**.
* **Import Setoran (`/bank-sampah/deposits/import`)**: **HTTP 200 (OK)**.

---

## 12. Files Changed & Files NOT Changed

### Files Changed:
* `tailwind.config.js`: Ditambahkan token warna semantik earthy, tipografi, radius, dan shadow.
* `resources/css/app.css`: Ditambahkan variabel CSS semantik Light/Dark mode.
* `resources/views/layouts/dashboard.blade.php`: Integrasi state sidebar collapsible dan global toast container.
* `resources/views/layouts/partials/sidebar.blade.php`: Dukungan mode ciut 80px/lebar 256px dan token warna baru.
* `resources/views/layouts/partials/topbar.blade.php`: Tombol toggle ciut desktop dan token warna baru.
* `resources/views/components/primary-button.blade.php`: Diselaraskan dengan brand button semantik.
* `resources/views/components/secondary-button.blade.php`: Diselaraskan dengan brand button semantik.
* `resources/views/components/danger-button.blade.php`: Diselaraskan dengan brand button semantik.
* `resources/views/components/text-input.blade.php`: Diselaraskan dengan hybrid input semantik.
* `resources/views/components/input-label.blade.php`: Ditambahkan dukungan teks `(Wajib diisi)`.
* `resources/views/components/sidebar-link.blade.php`: Diselaraskan dengan state sidebar ciut.

### Files NOT Changed (Sengaja Tidak Dimigrasikan / Dijaga Utuh pada Phase 1):
* Seluruh controller di `app/Http/Controllers/`.
* Seluruh service di `app/Services/`.
* Seluruh model di `app/Models/`.
* Seluruh berkas migrasi di `database/migrations/`.
* Halaman bisnis: `dashboard/admin-bank-sampah.blade.php` (belum diredesain, menunggu Phase 4).
* Halaman bisnis: `bank-sampah/deposits/create.blade.php` & `index.blade.php` (menunggu Phase 2).
* Halaman bisnis: `bank-sampah/sales/create.blade.php` & `index.blade.php` (menunggu Phase 3).
* Halaman bisnis: `warga/savings/*.blade.php` (menunggu Phase 4).
* Halaman bisnis: `bank-sampah/customers/*.blade.php`, `collectors/*.blade.php`, `waste-prices/*.blade.php`, `reports/*.blade.php`.

---

## 13. Known Limitations (Limitasi yang Direncanakan)

* Halaman-halaman operasional existing masih menggunakan kelas warna dan ukuran font bawaan (`bg-slate-50`, `text-xs`, `text-emerald-600`). Hal ini **disengaja** untuk mematuhi aturan ketat: *No Mass Migration* pada Phase 1.
* Migrasi penerapan komponen baru (`<x-ui.button>`, `<x-ui.badge>`, `<x-ui.card>`, `<x-ui.table>`, `<x-ui.page-header>`) akan dilakukan secara bertahap pada fase-fase berikutnya dimulai dari alur rujukan Setoran (Phase 2).

---
*Dokumen ini merupakan catatan resmi penuntasan Phase 1 Design Foundation dan siap dijadikan pijakan untuk Phase 2.*
