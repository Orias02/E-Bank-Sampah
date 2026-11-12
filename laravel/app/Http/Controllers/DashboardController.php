<?php

namespace App\Http\Controllers;

use App\Models\PengajuanSetoran;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const JUMLAH_AKTIVITAS = 5;

    /** Dilindungi middleware EnsureNasabahAktif: nasabah sudah pasti ada dan aktif. */
    public function __invoke(Request $request): View
    {
        $nasabah = $request->attributes->get('nasabah');

        return view('nasabah.dashboard', [
            'nasabah' => $nasabah,
            'saldo' => $nasabah->saldo(),
            'totalBerat' => $nasabah->totalBeratDisetor(),
            'aktivitas' => $this->aktivitasTerakhir($nasabah),
        ]);
    }

    /**
     * Gabungan transaksi (dicatat admin) dan pengajuan setoran yang belum selesai.
     * Pengajuan berstatus "selesai" tidak ditampilkan lagi di sini karena sudah diwakili transaksinya.
     * Tiap baris: tipe (setoran|penarikan|pengajuan), nama, ikon, tanggal, berat, berat_perkiraan (juga berlaku untuk nilai), status, nilai.
     */
    private function aktivitasTerakhir($nasabah): Collection
    {
        $transaksi = $nasabah->transaksi()
            ->with('jenisSampah')
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->limit(self::JUMLAH_AKTIVITAS)
            ->get()
            ->map(fn ($trx) => (object) [
                'tipe' => $trx->tipe,
                'nama' => $trx->tipe === 'penarikan' ? 'Penarikan Saldo' : ($trx->jenisSampah?->nama ?? 'Sampah'),
                'ikon' => $trx->tipe === 'penarikan' ? 'wallet' : ($trx->jenisSampah?->ikon ?? 'recycle'),
                'tanggal' => $trx->tanggal,
                'berat' => $trx->berat_kg,
                'berat_perkiraan' => false,
                'status' => $trx->status,
                'nilai' => $trx->nilai,
                'urut' => $trx->created_at ?? $trx->tanggal,
            ]);

        $pengajuan = $nasabah->pengajuanSetoran()
            ->whereIn('status', [PengajuanSetoran::MENUNGGU, PengajuanSetoran::DITERIMA, PengajuanSetoran::DITOLAK])
            ->with('jenisSampah')
            ->latest()
            ->limit(self::JUMLAH_AKTIVITAS)
            ->get()
            ->map(function ($p) {
                $nama = $p->jenisSampah->pluck('nama');
                $berat = $p->jenisSampah->pluck('pivot.perkiraan_berat_kg')->filter(fn ($b) => $b !== null);
                // Estimasi nilai = perkiraan berat x harga per kg tiap kategori (nilai pasti baru ada setelah admin menimbang).
                $nilai = $p->jenisSampah->sum(fn ($j) => (float) $j->pivot->perkiraan_berat_kg * (float) $j->harga_per_kg);

                return (object) [
                    'tipe' => 'pengajuan',
                    'nama' => $nama->isEmpty()
                        ? 'Setoran Sampah'
                        : ($nama->count() === 1 ? $nama->first() : $nama->first() . ' +' . ($nama->count() - 1) . ' lainnya'),
                    'ikon' => $p->jenisSampah->first()?->ikon ?? 'recycle',
                    'tanggal' => $p->created_at,
                    'berat' => $berat->isEmpty() ? null : (float) $berat->sum(),
                    'berat_perkiraan' => true,
                    'status' => $p->status,
                    'nilai' => $berat->isEmpty() ? null : round($nilai),
                    'urut' => $p->created_at,
                ];
            });

        return $transaksi
            ->concat($pengajuan)
            ->sort(fn ($a, $b) => [$b->tanggal->toDateString(), $b->urut->getTimestamp()]
                <=> [$a->tanggal->toDateString(), $a->urut->getTimestamp()])
            ->take(self::JUMLAH_AKTIVITAS)
            ->values();
    }
}