<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nasabah', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('bank_sampah_id')->constrained('bank_sampah')->restrictOnDelete();

            // Data diri
            $table->string('nama_lengkap');
            $table->string('nik', 16)->unique();
            $table->date('tanggal_lahir');
            $table->string('jenis_kelamin', 10);
            $table->string('whatsapp', 15);            // tanpa awalan +62
            $table->string('email');                   // email kontak (boleh beda dengan email login)
            $table->string('ktp_path')->nullable();    // lokasi berkas di storage privat

            // Alamat
            $table->string('kota');
            $table->string('kecamatan');
            $table->string('kelurahan');
            $table->string('rt', 3);
            $table->string('rw', 3);
            $table->text('alamat_lengkap');

            // 'menunggu' sampai admin memverifikasi, lalu 'aktif'
            $table->string('status', 20)->default('menunggu')->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nasabah');
    }
};