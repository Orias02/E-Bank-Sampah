<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenisSampah;
use App\Models\PengajuanSetoran;
use App\Services\SetoranService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Pengajuan setoran dari nasabah: lihat, terima/tolak, dan catat hasil penimbangan. */
class PengajuanController extends Controller
{
    public const STATUS = [
        PengajuanSetoran::MENUNGGU => 'Menunggu',
        PengajuanSetoran::DITERIMA => 'Diterima',
        PengajuanSetoran::SELESAI => 'Selesai',
        PengajuanSetoran::DITOLAK => 'Ditolak',
    ];

    public function index(Request $request): View
    {
        $admin = $request->attributes->get('admin');
        $status = (string) $request->query('status', PengajuanSetoran::MENUNGGU);
        $status = isset(self::STATUS[$status]) ? $status : 'semua';
        $metode = in_array($request->query('metode'), [PengajuanSetoran::ANTAR, PengajuanSetoran::JEMPUT], true) ? $request->query('metode') : '';
        $cari = trim((string) $request->query('q', ''));

        $dasar = PengajuanSetoran::diCakupan($admin);
        $jumlah = (clone $dasar)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        $query = (clone $dasar)->with(['nasabah', 'jenisSampah']);

        if ($status !== 'semua') {
            $query->where('status', $status);
        }

        if ($metode !== '') {
            $query->where('metode', $metode);
        }

        if ($cari !== '') {
            $like = '%' . addcslashes($cari, '%_\\') . '%';
            $query->where(function ($w) use ($like, $cari) {
                $w->whereHas('nasabah', fn ($n) => $n->where('nama_lengkap', 'like', $like)->orWhere('kelurahan', 'like', $like));

                // Cari lewat kode, mis. PGJ-000007
                if (preg_match('/^pgj-?0*(\d+)$/i', $cari, $m)) {
                    $w->orWhere('id', (int) $m[1]);
                }
            });
        }

        // Yang menunggu diurutkan dari yang paling lama (didahulukan); lainnya dari yang terbaru.
        $query = in_array($status, [PengajuanSetoran::MENUNGGU, PengajuanSetoran::DITERIMA], true)
            ? $query->orderBy('tanggal_rencana')->orderBy('id')
            : $query->latest();

        return view('admin.pengajuan.index', [
            'pengajuan' => $query->paginate(10)->withQueryString(),
            'status' => $status,
            'metode' => $metode,
            'cari' => $cari,
            'jumlah' => $jumlah,
            'totalSemua' => $jumlah->sum(),
            'daftarStatus' => self::STATUS,
        ]);
    }

    public function show(Request $request, int $id): View
    {
        $pengajuan = $this->cari($request, $id)->load(['nasabah.bankSampah', 'jenisSampah', 'transaksi.jenisSampah']);

        $bisaDitimbang = in_array($pengajuan->status, [PengajuanSetoran::MENUNGGU, PengajuanSetoran::DITERIMA], true);

        // Form penimbangan: semua kategori aktif, ditambah kategori pengajuan yang kini nonaktif.
        $jenisSampah = $bisaDitimbang
            ? JenisSampah::where(fn ($q) => $q->where('aktif', true)->orWhereIn('id', $pengajuan->jenisSampah->pluck('id')))->orderBy('id')->get()
            : collect();

        return view('admin.pengajuan.show', [
            'pengajuan' => $pengajuan,
            'nasabah' => $pengajuan->nasabah,
            'bisaDitimbang' => $bisaDitimbang,
            'jenisSampah' => $jenisSampah,
            // Isian awal berat = perkiraan nasabah (jika form belum dikirim sebelumnya).
            'perkiraan' => $pengajuan->jenisSampah->mapWithKeys(fn ($j) => [$j->id => $j->pivot->perkiraan_berat_kg])->all(),
        ]);
    }

    public function terima(Request $request, int $id): RedirectResponse
    {
        $pengajuan = $this->cari($request, $id);

        if ($pengajuan->status !== PengajuanSetoran::MENUNGGU) {
            return back()->withErrors(['aksi' => 'Hanya pengajuan yang berstatus Menunggu yang bisa diterima.']);
        }

        $pengajuan->update(['status' => PengajuanSetoran::DITERIMA]);

        return redirect()->route('admin.pengajuan.show', $pengajuan->id)->with('status', 'Pengajuan diterima. Catat penimbangan setelah sampahnya tiba.');
    }

    public function tolak(Request $request, int $id): RedirectResponse
    {
        $pengajuan = $this->cari($request, $id);

        $data = $request->validate([
            'alasan' => ['required', 'string', 'min:5', 'max:255'],
        ], [
            'alasan.required' => 'Tulis alasan penolakan.',
            'alasan.min' => 'Alasan terlalu singkat (minimal 5 karakter).',
            'alasan.max' => 'Alasan maksimal 255 karakter.',
        ]);

        if (! in_array($pengajuan->status, [PengajuanSetoran::MENUNGGU, PengajuanSetoran::DITERIMA], true)) {
            return back()->withErrors(['aksi' => 'Pengajuan ini sudah diproses sebelumnya.']);
        }

        $pengajuan->update(['status' => PengajuanSetoran::DITOLAK, 'alasan' => $data['alasan']]);

        return redirect()->route('admin.pengajuan.show', $pengajuan->id)->with('status', 'Pengajuan ditolak. Alasan tampil di riwayat nasabah.');
    }

    /** Catat hasil timbangan: membuat transaksi setoran selesai (menambah saldo) dan menutup pengajuan. */
    public function timbang(Request $request, int $id, SetoranService $service): RedirectResponse
    {
        $admin = $request->attributes->get('admin');
        $pengajuan = $this->cari($request, $id)->load('nasabah');

        if ($pengajuan->nasabah->status !== 'aktif') {
            return back()->withErrors(['aksi' => 'Akun nasabah tidak aktif, setoran tidak bisa dicatat.']);
        }

        $hasil = $service->catat($pengajuan->nasabah, $service->bacaBerat($request), $pengajuan->metode, $admin, $pengajuan);

        return redirect()->route('admin.pengajuan.show', $pengajuan->id)->with('status', sprintf(
            'Penimbangan tercatat: %s kg senilai Rp %s sudah masuk ke saldo %s.',
            rtrim(rtrim(number_format($hasil['kg'], 2, ',', '.'), '0'), ','),
            number_format($hasil['nilai'], 0, ',', '.'),
            $pengajuan->nasabah->nama_lengkap,
        ));
    }

    private function cari(Request $request, int $id): PengajuanSetoran
    {
        return PengajuanSetoran::diCakupan($request->attributes->get('admin'))->findOrFail($id);
    }
}