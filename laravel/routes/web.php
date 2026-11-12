<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KatalogController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\RiwayatController;
use App\Http\Controllers\SetorController;
use App\Http\Controllers\TarikController;
use App\Http\Middleware\EnsureNasabahAktif;
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

// Area nasabah: harus login dan berstatus aktif
Route::middleware(['auth', EnsureNasabahAktif::class])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Profil
    Route::get('/profil', [ProfilController::class, 'show'])->name('nasabah.profil');
    Route::post('/profil', [ProfilController::class, 'update'])->name('nasabah.profil.update');
    Route::post('/profil/foto', [ProfilController::class, 'updateFoto'])->name('nasabah.profil.foto');
    Route::get('/profil/foto', [ProfilController::class, 'foto'])->name('nasabah.foto');

    // Setor sampah
    Route::get('/setor-sampah', [SetorController::class, 'show'])->name('nasabah.setor');
    Route::post('/setor-sampah', [SetorController::class, 'store'])->name('nasabah.setor.store');

    // Tarik saldo
    Route::get('/tarik-saldo', [TarikController::class, 'show'])->name('nasabah.tarik');
    Route::post('/tarik-saldo', [TarikController::class, 'store'])->middleware('throttle:10,1')->name('nasabah.tarik.store');
    Route::get('/tarik-saldo/berhasil', [TarikController::class, 'berhasil'])->name('nasabah.tarik.berhasil');

    // Katalog harga sampah
    Route::get('/harga-sampah', KatalogController::class)->name('nasabah.harga');

    // Riwayat transaksi
    Route::get('/riwayat', [RiwayatController::class, 'show'])->name('nasabah.riwayat');
    Route::get('/riwayat/laporan', [RiwayatController::class, 'laporan'])->name('nasabah.riwayat.laporan');

});