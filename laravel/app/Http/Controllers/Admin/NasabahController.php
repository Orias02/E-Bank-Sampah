<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenisSampah;
use App\Models\Nasabah;
use App\Services\SetoranService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

/** Verifikasi nasabah, detail nasabah, dan pencatatan setoran langsung. */
class NasabahController extends Controller
{
    public const STATUS = [
        'menunggu' => 'Menunggu',
        'aktif' => 'Aktif',
        'ditolak' => 'Ditolak',
        'nonaktif' => 'Nonaktif',
    ];

    public function index(Request $request): View
    {
        $admin = $request->attributes->get('admin');
        $status = (string) $request->query('status', 'menunggu');
        $status = isset(self::STATUS[$status]) ? $status : 'semua';
        $cari = trim((string) $request->query('q', ''));

        $dasar = Nasabah::diCakupan($admin);
        $jumlah = (clone $dasar)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        $query = (clone $dasar)->with('bankSampah');

        if ($status !== 'semua') {
            $query->where('status', $status);
        }

        if ($cari !== '') {
            $like = '%' . addcslashes($cari, '%_\\') . '%';
            $query->where(function ($w) use ($like, $cari) {
                $w->where('nama_lengkap', 'like', $like)
                    ->orWhere('nik', 'like', $like)
                    ->orWhere('whatsapp', 'like', $like)
                    ->orWhere('kelurahan', 'like', $like);

                // Cari lewat kode, mis. NAS-000012
                if (preg_match('/^nas-?0*(\d+)$/i', $cari, $m)) {
                    $w->orWhere('id', (int) $m[1]);
                }
            });
        }

        return view('admin.nasabah.index', [
            'nasabah' => $query->latest()->paginate(10)->withQueryString(),
            'status' => $status,
            'cari' => $cari,
            'jumlah' => $jumlah,
            'totalSemua' => $jumlah->sum(),
            'daftarStatus' => self::STATUS,
        ]);
    }

    public function show(Request $request, int $id): View
    {
        $nasabah = $this->cari($request, $id)->load('bankSampah');

        return view('admin.nasabah.show', [
            'nasabah' => $nasabah,
            'saldo' => $nasabah->saldo(),
            'totalBerat' => $nasabah->totalBeratDisetor(),
            'transaksi' => $nasabah->transaksi()->with('jenisSampah')->latest('tanggal')->latest('id')->limit(5)->get(),
            'ktpPdf' => $nasabah->ktp_path && str_ends_with(strtolower($nasabah->ktp_path), '.pdf'),
        ]);
    }

    /** Foto KTP ada di penyimpanan privat, hanya bisa dibuka admin lewat route ini. */
    public function ktp(Request $request, int $id): Response
    {
        $nasabah = $this->cari($request, $id);

        abort_unless($nasabah->ktp_path && Storage::exists($nasabah->ktp_path), 404);

        return Storage::response($nasabah->ktp_path, null, ['Cache-Control' => 'private, max-age=3600']);
    }

    public function aktifkan(Request $request, int $id): RedirectResponse
    {
        $nasabah = $this->cari($request, $id);

        if ($nasabah->status === 'aktif') {
            return back();
        }

        $nasabah->update(['status' => 'aktif', 'alasan_status' => null, 'diverifikasi_pada' => now()]);

        return redirect()->route('admin.nasabah.show', $nasabah->id)
            ->with('status', "Akun {$nasabah->nama_lengkap} sudah diaktifkan. Nasabah bisa langsung login.");
    }

    public function tolak(Request $request, int $id): RedirectResponse
    {
        $nasabah = $this->cari($request, $id);

        $data = $request->validate([
            'alasan' => ['required', 'string', 'min:5', 'max:255'],
        ], [
            'alasan.required' => 'Tulis alasan penolakan.',
            'alasan.min' => 'Alasan terlalu singkat (minimal 5 karakter).',
            'alasan.max' => 'Alasan maksimal 255 karakter.',
        ]);

        if ($nasabah->status !== 'menunggu') {
            return back()->withErrors(['aksi' => 'Hanya pendaftaran yang berstatus Menunggu yang bisa ditolak.']);
        }

        $nasabah->update(['status' => 'ditolak', 'alasan_status' => $data['alasan'], 'diverifikasi_pada' => now()]);

        return redirect()->route('admin.nasabah.show', $nasabah->id)->with('status', 'Pendaftaran ditolak. Alasan akan tampil saat nasabah mencoba login.');
    }

    public function nonaktifkan(Request $request, int $id): RedirectResponse
    {
        $nasabah = $this->cari($request, $id);

        $data = $request->validate([
            'alasan' => ['nullable', 'string', 'max:255'],
        ], [
            'alasan.max' => 'Alasan maksimal 255 karakter.',
        ]);

        if ($nasabah->status !== 'aktif') {
            return back()->withErrors(['aksi' => 'Hanya akun yang berstatus Aktif yang bisa dinonaktifkan.']);
        }

        $nasabah->update(['status' => 'nonaktif', 'alasan_status' => $data['alasan'] ?? null]);

        return redirect()->route('admin.nasabah.show', $nasabah->id)->with('status', 'Akun dinonaktifkan. Nasabah tidak bisa login sampai diaktifkan lagi.');
    }

    /** Form setoran langsung: nasabah datang membawa sampah tanpa pengajuan. */
    public function setoran(Request $request, int $id): View|RedirectResponse
    {
        $nasabah = $this->cari($request, $id);

        if ($nasabah->status !== 'aktif') {
            return redirect()->route('admin.nasabah.show', $nasabah->id)->withErrors(['aksi' => 'Setoran hanya bisa dicatat untuk nasabah yang berstatus Aktif.']);
        }

        return view('admin.nasabah.setoran', [
            'nasabah' => $nasabah,
            'jenisSampah' => JenisSampah::aktif()->orderBy('id')->get(),
            'saldo' => $nasabah->saldo(),
        ]);
    }

    public function simpanSetoran(Request $request, int $id, SetoranService $service): RedirectResponse
    {
        $admin = $request->attributes->get('admin');
        $nasabah = $this->cari($request, $id);

        if ($nasabah->status !== 'aktif') {
            return back()->withErrors(['aksi' => 'Setoran hanya bisa dicatat untuk nasabah yang berstatus Aktif.']);
        }

        $data = $request->validate([
            'metode' => ['required', 'in:antar,jemput'],
        ], [
            'metode.required' => 'Pilih metode penyetoran.',
            'metode.in' => 'Pilih metode penyetoran.',
        ]);

        $hasil = $service->catat($nasabah, $service->bacaBerat($request), $data['metode'], $admin);

        return redirect()->route('admin.nasabah.show', $nasabah->id)->with('status', sprintf(
            'Setoran tercatat: %s kg senilai Rp %s. Saldo %s sekarang Rp %s.',
            rtrim(rtrim(number_format($hasil['kg'], 2, ',', '.'), '0'), ','),
            number_format($hasil['nilai'], 0, ',', '.'),
            $nasabah->nama_lengkap,
            number_format($nasabah->saldo(), 0, ',', '.'),
        ));
    }

    /** Ambil nasabah hanya dari cakupan admin (bank sampahnya), selain itu 404. */
    private function cari(Request $request, int $id): Nasabah
    {
        return Nasabah::diCakupan($request->attributes->get('admin'))->findOrFail($id);
    }
}