<?php

use App\Http\Controllers\CommunityContributionController;
use App\Http\Controllers\CommunityExpenseController;
use App\Http\Controllers\FundCategoryController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Main dashboard route - redirects based on role
Route::get('/dashboard', function () {
    $user = auth()->user();

    if ($user->hasRole('admin_rt')) {
        return view('dashboard.admin-rt');
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

    // Fallback for users without role
    return view('dashboard.admin-rt');
})->middleware(['auth', 'verified'])->name('dashboard');

// Bendahara dashboard
Route::get('/bendahara/dashboard', function () {
    return view('dashboard.bendahara');
})->middleware(['auth', 'role:bendahara|admin_rt'])->name('bendahara.dashboard');

// Bank Sampah dashboard
Route::get('/bank-sampah/dashboard', function () {
    return view('dashboard.admin-bank-sampah');
})->middleware(['auth', 'role:admin_bank_sampah|admin_rt'])->name('bank-sampah.dashboard');

// Warga dashboard
Route::get('/warga/dashboard', function () {
    return view('dashboard.warga');
})->middleware(['auth', 'role:warga|admin_rt'])->name('warga.dashboard');

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

require __DIR__.'/auth.php';
