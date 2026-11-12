<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nasabah_id')->constrained('nasabah')->cascadeOnDelete();
            $table->foreignId('jenis_sampah_id')->nullable()->constrained('jenis_sampah')->nullOnDelete();

            // 'setoran' (nasabah menyetor sampah) atau 'penarikan' (nasabah menarik saldo)
            $table->string('tipe', 15)->index();
            $table->decimal('berat_kg', 8, 2)->nullable();    // hanya untuk setoran
            $table->unsignedBigInteger('nilai');              // rupiah
            $table->string('status', 15)->default('proses');  // proses | selesai | dibatalkan
            $table->date('tanggal');

            $table->timestamps();

            $table->index(['nasabah_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi');
    }
};