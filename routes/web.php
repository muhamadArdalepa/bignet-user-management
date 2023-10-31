<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TransaksiController;
use App\Http\Controllers\Api\PelangganController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Auth\LoginController;

Route::middleware('guest')->group(function () {
    Route::get('/login',[LoginController::class,'index'])->name('login');
    Route::post('/login',[LoginController::class,'login']);
});

Route::get('/', function () {
    return redirect(url('home'));
});


Route::middleware('auth')->group(function () {
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::get('/transaksi', function () {
        return view('transaksi');
    });

    Route::post('/logout',[LoginController::class,'logout']);
});



// APIS
Route::middleware('auth.api')->group(function () {
    Route::patch('/api/pelanggan/isolir-batch', [PelangganController::class, 'isolirBatch'])->middleware('auth.api.admin');
    Route::patch('/api/pelanggan/isolir/{pelanggan}', [PelangganController::class, 'isolir'])->middleware('auth.api.admin');
    Route::resource('/api/pelanggan', PelangganController::class);

    Route::get('/api/invoice/{id}', [InvoiceController::class, 'show']);
    Route::get('/api/invoice', [InvoiceController::class, 'index']);

    Route::get('/api/transaksi', [TransaksiController::class, 'index']);
    Route::get('/api/transaksi/export', [TransaksiController::class, 'export']);
    Route::get('/api/transaksi/{id}/edit', [TransaksiController::class, 'edit'])->middleware('auth.api.admin');
    Route::patch('/api/transaksi/{id}', [TransaksiController::class, 'update'])->middleware('auth.api.admin');
    Route::post('/api/transaksi/{id}', [TransaksiController::class, 'store']);
    Route::delete('/api/transaksi/{id}', [TransaksiController::class, 'destroy'])->middleware('auth.api.admin');
});
