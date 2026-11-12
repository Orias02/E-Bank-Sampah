<?php

namespace Database\Seeders;

use App\Models\JenisSampah;
use Illuminate\Database\Seeder;

class JenisSampahSeeder extends Seeder
{
    /**
     * HARGA DAN DESKRIPSI CONTOH. Ganti dengan data bank sampah yang sebenarnya.
     * Jalankan: php artisan db:seed --class=JenisSampahSeeder
     *
     * Aman dijalankan ulang. Kunci "dulu" adalah nama versi lama; barisnya diganti nama (bukan dibuat baru)
     * supaya riwayat transaksi yang sudah ada tetap tersambung.
     */
    public function run(): void
    {
        // [nama, kategori, ikon, harga sekarang, harga sebelumnya, deskripsi, nama lama]
        $items = [
            ['Kaleng Aluminium', 'Logam', 'can', 5000, 4500, 'Kaleng minuman bekas (soda/kopi). Pastikan kosong, bersih dari cairan, dan dipipihkan.', 'Logam & Kaleng Aluminium'],
            ['Kertas HVS / Buku Bekas', 'Kertas', 'file-text', 3500, 3500, 'Kertas putih bekas catatan kuliah, print, atau buku tulis tanpa sampul plastik.', 'Kardus & Kertas HVS'],
            ['Botol Plastik PET Bening', 'Plastik', 'cup', 3000, 2800, 'Botol air mineral bening. Lepaskan label merek dan tutup botol agar harga maksimal.', 'Botol / Plastik PET'],
            ['Kardus Cokelat', 'Kertas', 'package', 2500, 2500, 'Kardus bekas paket atau kemasan dalam kondisi kering, tidak berminyak, dan sudah dilipat.', null],
            ['Gelas Plastik (PP)', 'Plastik', 'cup', 2000, 2000, 'Gelas dan wadah plastik bekas minuman. Bilas hingga bersih dan keringkan sebelum disetor.', null],
            ['Botol Kaca / Beling', 'Kaca', 'bottle', 500, 600, 'Botol dan pecahan kaca bening atau berwarna. Pisahkan dari tutup logam dan bungkus dengan aman.', 'Beling / Botol Kaca'],
        ];

        foreach ($items as [$nama, $kategori, $ikon, $harga, $sebelumnya, $deskripsi, $dulu]) {
            $jenis = JenisSampah::where('nama', $nama)->first()
                ?? ($dulu ? JenisSampah::where('nama', $dulu)->first() : null)
                ?? new JenisSampah;

            $jenis->fill([
                'nama' => $nama,
                'kategori' => $kategori,
                'deskripsi' => $deskripsi,
                'ikon' => $ikon,
                'harga_per_kg' => $harga,
                'harga_sebelumnya' => $sebelumnya,
                'harga_diperbarui_pada' => now()->toDateString(),
            ])->save();
        }
    }
}