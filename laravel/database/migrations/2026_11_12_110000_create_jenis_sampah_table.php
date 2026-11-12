<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jenis_sampah', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->unique();
            $table->unsignedInteger('harga_per_kg');          // rupiah per kilogram
            $table->string('ikon', 20)->default('recycle');   // nama ikon di components/icon.blade.php
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jenis_sampah');
    }
};