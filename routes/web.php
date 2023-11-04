<?php

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\PaketController;
use App\Http\Controllers\Api\PelangganController;
use App\Http\Controllers\Api\TransaksiController;
use App\Http\Controllers\Api\RegionController;
use App\Http\Controllers\Api\ServerController;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'index'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::get('/', function () {
    return redirect(url('home'));
});


Route::middleware('auth')->group(function () {
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::get('/transaksi', function () {
        return view('transaksi');
    });
    Route::get('/setting', function () {
        return view('setting');
    });

    Route::post('/logout', [LoginController::class, 'logout']);
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



    Route::get('/api/region', [RegionController::class, 'index'])->middleware('auth.api.admin');
    Route::post('/api/region', [RegionController::class, 'store'])->middleware('auth.api.admin');
    Route::delete('/api/region/{id}', [RegionController::class, 'destroy'])->middleware('auth.api.admin');

    Route::get('/api/server', [ServerController::class, 'index'])->middleware('auth.api.admin');
    Route::get('/api/server/{id}/edit', [ServerController::class, 'edit'])->middleware('auth.api.admin');
    Route::post('/api/server', [ServerController::class, 'store'])->middleware('auth.api.admin');
    Route::delete('/api/server/{id}', [ServerController::class, 'destroy'])->middleware('auth.api.admin');


    Route::get('/api/paket', [PaketController::class, 'index'])->middleware('auth.api.admin');
    Route::post('/api/paket', [PaketController::class, 'store'])->middleware('auth.api.admin');
    Route::delete('/api/paket/{id}', [PaketController::class, 'destroy'])->middleware('auth.api.admin');
});
