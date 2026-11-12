<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Nasabah;
use App\Models\PengajuanSetoran;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const ZONA = 'Asia/Jakarta';

    public function __invoke(Request $request): View
    {
        $admin = $request->attributes->get('admin');
        $hariIni = now(self::ZONA)->startOfDay();
        $besok = $hariIni->copy()->addDay();
        $idNasabah = Nasabah::diCakupan($admin)->select('id');

        $nasabahMenunggu = Nasabah::diCakupan($admin)->where('status', 'menunggu');
        $pengajuanMenunggu = PengajuanSetoran::diCakupan($admin)->where('status', PengajuanSetoran::MENUNGGU);

        // Saldo seluruh nasabah = setoran selesai - penarikan (proses/selesai): kewajiban bank sampah ke nasabah.
        $masuk = Transaksi::whereIn('nasabah_id', $idNasabah)->where('tipe', Transaksi::SETORAN)->where('status', Transaksi::SELESAI)->sum('nilai');
        $keluar = Transaksi::whereIn('nasabah_id', $idNasabah)->where('tipe', Transaksi::PENARIKAN)->whereIn('status', [Transaksi::PROSES, Transaksi::SELESAI])->sum('nilai');

        return view('admin.dashboard', [
            'jumlahNasabahMenunggu' => (clone $nasabahMenunggu)->count(),
            'jumlahPengajuanMenunggu' => (clone $pengajuanMenunggu)->count(),
            'sampahBulanIni' => (float) Transaksi::whereIn('nasabah_id', $idNasabah)
                ->where('tipe', Transaksi::SETORAN)->where('status', Transaksi::SELESAI)
                ->whereBetween('tanggal', [$hariIni->copy()->startOfMonth()->toDateString(), $hariIni->copy()->endOfMonth()->toDateString()])
                ->sum('berat_kg'),
            'totalSaldo' => (int) ($masuk - $keluar),
            'nasabahTerbaru' => (clone $nasabahMenunggu)->oldest()->limit(5)->get(),
            'pengajuanTerbaru' => (clone $pengajuanMenunggu)->with(['nasabah', 'jenisSampah'])->oldest()->limit(5)->get(),
            'jadwalJemput' => PengajuanSetoran::diCakupan($admin)
                ->where('metode', PengajuanSetoran::JEMPUT)
                ->whereIn('status', [PengajuanSetoran::MENUNGGU, PengajuanSetoran::DITERIMA])
                ->whereBetween('tanggal_rencana', [$hariIni->toDateString(), $besok->toDateString()])
                ->with('nasabah')
                ->orderBy('tanggal_rencana')->orderBy('id')
                ->get(),
            'hariIni' => $hariIni->toDateString(),
        ]);
    }
}