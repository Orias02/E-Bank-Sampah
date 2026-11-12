<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jenis_sampah', function (Blueprint $table) {
            $table->string('kategori', 30)->default('Lainnya')->after('nama');   // Plastik, Kertas, Logam, Kaca, ...
            $table->text('deskripsi')->nullable()->after('kategori');            // cara menyiapkan sampah
            $table->unsignedInteger('harga_sebelumnya')->nullable()->after('harga_per_kg');
            $table->date('harga_diperbarui_pada')->nullable()->after('harga_sebelumnya');
        });
    }

    public function down(): void
    {
        Schema::table('jenis_sampah', function (Blueprint $table) {
            $table->dropColumn(['kategori', 'deskripsi', 'harga_sebelumnya', 'harga_diperbarui_pada']);
        });
    }
};