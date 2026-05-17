<?php

use App\Http\Controllers\CommunityContributionController;
use App\Http\Controllers\CommunityExpenseController;
use App\Http\Controllers\CommunityCashReportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FundCategoryController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WasteCategoryController;
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
    ->middleware(['auth', 'role:bendahara|admin_rt'])->name('bendahara.dashboard');

// Bank Sampah dashboard
Route::get('/bank-sampah/dashboard', [DashboardController::class, 'bankSampah'])
    ->middleware(['auth', 'role:admin_bank_sampah|admin_rt'])->name('bank-sampah.dashboard');

// Warga dashboard
Route::get('/warga/dashboard', [DashboardController::class, 'warga'])
    ->middleware(['auth', 'role:warga|admin_rt'])->name('warga.dashboard');

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
    Route::resource('contributions', CommunityContributionController::class)->only(['index', 'create', 'store']);
});

// Community Cash - Expenses
Route::middleware(['auth', 'permission:manage_expenses'])->prefix('community-cash')->name('community-cash.')->group(function () {
    Route::resource('expenses', CommunityExpenseController::class)->only(['index', 'create', 'store']);
});

// Community Cash - Report (Admin/Bendahara)
Route::get('/community-cash/report', [CommunityCashReportController::class, 'index'])
    ->middleware(['auth', 'permission:view_cash_reports'])->name('community-cash.report');

Route::get('/community-cash/report/export', [CommunityCashReportController::class, 'export'])
    ->middleware(['auth', 'permission:view_cash_reports'])->name('community-cash.report.export');

// Warga - Public Cash Report
Route::get('/warga/cash-report', [CommunityCashReportController::class, 'publicReport'])
    ->middleware(['auth', 'permission:view_public_cash_report'])->name('warga.cash-report');

// Members / Data Warga
Route::middleware(['auth', 'permission:manage_members'])->group(function () {
    Route::resource('members', MemberController::class)->except(['show']);
});

// Bank Sampah - Waste Categories
Route::middleware(['auth', 'permission:manage_waste_prices'])->prefix('bank-sampah')->name('bank-sampah.')->group(function () {
    Route::resource('waste-categories', WasteCategoryController::class)->except(['show']);
});

require __DIR__.'/auth.php';
