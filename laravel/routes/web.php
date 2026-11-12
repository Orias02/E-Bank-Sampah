<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\JenisSampahController as AdminJenisSampahController;
use App\Http\Controllers\Admin\NasabahController as AdminNasabahController;
use App\Http\Controllers\Admin\PengajuanController as AdminPengajuanController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KatalogController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\RiwayatController;
use App\Http\Controllers\SetorController;
use App\Http\Controllers\TarikController;
use App\Http\Middleware\EnsureAdmin;
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

// Area admin: harus login dengan role admin. Data dibatasi ke bank sampah milik admin.
Route::prefix('admin')->name('admin.')->middleware(['auth', EnsureAdmin::class])->group(function () {
    Route::get('/', AdminDashboardController::class)->name('dashboard');

    // Verifikasi dan data nasabah
    Route::get('/nasabah', [AdminNasabahController::class, 'index'])->name('nasabah.index');
    Route::get('/nasabah/{id}', [AdminNasabahController::class, 'show'])->whereNumber('id')->name('nasabah.show');
    Route::get('/nasabah/{id}/ktp', [AdminNasabahController::class, 'ktp'])->whereNumber('id')->name('nasabah.ktp');
    Route::post('/nasabah/{id}/aktifkan', [AdminNasabahController::class, 'aktifkan'])->whereNumber('id')->name('nasabah.aktifkan');
    Route::post('/nasabah/{id}/tolak', [AdminNasabahController::class, 'tolak'])->whereNumber('id')->name('nasabah.tolak');
    Route::post('/nasabah/{id}/nonaktifkan', [AdminNasabahController::class, 'nonaktifkan'])->whereNumber('id')->name('nasabah.nonaktifkan');
    Route::get('/nasabah/{id}/setoran', [AdminNasabahController::class, 'setoran'])->whereNumber('id')->name('nasabah.setoran');
    Route::post('/nasabah/{id}/setoran', [AdminNasabahController::class, 'simpanSetoran'])->whereNumber('id')->middleware('throttle:30,1')->name('nasabah.setoran.simpan');

    // Pengajuan setoran
    Route::get('/pengajuan', [AdminPengajuanController::class, 'index'])->name('pengajuan.index');
    Route::get('/pengajuan/{id}', [AdminPengajuanController::class, 'show'])->whereNumber('id')->name('pengajuan.show');
    Route::post('/pengajuan/{id}/terima', [AdminPengajuanController::class, 'terima'])->whereNumber('id')->name('pengajuan.terima');
    Route::post('/pengajuan/{id}/tolak', [AdminPengajuanController::class, 'tolak'])->whereNumber('id')->name('pengajuan.tolak');
    Route::post('/pengajuan/{id}/timbang', [AdminPengajuanController::class, 'timbang'])->whereNumber('id')->middleware('throttle:30,1')->name('pengajuan.timbang');

    // Harga dan kategori sampah
    Route::get('/harga-sampah', [AdminJenisSampahController::class, 'index'])->name('harga.index');
    Route::get('/harga-sampah/baru', [AdminJenisSampahController::class, 'create'])->name('harga.create');
    Route::post('/harga-sampah', [AdminJenisSampahController::class, 'store'])->name('harga.store');
    Route::get('/harga-sampah/{id}/ubah', [AdminJenisSampahController::class, 'edit'])->whereNumber('id')->name('harga.edit');
    Route::put('/harga-sampah/{id}', [AdminJenisSampahController::class, 'update'])->whereNumber('id')->name('harga.update');
    Route::post('/harga-sampah/{id}/status', [AdminJenisSampahController::class, 'toggle'])->whereNumber('id')->name('harga.toggle');
});