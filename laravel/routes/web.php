<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RegistrationController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'landing')->name('home');

// Login dan logout
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Pendaftaran nasabah: Akun → Data Diri → Alamat → Berhasil
Route::controller(RegistrationController::class)->prefix('pendaftaran')->group(function () {
    Route::get('/', 'showAkun')->name('nasabah.register');
    Route::post('/', 'storeAkun');

    Route::get('/data-diri', 'showDataDiri')->name('nasabah.register.data-diri');
    Route::post('/data-diri', 'storeDataDiri');

    Route::get('/alamat', 'showAlamat')->name('nasabah.register.alamat');
    Route::post('/alamat', 'storeAlamat');

    Route::get('/berhasil', 'showBerhasil')->name('nasabah.register.berhasil');
});

// Area nasabah (harus login)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Halaman di bawah ini belum dibuat: sementara menampilkan "segera hadir".
    // Ganti dengan controller/view asli saat desainnya sudah ada.
    Route::view('/setor-sampah', 'nasabah.segera', ['judul' => 'Setor Sampah Baru'])->name('nasabah.setor');
    Route::view('/tarik-saldo', 'nasabah.segera', ['judul' => 'Tarik Saldo'])->name('nasabah.tarik');
    Route::view('/harga-sampah', 'nasabah.segera', ['judul' => 'Harga Sampah'])->name('nasabah.harga');
    Route::view('/riwayat', 'nasabah.segera', ['judul' => 'Riwayat Transaksi'])->name('nasabah.riwayat');
});