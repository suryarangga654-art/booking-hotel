<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TipeKamarController;
use App\Http\Controllers\Api\KamarController;
use App\Http\Controllers\Api\PemesananController;
use App\Http\Controllers\Api\LayananTambahanController;
use App\Http\Controllers\Api\UlasanController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\PromoController;
use App\Http\Controllers\Api\PembayaranController;

/*
|--------------------------------------------------------------------------
| Public Routes (Tanpa Auth / Bebas Akses)
|--------------------------------------------------------------------------
*/

// Authentication Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Catalog Routes (Dapat dilihat oleh Siapa Saja)
Route::get('/tipe-kamar', [TipeKamarController::class, 'index']);
Route::get('/tipe-kamar/{id}', [TipeKamarController::class, 'show']);
Route::get('/layanan-tambahan', [LayananTambahanController::class, 'index']);
Route::get('/ulasan', [UlasanController::class, 'index']);
Route::post('/promo/validate', [PromoController::class, 'validateCode']);
Route::get('/tipe-kamar/{id}/ulasan', [UlasanController::class, 'getByTipeKamar']);


/*
|--------------------------------------------------------------------------
| Protected Routes (Wajib Login / Bearer Token Sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // User Profile & Logout (Akses Semua Role yang Login)
    Route::get('/me', [AuthController::class, 'me']);
    Route::patch('/me', [AuthController::class, 'updateProfile']);
    Route::get('/user', [AuthController::class, 'me']);
    Route::patch('/user/profile', [AuthController::class, 'updateProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // 1. ROUTE TAMU (Hanya bisa diakses oleh role 'tamu')
    Route::middleware('role:tamu')->group(function () {
        Route::get('/my-bookings', [PemesananController::class, 'myBookings']);
        Route::get('/tamu/pemesanan', [PemesananController::class, 'myBookings']);
        Route::post('/pemesanan', [PemesananController::class, 'store']);
        Route::post('/ulasan', [UlasanController::class, 'store']);
        Route::post('/tamu/ulasan', [UlasanController::class, 'store']);
    });

    // 2. ROUTE RESEPSIONIS & ADMIN (Kelola Transaksi, Check-In/Out)
    Route::middleware('role:admin,resepsionis')->group(function () {
        Route::get('/admin/pembayaran', [PembayaranController::class, 'index']);
        Route::post('/admin/pembayaran/{id}/konfirmasi', [PembayaranController::class, 'confirm']);
        Route::get('/admin/pemesanan', [PemesananController::class, 'index']);
        Route::patch('/admin/pemesanan/{id}/status', [PemesananController::class, 'updateStatus']);
        Route::post('/admin/pemesanan/{id}/check-in', [PemesananController::class, 'checkIn']);
        Route::post('/admin/pemesanan/{id}/check-out', [PemesananController::class, 'checkOut']);
        Route::patch('/admin/kamar/{id}/status', [KamarController::class, 'updateStatus']);
        Route::get('/admin/dashboard', [DashboardController::class, 'stats']);
    });

    // 3. ROUTE KHUSUS ADMIN (Kelola Master Data: Kamar, Tipe Kamar, Layanan)
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/ulasan', [UlasanController::class, 'index']);
        Route::delete('/admin/ulasan/{id}', [UlasanController::class, 'destroy']);
        Route::get('/admin/promo', [PromoController::class, 'index']);
        Route::post('/admin/promo', [PromoController::class, 'store']);
        Route::put('/admin/promo/{id}', [PromoController::class, 'update']);
        Route::delete('/admin/promo/{id}', [PromoController::class, 'destroy']);
        Route::get('/admin/users', [\App\Http\Controllers\Api\AdminUserController::class, 'index']);
        Route::post('/admin/users', [\App\Http\Controllers\Api\AdminUserController::class, 'store']);
        Route::put('/admin/users/{id}', [\App\Http\Controllers\Api\AdminUserController::class, 'update']);
        Route::delete('/admin/users/{id}', [\App\Http\Controllers\Api\AdminUserController::class, 'destroy']);

        // Master Kamar Fisik
        Route::get('/admin/kamar', [KamarController::class, 'index']);
        Route::post('/admin/kamar', [KamarController::class, 'store']);
        Route::put('/admin/kamar/{id}', [KamarController::class, 'update']);
        Route::delete('/admin/kamar/{id}', [KamarController::class, 'destroy']);

        // Master Tipe Kamar
        Route::get('/admin/tipe-kamar', [TipeKamarController::class, 'index']);
        Route::post('/admin/tipe-kamar', [TipeKamarController::class, 'store']);
        Route::put('/admin/tipe-kamar/{id}', [TipeKamarController::class, 'update']);
        Route::post('/admin/tipe-kamar/{id}/foto', [TipeKamarController::class, 'storePhotos']);
        Route::delete('/admin/tipe-kamar/{id}/foto/{fotoId}', [TipeKamarController::class, 'destroyPhoto']);
        Route::delete('/admin/tipe-kamar/{id}', [TipeKamarController::class, 'destroy']);

        // Master Layanan Tambahan
        Route::post('/admin/layanan-tambahan', [LayananTambahanController::class, 'store']);
        Route::put('/admin/layanan-tambahan/{id}', [LayananTambahanController::class, 'update']);
        Route::delete('/admin/layanan-tambahan/{id}', [LayananTambahanController::class, 'destroy']);
    });

    // 4. ROUTE PETUGAS KEBERSIHAN & ADMIN (Pantau Kebersihan & Status Kamar)
    Route::middleware('role:admin,petugas_kebersihan')->group(function () {
        Route::get('/kebersihan/kamar', [KamarController::class, 'index']);
    });

});