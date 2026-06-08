# PROJECT_CLASS_DIAGRAM_KNOWLEDGE.md

Dokumen ini berisi informasi teknis hasil analisis kode asli untuk kebutuhan pembuatan Class Diagram PlantUML pada project Ecobank026.

## 1. Ringkasan Modul

Project ini terbagi menjadi 4 modul utama:
- **Modul Kependudukan (RT/RW & Warga):** Mengelola data struktur RT, Kartu Keluarga (KK), dan Anggota Keluarga (Member) serta akun pengguna (User).
- **Modul Kas Komunitas (Community Cash):** Mengelola kategori dana (iuran/sosial), pencatatan pemasukan (kontribusi), pengeluaran, tagihan bulanan (Bill), dan laporan kas.
- **Modul Bank Sampah (Waste Bank):** Mengelola kategori sampah, harga sampah, nasabah (WasteCustomer), transaksi setoran (Deposit), penarikan saldo (Withdrawal), penjualan sampah ke pengepul (Sale), dan kas internal bank sampah.
- **Modul Log & Pengaturan:** Mengelola log aktivitas, riwayat import, dan pengaturan aplikasi.

## 2. Role Pengguna

Berdasarkan `RolePermissionSeeder.php`, role yang tersedia adalah:
- **admin_rw:** Akses penuh ke seluruh fitur sistem (Manajemen User, Kas RT/RW, Bank Sampah, Laporan).
- **admin_rt:** Mengelola user di wilayah RT-nya, kas komunitas RT, dan memantau bank sampah.
- **bendahara / bendahara_rw:** Fokus pada manajemen kas komunitas, kategori dana, kontribusi, dan pengeluaran.
- **admin_bank_sampah:** Fokus pada operasional bank sampah (Setoran, Penarikan, Penjualan, Harga Sampah, Nasabah).
- **warga:** Akses dashboard pribadi, melihat saldo tabungan sampah, dan laporan kas publik.

## 3. Model Utama

### Modul Kependudukan
| Nama Model | Nama Tabel | Atribut Penting | Relasi Utama |
|---|---|---|---|
| **Rt** | `rts` | `rt_number`, `description` | HasMany: `Kk`, `User` (admin) |
| **Kk** | `kks` | `kk_number`, `family_head`, `status` | BelongsTo: `Rt`; HasMany: `Member`, `Bill` |
| **Member** | `members` | `member_code`, `name`, `relationship`, `gender` | BelongsTo: `Kk`, `User`; HasMany: `CommunityContribution` |
| **User** | `users` | `name`, `email`, `phone`, `password`, `rt_id` | HasOne: `Member`; HasMany: `WasteCustomer` |

### Modul Kas Komunitas
| Nama Model | Nama Tabel | Atribut Penting | Relasi Utama |
|---|---|---|---|
| **FundCategory** | `fund_categories` | `name`, `target_amount`, `is_mandatory`, `monthly_amount` | BelongsTo: `Rt`; HasMany: `Contribution`, `Expense`, `Bill` |
| **CommunityContribution** | `community_contributions` | `amount`, `date`, `recorded_by` | BelongsTo: `FundCategory`, `Member`, `Rt`, `User` |
| **CommunityExpense** | `community_expenses` | `amount`, `date`, `description` | BelongsTo: `FundCategory`, `Rt` |
| **Bill** | `bills` | `bill_code`, `amount`, `month`, `year`, `status` | BelongsTo: `Kk`, `FundCategory`; HasMany: `BillPayment` |
| **BillPayment** | `bill_payments` | `amount_paid`, `payment_method`, `paid_at` | BelongsTo: `Bill`, `CommunityContribution` |

### Modul Bank Sampah
| Nama Model | Nama Tabel | Atribut Penting | Relasi Utama |
|---|---|---|---|
| **WasteCategory** | `waste_categories` | `name`, `unit`, `code` | BelongsTo: `WasteCategoryGroup`; HasMany: `WastePrice` |
| **WasteCustomer** | `waste_customers` | `customer_code`, `name`, `status` | BelongsTo: `Member`, `User`; HasMany: `Deposit`, `Withdrawal` |
| **Deposit** | `deposits` | `date`, `total_amount` | BelongsTo: `WasteCustomer`, `Member`, `Collector`; HasMany: `DepositDetail` |
| **DepositDetail** | `deposit_details` | `weight`, `price_per_unit`, `subtotal` | BelongsTo: `Deposit`, `WasteCategory` |
| **Withdrawal** | `withdrawals` | `date`, `amount` | BelongsTo: `WasteCustomer` |
| **SavingsLedger** | `savings_ledgers` | `type`, `amount`, `reference_id` | BelongsTo: `WasteCustomer`, `Member` |
| **Sale** | `sales` | `date`, `total_amount` | BelongsTo: `Collector`; HasMany: `SaleDetail` |
| **SaleDetail** | `sale_details` | `weight`, `price_per_unit`, `subtotal` | BelongsTo: `Sale`, `WasteCategory` |

## 4. Relasi Antar Model (Ringkasan)

- `Rt` 1..* `Kk`
- `Kk` 1..* `Member`
- `Kk` 1..* `Bill`
- `FundCategory` 1..* `CommunityContribution`
- `FundCategory` 1..* `Bill`
- `Bill` 1..* `BillPayment`
- `WasteCustomer` 1..* `Deposit`
- `WasteCustomer` 1..* `Withdrawal`
- `WasteCustomer` 1..* `SavingsLedger`
- `Deposit` 1..* `DepositDetail`
- `Sale` 1..* `SaleDetail`
- `WasteCategory` 1..* `DepositDetail`
- `WasteCategory` 1..* `SaleDetail`

## 5. Service Penting

| Nama Service | Fungsi Utama | Method Penting | Model Terkait |
|---|---|---|---|
| **BankSampahService** | Logika inti transaksi bank sampah | `deposit()`, `withdraw()`, `calculateBalance()` | Deposit, Withdrawal, SavingsLedger |
| **CommunityCashService** | Logika pencatatan kas komunitas | `recordContribution()`, `recordExpense()` | Contribution, Expense, CashLedger |
| **BillService** | Manajemen tagihan otomatis | `generateMonthlyBills()`, `payBill()` | Bill, BillPayment, Contribution |
| **RtScopeService** | Filtering data berbasis wilayah RT | `applyRtScope()`, `getCurrentRtId()` | User, Rt |

## 6. Pembagian Class Diagram (Rekomendasi A4 Portrait)

Agar diagram tidak terlalu padat dan terbaca di laporan A4, disarankan dibagi menjadi 3 bagian:

1.  **Class Diagram Core & Kependudukan:** Menampilkan User, Role, Rt, Kk, Member.
2.  **Class Diagram Kas RT/RW:** Menampilkan Rt, Kk, FundCategory, Contribution, Expense, Bill, BillPayment.
3.  **Class Diagram Bank Sampah:** Menampilkan WasteCustomer, WasteCategory, WastePrice, Deposit, Withdrawal, Sale, SavingsLedger.

## 7. Kandidat Class Diagram

### Class Diagram Kas RT/RW (Modul Iuran)
- **Entities:** Rt, Kk, Member, User, FundCategory, CommunityContribution, CommunityExpense, CommunityCashLedger, Bill, BillPayment.
- **Services:** CommunityCashService, BillService.

### Class Diagram Bank Sampah
- **Entities:** WasteCustomer, Member, User, WasteCategory, WasteCategoryGroup, WastePrice, Deposit, DepositDetail, Withdrawal, SavingsLedger, Sale, SaleDetail, Collector.
- **Services:** BankSampahService.

### Class Diagram User & Role (Sistem)
- **Entities:** User, Role, Permission, ActivityLog, Rt, AppSetting.

---
*Catatan: Nama class di atas sesuai dengan nama model di `app/Models`. Relasi "BelongsTo" pada Laravel diimplementasikan sebagai Arrow (Composition/Association) pada PlantUML.*
