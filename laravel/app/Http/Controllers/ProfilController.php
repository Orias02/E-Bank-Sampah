<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Profil nasabah. Dilindungi middleware EnsureNasabahAktif.
 * Nama, NIK, dan wilayah (RT/RW, kelurahan, kecamatan, kota) tidak bisa diubah dari sini:
 * nama dan NIK harus sesuai KTP, dan bank sampah nasabah terikat ke wilayahnya,
 * jadi perubahannya lewat Helpdesk/admin.
 */
class ProfilController extends Controller
{
    /** Pilihan metode pencairan saldo. */
    public const METODE_PENCAIRAN = [
        'GoPay', 'OVO', 'DANA', 'ShopeePay', 'LinkAja',
        'Bank BCA', 'Bank BRI', 'Bank BNI', 'Bank Mandiri',
    ];

    public function show(Request $request): View
    {
        return view('nasabah.profil', [
            'nasabah' => $request->attributes->get('nasabah'),
            'metodePencairan' => self::METODE_PENCAIRAN,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $nasabah = $request->attributes->get('nasabah');

        $request->merge([
            'whatsapp' => $this->normalizeWhatsapp($request->input('whatsapp')),
            'nomor_rekening' => preg_replace('/\D+/', '', (string) $request->input('nomor_rekening')),
        ]);

        $data = $request->validate([
            'whatsapp' => ['required', 'regex:/^[0-9]{9,13}$/'],
            'email' => ['required', 'email', 'max:255'],
            'alamat_lengkap' => ['required', 'string', 'min:10', 'max:255'],
            'metode_pencairan' => ['nullable', Rule::in(self::METODE_PENCAIRAN), 'required_with:nomor_rekening'],
            'nomor_rekening' => ['nullable', 'regex:/^\d{8,20}$/', 'required_with:metode_pencairan'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ], [
            'whatsapp.required' => 'Nomor WhatsApp wajib diisi.',
            'whatsapp.regex' => 'Masukkan nomor WhatsApp 9–13 digit (tanpa +62).',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email belum valid. Contoh: nama@gmail.com.',
            'alamat_lengkap.required' => 'Alamat penjemputan wajib diisi.',
            'alamat_lengkap.min' => 'Alamat terlalu singkat. Tulis nama jalan dan nomor rumah.',
            'alamat_lengkap.max' => 'Alamat terlalu panjang (maksimal 255 karakter).',
            'metode_pencairan.in' => 'Pilih metode dari daftar.',
            'metode_pencairan.required_with' => 'Pilih metode pencairan.',
            'nomor_rekening.regex' => 'Nomor rekening 8–20 digit angka.',
            'nomor_rekening.required_with' => 'Nomor rekening wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak sama.',
        ]);

        DB::transaction(function () use ($nasabah, $data) {
            $nasabah->update([
                'whatsapp' => $data['whatsapp'],
                'email' => $data['email'],
                'alamat_lengkap' => $data['alamat_lengkap'],
                'metode_pencairan' => $data['metode_pencairan'] ?: null,
                'nomor_rekening' => $data['nomor_rekening'] ?: null,
            ]);

            $user = $nasabah->user;
            if (! empty($data['password'])) {
                $user->password = Hash::make($data['password']);
            }

            $user->save();
        });

        return redirect()->route('nasabah.profil')->with('status', 'Perubahan berhasil disimpan.');
    }

    public function updateFoto(Request $request): RedirectResponse
    {
        $nasabah = $request->attributes->get('nasabah');

        $request->validate([
            'foto' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'foto.required' => 'Pilih foto terlebih dahulu.',
            'foto.image' => 'Berkas harus berupa gambar JPG, PNG, atau WebP.',
            'foto.mimes' => 'Format foto harus JPG, PNG, atau WebP.',
            'foto.max' => 'Ukuran foto melebihi 2 MB. Pilih foto yang lebih kecil.',
            'foto.uploaded' => 'Foto gagal diunggah. Pastikan ukurannya tidak melebihi 2 MB.',
        ]);

        $old = $nasabah->foto_path;
        $nasabah->update(['foto_path' => $request->file('foto')->store('foto-profil')]);

        if ($old) {
            Storage::delete($old);
        }

        return redirect()->route('nasabah.profil')->with('status', 'Foto profil diperbarui.');
    }

    /** Menampilkan foto profil dari storage privat (hanya untuk pemiliknya). */
    public function foto(Request $request): Response
    {
        $path = $request->attributes->get('nasabah')->foto_path;

        abort_unless($path && Storage::exists($path), 404);

        return Storage::response($path, null, ['Cache-Control' => 'private, max-age=86400']);
    }

    /** Ambil angka saja, lalu buang awalan 0 atau 62 (nomor disimpan tanpa +62). */
    private function normalizeWhatsapp(mixed $value): string
    {
        $digits = ltrim(preg_replace('/\D+/', '', (string) $value), '0');

        if (str_starts_with($digits, '62')) {
            $digits = ltrim(substr($digits, 2), '0');
        }

        return $digits;
    }
}