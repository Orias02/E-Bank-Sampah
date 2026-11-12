<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pengajuan setoran: nasabah memberi tahu admin akan menyetor sampah apa dan kapan.
        // Berat dan nilai sebenarnya dicatat admin nanti di tabel 'transaksi'.
        Schema::create('pengajuan_setoran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nasabah_id')->constrained('nasabah')->cascadeOnDelete();
            $table->string('metode', 10);                       // 'antar' ke bank sampah atau 'jemput'
            $table->date('tanggal_rencana');
            $table->text('catatan')->nullable();
            $table->string('status', 15)->default('menunggu')->index(); // menunggu | diterima | selesai | ditolak | dibatalkan
            $table->timestamps();

            $table->index(['nasabah_id', 'status']);
        });

        // Kategori sampah yang akan dibawa pada satu pengajuan.
        Schema::create('pengajuan_setoran_jenis', function (Blueprint $table) {
            $table->foreignId('pengajuan_setoran_id')->constrained('pengajuan_setoran')->cascadeOnDelete();
            $table->foreignId('jenis_sampah_id')->constrained('jenis_sampah')->cascadeOnDelete();
            $table->primary(['pengajuan_setoran_id', 'jenis_sampah_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_setoran_jenis');
        Schema::dropIfExists('pengajuan_setoran');
    }
};