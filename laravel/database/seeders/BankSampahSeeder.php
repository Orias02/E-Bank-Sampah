<?php

namespace Database\Seeders;

use App\Models\BankSampah;
use Illuminate\Database\Seeder;

class BankSampahSeeder extends Seeder
{
    /**
     * DATA CONTOH. Ganti dengan data bank sampah yang sebenarnya.
     * Jalankan: php artisan db:seed --class=BankSampahSeeder
     */
    public function run(): void
    {
        $banks = [
            [
                'nama' => 'Bank Sampah Contoh Dinoyo', // contoh: ganti dengan bank sampah asli
                'alamat' => 'Dinoyo, Lowokwaru, Kota Malang',
                'jadwal' => 'Sabtu • 08.00–12.00 WIB',
                'kelurahan' => 'Dinoyo',
            ],
            [
                'nama' => 'Bank Sampah FILKOM',
                'alamat' => 'Jl Veteran SIGURA GURA',
                'jadwal' => 'Sabtu • 08.00–12.00 WIB',
                'kelurahan' => null, // tersedia untuk semua kelurahan
            ],
        ];

        foreach ($banks as $bank) {
            BankSampah::updateOrCreate(['nama' => $bank['nama']], $bank);
        }
    }
}