<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengajuan_setoran_jenis', function (Blueprint $table) {
            // Perkiraan berat (kg) yang diisi nasabah per kategori. Berat sebenarnya dicatat admin di 'transaksi'.
            $table->decimal('perkiraan_berat_kg', 8, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('pengajuan_setoran_jenis', function (Blueprint $table) {
            $table->dropColumn('perkiraan_berat_kg');
        });
    }
};