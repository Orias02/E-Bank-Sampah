<?php

namespace Database\Seeders;

use App\Models\JenisSampah;
use App\Models\Nasabah;
use App\Models\PengajuanSetoran;
use App\Models\Transaksi;
use Illuminate\Database\Seeder;

class DemoTransaksiSeeder extends Seeder
{
    /**
     * HANYA UNTUK DEMO / PENGUJIAN TAMPILAN. Jangan dijalankan di data sungguhan.
     *
     * Mengisi riwayat contoh (setoran, penarikan, transaksi gagal, pengajuan) untuk nasabah
     * yang belum punya transaksi sama sekali.
     * Jalankan: php artisan db:seed --class=DemoTransaksiSeeder
     * Untuk mengisi ulang nasabah yang sudah terisi: kosongkan dulu lewat tinker
     * (App\Models\Transaksi::truncate(); lalu hapus pengajuannya), baru jalankan seeder ini.
     */
    public function run(): void
    {
        $this->call(JenisSampahSeeder::class);

        $jenis = JenisSampah::all()->keyBy('nama');
        $waktu = fn (int $mundur, string $jam) => now('Asia/Jakarta')->subDays($mundur)->setTimeFromTimeString($jam);

        // [tipe, jenis/tujuan, berat kg, status, hari ke belakang, jam, metode, keterangan]
        $rows = [
            ['setoran', 'Kertas HVS / Buku Bekas', 83.5, Transaksi::SELESAI, 40, '10:15', 'antar', null],
            ['setoran', 'Botol Plastik PET Bening', 10.5, Transaksi::SELESAI, 30, '09:40', 'jemput', null],
            ['setoran', 'Kaleng Aluminium', 10, Transaksi::SELESAI, 20, '13:05', 'antar', null],
            ['penarikan', 50000, null, Transaksi::SELESAI, 14, '11:20', 'GoPay', null],
            ['penarikan', 75000, null, Transaksi::DIBATALKAN, 12, '11:20', 'Bank BCA', 'Nomor Rekening Tidak Valid'],
            ['setoran', 'Botol Kaca / Beling', 8, Transaksi::SELESAI, 7, '16:45', 'antar', null],
            ['setoran', 'Kertas HVS / Buku Bekas', 12.5, Transaksi::SELESAI, 2, '14:30', 'antar', null],
            ['penarikan', 30000, null, Transaksi::SELESAI, 2, '09:15', 'GoPay', null],
            ['setoran', 'Kaleng Aluminium', 3.2, Transaksi::PROSES, 1, '15:00', 'jemput', null],
        ];

        foreach (Nasabah::doesntHave('transaksi')->get() as $nasabah) {
            foreach ($rows as [$tipe, $isi, $berat, $status, $mundur, $jam, $metode, $keterangan]) {
                $setoran = $tipe === 'setoran';
                $saat = $waktu($mundur, $jam);

                Transaksi::unguarded(fn () => Transaksi::create([
                    'nasabah_id' => $nasabah->id,
                    'jenis_sampah_id' => $setoran ? $jenis[$isi]->id : null,
                    'tipe' => $tipe,
                    'metode' => $metode,
                    'berat_kg' => $berat,
                    'nilai' => $setoran ? (int) round($berat * $jenis[$isi]->harga_per_kg) : $isi,
                    'status' => $status,
                    'keterangan' => $keterangan,
                    'tanggal' => $saat->toDateString(),
                    'created_at' => $saat,
                    'updated_at' => $saat,
                ]));
            }

            // Satu pengajuan yang masih menunggu dan satu yang ditolak.
            if (! $nasabah->pengajuanSetoran()->exists()) {
                $menunggu = PengajuanSetoran::create([
                    'nasabah_id' => $nasabah->id,
                    'metode' => PengajuanSetoran::JEMPUT,
                    'tanggal_rencana' => now()->addDays(3)->toDateString(),
                    'catatan' => 'Kos Suhat',
                    'status' => PengajuanSetoran::MENUNGGU,
                ]);
                $menunggu->jenisSampah()->sync([
                    $jenis['Botol Plastik PET Bening']->id => ['perkiraan_berat_kg' => 6],
                    $jenis['Kertas HVS / Buku Bekas']->id => ['perkiraan_berat_kg' => 5],
                ]);

                $ditolak = PengajuanSetoran::create([
                    'nasabah_id' => $nasabah->id,
                    'metode' => PengajuanSetoran::ANTAR,
                    'tanggal_rencana' => now()->subDays(5)->toDateString(),
                    'status' => PengajuanSetoran::DITOLAK,
                    'alasan' => 'Sampah belum dipilah',
                ]);
                $ditolak->jenisSampah()->sync([$jenis['Botol Kaca / Beling']->id => ['perkiraan_berat_kg' => 4]]);
            }
        }
    }
}