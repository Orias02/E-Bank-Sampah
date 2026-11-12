<?php

namespace App\Http\Controllers;

use App\Models\JenisSampah;
use App\Models\PengajuanSetoran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Pengajuan setoran sampah oleh nasabah. Dilindungi middleware EnsureNasabahAktif.
 */
class SetorController extends Controller
{
    /** Batas pengajuan yang masih menunggu, supaya tidak menumpuk. */
    private const MAKS_MENUNGGU = 3;

    /** Rencana setor paling jauh berapa hari ke depan. */
    private const MAKS_HARI = 60;

    /** Syarat penjemputan: total perkiraan berat minimal (kg). */
    private const MIN_BERAT_JEMPUT = 10;

    /** Batas perkiraan berat per kategori (kg), mencegah salah ketik. */
    private const MAKS_BERAT = 500;

    public function show(Request $request): View
    {
        $nasabah = $request->attributes->get('nasabah')->load('bankSampah');

        return view('nasabah.setor', [
            'nasabah' => $nasabah,
            'jenisSampah' => JenisSampah::orderBy('id')->get(['id', 'nama', 'ikon']),
            'tanggalMin' => now()->toDateString(),
            'tanggalMax' => now()->addDays(self::MAKS_HARI)->toDateString(),
            'tanggalDefault' => now()->addDays(3)->toDateString(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $nasabah = $request->attributes->get('nasabah');

        // Berat boleh ditulis dengan koma (12,5): ubah ke titik sebelum diperiksa.
        $request->merge([
            'berat' => array_map(
                fn ($v) => is_string($v) ? str_replace(',', '.', trim($v)) : $v,
                (array) $request->input('berat', [])
            ),
        ]);

        $data = $request->validate([
            'metode' => ['required', 'in:' . PengajuanSetoran::ANTAR . ',' . PengajuanSetoran::JEMPUT],
            'jenis_sampah' => ['required', 'array', 'min:1'],
            'jenis_sampah.*' => ['integer', 'exists:jenis_sampah,id'],
            'berat' => ['nullable', 'array'],
            'tanggal_rencana' => ['required', 'date', 'after_or_equal:today', 'before_or_equal:' . now()->addDays(self::MAKS_HARI)->toDateString()],
            'catatan' => ['nullable', 'string', 'max:500'],
        ], [
            'metode.required' => 'Pilih metode penyetoran.',
            'metode.in' => 'Pilih metode penyetoran.',
            'jenis_sampah.required' => 'Pilih minimal satu kategori sampah.',
            'jenis_sampah.min' => 'Pilih minimal satu kategori sampah.',
            'jenis_sampah.*.exists' => 'Kategori sampah tidak valid.',
            'tanggal_rencana.required' => 'Pilih tanggal setor.',
            'tanggal_rencana.date' => 'Tanggal setor tidak valid.',
            'tanggal_rencana.after_or_equal' => 'Tanggal setor tidak boleh sebelum hari ini.',
            'tanggal_rencana.before_or_equal' => 'Tanggal setor maksimal ' . self::MAKS_HARI . ' hari dari sekarang.',
            'catatan.max' => 'Catatan maksimal 500 karakter.',
        ]);

        // Perkiraan berat wajib untuk setiap kategori yang dipilih.
        $ids = array_values(array_unique(array_map('intval', $data['jenis_sampah'])));
        $nama = JenisSampah::whereIn('id', $ids)->pluck('nama', 'id');
        $sync = [];
        $total = 0.0;

        foreach ($ids as $id) {
            $raw = (string) $request->input("berat.$id", '');

            if (! preg_match('/^\d{1,3}(\.\d{1,2})?$/', $raw) || (float) $raw <= 0) {
                throw ValidationException::withMessages([
                    'berat' => "Isi perkiraan berat {$nama[$id]} (lebih dari 0 kg).",
                ]);
            }

            if ((float) $raw > self::MAKS_BERAT) {
                throw ValidationException::withMessages([
                    'berat' => "Perkiraan berat {$nama[$id]} maksimal " . self::MAKS_BERAT . ' kg.',
                ]);
            }

            $sync[$id] = ['perkiraan_berat_kg' => round((float) $raw, 2)];
            $total += (float) $raw;
        }

        if ($data['metode'] === PengajuanSetoran::JEMPUT && $total < self::MIN_BERAT_JEMPUT) {
            $totalTeks = rtrim(rtrim(number_format($total, 2, ',', '.'), '0'), ',');

            throw ValidationException::withMessages([
                'berat' => 'Penjemputan minimal total berat ' . self::MIN_BERAT_JEMPUT . " kg. Total perkiraan Anda baru {$totalTeks} kg.",
            ]);
        }

        $menunggu = PengajuanSetoran::where('nasabah_id', $nasabah->id)
            ->where('status', PengajuanSetoran::MENUNGGU)
            ->count();

        if ($menunggu >= self::MAKS_MENUNGGU) {
            return back()->withInput()->withErrors([
                'pengajuan' => 'Anda masih punya ' . self::MAKS_MENUNGGU . ' pengajuan yang menunggu. Tunggu admin memprosesnya dulu.',
            ]);
        }

        DB::transaction(function () use ($nasabah, $data, $sync) {
            $pengajuan = PengajuanSetoran::create([
                'nasabah_id' => $nasabah->id,
                'metode' => $data['metode'],
                'tanggal_rencana' => $data['tanggal_rencana'],
                'catatan' => $data['catatan'] ?? null,
                'status' => PengajuanSetoran::MENUNGGU,
            ]);

            $pengajuan->jenisSampah()->sync($sync);
        });

        return redirect()->route('nasabah.setor')
            ->with('status', 'Pengajuan setoran terkirim. Admin akan menghubungi Anda lewat WhatsApp untuk konfirmasi.');
    }
}