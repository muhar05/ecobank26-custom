<?php

use App\Http\Controllers\CollectorController;
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
use App\Http\Controllers\WasteBankCashReportController;
use App\Http\Controllers\WasteCategoryController;
use App\Http\Controllers\WastePriceController;
use App\Http\Controllers\WastePriceImportController;
use App\Http\Controllers\WasteCategoryImportController;
use App\Http\Controllers\WithdrawalController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Main dashboard route - redirects based on role
Route::get('/dashboard', function () {
    $user = auth()->user();

    if ($user->hasRole('admin_rt')) {
        return app(DashboardController::class)->adminRt();
    }
    if ($user->hasRole('bendahara')) {
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

// Community Cash - Expenses
Route::middleware(['auth', 'permission:manage_expenses'])->prefix('community-cash')->name('community-cash.')->group(function () {
    Route::resource('expenses', CommunityExpenseController::class)->except(['show']);
});

// Community Cash - Report (Admin/Bendahara)
Route::get('/community-cash/report', [CommunityCashReportController::class, 'index'])
    ->middleware(['auth', 'permission:view_cash_reports'])->name('community-cash.report');

Route::get('/community-cash/report/export', [CommunityCashReportController::class, 'export'])
    ->middleware(['auth', 'permission:view_cash_reports'])->name('community-cash.report.export');

// Warga - Public Cash Report
Route::get('/warga/cash-report', [CommunityCashReportController::class, 'publicReport'])
    ->middleware(['auth', 'permission:view_public_cash_report'])->name('warga.cash-report');

// Warga - Bank Sampah Savings
Route::middleware(['auth', 'permission:view_own_savings'])->group(function () {
    Route::get('/warga/savings', [WargaSavingsController::class, 'index'])->name('warga.savings');
    Route::get('/warga/savings/history', [WargaSavingsController::class, 'history'])->name('warga.savings.history');
});

// Members / Data Warga
Route::middleware(['auth', 'permission:manage_members'])->group(function () {
    Route::get('members/import', [MemberImportController::class, 'showForm'])->name('members.import');
    Route::post('members/import', [MemberImportController::class, 'import'])->name('members.import.store');
    Route::get('members/import/template', [MemberImportController::class, 'template'])->name('members.import.template');
    Route::resource('members', MemberController::class);
});

// Bank Sampah - Waste Categories
Route::middleware(['auth', 'permission:manage_waste_prices'])->prefix('bank-sampah')->name('bank-sampah.')->group(function () {
    Route::get('waste-categories/import', [WasteCategoryImportController::class, 'showForm'])->name('waste-categories.import');
    Route::post('waste-categories/import', [WasteCategoryImportController::class, 'import'])->name('waste-categories.import.store');
    Route::get('waste-categories/import/template', [WasteCategoryImportController::class, 'template'])->name('waste-categories.import.template');
    Route::get('waste-prices/import', [WastePriceImportController::class, 'showForm'])->name('waste-prices.import');
    Route::post('waste-prices/import', [WastePriceImportController::class, 'import'])->name('waste-prices.import.store');
    Route::get('waste-prices/import/template', [WastePriceImportController::class, 'template'])->name('waste-prices.import.template');
    Route::resource('waste-categories', WasteCategoryController::class)->except(['show']);
    Route::resource('collectors', CollectorController::class)->except(['show']);
    Route::resource('waste-prices', WastePriceController::class)->except(['show']);
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

require __DIR__.'/auth.php';
