# Phase 3 — Dashboard Reference Implementation

> **Status:** Completed  
> **Acuan Desain:** `DESIGN.md` (EcoBank026 Design System v1.0)  
> **Baseline Audit:** `UI-AUDIT.md` (Section 3.1 & 10)  
> **Fondasi Sistem:** `PHASE-1-FOUNDATION.md`  
> **Workflow Reference:** `PHASE-2-SETORAN.md`  
> **Fokus Utama:** Redesain visual dan hierarki informasi **Dashboard Bank Sampah** sebagai **VISUAL REFERENCE IMPLEMENTATION** kedua setelah modul Setoran.

---

## 1. Design Direction (Arah Desain)

Dashboard Bank Sampah telah ditransformasikan secara menyeluruh dari *AI-generated generic SaaS template* menjadi **Modern Operational × Warm Human**:
* **Action-First Priority:** Menu aksi utama (*+ Setoran Baru* dan *Jual ke Agregator*) diletakkan langsung di bagian atas hero header (bukan terkubur di bagian paling bawah layar).
* **Eliminasi AI-Slop:**
  - Menghapus lingkaran blur gradien dekoratif (`blur-3xl`, `from-blue-500/10`).
  - Menghapus radius sudut ekstrem (`rounded-[2rem]`) dan menggantinya dengan soft radius semantik (`rounded-radius-lg` 14px dan `rounded-radius-md` 10px).
  - Menghapus penundaan animasi bertingkat artifisial (`delay-[200ms]` s/d `delay-[1000ms]`) sehingga dashboard termuat seketika tanpa jeda kosong.
  - Menghapus kartu metrik vanity dan formula donut "Total Aset" yang menggabungkan kas dan liabilitas secara keliru.
* **Balanced Density & Readability:**
  - Seluruh teks body dan metrik operasional memenuhi standar baseline 16px (`text-body`) hingga 24–30px (`text-heading-lg`/`xl`).
  - Label dan metadata menggunakan `text-caption` (12px, line-height 1rem) yang proporsional.
  - Tidak ada lagi penggunaan `text-[10px]`, `text-[11px]`, atau `text-xs` pada informasi utama dashboard.

---

## 2. Information Hierarchy (Hierarki Informasi)

Hierarki informasi disusun secara terstruktur dari yang paling penting dan sering dibutuhkan operator:

```text
1. HERO / ACTION-FIRST HEADER
   ├── Contextual Badge ("Pusat Kendali Bank Sampah")
   ├── Page Title ("Bank Sampah EcoBank026")
   ├── Brief Purpose Description
   └── Primary Actions:
       ├── [+ Setoran Baru] (Primary action button)
       └── [Jual ke Agregator] (Secondary outline button)

2. CORE OPERATIONAL KPIS (4 Kartu Utama)
   ├── Kas Bank Sampah (Likuiditas kas fisik operasional berjalan)
   ├── Total Saldo Nasabah (Akumulasi simpanan tabungan nasabah)
   ├── Nasabah Terdaftar (Jumlah nasabah aktif dengan tautan ke direktori)
   └── Total Setoran (Akumulasi nilai seluruh timbangan masuk)

3. SUPPORTING VOLUME METRICS (2 Kartu Sekunder)
   ├── Total Penarikan Saldo (Arus kas keluar tabungan nasabah)
   └── Total Penjualan Agregator (Omzet hasil muat truk ke pembeli/pengepul)

4. CHARTS & FINANCIAL ARUS (Dengan Honest Empty States)
   ├── Arus Timbangan (Setoran Masuk vs Penarikan Saldo - Bar Chart)
   └── Komposisi Distribusi Dana (Saldo Nasabah, Kas, Penjualan - Donut Chart)

5. RECENT OPERATIONAL ACTIVITY (2 Kolom Terpadu)
   ├── Mutasi Tabungan Nasabah Terbaru (Nama, Tanggal, Ket, +Rp / -Rp)
   └── Arus Kas Operasional Terbaru (Deskripsi, Tanggal, Masuk/Keluar)

6. MENU CEPAT OPERASIONAL (8 Direct Shortcuts Grid)
   └── Setoran, Penarikan, Penjualan, Pengeluaran, Kategori, Harga, Agregator, Nasabah
```

---

## 3. Asset Used (Penggunaan Aset Resmi)

* **Logo Resmi:** Menggunakan aset asli `public/images/logo.png` (PNG 500x500 piksel).
* **Sidebar Integration:**
  - Pada mode expanded (256px): Logo tampil proporsional (`w-8 h-8 rounded-radius-md object-contain shrink-0`) berdampingan dengan tipografi brand `ECOBANK026` dan sub-label `Bank Sampah`.
  - Pada mode collapsed (80px): Logo tampil di tengah secara bersih tanpa distorsi aspect ratio.
  - **Zero Fake Logo:** Tidak lagi menggunakan inisial kotak "EB" hijau buatan atau SVG generic.
* **Hero Illustration:** Sesuai audit dan aturan Section 0, tidak ada gambar ilustrasi yang dipaksakan. Dashboard tampil lengkap, rapi, dan matang mengandalkan tipografi, ikon semantik, kartu hibrida, dan whitespace terukur.

---

## 4. Components Reused (Komponen yang Digunakan Kembali)

* `<x-layouts.dashboard>`: Layout shell utama dengan collapsible sidebar, topbar kontekstual, dark mode switch, dan toast container.
* `x-sidebar-link`: Tautan navigasi sidebar dengan status aktif semantik dan integrasi mode ciut.
* Palet warna semantik Phase 1: `bg-surface`, `bg-surface-muted`, `bg-background`, `border-border`, `border-border-subtle`, `text-text`, `text-text-muted`, `bg-primary`, `bg-primary-light`, `text-primary`, `bg-success-light`, `text-success-text`, `bg-danger-light`, `text-danger-text`, `bg-secondary-light`, `text-secondary`.
* Token border radius Phase 1: `rounded-radius-sm` (6px), `rounded-radius-md` (10px), `rounded-radius-lg` (14px).

---

## 5. Components Changed (Komponen yang Diubah)

1. `resources/views/layouts/partials/sidebar.blade.php`:
   - Mengganti kotak placeholder inisial teks "EB" dengan aset resmi `public/images/logo.png` pada mode expanded dan collapsed.
2. `resources/views/dashboard/admin-bank-sampah.blade.php`:
   - Redesain 100% dari struktur lama berbasis slate dan AI-slop ke arsitektur semantic tokens.
   - Mengintegrasikan *Honest Empty State* pada visualisasi grafik sehingga jika database kosong (`0`), sistem menampilkan pesan informatif bermakna dan tombol aksi (bukan grafik kosong yang tampak seperti bug).
   - Memperbaiki tautan route `bank-sampah.savings` dan `bank-sampah.cash-report`.
   - Mengisolasi inisialisasi ApexCharts ke fungsi `initDashboardCharts()` yang melacak instans pada `window._ecoDashboardCharts` untuk mencegah memory leak.

---

## 6. Responsive Behavior (Perilaku Desktop, Tablet, Mobile)

| Viewport | Perilaku Layout | Penyesuaian Visual |
|---|---|---|
| **Desktop ($\ge$ 1024px)** | Layout grid 4 kolom untuk KPI, 2 kolom terbagi 7:5 untuk grafik, 2 kolom untuk aktivitas terbaru, 4 kolom untuk menu cepat. | Sidebar persisten (mode 256px atau 80px), aksi header sejajar horizontal dengan judul. |
| **Tablet (768px – 1023px)** | KPI bertransisi menjadi grid 2x2. Grafik menumpuk secara vertikal (1 kolom penuh per grafik). Menu cepat bertransisi menjadi 4 kolom atau 2 kolom. | Padding kartu menyesuaikan (`p-5`), tipografi tetap proporsional tanpa clipping. |
| **Mobile (< 768px)** | Header hero bertumpuk secara vertikal (judul di atas, tombol aksi lebar penuh di bawah). KPI menjadi 1 kolom vertikal. Tabel/list aktivitas menumpuk rapi tanpa overflow horizontal. | Tombol aksi primer mudah dijangkau ibu jari (*thumb-friendly*), grafik menyesuaikan lebar layar tanpa scroll menyamping. |

---

## 7. Dark Mode (Penyelarasan Tema Gelap)

* **Contrast & Surface:**
  - Background beralih ke Charcoal Warm (`#0c0a09` / `bg-background`).
  - Card Surface menggunakan Card Stone-900 (`#1c1917` / `bg-surface`).
  - Border tetap terlihat halus menggunakan Stone-800 (`#292524` / `border-border`).
  - Teks menggunakan Stone-100 (`#f5f5f4`) dan muted Stone-400 (`#a8a29e`), menghasilkan kontras rasio WCAG AA tinggi.
* **ApexCharts Dark Mode:**
  - Sumbu (axis) dan teks legend otomatis mendeteksi mode gelap melalui properti `theme: { mode: isDark ? 'dark' : 'light' }`.
  - Garis grid menggunakan warna border semantik `#292524` (dark) dan `#e7e5e4` (light).
  - Stroke batas pie/donut mengikuti warna surface kartu (`#1c1917` pada dark mode, `#ffffff` pada light mode).

---

## 8. SPA Navigation Compatibility (Kompabilitas Navigasi SPA)

* Dashboard beroperasi 100% selaras dengan arsitektur navigasi instan:
  - Saat navigasi ke Dashboard via SPA (`X-Eco-Nav: 1`), server hanya mengembalikan fragment `<div id="eco-spa-fragment">` (ukuran payload ~25 KB vs ~60 KB dokumen penuh, hemat ~58% bandwidth).
  - Fungsi `initDashboardCharts()` berjalan otomatis baik pada initial load (`DOMContentLoaded`) maupun setelah swap DOM selesai (`document.readyState === 'complete'`).
  - Saat berpindah keluar dari Dashboard (misal ke Data Nasabah), array instans chart pada `window._ecoDashboardCharts` dihancurkan via `chart.destroy()` untuk mencegah orphan timer atau memory leak.
  - Saat menekan tombol browser *Back* kembali ke Dashboard, grafik ter-render ulang seketika.

---

## 9. Verification & Test Evidence (Bukti Pengujian)

```bash
# 1. Kompilasi Aset Frontend (Vite)
npm run build
# Hasil: built in 687ms (CSS: 101.72 kB, JS: 93.20 kB) - 0 errors, 0 warnings

# 2. Pembersihan Cache View & Optimize
php artisan view:clear && php artisan optimize:clear
# Hasil: Compiled views & cached bootstrap files cleared successfully

# 3. Eksekusi Test Khusus Dashboard Reference
php artisan test tests/Feature/DashboardReferenceTest.php
# Hasil:
# ✓ dashboard renders with official logo and semantic tokens
# ✓ dashboard renders charts and activities when data exists
# ✓ dashboard spa fragment contains no micro text or slate
# Tests: 3 passed (33 assertions) - Duration: 270ms

# 4. Eksekusi Seluruh Regression Suite (13 Test Suites)
php artisan test tests/Feature/DashboardReferenceTest.php tests/Feature/SpaNavigationTest.php tests/Feature/ImportTemplateTest.php tests/Feature/DeviceTrackingTest.php tests/Feature/WastePriceHistoryTest.php tests/Feature/MaterialAliasTest.php tests/Feature/DepositImportTest.php tests/Feature/WasteSaleFlowTest.php tests/Feature/ProfileTest.php tests/Feature/Auth/AuthenticationTest.php tests/Feature/Auth/PasswordUpdateTest.php tests/Feature/WasteBankReportsTest.php tests/Feature/BalanceCheckTest.php
# Hasil: 111 passed, 474 assertions (100% PASS)

# 5. Uji Regresi Transaksi Setoran
php artisan test tests/Feature/WasteCustomerTest.php --filter=test_deposit
# Hasil: 1 passed, 4 assertions (100% PASS)
```

---

## 10. Known Limitations (Limitasi yang Diketahui)

1. **Dependensi CDN ApexCharts:** ApexCharts saat ini masih dimuat melalui CDN script pada layout shell. Di masa mendatang pada fase pembersihan aset, bundle visualisasi dapat diintegrasikan secara modular ke dalam bundle Vite lokal.
2. **Data Sampel Awal:** Pada instalasi database fresh, seluruh nilai finansial bernilai Rp 0 sehingga visualisasi menampilkan *Honest Empty State*. Grafik bar dan donut akan otomatis aktif begitu transaksi setoran, penarikan, atau penjualan pertama dicatat.

---

## 11. Phase 3.1 — Visual Polish (Typography Lock + Official Hero Illustration)

> **Status:** Completed — limited polish only, no dashboard redesign, no business-logic change.

### 11.1 Typography decision → Inter (single system)
* **Audit:** app previously used **Figtree** (Bunny Fonts) as `fontFamily.sans` + 7 Blade font links + ApexCharts `fontFamily`. Print/PDF views intentionally keep system fonts (print context, out of scope).
* **Decision:** **Inter** everywhere via the existing Bunny Fonts mechanism (no new dependency): `tailwind.config.js` (`sans: ['Inter', ...]`), all 7 Blade font links (`family=inter:`, same weights), dashboard ApexCharts `fontFamily: 'Inter, sans-serif'`. Verified: zero `figtree` leftovers in app views.
* **Hierarchy** (enforced by existing size/weight tokens, consistent with Setoran reference): page title Inter 700 (`text-heading-lg/xl`), section title Inter 600 (`text-heading-sm`), KPI value Inter 700 (`text-heading-lg` mono numerals), body Inter 400–500 (`text-body/body-sm`), buttons Inter 600 (`text-body-sm font-semibold`), caption/meta Inter 400–500 (`text-caption`). No `text-[10px]`/`text-[11px]`/`text-xs` on primary info.

### 11.2 Illustration asset (official, provided)
* **Asset (WAJIB, sudah disediakan):** `public/images/dashboard-bank-sampah-illustration.png` (1950×807, RGBA, ~1.2 MB). Used as-is — no regeneration, no replacement, no internet assets, no new logo (`logo.png` untouched).
* **Hero layout:** LEFT content (`flex-1 min-w-0`: context label → title → description → actions) + RIGHT illustration (`md:basis-[36%] md:max-w-[36%]`, `md:max-w-[320px]`, `object-contain`, no distortion). Landscape 2.4:1 ratio keeps hero compact (~124px tall at 320px wide). Mobile stacks label → title → description → actions → illustration (`w-56 sm:w-64`, centered, no overflow, buttons never covered).
* **Light mode:** PNG blends into existing `bg-surface` hero; no extra background card added; semantic tokens unchanged.
* **Dark mode:** single asset, non-destructive CSS treatment only (`.dark .hero-illustration { filter: brightness(1.07) saturate(1.05); }`) so it stays legible without overpowering text hierarchy.
* **SPA compatibility:** static `<img>` inside `#app-content-slot`; no JS init, no duplicate init, no layout jump. Verified across Dashboard → Nasabah → Back → Dashboard cycle at fragment level.

### 11.3 Responsive behavior
| Viewport | Hero layout |
|---|---|
| Desktop (≥1024px) | Text + actions left (~64%), illustration right (~36%, ≤320px). |
| Tablet (768–1023px) | Horizontal while comfortable (`md:` breakpoint); stacks naturally below. |
| Mobile (<768px) | Vertical stack: label → title → description → actions → illustration; no horizontal overflow, hero stays compact. |

### 11.4 Verification (Phase 3.1)
```bash
npm run build
# 0 errors (CSS/JS rebuilt)
php artisan view:clear && php artisan optimize:clear  # cleared
php artisan test <13-suite regression incl. DashboardReferenceTest + SpaNavigationTest>
# 111 passed (100% PASS)
php artisan test tests/Feature/WasteCustomerTest.php --filter=test_deposit
# 1 passed, 4 assertions (100% PASS)
```
* Test assertions: `family=inter:` present, `family=figtree:` absent, `images/logo.png` present, `images/dashboard-bank-sampah-illustration.png` present, `.svg` variant absent, `hero-illustration` class present.
* Smoke (normal + `X-Eco-Nav` fragment): logo ok (persistent shell), illustration ok in both, zero Figtree leftovers.
* Visual checklist: logo resmi ok, illustration proporsional tanpa distorsi/overflow, CTA tetap dominan, hero compact, tidak ada slate hardcoded baru, tidak ada broken asset.

### 11.5 Files changed (Phase 3.1 scope only)
* `resources/views/dashboard/admin-bank-sampah.blade.php` — hero RIGHT `<img>` → official PNG (+ `object-contain`, responsive widths).
* `tests/Feature/DashboardReferenceTest.php` — illustration assertions → `.png` (+ `.svg` absence).
* Typography lock (retained): `tailwind.config.js`, 7 Blade font links, dashboard ApexCharts `fontFamily`.
* No backend / database / business-logic / other-page changes.

---
*Dokumen ini merupakan laporan resmi penuntasan Redesain Dashboard Bank Sampah sebagai Reference Implementation Visual EcoBank026.*
