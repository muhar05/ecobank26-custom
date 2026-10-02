# Flow Setoran Audit

Dokumen ini merupakan hasil audit menyeluruh terhadap **Flow Setoran Sampah (Manual & Import Excel)** pada aplikasi **Bank Sampah / EcoBank026**.
Audit dilakukan secara statis dan non-destruktif (*code-level inspection*) tanpa mengubah kode, skema basis data, atau mengimpor data riil ke database.

---

## 1. Executive Summary

| Area / Komponen | Status Overall | Ringkasan Kondisi Saat Ini |
|---|---|---|
| **Manual Setoran** | **Existing** | Lengkap: `create`, `edit`, `destroy` terintegrasi dengan `BankSampahService`, `Deposit`, `DepositDetail`, dan `SavingsLedger`. |
| **Deposit Grouping (1 Cust + 1 Date = 1 Deposit)** | **Existing** | Sudah mengelompokkan baris per nasabah per tanggal transaksi menjadi 1 `Deposit` dengan banyak `DepositDetail` dan 1 `SavingsLedger` CREDIT. |
| **Withdrawal Eligibility (Min 2 Deposits)** | **Existing** | Sudah aktif di `BankSampahService::recordWithdrawal` dan `WithdrawalController`. Dihitung berdasarkan `Deposit::count()`, bukan baris/detail/berat. |
| **Customer Matching & Resolution** | **Existing** | Pipeline: Scoped mapping &rarr; Customer code &rarr; Normalized name. Ambiguitas dideteksi. Resolusi berbasis unique entity. Bulk Add tersedia. |
| **Material Resolution & Alias** | **Existing** | Canonical `WasteCategory` + `MaterialAlias` scoped ke `BANK_SAMPAH`. Resolusi berbasis unique label. Deteksi candidate suggestif tanpa auto-create. |
| **Perhitungan Total (Source of Truth vs Rumus)** | **Conflict** | **Saat ini**: Total selalu dihitung ulang `round(weight * used_price)` dan menimpa Total Excel. Jika selisih > Rp 2, status diturunkan menjadi WARNING. **Harapan**: Jika Total tersedia dari source (Sirsak), gunakan Total source sebagai nilai transaksi. |
| **Rekap Material (Tabel + Grand Total)** | **Missing** | Belum ada tabel agregat `Material \| Satuan \| Total Berat` beserta Grand Total Berat sebelum commit. |
| **Rekap Nasabah (Tabel + Grand Total)** | **Missing** | Belum ada tabel agregat `Nasabah \| Total Berat \| Total Nilai` beserta Grand Total Nilai sebelum commit. |
| **Re-import / Duplicate File Warning** | **Partial / Conflict** | Fingerprint protection ada via md5 hash, namun saat ini melempar *hard blocking validation error*. **Harapan**: Menampilkan banner/informasi peringatan kontekstual. |
| **Rollback / Atomicity** | **Partial** | Eksekusi commit transaksi deposit dibungkus `DB::transaction` penuh (atomic). Namun entitas yang dibuat di tahap resolusi (nasabah via bulk add / alias) sudah tersimpan di database sebelum tombol commit ditekan. |

---

## 2. Current Flow

### A. Flow Manual Setoran
```text
Operator buka form create setoran (/bank-sampah/deposits/create)
  ↓
Pilih Nasabah terdaftar (select/search) & Tanggal setoran
  ↓
Input 1 atau lebih baris timbangan (Material, Berat, Harga default dari master)
  ↓
DepositController::store() memvalidasi input & parse details
  ↓
BankSampahService::recordDeposit() [DB::transaction]:
  ├── 1. Create Deposit (waste_customer_id, date, total_amount, notes)
  ├── 2. Create N DepositDetail (deposit_id, waste_category_id, weight, price_per_unit, subtotal)
  └── 3. Create 1 SavingsLedger CREDIT (waste_customer_id, type='credit', amount=total_amount, reference=Deposit)
  ↓
Saldo Nasabah bertambah otomatis (dihitung dinamis dari sum(credit) - sum(debit))
  ↓
ActivityLog dicatat setelah transaction commit (deposit.create)
```

### B. Flow Import Setoran Excel
```text
Step 1: Upload File (/bank-sampah/deposits/import)
  ├── Upload berkas (.xlsx / .xls)
  ├── DepositImportController::parseFile() memvalidasi berkas & simpan di storage/app/temp_imports/{token}.xlsx
  ├── DepositImportService::listSheets() membaca daftar sheet
  ├── DepositImportService::guessDateFromSheetName() menebak tanggal dari nama sheet
  └── Simpan metadata awal di Cache ('deposit_import_{token}')
  ↓
Step 1.5: Pilih Sheet & Konfirmasi Tanggal (/bank-sampah/deposits/import?token=...)
  ├── Operator memilih sheet & mengonfirmasi tanggal transaksi resmi
  ├── DepositImportController::previewSheet()
  │     ├── Hitung fingerprint md5(fileHash | sheetName | confirmedDate)
  │     ├── Cek isFingerprintImported() (jika pernah diimpor &rarr; hard validation error)
  │     ├── Parse baris via DepositImportService::parseSheet() (scan header 10 baris pertama)
  │     ├── Jalankan matching awal via DepositImportService::processRows()
  │     └── Simpan staging rows di Cache
  ↓
Step 2: Preview & Resolution (/bank-sampah/deposits/import?token=...)
  ├── Tampilkan status kesiapan global ($canCommit)
  ├── Tampilkan metrik ringkasan baris
  ├── Resolusi Nasabah (jika ada $unmatchedCustomers):
  │     ├── Bulk Add: Modal daftarkan semua nasabah baru sekaligus (atomic DB::transaction)
  │     ├── Quick Add: Daftarkan nasabah satuan
  │     └── Petakan ke nasabah terdaftar (dropdown &rarr; submit form)
  ├── Resolusi Material (jika ada $unmatchedMaterials):
  │     ├── Petakan ke WasteCategory master (bisa simpan alias permanen)
  │     └── Quick Add: Modal tambah kategori master baru
  ├── Penyesuaian Opsional (jika ada selisih harga atau duplikasi tanggal):
  │     ├── Pilih mode harga: 'excel' vs 'master' (auto-submit)
  │     └── Pilih kebijakan duplikat: 'append' vs 'skip' (auto-submit)
  ├── Tabel Pratinjau 10 kolom rincian baris data
  └── Tombol "Import Data Sekarang" (hanya aktif jika $canCommit === true)
  ↓
Commit Import (POST /bank-sampah/deposits/import/commit)
  ├── DepositImportController::commitImport() memanggil DepositImportService::commit()
  ├── Verifikasi ulang: fingerprint belum pernah diimpor & nol baris error/unmapped
  ├── Grouping baris berdasarkan customer_id pada tanggal transaksi yang sama
  ├── DB::transaction:
  │     └── Loop per grup nasabah: panggil BankSampahService::recordDeposit($group)
  │           ├── Buat 1 Deposit
  │           ├── Buat N DepositDetail
  │           └── Buat 1 SavingsLedger CREDIT
  ├── Simpan fingerprint permanen di Cache (deposit_import_fp_{fingerprint})
  ├── Hapus file fisik temp & hapus staging cache
  └── Redirect ke Step 3 (Hasil Impor) dengan session flash
```

---

## 3. Route &rarr; Controller &rarr; Service &rarr; Model

| Tahap / Aksi | File | Class / Method | Current Behavior |
|---|---|---|---|
| **Daftar Setoran** | `app/Http/Controllers/DepositController.php` | `DepositController@index` | Menampilkan riwayat setoran berpaginasi (20 item/hal) dengan filter tanggal & pencarian nama/kode/catatan. |
| **Form Setoran Manual** | `app/Http/Controllers/DepositController.php` | `DepositController@create` | Menyiapkan dropdown nasabah aktif, kategori sampah, dan rekap timbangan tanggal terpilih. |
| **Simpan Setoran Manual** | `app/Http/Controllers/DepositController.php` | `DepositController@store` | Validasi input, parsing detail timbangan, lalu delegasi ke `BankSampahService@recordDeposit`. |
| **Update Setoran Manual** | `app/Http/Controllers/DepositController.php` | `DepositController@update` | Memperbarui `Deposit`, mengganti seluruh `DepositDetail`, dan mengupdate nominal pada `SavingsLedger`. |
| **Hapus Setoran Manual** | `app/Http/Controllers/DepositController.php` | `DepositController@destroy` | Menghapus `SavingsLedger`, `DepositDetail`, dan `Deposit` dalam `DB::transaction`. |
| **Eksekusi Setoran & Ledger** | `app/Services/BankSampahService.php` | `BankSampahService@recordDeposit` | Membuat `Deposit`, batch insert `DepositDetail`, dan membuat 1 record `SavingsLedger` (credit) secara atomic. |
| **Upload File Import** | `app/Http/Controllers/DepositImportController.php` | `DepositImportController@parseFile` | Validasi ekstensi/mimes (xlsx, xls, max 5MB), upload ke `temp_imports`, baca sheet names, tebak tanggal, simpan cache. |
| **Pilih Sheet & Pratinjau** | `app/Http/Controllers/DepositImportController.php` | `DepositImportController@previewSheet` | Validasi fingerprint, parse sheet, jalankan `processRows()`, generate batch code, simpan cache staging. |
| **Parsing Sheet Excel** | `app/Services/DepositImportService.php` | `DepositImportService@parseSheet` | Deteksi baris header otomatis (scan 10 baris pertama untuk kolom Nasabah, Material, Berat, Harga, Total), parse angka ID/EN. |
| **Matching & Processing Baris** | `app/Services/DepositImportService.php` | `DepositImportService@processRows` | Matching nasabah (code, normalized name), material via `MaterialMappingService`, hitung subtotal & delta, deteksi duplikasi tanggal. |
| **Resolusi Mapping** | `app/Http/Controllers/DepositImportController.php` | `DepositImportController@resolveMapping` | Menyimpan mapping manual nasabah/material, opsi simpan alias permanen, update mode harga/duplikat, lalu re-process baris. |
| **Bulk Add Nasabah Baru** | `app/Http/Controllers/DepositImportController.php` | `DepositImportController@bulkAddCustomers` | Membuat banyak nasabah baru sekaligus via `DepositImportService@bulkAddCustomers` dalam `DB::transaction`, petakan otomatis. |
| **Quick Add Kategori Baru** | `app/Http/Controllers/DepositImportController.php` | `DepositImportController@quickAddCategory` | Membuat `WasteCategory` baru dan mendaftarkan alias opsional via `MaterialMappingService@registerAlias`. |
| **Commit Import** | `app/Http/Controllers/DepositImportController.php` | `DepositImportController@commitImport` | Memanggil `DepositImportService@commit`: grouping baris per customer, insert deposit + detail + ledger via `BankSampahService`. |
| **Pengecekan Penarikan** | `app/Services/BankSampahService.php` | `BankSampahService@recordWithdrawal` | Mengecek syarat minimal 2 deposit (`Deposit::where(...)->count() < 2`), cek saldo tersedia, buat `Withdrawal` & `SavingsLedger` debit. |
| **Kalkulasi Saldo Nasabah** | `app/Services/BankSampahService.php` | `BankSampahService@getCustomerBalance` | Menghitung `sum(credit) - sum(debit)` dari tabel `savings_ledgers` untuk nasabah terkait. |

---

## 4. Customer Resolution

1. **Bagaimana customer dicocokkan sekarang?**
   - Menggunakan pipeline bertingkat di `DepositImportService::processRows`:
     - *Tingkat 1*: Cek pemetaan manual sesi (`$customerMappings[$rawCust]` atau versi normalisasi string).
     - *Tingkat 2*: Cek kecocokan persis kode nasabah (`strtolower(trim($c->customer_code))`).
     - *Tingkat 3*: Cek kecocokan persis nama ternormalisasi (`$this->normalizeString($c->name)`).
     - *Tingkat 4*: Jika ditemukan tepat 1 nasabah &rarr; status `MATCHED`. Jika > 1 &rarr; status `AMBIGUOUS`. Jika 0 &rarr; status `NEEDS MAPPING`.
2. **Apakah matching berdasarkan customer code?**
   - Ya, kode nasabah (`customer_code`) diprioritaskan sebelum pencocokan nama.
3. **Apakah matching berdasarkan normalized name?**
   - Ya, normalisasi melakukan `strtolower(trim(preg_replace('/\s+/', ' ', $str)))`.
4. **Apakah satu raw customer dapat muncul di banyak rows?**
   - Ya, satu nama nasabah mentah (misal: "Budi Santoso") dapat muncul di puluhan baris berbeda dalam spreadsheet.
5. **Apakah mapping dilakukan per-row atau unique entity?**
   - Mapping disimpan berdasarkan string label mentah unik (`$staging['customer_mappings'][$rawCust] = $id`). Satu pemetaan otomatis diaplikasikan ke seluruh baris yang memiliki label mentah tersebut.
6. **Bagaimana customer baru dibuat?**
   - Melalui modal pendaftaran (`quickAddCustomer` untuk satuan atau `bulkAddCustomers` untuk banyak nasabah). Keduanya memanggil `WasteCustomer::generateNextCustomerCode()` secara sekuensial (format `NSB-XXXXXX`).
7. **Apakah sudah ada Bulk Add?**
   - Ya, endpoint `POST /bank-sampah/deposits/import/bulk-add-customers` dan modal form bulk add sudah ada.
8. **Apakah bulk creation atomic?**
   - Ya, metode `DepositImportService::bulkAddCustomers` dibungkus dalam `DB::transaction`. Jika satu entri gagal, seluruh pembuatan dibatalkan.
9. **Apakah duplicate customer mungkin terjadi?**
   - Pada level database, tabel `waste_customers` hanya memiliki indeks unik pada kolom `customer_code`. Kolom `name` dan `phone` tidak memiliki constraint unik. Jika operator mendaftarkan dua nama yang sama secara terpisah, dua record nasabah berbeda akan terbuat. Namun, pada level UI import, entitas yang belum terdaftar dikelompokkan berdasarkan label mentah unik, sehingga satu label hanya muncul sekali di daftar pemetaan/modal.
10. **Bagaimana ambiguous customer ditangani?**
    - Jika nama nasabah di Excel cocok dengan lebih dari satu record nasabah di master (misal ada dua nasabah bernama "Ahmad"), baris ditandai sebagai `AMBIGUOUS`.
    - Status ini memblokir impor (`canCommit = false`).
    - Operator wajib memilih salah satu nasabah terdaftar secara spesifik via dropdown yang menampilkan nama dan kode nasabah.

---

## 5. Material Resolution

1. **Bagaimana material dicocokkan?**
   - Melalui `MaterialMappingService::resolve`:
     - *Tahap 1*: Cek pemetaan sesi manual (`$materialMappings[$rawMat]`).
     - *Tahap 2*: Cek canonical `WasteCategory` (cocok persis kode atau nama ternormalisasi).
     - *Tahap 3*: Cek alias aktif pada scope `BANK_SAMPAH` (`source_id = null`).
     - *Tahap 4*: Jika tidak cocok, cari kandidat saran berdasarkan awalan kata / grup kategori dan tandai `NEEDS MAPPING`.
2. **Apakah canonical WasteCategory digunakan?**
   - Ya, model `App\Models\WasteCategory` adalah representasi master tunggal.
3. **Bagaimana MaterialAlias digunakan?**
   - Menghubungkan label eksternal mentah (`external_name` & `normalized_name`) ke `waste_category_id`. Alias hanya menyimpan relasi pemetaan tanpa menyimpan harga.
4. **Apakah alias source-scoped?**
   - Ya, dipisahkan berdasarkan kolom `source_type` (`BANK_SAMPAH` dengan `source_id = null`, versus `AGGREGATOR` dengan `source_id = collector_id`). Tabel memiliki constraint unik: `unique(['source_type', 'source_id', 'normalized_name'])`.
5. **Apakah unknown material otomatis dibuat?**
   - Tidak. Label yang tidak dikenal diberi status `NEEDS MAPPING`. Kategori baru hanya dibuat jika operator secara eksplisit mengisi form Quick Add Category.
6. **Bagaimana manual mapping dilakukan?**
   - Operator memilih kategori master dari dropdown select. Form menyediakan checkbox `save_permanent_aliases` (default tercentang) untuk mendaftarkan alias ke database permanen.
7. **Apakah mapping dilakukan per-row atau unique raw label?**
   - Berbasis unique raw label (`$materialMappings[$rawMat] = $categoryId`). Menyimpan pemetaan satu label otomatis menyelesaikan seluruh baris dengan label tersebut.
8. **Apakah persistent alias tersedia?**
   - Ya, tersimpan pada tabel `material_aliases` via `MaterialMappingService::registerAlias`.
9. **Apakah material berbeda seperti "Kardus Bekas" vs "Kardus Remuk" tetap dianggap berbeda?**
   - Ya. String normalisasi "kardus bekas" tidak sama dengan "kardus remuk". Keduanya diperlakukan sebagai entitas material terpisah dan tidak pernah digabung secara otomatis.
10. **Dari mana satuan material berasal?**
    - Dari kolom `unit` pada master `WasteCategory` terkait (`$matchedCategory->unit`, umumnya `kg`).

---

## 6. Price & Total Audit

1. **Harga (Price):**
   - Diambil dari kolom harga Excel (`excel_price`).
   - Tersedia opsi override per material: jika operator memilih mode `master`, sistem beralih menggunakan `member_price` dari master kategori.
   - Jika harga Excel kosong, sistem otomatis menggunakan `master_price`.
2. **Total (Subtotal Transaksi):**
   - **Kondisi Kode Saat Ini**:
     - Sistem **selalu menghitung ulang total**: `$systemTotal = round($weight * $usedPrice)`.
     - Nilai `$excelTotal` yang ada di berkas hanya disimpan sebagai pembanding (`$delta = $systemTotal - $excelTotal`).
     - Pada saat commit, subtotal yang disimpan ke `deposit_details` adalah `$row['system_total']`, bukan `$row['excel_total']`.
     - Jika selisih `abs($delta) > 2`, sistem menandai `$hasTotalWarning = true` dan menurunkan status baris menjadi `WARNING`.
   - **Temuan Gap / Konflik**:
     - Bertentangan dengan prinsip *Source of Truth* (Bagian 3 prompt): Jika data berasal dari ekspor aplikasi lain (misal Sirsak) yang sudah menyertakan Total, nilai Total dari source tersebut seharusnya dihormati sebagai nilai transaksi, bukan dihitung ulang yang dapat mengubah angka pembukuan.

---

## 7. Deposit Grouping Audit

- **Aturan Pengelompokan**:
  - Diimplementasikan di `DepositImportService::commit` (baris 554–578).
  - Mengelompokkan seluruh baris data berdasarkan `customer_id` untuk tanggal transaksi sesi tersebut (`$confirmedDate`).
- **Grouping Key**: `$row['customer_id']` pada `$confirmedDate`.
- **Hasil Pengelompokan**:
  - 1 Nasabah dengan N baris material pada tanggal yang sama &rarr; **1 Record `Deposit`**, **N Record `DepositDetail`**, dan **1 Record `SavingsLedger` CREDIT**.
  - Bukan 1 Deposit per row.
- **Kesimpulan**: Grouping setoran sudah memenuhi aturan `1 Customer + 1 Date = 1 Deposit`.

---

## 8. SavingsLedger & Saldo Audit

1. **Kapan SavingsLedger dibuat?**
   - Dibuat secara otomatis di dalam `BankSampahService::recordDeposit` segera setelah `Deposit` dan `DepositDetail` terbentuk di dalam database transaction yang sama.
2. **Berapa ledger entry per Deposit?**
   - Tepat **1 entri `SavingsLedger` berjenis `credit` per record `Deposit`**, dengan nominal `amount` sama dengan `total_amount` deposit (akumulasi subtotal seluruh detail).
3. **Apakah saldo otomatis bertambah?**
   - Ya. Saldo nasabah dihitung secara dinamis dari jurnal tabungan (`sum(credit) - sum(debit)`). Penambahan record credit pada ledger otomatis menambah saldo saat itu juga tanpa perlu proses verifikasi terpisah.
4. **Apakah ada kemungkinan double credit?**
   - Pada pemrosesan normal: Tidak ada double credit (1 deposit = 1 credit).
   - Pada re-import: Dilindungi oleh pengecekan sidik jari (*fingerprint protection*).
5. **Apakah edit Deposit mengubah ledger?**
   - Ya. Pada `DepositController::update`, baris ledger terkait (`reference_type = Deposit::class`, `reference_id = $deposit->id`) diperbarui nominalnya agar tetap sinkron.
6. **Apakah historical snapshot tetap?**
   - Ya. `DepositDetail` menyimpan snapshot `weight`, `price_per_unit`, dan `subtotal` pada saat transaksi terjadi. Perubahan harga master di kemudian hari tidak mengubah transaksi historis.
7. **Apakah import dan manual setoran menggunakan service yang sama?**
   - Ya, keduanya sama-sama memanggil `BankSampahService::recordDeposit()`.

---

## 9. Withdrawal Eligibility Audit

- **Aturan Bisnis Saat Ini**:
  - Diatur dalam konstanta: `BankSampahService::MIN_DEPOSITS_BEFORE_WITHDRAWAL = 2`.
  - Diimplementasikan di:
    - `BankSampahService::recordWithdrawal` (baris 90–94).
    - `WithdrawalController::update` (baris 75–79).
- **Apa yang sebenarnya dihitung?**
  - Kode: `Deposit::where('waste_customer_id', $customer->id)->count()`.
  - Yang dihitung adalah **jumlah record pada tabel `deposits`**.
  - Bukan jumlah baris Excel, bukan jumlah `DepositDetail`, bukan total kilogram, dan bukan nominal rupiah.
  - Seorang nasabah yang memiliki 1 deposit dengan 10 jenis material tetap dihitung memiliki 1 kali setoran. Nasabah tersebut baru bisa melakukan penarikan setelah memiliki minimal 2 record `Deposit`.
- **Kesimpulan**: Aturan minimal 2 kali setoran sudah ada dan sudah menghitung entitas `Deposit`.

---

## 10. Duplicate / Re-import Audit

1. **Duplicate Row (Baris Ganda / Nasabah Sama pada Tanggal yang Sama)**:
   - Terdeteksi saat nasabah sudah memiliki record deposit di database pada tanggal transaksi terpilih (`has_existing_deposit`).
   - Operator dapat memilih kebijakan:
     - `append` (default): Transaksi baru akan ditambahkan sebagai setoran tersendiri.
     - `skip`: Baris nasabah tersebut dilewati saat commit.
2. **Duplicate Customer**:
   - Resolusi dilakukan per entitas unik, bukan per baris.
3. **Duplicate File / Re-import**:
   - Menggunakan fingerprint deterministik: `md5(md5_file($filePath) . '|' . $sheetName . '|' . $confirmedDate)`.
   - Disimpan di cache (`deposit_import_fp_{fingerprint}`) dan tercatat di kolom `notes` tabel deposits (`[FP:{fingerprint}]`).
   - **Kondisi Saat Ini**: Jika file dengan fingerprint yang sama diunggah ulang, controller melempar `ValidationException` keras pada form pemilihan sheet:
     *"Sheet '...' pada file ini untuk tanggal ... sudah pernah berhasil diimpor sebelumnya. Impor ulang file/sheet yang sama diblokir..."*.
   - **Temuan Gap / Konflik**: Belum menampilkan format peringatan kontekstual sesuai panduan Bagian 17 prompt (*"⚠️ File ini sebelumnya sudah pernah dicoba diimpor..."*).

---

## 11. Rollback / Atomicity Audit

- **Batasan Transaksi Commit**:
  - Pada `DepositImportService::commit`, seluruh proses iterasi grup nasabah dan pemanggilan `recordDeposit` dibungkus dalam satu blok:
    ```php
    DB::transaction(function () use ($grouped, $bankSampahService, &$createdDeposits) {
        foreach ($grouped as $group) {
            $createdDeposits[] = $bankSampahService->recordDeposit($group);
        }
    });
    ```
  - Jika terjadi kegagalan pada baris mana pun saat commit: Seluruh `Deposit`, `DepositDetail`, dan `SavingsLedger` yang terbentuk pada sesi impor tersebut akan di-rollback secara penuh. Tidak ada saldo parsial yang tersisa.
- **Catatan Entitas Pre-Commit**:
  - Nasabah baru yang dibuat via Bulk Add/Quick Add dan Alias material yang didaftarkan pada Step 2 dibuat melalui request HTTP terpisah sebelum tombol commit ditekan. Masing-masing memiliki database transaction tersendiri. Jika proses impor dibatalkan atau commit gagal, data profil nasabah baru atau alias yang sudah terlanjur dibuat di Step 2 tetap tersimpan di database master.

---

## 12. Recap Material Audit

- **Status**: **BELUM ADA (MISSING)**.
- **Temuan**:
  - Halaman pratinjau (`resources/views/bank-sampah/deposits/import.blade.php`) dan controller (`DepositImportController.php`) saat ini belum memiliki tabel agregasi:
    `Material | Satuan | Total Berat` beserta `Grand Total Berat (Σ seluruh Berat)`.
  - Data yang ada saat ini hanya berupa kartu total berat global (`summary['total_weight']`) dan tabel rincian transaksi per baris.

---

## 13. Recap Nasabah Audit

- **Status**: **BELUM ADA (MISSING)**.
- **Temuan**:
  - Halaman pratinjau dan controller saat ini belum memiliki tabel agregasi:
    `Nasabah | Total Berat | Total Nilai` beserta `Grand Total Nilai Nasabah`.
  - Data yang ada saat ini hanya berupa kartu ringkasan global (`summary['deposits_count']`, `summary['total_amount']`) dan tabel rincian transaksi per baris.

---

## 14. Date Handling Audit

1. **Apakah satu file menggunakan satu tanggal?**
   - Ya. Satu sesi impor spreadsheet hanya menggunakan satu tanggal transaksi (`$confirmedDate`) untuk seluruh baris data di sheet tersebut.
2. **Dari mana tanggal berasal?**
   - Ditebak secara otomatis dari judul sheet (`guessDateFromSheetName`) jika polanya cocok dengan format tanggal umum (DD-MM-YYYY, DD/MM/YYYY, YYYY-MM-DD).
   - Ditampilkan dan dapat dikonfirmasi/diubah secara manual oleh operator pada Step 1.5 melalui input `confirmed_date`.
   - Nilai tanggal yang dikonfirmasi disimpan di staging cache dan diaplikasikan ke semua deposit yang terbuat.
3. **Apakah tanggal berbeda per row diperbolehkan?**
   - Tidak. Sistem tidak membaca tanggal per baris dari kolom Excel; tanggal ditentukan secara seragam pada tingkat worksheet.

---

## 15. Existing Tests Inventory

| Test File | Method / Test Case | Purpose | Status |
|---|---|---|---|
| `DepositImportTest.php` | `test_unauthorized_user_cannot_access_import_routes` | Memastikan hak akses `manage_deposits` terjaga. | Passed |
| `DepositImportTest.php` | `test_upload_file_detects_sheets_and_guesses_date` | Deteksi sheet & ekstraksi tanggal dari nama sheet. | Passed |
| `DepositImportTest.php` | `test_invalid_file_rejected_by_mime_and_extension` | Validasi tipe berkas spreadsheet. | Passed |
| `DepositImportTest.php` | `test_parsing_exact_customer_and_material_auto_matches` | Pencocokan otomatis master nasabah & kategori. | Passed |
| `DepositImportTest.php` | `test_unmatched_customer_triggers_needs_mapping` | Status `NEEDS MAPPING` pada nasabah baru. | Passed |
| `DepositImportTest.php` | `test_quick_add_customer_resolves_all_identical_rows` | Pendaftaran nasabah tunggal & propagasi ke baris identik. | Passed |
| `DepositImportTest.php` | `test_bulk_add_customers_resolves_multiple_unmatched` | Pendaftaran banyak nasabah sekaligus. | Passed |
| `DepositImportTest.php` | `test_unmatched_material_mapped_once_resolves_all_rows` | Pemetaan material tunggal menyelesaikan seluruh baris terkait. | Passed |
| `DepositImportTest.php` | `test_excel_price_used_as_snapshot_does_not_modify_master_member_price` | Verifikasi snapshot harga transaksi tidak mengubah master. | Passed |
| `DepositImportTest.php` | `test_deposit_grouping_multiple_materials_same_customer_creates_one_deposit_and_one_savings_ledger` | Verifikasi 1 nasabah + 1 tanggal = 1 Deposit & 1 Ledger credit. | Passed |
| `DepositImportTest.php` | `test_exact_reimport_fingerprint_blocked` | Perlindungan re-import via fingerprint. | Passed |
| `DepositImportTest.php` | `test_commit_blocked_if_errors_or_unmapped_rows_remain` | Penolakan commit jika masih ada baris error/unmapped. | Passed |
| `DepositImportTest.php` | `test_total_calculation_and_rounding_validation` | Uji kalkulasi delta total & status warning. | Passed |
| `DepositImportTest.php` | `test_duplicate_same_customer_and_date_append_or_skip` | Kebijakan duplikasi nasabah pada tanggal sama (append vs skip). | Passed |
| `DepositImportTest.php` | `test_empty_rows_are_ignored_and_invalid_weight_produces_error` | Pengabaian baris kosong & validasi berat angka. | Passed |
| `DepositImportTest.php` | `test_300_rows_resolve_through_unique_entity_actions_only` | Uji resolusi massal 300 baris dengan entitas berulang. | Passed |
| `DepositImportTest.php` | `test_bulk_customer_creation_is_atomic_on_validation_failure` | Uji atomisitas pembuatan bulk customer saat validasi gagal. | Passed |
| `DepositIndexReferenceTest.php` | (4 test cases) | Keterbacaan daftar setoran, token semantik, & aksi. | Passed |
| `WasteCustomerTest.php` | `test_withdrawal_creation_for_manual_customer_succeeds` | Uji penarikan saldo dan validasi syarat deposit. | Passed |
| `MaterialAliasTest.php` | (5 test cases) | Uji registrasi alias, scoping, dan normalisasi. | Passed |
| `WasteSaleFlowTest.php` | `test_deposit_uses_master_member_price_without_aggregator` | Isolasi harga setoran nasabah dari alur penjualan agregator. | Passed |

---

## 16. Real Dataset Compatibility (344 Rows Benchmark)

Berdasarkan audit statis terhadap berkas `docs/Bank Sampah.xlsx`:
- **Karakteristik Data**:
  - Total data: 344 baris transaksi.
  - Nasabah unik: 53 nasabah.
  - Material unik: 30 jenis material.
  - Total berat: 621,21 kg.
  - Total nilai transaksi Excel: ~Rp 1.159.000.
- **Kesiapan Arsitektur**:
  - **Memory & Session**: Penyimpanan 344 baris pada Cache staging memakan memori < 250 KB (sangat aman).
  - **Batas Baris**: Limit controller saat ini adalah 2.000 baris (`count($rawRows) > 2000`), sehingga 344 baris berada jauh di bawah batas maksimum.
  - **Resolusi Entitas**: Operator hanya perlu menangani 53 entitas nasabah dan 30 material unik, bukan 344 baris individual.
  - **Kalkulasi & Pembulatan**: Terdapat beberapa baris dengan pembulatan sen pada spreadsheet sumber. Jika sistem tetap memaksakan perhitungan ulang `weight * price`, akan timbul selisih dan warning yang tidak diharapkan.

---

## 17. Gaps

### CONFIRMED GAP
1. **Source of Truth Total Transaksi**:
   - Sistem saat ini selalu menghitung ulang total dari `round(weight * used_price)` dan mengabaikan nilai `excel_total` asli berkas saat commit.
2. **Rekap Material Belum Tersedia**:
   - Belum ada agregasi per material (`Material | Satuan | Total Berat`) dan `Grand Total Berat` sebelum commit.
3. **Rekap Nasabah Belum Tersedia**:
   - Belum ada agregasi per nasabah (`Nasabah | Total Berat | Total Nilai`) dan `Grand Total Nilai` sebelum commit.

### PARTIAL
1. **Penanganan Re-import / Duplicate File**:
   - Mekanisme fingerprint sudah mendeteksi berkas duplikat, namun penanganannya saat ini memblokir secara keras (*hard error*) alih-alih memberikan pesan peringatan kontekstual.
2. **Atomisitas Sesi Impor Lengkap**:
   - Commit deposit dan ledger sudah atomic dalam `DB::transaction`. Namun pendaftaran nasabah baru via bulk add yang dilakukan di Step 2 tersimpan permanen mendahului commit deposit.

### UNKNOWN
- Tidak ada status yang dikategorikan *Unknown*. Seluruh alur data, model, service, dan skema database berhasil di-trace secara transparan dari repository.

---

## 18. Potential Conflicts

### Konflik 1: Perhitungan Nilai Total Transaksi
- **Current Behavior**:
  `$systemTotal` selalu dihitung ulang dengan rumus `round($weight * $usedPrice)`. Pada saat commit, nilai `$systemTotal` inilah yang disimpan ke database sebagai subtotal deposit detail. Jika berbeda dengan total berkas Excel (`abs($delta) > 2`), status baris diturunkan menjadi `WARNING`.
- **Expected / Desired Behavior**:
  Jika Total tersedia dari berkas sumber (misalnya hasil ekspor Sirsak), gunakan Total sumber tersebut sebagai nilai transaksi tanpa menghitung ulang dan tanpa memunculkan warning akibat selisih pembulatan kecil. Jika Total tidak tersedia pada berkas, baru hitung `Total = Berat × Harga`.
- **Conflict**:
  Mengubah perilaku perhitungan total akan mempengaruhi nominal subtotal dan total amount yang dicatat pada `DepositDetail` dan `SavingsLedger` untuk berkas yang menyertakan kolom total.

### Konflik 2: Tampilan Pratinjau vs Rekap Sebelum Commit
- **Current Behavior**:
  Halaman Step 2 saat ini menampilkan tabel rincian transaksi per baris (hingga ratusan baris) dan 4 kartu metrik global, tanpa tabel rekap agregat.
- **Expected / Desired Behavior**:
  Setelah resolusi nasabah dan material selesai, sistem harus menampilkan Rekap Material (`Material | Satuan | Total Berat`) dan Rekap Nasabah (`Nasabah | Total Berat | Total Nilai`) beserta Grand Total yang konsisten sebelum operator melakukan commit.

### Konflik 3: Respons Terhadap Berkas Duplikat
- **Current Behavior**:
  Sistem menolak pratinjau lembar kerja dan mengembalikan pesan error validasi formulir saat mendeteksi fingerprint berkas yang sudah ada.
- **Expected / Desired Behavior**:
  Menampilkan pesan peringatan informatif (*"⚠️ File ini sebelumnya sudah pernah dicoba diimpor..."*) sambil tetap menjaga integritas agar tidak terjadi double credit.

---

## 19. Catatan Observasi Runtime (PhpSpreadsheet Deprecation)
- **Observasi**:
  Saat pustaka `PhpSpreadsheet` mengevaluasi formula spreadsheet pada baris `toArray(null, true, false, false)` di lingkungan PHP 8.5+, komponen internal `BranchPruner` memicu peringatan deprecation:
  *`Using null as an array offset is deprecated in BranchPruner.php`*.
- **Status**:
  Sesuai instruksi audit, observasi ini dicatat sebagai temuan pustaka pihak ketiga dan tidak dilakukan perubahan kode.
