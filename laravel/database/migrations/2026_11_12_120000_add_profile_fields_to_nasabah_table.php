<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nasabah', function (Blueprint $table) {
            $table->string('foto_path')->nullable()->after('ktp_path');            // foto profil (storage privat)
            $table->string('metode_pencairan', 30)->nullable()->after('status');   // GoPay, OVO, Bank BCA, ...
            $table->string('nomor_rekening', 30)->nullable()->after('metode_pencairan');
        });
    }

    public function down(): void
    {
        Schema::table('nasabah', function (Blueprint $table) {
            $table->dropColumn(['foto_path', 'metode_pencairan', 'nomor_rekening']);
        });
    }
};