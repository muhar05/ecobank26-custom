# PHASE-SETORAN-RESOLUTION-UX.md

UX restructuring of Deposit Import Step 2 (Preview & Resolution) + Step 3 (Recap).
Backend/business logic untouched — pure presentation-layer reorganization on top
of the existing unique-entity resolution engine.

---

## 1. Before vs After UX

**Before:** one giant vertically stacked page — status banner, metadata, 6 metric
chips, nasabah panel, material panel, price panel, duplicate panel, full raw
rows table (up to 1000+ rows rendered at once), recap tables, summary tiles,
commit bar. Operator scrolled through everything; "52 nasabah + 30 material"
read as 82 manual chores; no sense of step or progress.

**After:** two workspaces.
- **Step 2 — Resolve Entities:** global summary cards → resolution progress
  (X/Y Nasabah, X/Y Material) + gated CTA → tab bar
  `[ Nasabah (N) ] [ Material (M) ] [ Penyesuaian (P) ] [ Preview Data ]`
  → per-tab search + 20-per-page pagination → mapping controls per UNIQUE
  entity with "muncul di N baris" reassurance.
- **Step 3 — Rekap (`?view=recap`):** back link → Siap Import header
  (rows/customers/materials/weight/value) → Rekap Material + Rekap Nasabah
  with grand totals → validation checklist → summary tiles → sticky commit bar
  (`Import N Baris & Tambah Saldo`).

## 2. Entity-Level Resolution (unchanged engine, new presentation)

- Tabs show UNIQUE unresolved labels only (never raw rows): customer mapping
  keyed by raw name, material mapping keyed by raw label — one action covers
  all repeated rows (e.g. "Budi Baru — muncul di 45 baris").
- Counts in tab labels are unique-entity counts (`Nasabah (10)`), and the
  overview cards state "52 nasabah unik belum ditemukan"-style wording via
  `uniqueCounts` (no "X items" ambiguity).
- Blocking (nasabah/material/error) vs optional (price snapshot default Excel,
  duplicate default append) is visually separated; optional lives in its own
  Penyesuaian tab with an explicit non-blocking label.

## 3. Bulk Customer Creation

- Unchanged endpoint/behavior/validation/code-generation/activity-log/atomicity.
- Presentation: header button `+ Tambahkan N Nasabah (Bulk Add)` is the
  visually primary action whenever N ≥ 1 (no longer gated on N > 1);
  per-row `+ Daftar Baru` opens the same review modal; modal title and submit
  carry the live count (`Tambahkan N Nasabah & Petakan Otomatis`).
- After success, staging re-process propagates the new IDs to every matching
  staged row; UI shows `✓ Semua nasabah terselesaikan`.

## 4. Material Mapping

- One batch form per unique label (`Simpan Pemetaan Material`), alias checkbox
  preserved (`Simpan … permanen`), Quick-Add-Category modal preserved.
- No auto-create, no fuzzy matching, scoped `BANK_SAMPAH` aliases untouched.

## 5. Search

- Client-side, per-tab, filters the UNIQUE entity list (nasabah, material) and
  the raw preview rows; scoped to its own list only; 14–16px input, 44px+ tap
  targets; no backend change, no staging reload.

## 6. Pagination

- Client-side, 20 entities/rows per page with prev/next + `Halaman X / Y` and
  `Menampilkan A–B dari N` counters. Server still renders full lists (bounded
  mapping controls: exactly one select per unique label — 50 selects for
  50 customers, never 1000). State lives server-side in staging cache, so tab
  switches, search, and pagination never lose mappings; active tab persists
  via `sessionStorage` across the full-page POST-redirects the backend uses.

## 7. Preview Data

- Demoted to inspection-only tab with its own search + pagination + sticky
  header + internal horizontal scroll. No mapping controls inside.

## 8. Recap (Step 3)

- Same `buildRecap()` dataset as commit; grand totals; validation checklist
  (nasabah resolved / material resolved / weight consistent / value consistent
  / no blocking error / saldo note); summary tiles; single sticky commit bar.
- Commit button states the consequence: `Import N Baris & Tambah Saldo`.
- Mobile: sticky bar only exists on Step 3; `bottom-4` offset so it never
  covers content; tables scroll internally; tabs scroll horizontally.

## 9. Mobile Behavior (390px, verified)

- No page-level horizontal overflow on resolve or recap; tab bar scrolls;
  pager buttons 44×44; tab height ~42px; search input readable; recap CTA
  46px tall and visible without hunting.

## 10. Tests

- `tests/Feature/DepositImportTest.php` — 26 tests pass, including:
  - updated recap test (Step 3 URL, `Import 4 Baris`, back link);
  - **Dataset A** (100 rows / 10 customers / 5 materials): unique counts,
    tab badges, one bulk add, one-mapping-per-label, 10 deposits;
  - **Dataset B** (1000 rows / 50 customers / 20 materials): bounded controls
    (50 customer selects, 17 material selects), search/pager markup present,
    full resolve → 50 deposits + 1000 details;
  - **Dataset C** (duplicate labels): one mapping resolves all matching rows;
  - **Dataset D** (alias): auto-resolves with zero manual mapping.

## 11. Browser Smoke (real Chromium, synthetic 100-row fixture)

- Resolve tabs render with correct unique counts; search filters live;
  pagination advances pages; tab persists across reload; recap shows tables,
  validation, and `Import 100 Baris`; full resolve → commit → 10 deposits /
  100 details / 10 ledgers / correct saldo; desktop + 390px + dark mode clean;
  SPA navigation intact; **0 console errors**.

## 12. Limitations

- Lists are server-rendered then paginated client-side (DOM weight grows with
  entity count; mapping controls stay bounded by unique count).
- Auto-submit radios (price/duplicate) still full-reload by design; tab state
  is restored afterwards via `sessionStorage`.
- Fixed a pre-existing broken `x-text` quote-escaping in the Quick-Add
  Category modal (was throwing an Alpine init error on Step 2 with materials).
