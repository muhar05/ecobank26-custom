# Database Direction

Existing bank sampah tables:
- users
- members
- deposits
- deposit_details
- withdrawals
- savings_ledgers
- collectors
- waste_categories
- waste_prices
- sales
- sale_details
- cashflows

New community cash module:
- fund_categories
- community_contributions
- community_expenses
- community_cash_ledgers

Important rules:
- Saldo nasabah bank sampah adalah uang pribadi member.
- Kas warga RT/RW adalah uang kolektif.
- Jangan campur savings_ledgers dengan community cash.
- Contributions increase community cash.
- Expenses decrease community cash.
