<?php

use App\Http\Controllers\CollectorController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SavingsReportController;
use App\Http\Controllers\WargaSavingsController;
use App\Http\Controllers\WasteBankCashReportController;
use App\Http\Controllers\WasteCategoryController;
use App\Http\Controllers\WastePriceController;
use App\Http\Controllers\WastePriceImportController;
use App\Http\Controllers\WasteCategoryImportController;
use App\Http\Controllers\WithdrawalController;
use App\Http\Controllers\WasteCustomerController;
use App\Http\Controllers\WasteCategoryGroupController;
use App\Http\Controllers\WasteBankReportController;
use App\Http\Controllers\BalanceCheckController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Cek Saldo Nasabah - publik, tanpa login
Route::get('/cek-saldo', [BalanceCheckController::class, 'index'])->name('cek-saldo.index');
// POST di-throttle: per IP dan per kombinasi credential (hash) untuk cegah brute-force/enumeration
Route::post('/cek-saldo', [BalanceCheckController::class, 'check'])
    ->middleware(['throttle:cek-saldo', 'throttle:cek-saldo-credential'])
    ->name('cek-saldo.check');

// Main dashboard route - land per-role on a page they can access
Route::get('/dashboard', function () {
    $user = auth()->user();

    if ($user->can('view_waste_bank')) {
        return redirect()->route('bank-sampah.dashboard');
    }

    if ($user->hasRole('warga')) {
        return redirect()->route('warga.savings');
    }

    // Roles with no RT/RW module pages anymore (bendahara, bendahara_rw)
    return redirect()->route('profile.edit');
})->middleware(['auth', 'verified'])->name('dashboard');

// Bank Sampah dashboard
Route::get('/bank-sampah/dashboard', [DashboardController::class, 'bankSampah'])
    ->middleware(['auth', 'permission:view_waste_bank'])->name('bank-sampah.dashboard');

// Bank Sampah Monitoring Audit Dashboard
Route::get('/bank-sampah/monitoring', [\App\Http\Controllers\BankSampahMonitoringController::class, 'index'])
    ->middleware(['auth', 'role:admin_bank_sampah|admin_rw'])
    ->name('bank-sampah.monitoring');

// Warga dashboard - redirect to Bank Sampah dashboard
Route::redirect('/warga/dashboard', '/bank-sampah/dashboard')->name('warga.dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Warga - Bank Sampah Savings
Route::middleware(['auth', 'permission:view_own_savings'])->group(function () {
    Route::get('/warga/savings', [WargaSavingsController::class, 'index'])->name('warga.savings');
    Route::get('/warga/savings/history', [WargaSavingsController::class, 'history'])->name('warga.savings.history');
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