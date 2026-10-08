# CURRENT DATA DICTIONARY — EcoBank026

> Sumber: audit langsung atas migration, model, service, dan controller pada kondisi
> codebase saat ini. Tidak ada perubahan code/database dalam audit ini.
> DB sedang kosong (reset bisnis), jadi "contoh nilai" diambil dari logika code,
> bukan dari isi tabel.

Konvensi harga di aplikasi ini:

- **member_price** = Harga Beli Nasabah (bank sampah membeli dari warga).
- **collector_price / price_per_unit (sale)** = Harga Lapak (agregator membeli dari bank sampah).
- Aturan bisnis: `collector_price >= member_price` saat sale, jika tidak ditolak
  (`BankSampahService::recordSale`, `app/Services/BankSampahService.php:395`).

---

## 1. SETORAN / DEPOSIT

### 1.1 `deposits`

Satu baris = satu timbangan satu nasabah pada satu tanggal.
Dibuat oleh `BankSampahService::recordDeposit` (`app/Services/BankSampahService.php:18`)
atau commit `DepositImportService::commit` (`app/Services/DepositImportService.php:730`).

| Field | Arti bisnis | Tipe DB | Wajib? | Sumber data | Snapshot? | Contoh |
|---|---|---|---|---|---|---|
| `id` | PK setoran | bigint unsigned AI | Ya (auto) | DB | — | `101` |
| `waste_customer_id` | Nasabah pemilik setoran (FK `waste_customers`, `nullOnDelete`) | bigint unsigned | Nullable di DB; **wajib di form** (`DepositController@store`, `required\|exists`) | Pilihan nasabah / mapping import | Bukan (relasi live; kalau nasabah dihapus jadi NULL) | `12` |
| `date` | Tanggal setoran (boleh backdate; tidak ada validasi `before_or_equal:today` di store) | date | Wajib | Input tanggal / `confirmed_date` sheet import | Ya (tanggal peristiwa) | `2025-07-12` |
| `total_amount` | Total setoran = SUM(`deposit_details.subtotal`) milik deposit ini; **dihitung server**, tidak diambil dari input | decimal(15,2) | Wajib (computed) | `collect(details)->sum('subtotal')` | Ya (turunan snapshot) | `31365.00` |
| `notes` | Catatan bebas; import menulis `Import Excel: {file} (Sheet: {sheet}) [Batch: {code}] [FP: {fingerprint}]` | varchar(255) | Nullable | Input / auto import | Ya | `Import Excel: Bank Sampah.xlsx (Sheet: 12-7-2025) [Batch: IMP-SETORAN-20250712-AB12] [FP: ...]` |
| `created_at` / `updated_at` | Waktu pencatatan sistem (bukan tanggal transaksi) | timestamp nullable | Otomatis | Laravel | — | — |

Efek samping `recordDeposit`: selalu membuat 1 baris `savings_ledgers`
(`type='credit'`, `reference_type=App\Models\Deposit`, `reference_id=deposit.id`).
Saldo nasabah = SUM(credit) − SUM(debit) dari ledger, bukan dari `deposits`.

### 1.2 `deposit_details`

Satu baris = satu material dalam satu setoran.
FK `deposit_id` → `deposits` **`cascadeOnDelete`**.

| Field | Arti bisnis | Tipe DB | Wajib? | Sumber data | Snapshot? | Contoh |
|---|---|---|---|---|---|---|
| `id` | PK detail | bigint unsigned AI | Ya (auto) | DB | — | `5001` |
| `deposit_id` | Setoran induk | bigint unsigned | Wajib | Dibuat bersama deposit | — | `101` |
| `waste_category_id` | Material kanonik (FK `waste_categories`, restrict) | bigint unsigned | Wajib | Pilihan material / resolusi alias / auto-material | Bukan harga, tapi **referensi live** (nama master ikut berubah jika master di-rename) | `15` |
| `weight` | Berat (kg) | decimal(10,2) | Wajib, **> 0** (baris ≤ 0 dibuang; import: ERROR `Berat harus berupa angka > 0`) | Input timbangan / kolom Berat Excel | Ya | `18.45` |
| `price_per_unit` | **Harga beli nasabah per kg yang dipakai transaksi ini** | decimal(15,2) | Wajib | Manual: **selalu** `waste_categories.member_price` saat entry (harga dari browser sengaja diabaikan, `DepositController::parseDetails`). Import: `used_price` sesuai `price_mode` (`excel` = harga Excel, `master` = member_price; sel kosong → fallback master, master tidak ditimpa) | **Ya, snapshot historis** | `1000.00` |
| `subtotal` | Nilai baris | decimal(15,2) | Wajib | Manual: `round(weight × price_per_unit)`. Import: **memakai Total Excel apa adanya bila ada** (`system_total = excel_total`); baru `round(weight × used_price)` bila Total kosong | **Ya, snapshot historis** | `31365.00` |

---

## 2. PENJUALAN / SALE

### 2.1 `sales`

Satu baris = satu penjualan ke satu agregator pada satu tanggal.
Dibuat oleh `BankSampahService::recordSale` (`app/Services/BankSampahService.php:234`).

| Field | Arti bisnis | Tipe DB | Wajib? | Sumber data | Snapshot? | Contoh |
|---|---|---|---|---|---|---|
| `id` | PK penjualan | bigint unsigned AI | Ya (auto) | DB | — | `7` |
| `collector_id` | Agregator pembeli (FK `collectors`) | bigint unsigned | Wajib (`required\|exists:collectors,id`, `SaleController::validateSale`) | Pilihan agregator | Bukan (relasi live) | `1` |
| `date` | Tanggal penjualan (boleh backdate; stok divalidasi kumulatif, lihat §6) | date | Wajib | Input tanggal | Ya | `2025-07-26` |
| `total_amount` | Total penjualan = SUM(`sale_details.subtotal`); dihitung server | decimal(15,2) | Wajib (computed) | Server | Ya (turunan snapshot) | `748802.50` |
| `notes` | Catatan bebas | varchar(255) | Nullable | Input | Ya | `Timbangan KamiBox 26 Juli` |
| `created_at` / `updated_at` | Waktu pencatatan sistem | timestamp nullable | Otomatis | Laravel | — | — |

Efek samping `recordSale`: bila `totalMargin > 0`, membuat/mengupdate 1 baris
`waste_bank_cash_ledgers` (`type='in'`, `amount=totalMargin`,
`reference_type=App\Models\Sale`), lalu `recalculateWasteBankBalances()`
menghitung ulang **seluruh** saldo ledger urut tanggal. Margin 0 → ledger dihapus.

### 2.2 `sale_details`

Satu baris = satu material dalam satu penjualan.
FK `sale_id` → `sales` **`cascadeOnDelete`**. Duplikat material dalam satu
payload **diagregasi** (`groupBy waste_category_id`, berat dijumlah, dibulatkan 2 desimal).

| Field | Arti bisnis | Tipe DB | Wajib? | Sumber data | Snapshot? | Contoh |
|---|---|---|---|---|---|---|
| `id` | PK detail | bigint unsigned AI | Ya (auto) | DB | — | `9001` |
| `sale_id` | Penjualan induk | bigint unsigned | Wajib | Dibuat bersama sale | — | `7` |
| `waste_category_id` | Material kanonik (FK restrict) | bigint unsigned | Wajib | Pilihan material | Referensi live | `15` |
| `weight` | Berat jual (kg) | decimal(10,2) | Wajib, **> 0** (`min:0.01`; baris 0 dibuang) | Input, maks = sisa stok kumulatif s/d tanggal sale | Ya | `120.50` |
| `price_per_unit` | **Harga lapak per kg yang dipakai transaksi ini** | decimal(15,2) | Wajib | Prioritas: `custom_price` (> 0) → `waste_prices.collector_price` kolektor tsb → **ERROR** `Harga penjualan ... belum ditentukan` bila tidak ada. Saat edit tanpa custom: **snapshot lama dipertahankan**, master tidak dibaca ulang | **Ya, snapshot historis** | `1700.00` |
| `member_price` | **Harga beli nasabah per kg saat sale** (pembanding margin) | decimal(15,2), default 0 | Wajib (default 0) | `waste_categories.member_price` saat sale (atau snapshot lama saat edit) | **Ya, snapshot historis** | `1000.00` |
| `subtotal` | Nilai baris = `round(weight × price_per_unit)` | decimal(15,2) | Wajib (computed) | Server | **Ya** | `204850.00` |
| `margin` | Margin baris = `round((price_per_unit − member_price) × weight)` | decimal(15,2), default 0 | Wajib (computed) | Server | **Ya** | `84350.00` |

Master vs snapshot:

- **Master/current**: `waste_categories.member_price`, `waste_prices.collector_price`.
- **Historical snapshot** (tidak berubah walau master berubah):
  `deposit_details.price_per_unit`, `deposit_details.subtotal`,
  `sale_details.price_per_unit`, `sale_details.member_price`,
  `sale_details.subtotal`, `sale_details.margin`.
- Pengecualian satu-satunya: **edit sale** (`recordSale($data, $saleId)`) menghapus
  dan menulis ulang detail sale tersebut (snapshot baru), dan `DepositController@update`
  menghapus + menulis ulang detail deposit + mengupdate ledger (bukan menambah).

---

## 3. HARGA AGREGATOR

### 3.1 `waste_prices`

Harga lapak **berlaku saat ini** per (material, agregator).
Unique `(waste_category_id, collector_id)` — duplikat di level DB ditolak;
form juga menolak (`WastePriceController@store`, `CollectorController@storePrice`).
FK keduanya `cascadeOnDelete`.

| Field | Arti bisnis | Tipe DB | Wajib? | Sumber | Contoh |
|---|---|---|---|---|---|
| `id` | PK | bigint unsigned AI | Ya (auto) | DB | `6` |
| `waste_category_id` | Material | bigint unsigned | Wajib | Pilihan | `15` |
| `collector_id` | Agregator | bigint unsigned | Wajib | Pilihan / konteks halaman kolektor | `1` |
| `collector_price` | Harga lapak per kg **saat ini** | decimal(15,2) | **Nullable** | Form (`required\|numeric\|min:0`), import harga, atau `save_as_master_price` dari sale | `1700.00` / `NULL` / `0.00` |

**NULL vs 0** (perilaku code, bukan asumsi):

- `NULL` = belum ada harga. Di `recordSale`, `isset($collectorPrices[$catId])`
  bernilai false untuk NULL → sale **ditolak** kecuali operator mengisi
  `custom_price`. Di `getDailyRemainingWaste`, material tanpa harga tetap tampil
  (stok > 0) dengan `collector_price: null`.
- `0` = harga nol eksplisit (form mengizinkan `min:0`). Lolos cek `isset`, lalu
  kena aturan `collectorPrice < memberPrice` → ditolak kecuali member juga 0.
  Praktis: untuk material "tidak diterima" (mis. Asoy di KamiBox), **tidak ada baris**
  (= NULL) lebih tepat daripada baris 0.

### 3.2 `waste_price_histories`

Jejak perubahan harga lapak. Ditulis **hanya bila harga benar-benar berubah**
(`abs(old − new) > 0.001`; harga identik tidak dicatat) dan saat pembuatan baris
baru (`old_price = NULL`).

| Field | Arti bisnis | Tipe DB | Wajib? | Sumber | Contoh |
|---|---|---|---|---|---|
| `id` | PK | bigint unsigned AI | Ya | DB | `31` |
| `waste_price_id` | Baris harga terkait (`nullOnDelete`) | bigint unsigned | Nullable | Sistem | `6` |
| `collector_id` | Agregator (`cascadeOnDelete`) | bigint unsigned | Wajib | Sistem | `1` |
| `waste_category_id` | Material (`cascadeOnDelete`) | bigint unsigned | Wajib | Sistem | `15` |
| `old_price` | Harga sebelum (NULL = baris baru) | decimal(15,2) | Nullable | Sistem | `1500.00` / `NULL` |
| `new_price` | Harga sesudah | decimal(15,2) | Wajib | Sistem | `1700.00` |
| `unit` | Satuan material saat perubahan | varchar(20) | Nullable | `category->unit ?? 'kg'` | `kg` |
| `changed_at` | Waktu perubahan (manual: `now()`; historis idealnya tanggal price list) | timestamp | Wajib | Sistem | `2025-07-26 10:00:00` |
| `changed_by_user_id` | Operator (`nullOnDelete` users) | bigint unsigned | Nullable | `auth()->id()` | `2` |
| `admin_device_id` | Perangkat admin (`nullOnDelete`) | bigint unsigned | Nullable | DeviceTrackingService | `1` |
| `source` | `MANUAL` (form/inline/save-as-master) atau `IMPORT` (import harga) | varchar(20), default `MANUAL` | Wajib | Konstanta `SOURCE_MANUAL` / `SOURCE_IMPORT` | `IMPORT` |
| `import_batch_id` | ID batch import | varchar(255) | Nullable | **Tidak pernah diisi code saat ini** (hanya ada di fillable + schema) | `NULL` |

**Bagaimana harga dipakai saat membuat Sale** (`recordSale`):

1. `custom_price` (> 0, dari inline edit) menang atas segalanya.
2. Jika tidak ada, pakai `waste_prices.collector_price` untuk kolektor tersebut.
3. Jika tidak ada (baris tidak ada / NULL) → sale ditolak dengan pesan harga belum ditentukan.
4. Cek `collector_price >= member_price` (member = master saat itu), jika tidak → ditolak.
5. Flag `save_as_master_price` pada baris sale: menulis balik `waste_prices` +
   satu `waste_price_histories` (`source=MANUAL`).

---

## 4. MASTER DATA

### 4.1 `waste_categories` (Material kanonik)

| Field | Arti bisnis | Tipe DB | Wajib? | Contoh |
|---|---|---|---|---|
| `id` | PK material | bigint unsigned AI | Ya | `15` |
| `code` | Kode unik tampilan (`PREFIX.NN`, mis. `KRT.01`; tanpa grup → `UNC.01`; dibuat via `WasteCategory::generateCode`) | varchar(255) | Nullable (DB), dipakai untuk matching import (`findCanonical` cocokkan code atau nama) | `KRT.01` |
| `name` | Nama kanonik material | varchar(255) | Wajib (`max:100` di form) | `Kardus` |
| `unit` | Satuan (default `kg`) | varchar(255), default `kg` | Wajib | `kg` |
| `member_price` | **Harga beli nasabah master** (dibaca setiap entry setoran & setiap sale baru) | decimal(15,2), default 0 | Wajib | `1000.00` |
| `waste_category_group_id` | Grup (FK, nullable) | bigint unsigned | Nullable | `3` |
| `category_group` | Kolom legacy string nama grup (diisi seeder lama; **bukan** sumber relasi) | varchar(255) | Nullable | `Kertas` |

Relasi: ` wasteCategoryGroup()` belongsTo, `wastePrices()` hasMany,
`materialAliases()` hasMany. Contoh grup aktual: kode `BOT/BAJ/KER/PLA/GEL/RUM`.

### 4.2 `waste_category_groups`

| Field | Arti bisnis | Tipe DB | Wajib? | Contoh |
|---|---|---|---|---|
| `id` / `code` / `name` | PK / kode unik (`unique` + `max:10` di form) / nama | bigint / varchar(255) / varchar(255) | Wajib | `3` / `KER` / `Kertas` |
| `description` | Keterangan | text | Nullable | `Kategori sampah berbahan dasar kertas/karton` |
| `is_active` | Grup aktif (form setoran hanya tampilkan active) | boolean default true | Wajib | `1` |

### 4.3 `collectors` (Agregator — SoftDeletes)

| Field | Arti bisnis | Tipe DB | Wajib? | Contoh |
|---|---|---|---|---|
| `id` / `name` | PK / nama agregator (`required\|max:100`) | bigint / varchar(255) | Wajib | `1` / `KamiBox` |
| `phone` / `address` | Kontak | varchar(255) | Nullable | `0829xxxx` |
| `deleted_at` | Soft delete; `sales.collector_id` restrict (sale lama menahan hapus permanen di level DB) | timestamp | Nullable | `NULL` |

### 4.4 `material_aliases` (MaterialMapping)

Mekanisme mapping existing — **tidak ada sistem kedua**.
Unique DB: `(source_type, source_id, normalized_name)`.

| Field | Arti bisnis | Tipe DB | Wajib? | Contoh |
|---|---|---|---|---|
| `id` | PK | bigint unsigned AI | Ya | `27` |
| `source_type` | Ruang lingkup: `BANK_SAMPAH` (label setoran; `source_id` harus NULL) atau `AGGREGATOR` (label price-list agregator; `source_id` = collector) | varchar(30), default `BANK_SAMPAH` | Wajib | `BANK_SAMPAH` |
| `source_id` | Kolektor bila AGGREGATOR (FK `nullOnDelete`) | bigint unsigned | Nullable | `NULL` |
| `external_name` | Label apa adanya dari Excel | varchar(255) | Wajib | `Kardus Baik/Kering` |
| `normalized_name` | Kunci cocok: lowercase + trim + collapse spasi (`MaterialMappingService::normalizeLabel`) | varchar(255), indexed | Wajib (auto) | `kardus baik/kering` |
| `waste_category_id` | Material kanonik tujuan (`cascadeOnDelete`) | bigint unsigned | Wajib | `15` |
| `is_active` | Alias nonaktif diabaikan resolver | boolean default true | Wajib | `1` |

Pipeline resolusi (`MaterialMappingService::resolve`):
`EXACT` (code/nama kanonik) → `ALIAS` (scoped, aktif) → `NEEDS_MAPPING`
(kandidat saran ≤ 8, tidak pernah otomatis dipilih).
Catatan: `DepositImportService::findOrCreateAutoMaterial` membuat material baru
`1 label = 1 material` bila tak cocok (dipakai import setoran operasional).

### 4.5 `waste_customers` (hanya field untuk mapping historis)

| Field | Kebutuhan mapping historis | Tipe DB | Wajib? |
|---|---|---|---|
| `id` | PK, target `deposits.waste_customer_id` | bigint unsigned AI | Ya |
| `customer_code` | Kode unik `NSB-000001` dst (`generateNextCustomerCode`, sequensial, tak berubah) | varchar(255) | Wajib |
| `name` | Nama nasabah — kunci pencocokan utama (normalisasi lowercase+collapse spasi; duplikat nama → status AMBIGUOUS di import) | varchar(255) | Wajib |
| `phone` | Kontak (form `max:20`, nullable) | varchar(255) | Opsional |
| `address` | Alamat; dipakai menurunkan nomor RT (`getRtNumberAttribute`, pola `RT\s*0*(\d+)`) | text | Opsional |
| `status` | `active` / `inactive` (default `active`); import hanya memakai yang `active` | varchar(255) | Wajib |
| `joined_at` | Waktu dibuat profil | timestamp | Nullable |
| `user_id` | Kaitan akun login (FK users; operasional: selalu NULL) | bigint unsigned | Nullable — biarkan NULL untuk hasil import |

---

## 5. RELASI BISNIS (flow aktual)

### Setoran

```text
WasteCustomer (1) ──< Deposit (N) ──< DepositDetail (N) >── WasteCategory (1)
        │                    │
        │                    └── total_amount = SUM(details.subtotal) [computed]
        └── SavingsLedger credit (reference_type=Deposit) menambah saldo
```

- `deposit_details.price_per_unit` ← `waste_categories.member_price` **saat entry**
  (bukan live link).
- Hapus deposit: ledger + details dihapus dalam satu transaksi, tapi hanya bila
  `validateDepositCanBeDeleted` lolos (stok kumulatif tak boleh negatif).

### Penjualan

```text
Collector (1) ──< Sale (N) ──< SaleDetail (N) >── WasteCategory (1)
                           │
                           └── total_amount = SUM(details.subtotal) [computed]
                           └── WasteBankCashLedger 'in' amount = SUM(margin) bila > 0
```

- `sale_details.price_per_unit` ← `waste_prices.collector_price`
  (atau `custom_price`, atau snapshot lama saat edit).
- `sale_details.member_price` ← `waste_categories.member_price` **saat sale**.
- `sale_details.margin` = `(price_per_unit − member_price) × weight` (rounded).
- `sale_details.subtotal` = `weight × price_per_unit` (rounded).

---

## 6. CUMULATIVE STOCK

Fungsi: `BankSampahService::getDailyRemainingWaste($date, $collectorId)`
dan validasi `assertCumulativeStockNotNegative` (dipakai `recordSale`,
`validateDepositCanBeDeleted`, `validateDepositCanBeUpdated`).

Rumus per material per tanggal `D`:

```text
stok(D) = SUM(deposit_details.weight WHERE deposit.date <= D)
        − SUM(sale_details.weight    WHERE sale.date    <= D)
```

- **Carry-over otomatis**: seluruh histori s/d tanggal itu ikut dihitung.
- **Backdated sale**: (a) sisa `min(stok s/d tanggal sale, stok all-time)` harus
  > 0 dan ≥ berat diminta; (b) **full-timeline guard** membangun ulang kurva
  stok harian + perubahan pending dan menolak transaksi bila **tanggal mana pun**
  menjadi negatif (error menyebut tanggal + sisa, mis. `... negatif pada 2025-08-09 (-2.5 kg)`).
- **Implikasi urutan import**: hasil akhir setara selama stok tak pernah negatif
  di titik mana pun; praktisnya **import kronologis (tanggal menaik)** — setoran
  dulu lalu penjualan — agar tiap langkah lolos guard. Sale tanpa stok
  sebelumnya selalu ditolak.

Tambahan terkait:

- Saldo nasabah: `SUM(savings_ledgers credit) − SUM(debit)` per customer
  (`getCustomerBalance`); penarikan mensyaratkan ≥ 2 setoran dan amount ≤ saldo.
- Kas bank: `waste_bank_cash_ledgers` dihitung ulang penuh urut tanggal
  (`recalculateWasteBankBalances`); ledger sale = margin, bukan omzet.

---

## 7. FORMAT DATA UNTUK HISTORICAL MAPPING

### SETORAN

| Field Excel yang dibutuhkan | Field EcoBank026 | Wajib? | Keterangan |
|---|---|---|---|
| Nama nasabah | `waste_customers.name` (+ `customer_code` baru bila belum ada) | Ya | Normalisasi lowercase+rapikan spasi; duplikat nama = AMBIGUOUS, harus dipetakan manual ke id |
| RT / alamat | `waste_customers.address` | Tidak | Disarankan (menurunkan RT); `phone` bila ada |
| Jenis Sampah (label mentah) | `material_aliases.external_name` → `waste_categories.id` | Ya | Resolusi: kanonik → alias BANK_SAMPAH aktif → mapping manual eksplisit |
| Harga (kolom Harga) | `deposit_details.price_per_unit` | Ya* | *Wajib ada nilai per baris, tapi **boleh kosong** = fallback `member_price` master. Non-angka = ERROR. Negatif = ERROR |
| Berat | `deposit_details.weight` | Ya | Angka > 0; desimal koma/titik dan `Rp`/ribuan diparse (`parseNumber`) |
| Total Harga (kolom Total) | `deposit_details.subtotal` | Tidak | Bila ada dipakai **apa adanya**; bila kosong dihitung `round(weight × price)` |
| Tanggal (nama sheet / konfirmasi) | `deposits.date` | Ya | Format `DD-MM-YYYY`/`YYYY-MM-DD` (`guessDateFromSheetName`); 1 sheet = 1 tanggal |
| Total per nasabah per tanggal | `deposits.total_amount` | Otomatis | Dihitung server = SUM(subtotal); baris satu nasabah satu tanggal digabung jadi satu Deposit |
| Fingerprint file+sheet+tanggal | `deposits.notes` (`[FP:...]`) + cache | Otomatis | Re-import sheet+tanggal yang sama **diblokir** |

### PENJUALAN

| Field Excel yang dibutuhkan | Field EcoBank026 | Wajib? | Keterangan |
|---|---|---|---|
| Nama agregator (Bu Erta / Pak Surat / KamiBox) | `sales.collector_id` → `collectors.id` | Ya | Agregator harus ada di master dulu |
| Tanggal timbangan | `sales.date` | Ya | Stok kumulatif s/d tanggal ini harus cukup (guard §6) |
| Jenis barang (label lapak) | `sale_details.waste_category_id` via alias scope `AGGREGATOR` per kolektor (atau kanonik) | Ya | Alias agregator bersifat **per kolektor** (`source_id`), tidak global |
| Berat | `sale_details.weight` | Ya | > 0; agregasi per material per sale |
| Harga lapak | `sale_details.price_per_unit` | Ya | Dari `waste_prices.collector_price` kolektor tsb, atau `custom_price` eksplisit bila master tak punya |
| Harga beli nasabah saat itu | `sale_details.member_price` | Otomatis* | *Snapshot `member_price` master saat sale; untuk historis, master harus diset ke harga periode itu **sebelum** mencatat sale (atau tulis snapshot langsung bila via jalur import khusus) |
| Subtotal / margin / total | `subtotal`, `margin`, `sales.total_amount` | Otomatis | `round()` rupiah; margin = (lapak − member) × berat; wajib lapak ≥ member |
| Keterangan timbangan | `sales.notes` | Tidak | Disarankan (jejak audit) |

### HARGA AGREGATOR

| Field Excel yang dibutuhkan | Field EcoBank026 | Wajib? | Keterangan |
|---|---|---|---|
| Nama agregator (nama sheet / konteks) | `waste_prices.collector_id` + `waste_price_histories.collector_id` | Ya | Satu price list = satu kolektor |
| Jenis Barang | `waste_category_id` via kanonik / alias AGGREGATOR | Ya | Header yang dikenali: `Jenis Barang/Material/Nama Barang/...`; tanpa header cocok → 2 kolom (material, harga) |
| Harga Lapak | `waste_prices.collector_price` + `waste_price_histories.new_price` | Ya | Numerik ≥ 0; non-angka/negatif dilewati impor. `Tidak Terima` = **jangan buat baris (NULL)**, bukan 0 |
| Harga Nasabah (kolom pembanding) | — (tidak masuk `waste_prices`; hanya referensi validasi vs `member_price`) | Tidak | `waste_prices` hanya menyimpan `collector_price` (kolom `member_price`/`price_per_unit` lama sudah di-drop) |
| Satuan | `waste_price_histories.unit` (dan `waste_categories.unit`) | Tidak | Default `kg`; penting untuk item `unit/pcs/set/ltr` |
| Periode berlaku | `waste_price_histories.changed_at` (+ `old_price`) | Ya* | *Diisi `now()` oleh form; untuk historis isi tanggal price list agar jejak benar. `source=IMPORT`, `import_batch_id` tersedia tapi belum dipakai code |
| Material tak dikenal | Quick Add kategori (`member_price` default 0, dikelola terpisah) + alias AGGREGATOR otomatis bila nama beda | Kondisional | Tidak pernah fuzzy-match; harus eksplisit |

---

## 8. RULES FOR HISTORICAL IMPORT

Aturan yang WAJIB dipenuhi data hasil normalisasi sebelum masuk EcoBank026
(semua berasal dari perilaku code di atas, bukan asumsi):

1. **Referensi harus resolved**: setiap baris setoran menunjuk `waste_customer_id`
   dan `waste_category_id` yang ada; setiap sale menunjuk `collector_id` dan
   `waste_category_id` yang ada. Label mentah tanpa resolusi = tolak.
2. **Berat > 0** selalu; `weight <= 0` atau non-angka = ERROR.
3. **Harga deposit valid**: per baris ada `price_per_unit` (dari Excel atau
   fallback master); non-angka = ERROR, negatif = ERROR. Sel kosong = fallback
   master (bukan 0).
4. **Subtotal konsisten**: bila Total Excel dipakai, ia menjadi snapshot;
   bila dihitung, `subtotal = round(weight × price_per_unit)`,
   `total_amount = SUM(subtotal)` per deposit/sale. Jangan tulis total hitungan
   sendiri yang berbeda dari aturan ini.
5. **Sale butuh harga lapak**: tiap material sale harus punya `collector_price`
   kolektor tersebut atau `custom_price`; NULL/tidak ada = sale ditolak.
6. **Margin tidak negatif**: `collector_price >= member_price` per baris sale,
   jika tidak ditolak.
7. **Stok tidak negatif di titik mana pun**: untuk setiap material dan setiap
   tanggal, kumulatif setoran − penjualan ≥ 0. Praktis: import **kronologis**,
   setoran sebelum penjualan yang memakainya.
8. **NULL ≠ 0 pada harga lapak**: `NULL` (tanpa baris) = belum ada harga;
   `0` = harga nol eksplisit (hampir selalu gagal aturan no. 6). Material
   "Tidak Terima" = tanpa baris.
9. **Unik harga**: satu baris `waste_prices` per (material, kolektor). Update =
   ubah baris + tambah `waste_price_histories` hanya bila berubah.
10. **Alias scoped**: alias setoran (`BANK_SAMPAH`, `source_id` NULL) terpisah
    dari alias price-list agregator (`AGGREGATOR`, `source_id` = kolektor).
    Kunci unik `(source_type, source_id, normalized_name)`.
11. **Nasabah unik per nama**: duplikat nama = AMBIGUOUS; petakan ke id eksplisit.
    `customer_code` format `NSB-xxxxxx` sequensial; `status` hanya
    `active`/`inactive`; `user_id` biarkan NULL.
12. **Snapshot beku**: setelah tulis, `deposit_details.*` dan
    `sale_details.*` tidak berubah walau master berubah. Perubahan master
    (`member_price`, `collector_price`) hanya berlaku untuk transaksi **baru**.
    Untuk sale historis, set master periode itu dulu sebelum mencatat.
13. **Efek turunan wajib ikut**: tiap deposit menambah ledger tabungan
    (`credit`, ref Deposit); tiap sale bermargin menambah ledger kas
    (`in`, ref Sale) dan saldo dihitung ulang kronologis. Importer yang menulis
    langsung ke tabel wajib mereplikasi ini.
14. **Idempotensi**: kunci anti-duplikat adalah kombinasi file+sheet+tanggal
    (fingerprint di `notes` + cache). Jangan import ulang sheet yang sama tanpa
    mekanisme rollback.
15. **Satuan**: default `kg`; item non-kg (`unit/pcs/set/ltr`) wajib membawa
    `unit`-nya agar berat × harga bermakna.
