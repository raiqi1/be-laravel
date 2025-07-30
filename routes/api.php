<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MasterKaryawanController;
use App\Http\Controllers\MasterItemController;
use App\Http\Controllers\TransaksiKoperasiController;

Route::prefix('transaksi')->group(function () {
    Route::get('/', [TransaksiKoperasiController::class, 'index'])->name('transaksi.index');
    Route::get('/create', [TransaksiKoperasiController::class, 'create'])->name('transaksi.create');
    Route::post('/', [TransaksiKoperasiController::class, 'store'])->name('transaksi.store');
    Route::get('/{id}', [TransaksiKoperasiController::class, 'show'])->name('transaksi.show');
    Route::put('/{id}', [TransaksiKoperasiController::class, 'update'])->name('transaksi.update');
    Route::delete('/{id}', [TransaksiKoperasiController::class, 'destroy'])->name('transaksi.destroy');
    
    // Additional search routes
    Route::get('/search/date', [TransaksiKoperasiController::class, 'searchByDate'])->name('transaksi.search.date');
    Route::get('/search/npk', [TransaksiKoperasiController::class, 'searchByNpk'])->name('transaksi.search.npk');
    Route::get('/search/payment', [TransaksiKoperasiController::class, 'searchByPaymentStatus'])->name('transaksi.search.payment');
    
    // Summary/Statistics
    Route::get('/report/summary', [TransaksiKoperasiController::class, 'summary'])->name('transaksi.summary');
});

Route::prefix('karyawan')->group(function () {
    Route::get('/', [MasterKaryawanController::class, 'index'])->name('karyawan.index');
    Route::post('/', [MasterKaryawanController::class, 'store'])->name('karyawan.store');
});

Route::prefix('items')->group(function () {
    Route::get('/', [MasterItemController::class, 'index'])->name('items.index');
    Route::post('/', [MasterItemController::class, 'store'])->name('items.store');
});