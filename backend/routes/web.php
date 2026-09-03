<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KamarController;
use App\Http\Controllers\PemesananController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::resource('kamar', KamarController::class);

Route::resource('pemesanan', PemesananController::class);