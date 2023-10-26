<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\Api\PelangganController;

Route::get('/', function () {
    return view('home');
});



Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/transaksi', function () {
    return view('transaksi');
});



// APIS
Route::get('/api/pelanggan/invoice/{id}',[PelangganController::class,'invoice']);
Route::patch('/api/pelanggan/isolir-batch',[PelangganController::class,'isolirBatch']);
Route::patch('/api/pelanggan/isolir/{pelanggan}',[PelangganController::class,'isolir']);
Route::resource('/api/pelanggan',PelangganController::class);


Route::get('/api/transaksi/{id}/edit',[TransaksiController::class,'edit']);
Route::delete('/api/transaksi/{id}',[TransaksiController::class,'destroy']);
Route::post('/api/transaksi/{id}',[TransaksiController::class,'store']);
Route::get('/api/transaksi',[TransaksiController::class,'index']);
