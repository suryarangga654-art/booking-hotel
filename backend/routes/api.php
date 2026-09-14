<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// AUTH
use App\Http\Controllers\Api\AuthController;

// CONTROLLERS
use App\Http\Controllers\Api\TipeKamarController;
use App\Http\Controllers\Api\HargaMusimanController;
use App\Http\Controllers\Api\KamarController;
use App\Http\Controllers\Api\PemesananController;
use App\Http\Controllers\Api\DetailPemesananController;
use App\Http\Controllers\Api\LayananTambahanController;
use App\Http\Controllers\Api\DetailLayananController;
use App\Http\Controllers\Api\PembayaranController;
use App\Http\Controllers\Api\UlasanController;


/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/

// Register
Route::post('/register', [AuthController::class, 'register']);

// Login
Route::post('/login', [AuthController::class, 'login']);


/*
|--------------------------------------------------------------------------
| ROUTE YANG SUDAH LOGIN
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    // User yang sedang login
    Route::get('/user', [AuthController::class, 'user']);

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);


    /*
    |--------------------------------------------------------------------------
    | ADMIN
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin')->group(function () {

        // TIPE KAMAR
        Route::get('/tipe-kamar', [TipeKamarController::class, 'index']);
        Route::get('/tipe-kamar/{id}', [TipeKamarController::class, 'show']);
        Route::post('/tipe-kamar', [TipeKamarController::class, 'store']);
        Route::put('/tipe-kamar/{id}', [TipeKamarController::class, 'update']);
        Route::delete('/tipe-kamar/{id}', [TipeKamarController::class, 'destroy']);


        // HARGA MUSIMAN
        Route::get('/harga-musiman', [HargaMusimanController::class, 'index']);
        Route::get('/harga-musiman/{id}', [HargaMusimanController::class, 'show']);
        Route::post('/harga-musiman', [HargaMusimanController::class, 'store']);
        Route::put('/harga-musiman/{id}', [HargaMusimanController::class, 'update']);
        Route::delete('/harga-musiman/{id}', [HargaMusimanController::class, 'destroy']);


        // KAMAR
        Route::get('/kamar', [KamarController::class, 'index']);
        Route::get('/kamar/{id}', [KamarController::class, 'show']);
        Route::post('/kamar', [KamarController::class, 'store']);
        Route::put('/kamar/{id}', [KamarController::class, 'update']);
        Route::delete('/kamar/{id}', [KamarController::class, 'destroy']);


        // PEMESANAN
        Route::get('/pemesanan', [PemesananController::class, 'index']);
        Route::get('/pemesanan/{id}', [PemesananController::class, 'show']);
        Route::post('/pemesanan', [PemesananController::class, 'store']);
        Route::put('/pemesanan/{id}', [PemesananController::class, 'update']);
        Route::delete('/pemesanan/{id}', [PemesananController::class, 'destroy']);


        // DETAIL PEMESANAN
        Route::get('/detail-pemesanan', [DetailPemesananController::class, 'index']);
        Route::get('/detail-pemesanan/{id}', [DetailPemesananController::class, 'show']);
        Route::post('/detail-pemesanan', [DetailPemesananController::class, 'store']);
        Route::put('/detail-pemesanan/{id}', [DetailPemesananController::class, 'update']);
        Route::delete('/detail-pemesanan/{id}', [DetailPemesananController::class, 'destroy']);


        // LAYANAN TAMBAHAN
        Route::get('/layanan-tambahan', [LayananTambahanController::class, 'index']);
        Route::get('/layanan-tambahan/{id}', [LayananTambahanController::class, 'show']);
        Route::post('/layanan-tambahan', [LayananTambahanController::class, 'store']);
        Route::put('/layanan-tambahan/{id}', [LayananTambahanController::class, 'update']);
        Route::delete('/layanan-tambahan/{id}', [LayananTambahanController::class, 'destroy']);


        // DETAIL LAYANAN
        Route::get('/detail-layanan', [DetailLayananController::class, 'index']);
        Route::get('/detail-layanan/{id}', [DetailLayananController::class, 'show']);
        Route::post('/detail-layanan', [DetailLayananController::class, 'store']);
        Route::put('/detail-layanan/{id}', [DetailLayananController::class, 'update']);
        Route::delete('/detail-layanan/{id}', [DetailLayananController::class, 'destroy']);


        // PEMBAYARAN
        Route::get('/pembayaran', [PembayaranController::class, 'index']);
        Route::get('/pembayaran/{id}', [PembayaranController::class, 'show']);
        Route::post('/pembayaran', [PembayaranController::class, 'store']);
        Route::put('/pembayaran/{id}', [PembayaranController::class, 'update']);
        Route::delete('/pembayaran/{id}', [PembayaranController::class, 'destroy']);


        // ULASAN
        Route::get('/ulasan', [UlasanController::class, 'index']);
        Route::get('/ulasan/{id}', [UlasanController::class, 'show']);
        Route::delete('/ulasan/{id}', [UlasanController::class, 'destroy']);
    });


    /*
    |--------------------------------------------------------------------------
    | RESEPSIONIS
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:resepsionis')->group(function () {

        // Lihat tipe kamar
        Route::get('/tipe-kamar', [TipeKamarController::class, 'index']);
        Route::get('/tipe-kamar/{id}', [TipeKamarController::class, 'show']);

        // Lihat harga
        Route::get('/harga-musiman', [HargaMusimanController::class, 'index']);
        Route::get('/harga-musiman/{id}', [HargaMusimanController::class, 'show']);

        // KAMAR
        Route::get('/kamar', [KamarController::class, 'index']);
        Route::get('/kamar/{id}', [KamarController::class, 'show']);
        Route::put('/kamar/{id}', [KamarController::class, 'update']);

        // PEMESANAN
        Route::get('/pemesanan', [PemesananController::class, 'index']);
        Route::get('/pemesanan/{id}', [PemesananController::class, 'show']);
        Route::post('/pemesanan', [PemesananController::class, 'store']);
        Route::put('/pemesanan/{id}', [PemesananController::class, 'update']);

        // DETAIL PEMESANAN
        Route::get('/detail-pemesanan', [DetailPemesananController::class, 'index']);
        Route::get('/detail-pemesanan/{id}', [DetailPemesananController::class, 'show']);
        Route::post('/detail-pemesanan', [DetailPemesananController::class, 'store']);
        Route::put('/detail-pemesanan/{id}', [DetailPemesananController::class, 'update']);

        // LAYANAN
        Route::get('/layanan-tambahan', [LayananTambahanController::class, 'index']);
        Route::get('/layanan-tambahan/{id}', [LayananTambahanController::class, 'show']);

        // PEMBAYARAN
        Route::get('/pembayaran', [PembayaranController::class, 'index']);
        Route::get('/pembayaran/{id}', [PembayaranController::class, 'show']);
        Route::post('/pembayaran', [PembayaranController::class, 'store']);
        Route::put('/pembayaran/{id}', [PembayaranController::class, 'update']);

        // ULASAN
        Route::get('/ulasan', [UlasanController::class, 'index']);
        Route::get('/ulasan/{id}', [UlasanController::class, 'show']);
    });


    /*
    |--------------------------------------------------------------------------
    | TAMU
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:tamu')->group(function () {

        // Lihat tipe kamar
        Route::get('/tipe-kamar', [TipeKamarController::class, 'index']);
        Route::get('/tipe-kamar/{id}', [TipeKamarController::class, 'show']);

        // Lihat kamar
        Route::get('/kamar', [KamarController::class, 'index']);
        Route::get('/kamar/{id}', [KamarController::class, 'show']);

        // PEMESANAN
        Route::get('/pemesanan', [PemesananController::class, 'index']);
        Route::get('/pemesanan/{id}', [PemesananController::class, 'show']);
        Route::post('/pemesanan', [PemesananController::class, 'store']);

        // DETAIL PEMESANAN
        Route::get('/detail-pemesanan', [DetailPemesananController::class, 'index']);
        Route::get('/detail-pemesanan/{id}', [DetailPemesananController::class, 'show']);

        // LAYANAN
        Route::get('/layanan-tambahan', [LayananTambahanController::class, 'index']);
        Route::get('/layanan-tambahan/{id}', [LayananTambahanController::class, 'show']);

        // PEMBAYARAN
        Route::get('/pembayaran', [PembayaranController::class, 'index']);
        Route::get('/pembayaran/{id}', [PembayaranController::class, 'show']);
        Route::post('/pembayaran', [PembayaranController::class, 'store']);

        // ULASAN
        Route::get('/ulasan', [UlasanController::class, 'index']);
        Route::get('/ulasan/{id}', [UlasanController::class, 'show']);
        Route::post('/ulasan', [UlasanController::class, 'store']);
        Route::put('/ulasan/{id}', [UlasanController::class, 'update']);
        Route::delete('/ulasan/{id}', [UlasanController::class, 'destroy']);
    });
});