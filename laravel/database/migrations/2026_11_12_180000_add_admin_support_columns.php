<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Admin terikat ke satu bank sampah. Kosong (null) = super admin yang melihat semua bank sampah.
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('bank_sampah_id')->nullable()->after('role')->constrained('bank_sampah')->nullOnDelete();
        });

        // Status nasabah kini: menunggu | aktif | ditolak | nonaktif. Alasan diisi admin saat menolak/menonaktifkan.
        Schema::table('nasabah', function (Blueprint $table) {
            $table->string('alasan_status', 255)->nullable()->after('status');
            $table->timestamp('diverifikasi_pada')->nullable()->after('alasan_status');
        });

        // Jenis sampah yang dinonaktifkan tidak tampil di katalog dan form setor, riwayat lama tetap utuh.
        Schema::table('jenis_sampah', function (Blueprint $table) {
            $table->boolean('aktif')->default(true)->after('ikon');
        });

        // Penelusuran: setoran ini berasal dari pengajuan mana dan dicatat oleh admin siapa.
        Schema::table('transaksi', function (Blueprint $table) {
            $table->foreignId('pengajuan_setoran_id')->nullable()->after('nasabah_id')->constrained('pengajuan_setoran')->nullOnDelete();
            $table->foreignId('dicatat_oleh')->nullable()->after('keterangan')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropConstrainedForeignId('dicatat_oleh');
            $table->dropConstrainedForeignId('pengajuan_setoran_id');
        });

        Schema::table('jenis_sampah', function (Blueprint $table) {
            $table->dropColumn('aktif');
        });

        Schema::table('nasabah', function (Blueprint $table) {
            $table->dropColumn(['alasan_status', 'diverifikasi_pada']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('bank_sampah_id');
        });
    }
};