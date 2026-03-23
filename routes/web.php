<?php

use App\Http\Controllers\Admin\BrandController as AdminBrandController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\IntegrationController;
use App\Http\Controllers\Admin\RewardController as AdminRewardController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Kasir\KasirController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('admin.login');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [DashboardController::class, 'loginForm'])->name('login');
    Route::post('login', [DashboardController::class, 'login']);
    Route::get('logout', [DashboardController::class, 'logout'])->name('logout');

    Route::middleware(\App\Http\Middleware\AdminAuth::class)->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('brands', [AdminBrandController::class, 'index'])->name('brands.index');
        Route::get('brands/create', [AdminBrandController::class, 'create'])->name('brands.create');
        Route::post('brands', [AdminBrandController::class, 'store'])->name('brands.store');
        Route::get('brands/{brand}/edit', [AdminBrandController::class, 'edit'])->name('brands.edit');
        Route::put('brands/{brand}', [AdminBrandController::class, 'update'])->name('brands.update');
        Route::post('brands/{brand}/toggle', [AdminBrandController::class, 'toggle'])->name('brands.toggle');
        Route::delete('brands/{brand}', [AdminBrandController::class, 'destroy'])->name('brands.destroy');

        Route::get('rewards', [AdminRewardController::class, 'index'])->name('rewards.index');
        Route::get('rewards/create', [AdminRewardController::class, 'create'])->name('rewards.create');
        Route::post('rewards', [AdminRewardController::class, 'store'])->name('rewards.store');
        Route::post('rewards/{reward}/toggle', [AdminRewardController::class, 'toggle'])->name('rewards.toggle');

        Route::get('transactions', [TransactionController::class, 'index'])->name('transactions.index');
        Route::get('members', [TransactionController::class, 'members'])->name('members.index');

        Route::get('integrations', [IntegrationController::class, 'index'])->name('integrations.index');
        Route::post('integrations', [IntegrationController::class, 'store'])->name('integrations.store');
        Route::post('integrations/{integration}/toggle', [IntegrationController::class, 'toggle'])->name('integrations.toggle');
    });
});

Route::prefix('kasir')->group(function () {
    Route::get('/', [KasirController::class, 'index']);
    Route::post('cari', [KasirController::class, 'cariMember']);
    Route::post('transaksi', [KasirController::class, 'prosesTransaksi']);
    Route::post('verifikasi', [KasirController::class, 'verifikasiRedemption']);
    Route::post('claim', [KasirController::class, 'claimRedemption']);
});
