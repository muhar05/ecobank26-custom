# PHASE-SETORAN-ADJUSTMENT.md

Dokumentasi adjustment Flow Setoran Sampah / Import Setoran Excel.
Basis audit: `FLOW-SETORAN-AUDIT.md`. Bukan rewrite — hanya adjustment yang diminta.

---

## 1. Final Business Flow (berlaku)

```text
Excel (Nasabah | Material | Berat | Harga | Total, 1 tanggal transaksi)
  ↓ Upload + Parse (deteksi header otomatis, staging di Cache 3 jam)
  ↓ Resolve Customer (unique entity: code → normalized name → mapping → Bulk/Quick Add)
  ↓ Resolve Material (unique label: canonical → scoped alias → mapping → Quick Add)
  ↓ Rekap Material (Material | Satuan | Total Berat + Grand Total Berat)
  ↓ Rekap Nasabah (Nasabah | Total Berat | Total Nilai + Grand Total)
  ↓ Validation (canCommit + cross-check rekap; error/mapping/ambiguous memblokir)
  ↓ Atomic Commit (group by Customer + Date)
  ↓ 1 Deposit + N DepositDetail + 1 SavingsLedger CREDIT per nasabah
  ↓ Saldo nasabah otomatis bertambah (tidak ada approval manual tambahan)
```

## 2. Total Source-of-Truth Rule

Aturan final di `DepositImportService::processRows`:

```text
IF Excel Total tersedia (kolom Total terisi):
    transaction_total = Excel Total   (nilai source dihormati, mis. Sirsak)
ELSE:
    transaction_total = round(Berat × Harga)
```

- Tidak ada lagi warning agresif akibat selisih pembulatan kecil: jika Total
  source tersedia, itulah nilai transaksi (contoh: `0.55 × 150 = 82.5`
  tetapi source `83` → transaksi tercatat **Rp83**).
- `used_price` (snapshot harga Excel/master sesuai mode) tetap disimpan sebagai
  `price_per_unit`; `system_total` (= Total source bila ada) disimpan sebagai
  `subtotal` pada `DepositDetail`.
- Peringatan selisih harga Excel vs master (`has_price_warning`) dipertahankan
  karena berkaitan dengan pilihan mode harga, bukan pembulatan total.

## 3. Rekap Material

- Lokasi: Step 2 preview, di bawah tabel rincian, di atas ringkasan commit.
- Sumber data: `DepositImportService::buildRecap($rows, $duplicatePolicies)` —
  dataset yang sama dengan yang akan di-commit (baris resolved + tidak di-skip).
- Format: `Material | Satuan | Total Berat` + baris **Grand Total Berat**.
- Agregasi per `category_id` kanonis: label berbeda tetap berbeda
  (`Kardus Bekas ≠ Kardus Remuk`). Satuan dari master material.

## 4. Rekap Nasabah

- Format: `Nasabah | Total Berat | Total Nilai` + baris **Grand Total**.
- Rumus: `Total Berat = Σ berat detail`; `Total Nilai = Σ nilai transaksi detail`.
- Konsistensi: Grand Total Berat Rekap Material == Grand Total Berat Rekap
  Nasabah == total berat commit; Grand Total Nilai == total Deposit yang dicatat.

## 5. Cross-Check (gagal = commit diblokir)

- Di `DepositImportService::commit`: rekap material/nasabah dihitung ulang dari
  baris staging dan dibandingkan dengan total grouping. Selisih > 0.01
  melempar `RuntimeException` dengan pesan jelas — tidak pernah commit diam-diam.
- Gate UI `$canCommit` tetap: nol baris ERROR/NEEDS MAPPING/AMBIGUOUS.

## 6. Re-import Behavior

- Fingerprint `md5(file | sheet | date)` dipertahankan penuh (Cache + catatan
  `[FP:...]` di `notes` deposit). Double credit tetap mustahil.
- Pesan saat fingerprint terdeteksi diubah menjadi peringatan informatif:
  "⚠️ File ini sebelumnya sudah pernah dicoba diimpor. Silakan cek kembali
  hasil import sebelumnya sebelum melanjutkan. (Sheet '...', tanggal ... —
  impor ulang diblokir untuk mencegah duplikasi saldo.)"
- Perilaku tetap memblokir (validasi pada `sheet_name`), bukan sekadar info.

## 7. Behavior Existing yang Dipertahankan

- Manual setoran (`DepositController` + `BankSampahService::recordDeposit`).
- Grouping `1 Customer + 1 Date = 1 Deposit`; N detail; 1 ledger CREDIT.
- Withdrawal eligibility: `Deposit::count() >= 2` (bukan rows/details/kg/nominal).
- Unique-entity resolution, Bulk Add atomic, scoped `MaterialAlias`,
  `confirmed_date` tunggal per file, duplicate append/skip policy,
  historical snapshot (detail menyimpan harga & total transaksi),
  saldo dinamis `sum(credit) − sum(debit)`, permissions & routes.
- Bulk Add / alias Step 2 tetap tersimpan mendahului commit (existing behavior).

## 8. Bug Tambahan yang Diperbaiki (ditemukan saat verifikasi)

- Penghitung status `$counts['needs_mapping']` di `showForm` tidak pernah
  bertambah karena key memakai underscore sementara status baris memakai spasi
  (`strtolower('NEEDS MAPPING') = 'needs mapping'`). Akibatnya gate UI
  `$canCommit` selalu true untuk baris NEEDS MAPPING (commit service tetap
  aman karena memeriksa status baris langsung). Diperbaiki dengan normalisasi
  spasi→underscore sebelum pencacahan.

## 9. Tests

- `tests/Feature/DepositImportTest.php` — 22 tests, 773 assertions, PASS:
  - Update: `test_total_calculation_and_rounding_validation` (source total
    dipertahankan, tanpa warning pembulatan).
  - Baru: source total Rp83 end-to-end (deposit + ledger + saldo + snapshot
    imun terhadap perubahan master price); missing total → Berat×Harga;
    rekap material/nasabah + grand total + konsistensi + material berbeda
    tetap terpisah; withdrawal 1 vs 2 deposit; re-import warning + tetap
    diblokir + tanpa double credit.
- Regression terkait: `WasteCustomerTest`, `MaterialAliasTest`,
  `WasteSaleFlowTest`, `DepositIndexReferenceTest`, `DashboardReferenceTest` —
  PASS, kecuali 3 test monitoring dashboard yang gagal di tree pristine
  (route `bank-sampah.monitoring` tidak terdefinisi; pre-existing, di luar scope).

## 10. Known Limitations

- Rekap hanya mencakup baris resolved & tidak di-skip (baris pemblokir
  dikecualikan hingga teresolusi; grand total = dataset committable saat itu).
- Nasabah baru dari Bulk Add tetap tersimpan sebelum commit final (existing).
- Limit 2000 baris per impor dipertahankan.
- Peringatan deprecation PhpSpreadsheet (`BranchPruner`, `Worksheet`) pada
  PHP 8.5+ adalah isu kompatibilitas dependency terpisah — tidak diubah.
- Real Excel 344 baris TIDAK di-import ke database pada task ini (verifikasi
  memakai dataset sintetis terisolasi + automated tests).
