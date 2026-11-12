<?php

namespace Database\Seeders;

use App\Models\JenisSampah;
use App\Models\Nasabah;
use App\Models\Transaksi;
use Illuminate\Database\Seeder;

class DemoTransaksiSeeder extends Seeder
{
    /**
     * HANYA UNTUK DEMO / PENGUJIAN TAMPILAN DASHBOARD. Jangan dijalankan di data sungguhan.
     *
     * Mengisi riwayat contoh untuk nasabah yang belum punya transaksi sama sekali.
     * Jalankan: php artisan db:seed --class=DemoTransaksiSeeder
     */
    public function run(): void
    {
        $this->call(JenisSampahSeeder::class);

        $jenis = JenisSampah::pluck('id', 'nama');
        $hari = fn (int $mundur) => now()->subDays($mundur)->toDateString();

        // [jenis, berat kg, status, hari ke belakang]
        $rows = [
            ['Kardus Bekas', 83.5, Transaksi::SELESAI, 40],
            ['Botol Plastik PET', 10.5, Transaksi::SELESAI, 30],
            ['Kaleng Aluminium', 10, Transaksi::SELESAI, 20],
            ['Kardus Bekas', 12.5, Transaksi::SELESAI, 2],
            ['Botol Plastik PET', 8, Transaksi::SELESAI, 7],
            ['Kaleng Aluminium', 3.2, Transaksi::PROSES, 10],
        ];

        foreach (Nasabah::doesntHave('transaksi')->get() as $nasabah) {
            foreach ($rows as [$nama, $berat, $status, $mundur]) {
                $harga = JenisSampah::find($jenis[$nama])->harga_per_kg;

                Transaksi::create([
                    'nasabah_id' => $nasabah->id,
                    'jenis_sampah_id' => $jenis[$nama],
                    'tipe' => Transaksi::SETORAN,
                    'berat_kg' => $berat,
                    'nilai' => (int) round($berat * $harga),
                    'status' => $status,
                    'tanggal' => $hari($mundur),
                ]);
            }
        }
    }
}