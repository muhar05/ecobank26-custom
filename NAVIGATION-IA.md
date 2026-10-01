# Navigation IA + Typography Foundation — EcoBank026

> **Status:** Completed  
> **Acuan:** `DESIGN.md` · `UI-AUDIT.md` · `PHASE-1-FOUNDATION.md` · `PHASE-2-SETORAN.md` · `PHASE-3-DASHBOARD.md`  
> **Scope:** Sidebar Information Architecture + global Plus Jakarta Sans + light contextual links.  
> No redesign, no business-logic / database / route / permission changes.

---

## 1. Current Navigation Problem

Sidebar sebelumnya menampilkan **11 link datar dalam 4 seksi** (`Main`, `Data Master`, `Transaksi`, `Laporan`) yang semuanya terlihat sama penting:

- `Data Master` berisi campuran unrelated: Kategori Sampah, Agregator, Harga Sampah, **Perangkat Saya**, **Data Nasabah**.
- `Data Nasabah` (daily workflow) terkubur di bawah item settings-like.
- `Transaksi` mencampur alur harian (Setoran) dengan back-office (Pengeluaran).
- Tidak ada visual hierarchy antara *peta utama aplikasi* vs *pengaturan* vs *data referensi*.

## 2. Route Inventory (Bank Sampah)

| Route (name) | URL | Guard |
|---|---|---|
| `bank-sampah.dashboard` | `/bank-sampah/dashboard` | `permission:view_waste_bank` |
| `bank-sampah.deposits.*` | `/bank-sampah/deposits…` (+import) | `permission:manage_deposits` |
| `bank-sampah.withdrawals.*` | `/bank-sampah/withdrawals…` | `permission:manage_withdrawals` |
| `bank-sampah.sales.*` | `/bank-sampah/sales…` (+daily-recap) | `permission:manage_sales` |
| `bank-sampah.expenses.*` | `/bank-sampah/expenses…` | `role:admin_bank_sampah\|admin_rw` |
| `bank-sampah.customers.*` | `/bank-sampah/customers…` | `permission:manage_waste_customers` |
| `bank-sampah.savings` | `/bank-sampah/savings` | `permission:view_waste_reports` |
| `bank-sampah.cash-report` | `/bank-sampah/cash-report` | `permission:view_waste_reports` |
| `bank-sampah.waste-categories.*` | `/bank-sampah/waste-categories…` (+import, +groups) | `permission:manage_waste_prices` |
| `bank-sampah.collectors.*` | `/bank-sampah/collectors…` | `permission:manage_waste_prices` |
| `bank-sampah.waste-prices.*` | `/bank-sampah/waste-prices…` (+import, history) | `permission:manage_waste_prices` |
| `bank-sampah.devices.*` | `/bank-sampah/devices…` | `auth` |
| `bank-sampah.reports.*` | `/bank-sampah/reports…` (8 endpoints, no sidebar link) | `role:admin_bank_sampah\|admin_rw` |
| `warga.savings[.history]` | `/warga/savings…` | `permission:view_own_savings` (+`warga`) |

URL, permission, dan authorization **tidak diubah sama sekali**.

## 3. Route Classification

- **A. Primary workflow:** Setoran Sampah, Penjualan, Data Nasabah, Saldo Nasabah (harian, revenue-affecting).
- **B. Supporting workflow:** Penarikan Saldo, Pengeluaran Operasional, Kas Bank Sampah (operasional pendukung).
- **C. Master data:** Kategori Sampah, Agregator, Harga Sampah (referensi, jarang berubah).
- **D. Report:** Saldo Nasabah, Kas Bank Sampah, `reports.*` (read-only; `reports.*` tetap tanpa link sidebar seperti sebelumnya).
- **E. Settings:** Perangkat Saya (manajemen perangkat admin).
- **F. Contextual pages:** create/edit/import/history/show (diakses via aksi kontekstual, bukan sidebar).

## 4. Final Navigation Hierarchy

```text
MAIN
└── Dashboard
OPERASIONAL
├── Setoran Sampah
├── Penjualan
├── Penarikan Saldo
└── Pengeluaran Operasional
NASABAH
└── Data Nasabah
LAPORAN
├── Saldo Nasabah
└── Kas Bank Sampah
LAINNYA
├── Master Data (submenu, max depth 2)
│   ├── Kategori Sampah
│   ├── Agregator
│   └── Harga Sampah
└── Perangkat Saya
AKUN
└── Keluar
```

Keputusan vs starting point §8: **diikuti 100%**. Penarikan Saldo ditempatkan di OPERASIONAL (bukan NASABAH) karena ia adalah *transaction workflow* kas keluar yang dieksekusi operator setiap hari — sejalan dengan ordering Setoran → Penjualan → Penarikan → Pengeluaran (arus dana masuk → keluar). Seksi warga (`Bank Sampah Saya`) dan `admin_rt` view-only tidak diubah.

## 5. Primary Workflow

Setoran Sampah → Penjualan → Data Nasabah → Saldo Nasabah: 4 link teratas secara visual (2 klik pertama operator setiap hari), masing-masing 1 klik dari mana saja via sidebar persisten + SPA.

## 6. Supporting Workflow

Penarikan Saldo, Pengeluaran Operasional, Kas Bank Sampah, Perangkat Saya tetap 1 klik, tetapi secara visual diturunkan prioritasnya (seksi bawah).

## 7. Contextual Navigation (baru, ringan)

Hanya 3 penambahan, semua memakai route + permission yang sudah ada:

| Dari | Ke | Bentuk |
|---|---|---|
| Setoran index | Data Nasabah (`customers.index`, `@can manage_waste_customers`) | text link header |
| Nasabah detail → Keuangan | Buku Tabungan (`savings`, `@can view_waste_reports`) + Catat Setoran (`deposits.create`, `@can manage_deposits`) | footer link section |
| Penjualan index | Agregator (`collectors.index`) + Harga Sampah (`waste-prices.index`, `@can manage_waste_prices`) | text link header |

Tidak ada business flow baru; tidak ada UI penuh.

## 8. Master Data Strategy

Kategori/Agregator/Harga **tidak lagi** item primer. Satu parent `Master Data` (button + chevron, `data-master-toggle`) dengan submenu max-depth 2. State server-initial dari `request()->is()`; toggle manual via Alpine; SPA-nav auto-expand saat mendarat di child (`eco:master-sync` event, hanya open — tidak pernah auto-collapse).

## 9. Device / Settings Strategy

`Perangkat Saya` pindah dari `Data Master` ke `LAINNYA` (settings/account area). Route, controller, dan proteksi tidak berubah. `reports.*` tetap tanpa link sidebar (diakses kontekstual) seperti sebelumnya.

## 10. Typography Decision → Plus Jakarta Sans

Menggantikan Inter (CDN) sebagai sistem global tunggal: Page Title 700, Section Title 600, KPI/angka penting 600–700 (800 dipertahankan **hanya** untuk emphasis angka finansial existing di reference Setoran/Dashboard agar tidak regresi visual), Body 400–500, Button 500–600, Navigation 500 (`font-medium` existing), Caption/meta 400–500. Ukuran mengikuti token semantik (`text-body` 16px baseline); tidak ada `text-[10px]/[11px]/text-xs` baru pada info utama.

## 11. Font Implementation (self-hosted, tanpa CDN)

- Audit: Plus Jakarta Sans **belum tersedia**; app bergantung pada Bunny Fonts CDN (7 layout).
- Implementasi: 5 file `woff2` latin (400/500/600/700/800, ±12 KB/file, total ±60 KB) diunduh sekali lalu di-host di `resources/fonts/`, direferensikan via `@font-face` (`font-display: swap`) di `resources/css/app.css`, di-bundle Vite dengan hash (lihat `public/build/assets/plus-jakarta-sans-*-latin-*.woff2`).
- `tailwind.config.js`: `sans: ['Plus Jakarta Sans', ...default]`.
- **Semua 7 tag `<link fonts.bunny.net>` dihapus** — nol dependensi CDN font di production.
- Satu-satunya `fontFamily` JS (ApexCharts dashboard) → `'Plus Jakarta Sans, sans-serif'`.
- Catatan audit: file Google Fonts untuk semua weight ternyata byte-identik (salah ambil subset); diganti file statis fontsource per-weight yang terverifikasi (magic `wOF2`, hash unik).

## 12. Mobile Behavior

Drawer memakai hierarchy **identik** dengan desktop (MAIN/OPERASIONAL/NASABAH/LAPORAN/LAINNYA/AKUN). Master Data expandable di drawer; nav `overflow-y-auto` sehingga submenu panjang tetap terjangkau. Terverifikasi via screenshot 390px.

## 13. SPA Compatibility (`X-Eco-Nav: 1`)

- Sidebar/topbar di luar `#app-content-slot` → tidak reload; font tidak flash (font-face di CSS persisten).
- `POC_ELIGIBLE_PATHS` diperluas: `dashboard, deposits, deposits/create, customers` (memenuhi rute uji §18).
- `updateSidebarActive()` ditulis ulang generik (exact-or-prefix match) + sinkron parent Master Data (`data-master-toggle` + event `eco:master-sync`).
- Navigasi Dashboard → Setoran → Nasabah → Back → Forward terverifikasi di browser nyata, **0 console error**.
- Fallback native (redirect/error/JS-mati) tidak berubah.

## 14. Verification

```bash
npm run build
# CSS 94.11 kB (naik ~0.3 kB: @font-face) + 5 woff2 ±60 KB — 0 errors
php artisan test <14 suites: NavigationIaTest + DashboardReferenceTest + SpaNavigationTest + 11 regression>
# 116 passed, 521 assertions (100% PASS)
php artisan test tests/Feature/WasteCustomerTest.php --filter=test_deposit
# 1 passed, 4 assertions (100% PASS)
```

Browser smoke (Playwright/Chromium nyata, user `admin_bank_sampah`): expanded ✓, submenu expand ✓ (chevron rotate, indent `border-l`), collapsed 80px tanpa glitch ✓, mobile drawer + submenu ✓, dark mode earthy readable ✓, SPA 4-node + back/forward URL benar ✓, `getComputedStyle` = `Plus Jakarta Sans` pada body/h1/sidebar ✓, console errors = 0 ✓. Bug kecil ditemukan saat smoke dan diperbaiki: label ganda `+ + Setoran Baru` → `Setoran Baru`.

Screenshots: `docs/navigation-ia/` (`sidebar-desktop-expanded/collapsed`, `sidebar-submenu-master-data`, `sidebar-mobile-drawer`, `dashboard-dark`, `setoran-typography`).

**Batasan:** screenshot tablet tidak diambil (CSS breakpoint `sm/lg` tidak berubah, risiko rendah); font non-latin fallback ke system sans (latin-only subset, disengaja untuk bobot ringan).
