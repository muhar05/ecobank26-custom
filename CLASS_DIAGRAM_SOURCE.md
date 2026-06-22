# CLASS_DIAGRAM_SOURCE.md

## Daftar Model

### Core & Kependudukan
- **User**
    - Tabel: `users`
    - Atribut: `name`, `email`, `phone`, `password`, `rt_id`
    - Relasi: `belongsTo(Rt)`, `hasOne(Member)`, `hasMany(WasteCustomer)`
    - Method: `casts()`
- **Rt**
    - Tabel: `rts`
    - Atribut: `rt_number`, `description`
    - Relasi: `hasMany(Kk)`, `hasMany(User)`
- **Kk**
    - Tabel: `kks`ø
    - Atribut: `rt_id`, `kk_number`, `family_head`, `address`, `phone`, `status`
    - Relasi: `belongsTo(Rt)`, `hasMany(Member)`, `hasMany(Bill)`
    - Method: `getStatuses()`, `scopeActiveOrContract()`
- **Member**
    - Tabel: `members`
    - Atribut: `user_id`, `kk_id`, `member_code`, `name`, `phone`, `birth_date`, `gender`, `address`, `relationship`
    - Relasi: `belongsTo(User)`, `belongsTo(Kk)`, `hasMany(CommunityContribution)`, `hasMany(WasteCustomer)`
    - Method: `getAgeAttribute()`, `getAgeGroupAttribute()`, `generateNextCode()`
- **Collector**
    - Tabel: `collectors`
    - Atribut: `name`, `phone`
- **ImportHistory**
    - Tabel: `import_histories`

### Kas RT/RW
- **FundCategory**
    - Tabel: `fund_categories`
    - Atribut: `name`, `target_amount`, `mandatory_amount`, `rt_id`
- **CommunityContribution**
    - Tabel: `community_contributions`
    - Atribut: `member_id`, `fund_category_id`, `amount`, `rt_id`
- **CommunityExpense**
    - Tabel: `community_expenses`
    - Atribut: `fund_category_id`, `amount`, `description`, `rt_id`
- **CommunityCashLedger**
    - Tabel: `community_cash_ledgers`
    - Atribut: `rt_id`, `amount`, `type`, `description`
- **Bill**
    - Tabel: `bills`
    - Atribut: `kk_id`, `fund_category_id`, `amount`, `status`
    - Relasi: `belongsTo(Kk)`, `hasMany(BillPayment)`
- **BillPayment**
    - Tabel: `bill_payments`
    - Atribut: `bill_id`, `amount`, `payment_date`
    - Relasi: `belongsTo(Bill)`

### Bank Sampah
- **WasteCustomer**
    - Tabel: `waste_customers`
    - Atribut: `name`, `member_id`, `user_id`
    - Relasi: `belongsTo(Member)`, `hasMany(Deposit)`, `hasMany(Withdrawal)`, `hasMany(SavingsLedger)`
- **WasteCategory**
    - Tabel: `waste_categories`
    - Atribut: `name`, `code`, `waste_category_group_id`
- **WasteCategoryGroup**
    - Tabel: `waste_category_groups`
    - Atribut: `name`
- **WastePrice**
    - Tabel: `waste_prices`
    - Atribut: `waste_category_id`, `price_in`, `price_out`
- **Deposit**
    - Tabel: `deposits`
    - Atribut: `waste_customer_id`, `collector_id`
    - Relasi: `hasMany(DepositDetail)`
- **DepositDetail**
    - Tabel: `deposit_details`
    - Atribut: `deposit_id`, `waste_category_id`, `weight`, `price`
- **Withdrawal**
    - Tabel: `withdrawals`
    - Atribut: `waste_customer_id`, `amount`
- **SavingsLedger**
    - Tabel: `savings_ledgers`
    - Atribut: `waste_customer_id`, `amount`, `type`
- **Sale**
    - Tabel: `sales`
    - Atribut: `waste_customer_id`
    - Relasi: `hasMany(SaleDetail)`
- **SaleDetail**
    - Tabel: `sale_details`
    - Atribut: `sale_id`, `waste_category_id`, `weight`, `price`
- **WasteBankCashLedger**
    - Tabel: `waste_bank_cash_ledgers`
    - Atribut: `amount`, `type`
- **WasteBankExpense**
    - Tabel: `waste_bank_expenses`
    - Atribut: `amount`, `description`

### Log & Pengaturan
- **ActivityLog**
    - Tabel: `activity_logs`
- **AppSetting**
    - Tabel: `app_settings`

## Daftar Service
- **BankSampahService**: `recordDeposit`, `recordWithdrawal`, `recordSale`, `recordExpense` (Models: Deposit, Withdrawal, Sale, WasteBankExpense)
- **BillService**: `generateMonthlyBills`, `payBill` (Models: Bill, BillPayment, Kk)
- **CommunityCashService**: `recordContribution`, `recordExpense`, `getBalanceByCategory` (Models: CommunityContribution, CommunityExpense, FundCategory)
- **RtScopeService**: `applyRtScope`, `isGlobal`, `getUserRtId`
- **ActivityLogService**: `logAction` (Model: ActivityLog)
- **BankSampahAuditService**: Audit functionalities

## Relasi Utama
- Rt 1..* Kk
- Kk 1..* Member
- Kk 1..* Bill
- Bill 1..* BillPayment
- Member 1..* CommunityContribution
- WasteCustomer 1..* Deposit
- WasteCustomer 1..* Withdrawal
- WasteCustomer 1..* SavingsLedger
- Deposit 1..* DepositDetail

## Ringkasan
- Total model: 26
- Total service: 6
- Total relasi utama: 9 (terdata)
- Rekomendasi jumlah class diagram: 4 (dibagi sesuai modul)
