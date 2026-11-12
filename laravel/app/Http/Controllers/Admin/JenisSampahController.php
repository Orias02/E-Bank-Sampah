<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenisSampah;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Kelola jenis sampah, kategori, dan harga. Perubahan harga otomatis tercatat sebagai naik/turun di katalog. */
class JenisSampahController extends Controller
{
    /** Ikon yang tersedia (nama => label). Harus ada di components/icon.blade.php. */
    public const IKON = [
        'cup' => 'Gelas / Botol',
        'bottle' => 'Botol Kaca',
        'can' => 'Kaleng',
        'file-text' => 'Kertas',
        'package' => 'Kardus',
        'recycle' => 'Umum',
    ];

    public function index(): View
    {
        return view('admin.harga.index', [
            'jenisSampah' => JenisSampah::orderByDesc('aktif')->orderBy('id')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.harga.form', $this->dataForm(new JenisSampah(['ikon' => 'recycle', 'aktif' => true])));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validasi($request);

        $jenis = new JenisSampah($data);
        $jenis->harga_diperbarui_pada = now()->toDateString();
        $jenis->aktif = true;
        $jenis->save();

        return redirect()->route('admin.harga.index')->with('status', "{$jenis->nama} ditambahkan ke katalog.");
    }

    public function edit(int $id): View
    {
        return view('admin.harga.form', $this->dataForm(JenisSampah::findOrFail($id)));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $jenis = JenisSampah::findOrFail($id);
        $data = $this->validasi($request, $jenis);

        // Harga lama dan tanggal perubahan dicatat otomatis oleh model saat harga berubah.
        $jenis->update($data);

        return redirect()->route('admin.harga.index')->with('status', "{$jenis->nama} diperbarui.");
    }

    public function toggle(int $id): RedirectResponse
    {
        $jenis = JenisSampah::findOrFail($id);
        $jenis->update(['aktif' => ! $jenis->aktif]);

        return redirect()->route('admin.harga.index')->with('status', $jenis->aktif
            ? "{$jenis->nama} diaktifkan lagi dan tampil di katalog."
            : "{$jenis->nama} dinonaktifkan. Tidak tampil di katalog dan form setor, riwayat lama tetap utuh.");
    }

    private function dataForm(JenisSampah $jenis): array
    {
        return [
            'jenis' => $jenis,
            'daftarIkon' => self::IKON,
            'daftarKategori' => JenisSampah::query()->distinct()->orderBy('kategori')->pluck('kategori'),
        ];
    }

    private function validasi(Request $request, ?JenisSampah $jenis = null): array
    {
        $request->merge([
            'harga_per_kg' => preg_replace('/\D+/', '', (string) $request->input('harga_per_kg')),
            'nama' => trim((string) $request->input('nama')),
            'kategori' => trim((string) $request->input('kategori')),
        ]);

        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100', Rule::unique('jenis_sampah', 'nama')->ignore($jenis?->id)],
            'kategori' => ['required', 'string', 'max:30'],
            'deskripsi' => ['nullable', 'string', 'max:500'],
            'harga_per_kg' => ['required', 'integer', 'min:1', 'max:10000000'],
            'ikon' => ['required', Rule::in(array_keys(self::IKON))],
        ], [
            'nama.required' => 'Nama jenis sampah wajib diisi.',
            'nama.max' => 'Nama maksimal 100 karakter.',
            'nama.unique' => 'Nama ini sudah dipakai jenis sampah lain.',
            'kategori.required' => 'Kategori wajib diisi.',
            'kategori.max' => 'Kategori maksimal 30 karakter.',
            'deskripsi.max' => 'Deskripsi maksimal 500 karakter.',
            'harga_per_kg.required' => 'Harga per kg wajib diisi.',
            'harga_per_kg.integer' => 'Harga harus berupa angka.',
            'harga_per_kg.min' => 'Harga harus lebih dari 0.',
            'harga_per_kg.max' => 'Harga terlalu besar.',
            'ikon.required' => 'Pilih ikon.',
            'ikon.in' => 'Pilih ikon dari daftar.',
        ]);

        $data['harga_per_kg'] = (int) $data['harga_per_kg'];
        $data['deskripsi'] = $data['deskripsi'] ?: null;

        return $data;
    }
}