<?php

namespace App\Http\Controllers;

use App\Models\Nasabah;
use App\Models\Transaksi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Penarikan saldo. Dilindungi middleware EnsureNasabahAktif.
 *
 * Penarikan tidak memerlukan verifikasi admin: begitu dikirim, dicatat sebagai transaksi bertipe
 * "penarikan" berstatus "selesai", sehingga saldo (Nasabah::saldo()) langsung berkurang sebesar nominalnya.
 */
class TarikController extends Controller
{
    public const MIN_TARIK = 10000;
    public const MAKS_TARIK = 999999999;
    public const PILIHAN_CEPAT = [50000, 100000, 200000];

    public function show(Request $request): View
    {
        $nasabah = $request->attributes->get('nasabah');

        return view('nasabah.tarik', [
            'nasabah' => $nasabah,
            'saldo' => $nasabah->saldo(),
            'minTarik' => self::MIN_TARIK,
            'pilihanCepat' => self::PILIHAN_CEPAT,
            'metodePencairan' => ProfilController::METODE_PENCAIRAN,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $nasabah = $request->attributes->get('nasabah');

        $request->merge([
            'nominal' => preg_replace('/\D+/', '', (string) $request->input('nominal')),
            'nomor_tujuan' => preg_replace('/\D+/', '', (string) $request->input('nomor_tujuan')),
            'nama_pemilik' => trim(preg_replace('/\s+/', ' ', (string) $request->input('nama_pemilik'))),
        ]);

        $data = $request->validate([
            'nominal' => ['required', 'integer', 'min:' . self::MIN_TARIK, 'max:' . self::MAKS_TARIK],
            'tujuan' => ['required', Rule::in(ProfilController::METODE_PENCAIRAN)],
            'nomor_tujuan' => ['required', 'regex:/^\d{8,20}$/'],
            'nama_pemilik' => ['required', 'string', 'min:3', 'max:100', "regex:/^[\\p{L}\\s.'-]+$/u"],
        ], [
            'nominal.required' => 'Masukkan nominal penarikan.',
            'nominal.integer' => 'Nominal harus berupa angka.',
            'nominal.min' => 'Minimal penarikan Rp ' . $this->rupiah(self::MIN_TARIK) . '.',
            'nominal.max' => 'Nominal terlalu besar.',
            'tujuan.required' => 'Pilih tujuan pencairan.',
            'tujuan.in' => 'Pilih tujuan dari daftar.',
            'nomor_tujuan.required' => 'Nomor rekening / No. HP wajib diisi.',
            'nomor_tujuan.regex' => 'Nomor rekening 8–20 digit angka.',
            'nama_pemilik.required' => 'Nama pemilik rekening wajib diisi.',
            'nama_pemilik.min' => 'Nama pemilik minimal 3 karakter.',
            'nama_pemilik.max' => 'Nama pemilik terlalu panjang (maksimal 100 karakter).',
            'nama_pemilik.regex' => 'Nama hanya boleh berisi huruf.',
        ]);

        $nominal = (int) $data['nominal'];

        $transaksi = DB::transaction(function () use ($nasabah, $data, $nominal) {
            // Kunci baris nasabah supaya dua permintaan bersamaan tidak sama-sama lolos pemeriksaan saldo.
            Nasabah::whereKey($nasabah->id)->lockForUpdate()->first();

            $saldo = $nasabah->saldo();
            if ($nominal > $saldo) {
                throw ValidationException::withMessages([
                    'nominal' => 'Nominal melebihi saldo Anda (Rp ' . $this->rupiah($saldo) . ').',
                ]);
            }

            return Transaksi::create([
                'nasabah_id' => $nasabah->id,
                'tipe' => Transaksi::PENARIKAN,
                'metode' => $data['tujuan'],
                'nomor_tujuan' => $data['nomor_tujuan'],
                'nama_pemilik' => $data['nama_pemilik'],
                'nilai' => $nominal,
                'status' => Transaksi::SELESAI,
                'tanggal' => now('Asia/Jakarta')->toDateString(),
            ]);
        });

        return redirect()->route('nasabah.tarik.berhasil')->with('penarikan', [
            'kode' => $transaksi->kode(),
            'nominal' => $nominal,
            'tujuan' => $transaksi->metode,
            'nomor' => $transaksi->nomor_tujuan,
            'sisa' => $nasabah->saldo(),
        ]);
    }

    public function berhasil(Request $request): View|RedirectResponse
    {
        $penarikan = $request->session()->get('penarikan');

        // Dibuka langsung atau di-refresh: tidak ada data penarikan, kembali ke form.
        if (! $penarikan) {
            return redirect()->route('nasabah.tarik');
        }

        return view('nasabah.tarik-berhasil', [
            'nasabah' => $request->attributes->get('nasabah'),
            'penarikan' => $penarikan,
        ]);
    }

    private function rupiah(int $angka): string
    {
        return number_format($angka, 0, ',', '.');
    }
}