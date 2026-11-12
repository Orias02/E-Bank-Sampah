<?php

namespace App\Http\Controllers;

use App\Models\PengajuanSetoran;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class RiwayatController extends Controller
{
    private const PER_HALAMAN = 5;
    private const ZONA = 'Asia/Jakarta';

    /** Dilindungi middleware EnsureNasabahAktif: nasabah sudah pasti ada dan aktif. */
    public function show(Request $request): View
    {
        $nasabah = $request->attributes->get('nasabah');
        $baris = $this->semuaBaris($nasabah);

        $terakhir = max(1, (int) ceil($baris->count() / self::PER_HALAMAN));
        $halaman = min(max(1, $request->integer('page', 1)), $terakhir);

        $paginator = new LengthAwarePaginator(
            $baris->forPage($halaman, self::PER_HALAMAN)->values(),
            $baris->count(),
            self::PER_HALAMAN,
            $halaman,
            ['path' => route('nasabah.riwayat')],
        );

        return view('nasabah.riwayat', [
            'riwayat' => $paginator,
            'nomorHalaman' => $this->nomorHalaman($halaman, $terakhir),
        ]);
    }

    /** Halaman laporan siap cetak. Dari sini pengguna memilih "Simpan sebagai PDF" di dialog cetak browser. */
    public function laporan(Request $request): View
    {
        $nasabah = $request->attributes->get('nasabah');
        $baris = $this->semuaBaris($nasabah);

        return view('nasabah.riwayat-laporan', [
            'nasabah' => $nasabah->load('bankSampah'),
            'baris' => $baris,
            'saldo' => $nasabah->saldo(),
            'totalBerat' => $nasabah->totalBeratDisetor(),
            'totalSetoran' => $baris->where('jenis', 'setoran')->where('status', 'selesai')->sum('nilai'),
            'totalPenarikan' => $baris->where('jenis', 'penarikan')->whereIn('status', ['selesai', 'proses'])->sum('nilai'),
            'dicetak' => now(self::ZONA),
        ]);
    }

    /**
     * Transaksi (dicatat admin) digabung dengan pengajuan setoran yang belum selesai, terbaru di atas.
     * Pengajuan berstatus "selesai" tidak ikut karena sudah diwakili transaksinya.
     * Cukup untuk volume per nasabah yang wajar; jika kelak sangat besar, ganti dengan query UNION berhalaman.
     */
    private function semuaBaris($nasabah): Collection
    {
        $transaksi = $nasabah->transaksi()->with('jenisSampah')->get()->map(fn (Transaksi $t) => $this->barisTransaksi($t));

        $pengajuan = $nasabah->pengajuanSetoran()
            ->whereIn('status', [PengajuanSetoran::MENUNGGU, PengajuanSetoran::DITERIMA, PengajuanSetoran::DITOLAK])
            ->with('jenisSampah')
            ->get()
            ->map(fn (PengajuanSetoran $p) => $this->barisPengajuan($p));

        return $transaksi->concat($pengajuan)->sortByDesc(fn ($b) => $b->waktu->getTimestamp())->values();
    }

    private function barisTransaksi(Transaksi $t): object
    {
        $setoran = $t->tipe === Transaksi::SETORAN;
        $waktu = Carbon::parse(
            $t->tanggal->toDateString() . ' ' . $t->created_at->copy()->timezone(self::ZONA)->format('H:i'),
            self::ZONA,
        );

        $status = match ($t->status) {
            Transaksi::SELESAI => ['selesai', 'Selesai'],
            Transaksi::DIBATALKAN => ['gagal', 'Gagal'],
            default => ['proses', 'Diproses Admin'],
        };

        if ($setoran) {
            $judul = 'Setoran Sampah (' . ($t->jenisSampah?->nama ?? 'Sampah') . ')';
            $metode = match ($t->metode) {
                'jemput' => 'Dijemput',
                'antar' => 'Antar Sendiri',
                default => 'Setoran',
            };
            $keterangan = $t->berat_kg !== null ? "{$metode} - " . $this->kg($t->berat_kg) : $metode;
        } else {
            $judul = 'Penarikan Saldo' . ($t->metode ? " ({$t->metode})" : '');
            $akhir = $t->nomor_tujuan ? ' (••' . substr($t->nomor_tujuan, -4) . ')' : '';
            $keterangan = $t->metode ? "Pencairan via {$t->metode}{$akhir}" : 'Pencairan saldo';
        }

        $gagal = $status[0] === 'gagal';
        if ($gagal) {
            $keterangan = 'Gagal: ' . ($t->keterangan ?: 'Transaksi dibatalkan');
        }

        return (object) [
            'kode' => $t->kode(),
            'jenis' => $setoran ? 'setoran' : 'penarikan',
            'judul' => $judul,
            'waktu' => $waktu,
            'keterangan' => $keterangan,
            'keterangan_kecil' => null,
            'gagal' => $gagal,
            'status' => $status[0],
            'status_label' => $status[1],
            'nilai' => $t->nilai,
            'nilai_tanda' => $gagal ? 'batal' : ($setoran ? 'masuk' : 'keluar'),
        ];
    }

    private function barisPengajuan(PengajuanSetoran $p): object
    {
        $jemput = $p->metode === PengajuanSetoran::JEMPUT;
        $nama = $p->jenisSampah->pluck('nama');
        $berat = $p->jenisSampah->pluck('pivot.perkiraan_berat_kg')->filter(fn ($b) => $b !== null);
        $nilai = $p->jenisSampah->sum(fn ($j) => (float) $j->pivot->perkiraan_berat_kg * (float) $j->harga_per_kg);

        $judul = 'Setoran Sampah (' . match (true) {
            $nama->isEmpty() => 'Sampah',
            $nama->count() === 1 => $nama->first(),
            default => $nama->first() . ' +' . ($nama->count() - 1) . ' lainnya',
        } . ')';

        $status = match (true) {
            $p->status === PengajuanSetoran::DITOLAK => ['gagal', 'Ditolak'],
            $p->status === PengajuanSetoran::DITERIMA => $jemput ? ['info', 'Penjemputan Dijadwalkan'] : ['info', 'Diterima Admin'],
            default => $jemput ? ['proses', 'Menunggu Penjemputan'] : ['proses', 'Menunggu Setoran'],
        };

        $metode = $jemput ? 'Minta Dijemput' : 'Antar Sendiri';
        $ditolak = $status[1] === 'Ditolak';

        return (object) [
            'kode' => $p->kode(),
            'jenis' => 'pengajuan',
            'judul' => $judul,
            'waktu' => $p->created_at->copy()->timezone(self::ZONA),
            'keterangan' => $ditolak
                ? 'Ditolak: ' . ($p->alasan ?: 'Pengajuan tidak dapat diproses')
                : $metode . ($berat->isEmpty() ? '' : ' - ≈ ' . $this->kg($berat->sum())),
            'keterangan_kecil' => $ditolak ? null : 'Rencana: ' . $p->tanggal_rencana->locale('id')->translatedFormat('j M Y'),
            'gagal' => $ditolak,
            'status' => $status[0],
            'status_label' => $status[1],
            'nilai' => $berat->isEmpty() ? null : (int) round($nilai),
            'nilai_tanda' => $ditolak ? 'batal' : 'estimasi',
        ];
    }

    private function kg(float|int|string $angka): string
    {
        return rtrim(rtrim(number_format((float) $angka, 2, ',', '.'), '0'), ',') . ' Kg';
    }

    /** Nomor halaman yang ditampilkan; null berarti titik-titik ("…"). */
    private function nomorHalaman(int $sekarang, int $terakhir): array
    {
        if ($terakhir <= 7) {
            return range(1, $terakhir);
        }

        $nomor = collect([1, $terakhir, $sekarang - 1, $sekarang, $sekarang + 1])
            ->filter(fn ($n) => $n >= 1 && $n <= $terakhir)
            ->unique()->sort()->values();

        $hasil = [];
        $sebelum = 0;
        foreach ($nomor as $n) {
            if ($sebelum && $n - $sebelum > 1) {
                $hasil[] = null;
            }
            $hasil[] = $n;
            $sebelum = $n;
        }

        return $hasil;
    }
}