<?php

use App\Http\Controllers\CollectorController;
use App\Http\Controllers\RtController;
use App\Http\Controllers\KkController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\CommunityContributionController;
use App\Http\Controllers\CommunityExpenseController;
use App\Http\Controllers\CommunityCashReportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\FundCategoryController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MemberImportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SavingsReportController;
use App\Http\Controllers\WargaSavingsController;
use App\Http\Controllers\WargaBillController;
use App\Http\Controllers\WasteBankCashReportController;
use App\Http\Controllers\WasteCategoryController;
use App\Http\Controllers\WastePriceController;
use App\Http\Controllers\WastePriceImportController;
use App\Http\Controllers\WasteCategoryImportController;
use App\Http\Controllers\WithdrawalController;
use App\Http\Controllers\WasteCustomerController;
use App\Http\Controllers\WasteCategoryGroupController;
use App\Http\Controllers\WasteBankReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Main dashboard route - redirects based on role
Route::get('/dashboard', function () {
    $user = auth()->user();

    if ($user->hasRole('admin_rw')) {
        return app(DashboardController::class)->adminRw();
    }
    if ($user->hasRole('admin_rt')) {
        return app(DashboardController::class)->adminRt();
    }
    if ($user->hasRole('bendahara') || $user->hasRole('bendahara_rw')) {
        return redirect()->route('bendahara.dashboard');
    }
    if ($user->hasRole('admin_bank_sampah')) {
        return redirect()->route('bank-sampah.dashboard');
    }
    if ($user->hasRole('warga')) {
        return redirect()->route('warga.dashboard');
    }

    return app(DashboardController::class)->adminRt();
})->middleware(['auth', 'verified'])->name('dashboard');

// Bendahara dashboard
Route::get('/bendahara/dashboard', [DashboardController::class, 'bendahara'])
    ->middleware(['auth', 'permission:view_community_cash'])->name('bendahara.dashboard');

// Bank Sampah dashboard
Route::get('/bank-sampah/dashboard', [DashboardController::class, 'bankSampah'])
    ->middleware(['auth', 'permission:view_waste_bank'])->name('bank-sampah.dashboard');

// Bank Sampah Monitoring Audit Dashboard
Route::get('/bank-sampah/monitoring', [\App\Http\Controllers\BankSampahMonitoringController::class, 'index'])
    ->middleware(['auth', 'role:admin_bank_sampah|admin_rw'])
    ->name('bank-sampah.monitoring');

// Warga dashboard
Route::get('/warga/dashboard', [DashboardController::class, 'warga'])
    ->middleware(['auth', 'permission:view_own_dashboard'])->name('warga.dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Community Cash - Fund Categories
Route::middleware(['auth', 'permission:manage_fund_categories'])->prefix('community-cash')->name('community-cash.')->group(function () {
    Route::resource('categories', FundCategoryController::class)->except(['show']);
});

// Community Cash - Contributions
Route::middleware(['auth', 'permission:manage_contributions'])->prefix('community-cash')->name('community-cash.')->group(function () {
    Route::resource('contributions', CommunityContributionController::class)->except(['show']);
});

// Modul Tagihan Bulanan (Topic 15)
Route::middleware(['auth', 'permission:manage_contributions'])->prefix('iuran')->name('iuran.')->group(function () {
    Route::get('tagihan', [BillController::class, 'index'])->name('bills.index');
    Route::get('tagihan/generate', [BillController::class, 'create'])->name('bills.generate.form');
    Route::post('tagihan/generate', [BillController::class, 'generate'])->name('bills.generate');
    Route::post('tagihan/{bill}/pay', [BillController::class, 'pay'])->name('bills.pay');
    Route::get('tunggakan', [BillController::class, 'arrears'])->name('bills.arrears');
    Route::get('laporan-tahunan', [BillController::class, 'annualReport'])->name('bills.annual_report');
});

// Community Cash - Expenses
Route::middleware(['auth', 'permission:manage_expenses'])->prefix('community-cash')->name('community-cash.')->group(function () {
    Route::resource('expenses', CommunityExpenseController::class)->except(['show']);
});

// Community Cash - Report (Admin/Bendahara)
Route::get('/community-cash/report', [CommunityCashReportController::class, 'index'])
    ->middleware(['auth', 'permission:view_cash_reports'])->name('community-cash.report');

Route::get('/community-cash/report/export', [CommunityCashReportController::class, 'export'])
    ->middleware(['auth', 'permission:view_cash_reports'])->name('community-cash.report.export');

Route::get('/community-cash/report/pdf', [CommunityCashReportController::class, 'pdf'])
    ->middleware(['auth', 'permission:view_cash_reports'])->name('community-cash.report.pdf');

// Warga - Public Cash Report
Route::get('/warga/cash-report', [CommunityCashReportController::class, 'publicReport'])
    ->middleware(['auth', 'permission:view_public_cash_report'])->name('warga.cash-report');

// Warga - Bank Sampah Savings
Route::middleware(['auth', 'permission:view_own_savings'])->group(function () {
    Route::get('/warga/savings', [WargaSavingsController::class, 'index'])->name('warga.savings');
    Route::get('/warga/savings/history', [WargaSavingsController::class, 'history'])->name('warga.savings.history');
});

// Warga - Billing Portal
Route::middleware(['auth', 'permission:view_own_dashboard'])->group(function () {
    Route::get('/warga/tagihan', [WargaBillController::class, 'index'])->name('warga.bills');
});

// Members / Data Warga
Route::middleware(['auth', 'permission:manage_members'])->group(function () {
    // KK Import
    Route::get('kks/import', [\App\Http\Controllers\KkImportController::class, 'showForm'])->name('kks.import');
    Route::post('kks/import', [\App\Http\Controllers\KkImportController::class, 'import'])->name('kks.import.store');
    Route::get('kks/import/template', [\App\Http\Controllers\KkImportController::class, 'downloadTemplate'])->name('kks.import.template');
    Route::get('kks/import/failed-download', [\App\Http\Controllers\KkImportController::class, 'downloadFailed'])->name('kks.import.failed-download');

    // Members / Data Warga Import Upgraded
    Route::get('members/import-v2', [\App\Http\Controllers\MemberImportControllerV2::class, 'showForm'])->name('members.import-v2');
    Route::post('members/import-v2', [\App\Http\Controllers\MemberImportControllerV2::class, 'import'])->name('members.import-v2.store');
    Route::get('members/import-v2/template', [\App\Http\Controllers\MemberImportControllerV2::class, 'downloadTemplate'])->name('members.import-v2.template');
    Route::get('members/import-v2/failed-download', [\App\Http\Controllers\MemberImportControllerV2::class, 'downloadFailed'])->name('members.import-v2.failed-download');

    Route::get('members/import', [MemberImportController::class, 'showForm'])->name('members.import');
    Route::post('members/import', [MemberImportController::class, 'import'])->name('members.import.store');
    Route::get('members/import/template', [MemberImportController::class, 'template'])->name('members.import.template');
    Route::resource('members', MemberController::class);
    Route::post('members/{member}/reset-password', [MemberController::class, 'resetPassword'])->name('members.reset-password');
    Route::post('members/{member}/create-login-account', [MemberController::class, 'createLoginAccount'])->name('members.create-login-account');
    
    // RT & KK CRUD
    Route::resource('rts', RtController::class)->except(['show']);
    Route::resource('kks', KkController::class);
});

// Bank Sampah - Waste Categories
Route::middleware(['auth', 'permission:manage_waste_prices'])->prefix('bank-sampah')->name('bank-sampah.')->group(function () {
    Route::get('waste-categories/import', [WasteCategoryImportController::class, 'showForm'])->name('waste-categories.import');
    Route::post('waste-categories/import', [WasteCategoryImportController::class, 'import'])->name('waste-categories.import.store');
    Route::get('waste-categories/import/template', [WasteCategoryImportController::class, 'template'])->name('waste-categories.import.template');
    Route::get('waste-categories/import/failed-rows', [WasteCategoryImportController::class, 'downloadFailedRows'])->name('waste-categories.import.failed-rows');
    Route::get('waste-prices/import', [WastePriceImportController::class, 'showForm'])->name('waste-prices.import');
    Route::post('waste-prices/import', [WastePriceImportController::class, 'import'])->name('waste-prices.import.store');
    Route::get('waste-prices/import/template', [WastePriceImportController::class, 'template'])->name('waste-prices.import.template');
    Route::get('waste-prices/import/failed-rows', [WastePriceImportController::class, 'downloadFailedRows'])->name('waste-prices.import.failed-rows');
    Route::resource('waste-categories', WasteCategoryController::class)->except(['show']);
    Route::patch('waste-category-groups/{waste_category_group}/toggle', [WasteCategoryGroupController::class, 'toggle'])->name('waste-category-groups.toggle');
    Route::resource('waste-category-groups', WasteCategoryGroupController::class)->except(['show', 'destroy']);
    Route::resource('collectors', CollectorController::class)->except(['show']);
    Route::resource('waste-prices', WastePriceController::class)->except(['show']);
});

// Bank Sampah - Customers
Route::middleware(['auth', 'permission:manage_waste_customers'])->prefix('bank-sampah')->name('bank-sampah.')->group(function () {
    Route::resource('customers', WasteCustomerController::class);
});

// Bank Sampah - Deposits
Route::middleware(['auth', 'permission:manage_deposits'])->prefix('bank-sampah')->name('bank-sampah.')->group(function () {
    Route::resource('deposits', DepositController::class)->except(['show']);
});

// Bank Sampah - Withdrawals
Route::middleware(['auth', 'permission:manage_withdrawals'])->prefix('bank-sampah')->name('bank-sampah.')->group(function () {
    Route::resource('withdrawals', WithdrawalController::class)->except(['show']);
});

// Bank Sampah - Sales
Route::middleware(['auth', 'permission:manage_sales'])->prefix('bank-sampah')->name('bank-sampah.')->group(function () {
    Route::resource('sales', SaleController::class)->except(['show']);
});

// Bank Sampah - Expenses
Route::middleware(['auth', 'role:admin_bank_sampah|admin_rw'])->prefix('bank-sampah')->name('bank-sampah.')->group(function () {
    Route::resource('expenses', \App\Http\Controllers\WasteBankExpenseController::class)->only(['index', 'create', 'store', 'show']);
});

// Bank Sampah - Reports Layer
Route::middleware(['auth', 'role:admin_bank_sampah|admin_rw'])->prefix('bank-sampah/reports')->name('bank-sampah.reports.')->group(function () {
    Route::get('deposits', [WasteBankReportController::class, 'deposits'])->name('deposits');
    Route::get('deposits/export/excel', [WasteBankReportController::class, 'depositsExcel'])->name('deposits.excel');
    Route::get('deposits/export/print', [WasteBankReportController::class, 'depositsPrint'])->name('deposits.print');

    Route::get('sales', [WasteBankReportController::class, 'sales'])->name('sales');
    Route::get('sales/export/excel', [WasteBankReportController::class, 'salesExcel'])->name('sales.excel');
    Route::get('sales/export/print', [WasteBankReportController::class, 'salesPrint'])->name('sales.print');

    Route::get('savings-journal', [WasteBankReportController::class, 'savingsJournal'])->name('savings-journal');
    Route::get('savings-journal/export/excel', [WasteBankReportController::class, 'savingsJournalExcel'])->name('savings-journal.excel');
    Route::get('savings-journal/export/print', [WasteBankReportController::class, 'savingsJournalPrint'])->name('savings-journal.print');

    Route::get('cashflow', [WasteBankReportController::class, 'cashflow'])->name('cashflow');
    Route::get('cashflow/export/excel', [WasteBankReportController::class, 'cashflowExcel'])->name('cashflow.excel');
    Route::get('cashflow/export/print', [WasteBankReportController::class, 'cashflowPrint'])->name('cashflow.print');
});

// Bank Sampah - Savings Report
Route::get('/bank-sampah/savings', [SavingsReportController::class, 'index'])
    ->middleware(['auth', 'permission:view_waste_reports'])->name('bank-sampah.savings');

Route::get('/bank-sampah/savings/export', [SavingsReportController::class, 'export'])
    ->middleware(['auth', 'permission:view_waste_reports'])->name('bank-sampah.savings.export');

// Bank Sampah - Cash Report
Route::get('/bank-sampah/cash-report', [WasteBankCashReportController::class, 'index'])
    ->middleware(['auth', 'permission:view_waste_reports'])->name('bank-sampah.cash-report');

Route::get('/bank-sampah/cash-report/export', [WasteBankCashReportController::class, 'export'])
    ->middleware(['auth', 'permission:view_waste_reports'])->name('bank-sampah.cash-report.export');

Route::get('/bank-sampah/cash-report/pdf', [WasteBankCashReportController::class, 'pdf'])
    ->middleware(['auth', 'permission:view_waste_reports'])->name('bank-sampah.cash-report.pdf');

// Admin RW - Audit Trail Logs
Route::get('/admin/audit-logs', [\App\Http\Controllers\ActivityLogController::class, 'index'])
    ->middleware(['auth', 'role:admin_rw'])
    ->name('admin.audit-logs');

// Admin RW - Settings
Route::middleware(['auth', 'role:admin_rw'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('settings', [\App\Http\Controllers\AppSettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [\App\Http\Controllers\AppSettingController::class, 'update'])->name('settings.update');
});

require __DIR__.'/auth.php';
