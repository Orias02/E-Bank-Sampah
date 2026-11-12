<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tujuan pencairan untuk transaksi bertipe penarikan (nama tujuannya ada di kolom "metode").
        Schema::table('transaksi', function (Blueprint $table) {
            $table->string('nomor_tujuan', 30)->nullable()->after('metode');
            $table->string('nama_pemilik', 100)->nullable()->after('nomor_tujuan');
        });
    }

    public function down(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropColumn(['nomor_tujuan', 'nama_pemilik']);
        });
    }
};