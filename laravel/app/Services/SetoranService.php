<?php

namespace App\Services;

use App\Models\JenisSampah;
use App\Models\Nasabah;
use App\Models\PengajuanSetoran;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Mencatat hasil penimbangan sampah menjadi transaksi setoran berstatus "selesai".
 * Dipakai oleh penimbangan pengajuan dan oleh setoran langsung (tanpa pengajuan).
 */
class SetoranService
{
    /** Batas berat per kategori dalam satu penimbangan (kg), mencegah salah ketik. */
    public const MAKS_BERAT = 1000;

    /**
     * Baca isian berat[ID] dari form admin. Kolom kosong dilewati; berat boleh memakai koma.
     *
     * @return array<int, float> jenis_sampah_id => berat (kg), hanya yang lebih dari 0
     */
    public function bacaBerat(Request $request): array
    {
        $mentah = (array) $request->input('berat', []);
        $nama = JenisSampah::pluck('nama', 'id');
        $hasil = [];

        foreach ($mentah as $id => $nilai) {
            $nilai = str_replace(',', '.', trim((string) $nilai));

            if ($nilai === '') {
                continue;
            }

            $label = $nama[(int) $id] ?? null;
            if ($label === null) {
                throw ValidationException::withMessages(['berat' => 'Kategori sampah tidak dikenal.']);
            }

            if (! preg_match('/^\d{1,4}(\.\d{1,2})?$/', $nilai) || (float) $nilai <= 0 || (float) $nilai > self::MAKS_BERAT) {
                throw ValidationException::withMessages([
                    'berat' => "Berat {$label} harus berupa angka lebih dari 0 dan maksimal " . number_format(self::MAKS_BERAT, 0, ',', '.') . ' kg.',
                ]);
            }

            $hasil[(int) $id] = round((float) $nilai, 2);
        }

        if ($hasil === []) {
            throw ValidationException::withMessages(['berat' => 'Isi berat minimal satu kategori.']);
        }

        return $hasil;
    }

    /**
     * Catat setoran. Nilai tiap baris = berat x harga per kg saat ini (disimpan permanen di transaksi,
     * jadi perubahan harga di kemudian hari tidak mengubah nilai yang sudah tercatat).
     *
     * @param  array<int, float>  $berat  jenis_sampah_id => kg
     * @return array{kg: float, nilai: int}
     */
    public function catat(Nasabah $nasabah, array $berat, string $metode, $admin, ?PengajuanSetoran $pengajuan = null): array
    {
        return DB::transaction(function () use ($nasabah, $berat, $metode, $admin, $pengajuan) {
            if ($pengajuan) {
                // Kunci baris agar dua admin tidak menimbang pengajuan yang sama bersamaan.
                $pengajuan = PengajuanSetoran::whereKey($pengajuan->id)->lockForUpdate()->firstOrFail();

                if (! in_array($pengajuan->status, [PengajuanSetoran::MENUNGGU, PengajuanSetoran::DITERIMA], true)) {
                    throw ValidationException::withMessages(['aksi' => 'Pengajuan ini sudah diproses sebelumnya.']);
                }
            }

            $jenis = JenisSampah::whereIn('id', array_keys($berat))->get()->keyBy('id');
            $tanggal = now('Asia/Jakarta')->toDateString();
            $totalKg = 0.0;
            $totalNilai = 0;

            foreach ($berat as $id => $kg) {
                $nilai = (int) round($kg * $jenis[$id]->harga_per_kg);

                Transaksi::create([
                    'nasabah_id' => $nasabah->id,
                    'pengajuan_setoran_id' => $pengajuan?->id,
                    'jenis_sampah_id' => $id,
                    'tipe' => Transaksi::SETORAN,
                    'metode' => $metode,
                    'berat_kg' => $kg,
                    'nilai' => $nilai,
                    'status' => Transaksi::SELESAI,
                    'dicatat_oleh' => $admin->id,
                    'tanggal' => $tanggal,
                ]);

                $totalKg += $kg;
                $totalNilai += $nilai;
            }

            $pengajuan?->update(['status' => PengajuanSetoran::SELESAI]);

            return ['kg' => round($totalKg, 2), 'nilai' => $totalNilai];
        });
    }
}