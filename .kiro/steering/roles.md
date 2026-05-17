# Roles and Permissions

Use Spatie Laravel Permission.

Initial roles:
- admin_rt
- bendahara
- admin_bank_sampah
- warga

Role behavior:
- admin_rt: full access to all modules
- bendahara: manage community cash only
- admin_bank_sampah: manage bank sampah only
- warga: read-only access to own data and public cash reports

Initial permissions:

User/Admin:
- manage_users
- manage_roles

Community Cash:
- view_community_cash
- manage_fund_categories
- manage_contributions
- manage_expenses
- view_cash_reports

Bank Sampah:
- view_waste_bank
- manage_members
- manage_deposits
- manage_withdrawals
- manage_sales
- manage_waste_prices
- view_waste_reports

Member/Warga:
- view_own_dashboard
- view_own_savings
- view_public_cash_report

Rules:
- Prefer permission checks using can().
- Avoid hardcoding role names everywhere.
- New roles should be possible later without rewriting code.
