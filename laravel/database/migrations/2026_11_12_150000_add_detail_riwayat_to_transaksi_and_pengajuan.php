<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            // Setoran: 'antar' atau 'jemput'. Penarikan: nama tujuan pencairan (mis. GoPay, Bank BCA).
            $table->string('metode', 30)->nullable()->after('tipe');
            // Alasan jika transaksi gagal/dibatalkan (mis. "Nomor Rekening Tidak Valid").
            $table->string('keterangan', 255)->nullable()->after('status');
        });

        Schema::table('pengajuan_setoran', function (Blueprint $table) {
            // Alasan jika pengajuan ditolak admin.
            $table->string('alasan', 255)->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropColumn(['metode', 'keterangan']);
        });

        Schema::table('pengajuan_setoran', function (Blueprint $table) {
            $table->dropColumn('alasan');
        });
    }
};