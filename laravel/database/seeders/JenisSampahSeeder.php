<?php

namespace Database\Seeders;

use App\Models\JenisSampah;
use Illuminate\Database\Seeder;

class JenisSampahSeeder extends Seeder
{
    /**
     * HARGA CONTOH. Ganti dengan harga bank sampah yang sebenarnya.
     * Jalankan: php artisan db:seed --class=JenisSampahSeeder
     */
    public function run(): void
    {
        $items = [
            ['nama' => 'Kardus Bekas', 'harga_per_kg' => 2500, 'ikon' => 'package'],
            ['nama' => 'Botol Plastik PET', 'harga_per_kg' => 3000, 'ikon' => 'cup'],
            ['nama' => 'Kaleng Aluminium', 'harga_per_kg' => 5000, 'ikon' => 'can'],
        ];

        foreach ($items as $item) {
            JenisSampah::updateOrCreate(['nama' => $item['nama']], $item);
        }
    }
}