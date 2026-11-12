<?php

namespace App\Http\Controllers;

use App\Models\BankSampah;
use App\Models\Nasabah;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Pendaftaran nasabah: Akun → Data Diri → Alamat → Berhasil.
 *
 * Langkah Akun dan Data Diri disimpan sementara di session. Seluruh data baru
 * ditulis ke database sekali, saat langkah Alamat dikirim (storeAlamat).
 */
class RegistrationController extends Controller
{
    /* ----------------------------- Langkah 0: Akun ----------------------------- */

    public function showAkun(): View
    {
        return view('auth.register');
    }

    public function storeAkun(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email terlalu panjang.',
            'email.unique' => 'Email ini sudah terdaftar. Silakan login.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak sama.',
        ]);

        // Kata sandi langsung di-hash, tidak pernah disimpan sebagai teks biasa.
        $request->session()->put('register.akun', [
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        return redirect()->route('nasabah.register.data-diri');
    }

    /* --------------------------- Langkah 1: Data Diri --------------------------- */

    public function showDataDiri(Request $request): View|RedirectResponse
    {
        if ($redirect = $this->guard($request, 'akun')) {
            return $redirect;
        }

        $saved = $request->session()->get('register.data_diri', []);

        // Email dari langkah Akun dipakai sebagai isian awal (boleh diganti).
        $saved['email'] ??= $request->session()->get('register.akun.email');

        return view('auth.data-diri', ['saved' => $saved]);
    }

    public function storeDataDiri(Request $request): RedirectResponse
    {
        if ($redirect = $this->guard($request, 'akun')) {
            return $redirect;
        }

        $request->merge([
            'nik' => preg_replace('/\D+/', '', (string) $request->input('nik')),
            'whatsapp' => $this->normalizeWhatsapp($request->input('whatsapp')),
        ]);

        $saved = $request->session()->get('register.data_diri', []);
        $hasStoredKtp = ! empty($saved['ktp_path']) && ! $request->boolean('ktp_removed')
            && Storage::exists($saved['ktp_path']);

        $data = $request->validate([
            'nama_lengkap' => ['required', 'string', 'min:3', 'max:100', 'regex:/^[\pL\s.\'-]+$/u'],
            'nik' => ['required', 'digits:16', 'unique:nasabah,nik'],
            'tanggal_lahir' => ['required', 'date', 'before_or_equal:today'],
            'jenis_kelamin' => ['required', 'in:Laki-laki,Perempuan'],
            'whatsapp' => ['required', 'regex:/^[0-9]{9,13}$/'],
            'email' => ['required', 'email', 'max:255'],
            'foto_ktp' => [$hasStoredKtp ? 'nullable' : 'required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ], [
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'nama_lengkap.min' => 'Nama lengkap minimal 3 karakter.',
            'nama_lengkap.regex' => 'Nama hanya boleh berisi huruf.',
            'nik.required' => 'NIK wajib diisi.',
            'nik.digits' => 'NIK harus terdiri dari 16 digit angka.',
            'nik.unique' => 'NIK ini sudah terdaftar. Hubungi Helpdesk jika Anda merasa belum pernah mendaftar.',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
            'tanggal_lahir.date' => 'Tanggal lahir tidak valid.',
            'tanggal_lahir.before_or_equal' => 'Tanggal lahir tidak boleh di masa depan.',
            'jenis_kelamin.required' => 'Pilih jenis kelamin.',
            'jenis_kelamin.in' => 'Pilih jenis kelamin.',
            'whatsapp.required' => 'Nomor WhatsApp wajib diisi.',
            'whatsapp.regex' => 'Masukkan 9–13 digit nomor setelah +62.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email belum valid. Contoh: nama@gmail.com.',
            'foto_ktp.required' => 'Foto KTP wajib diunggah.',
            'foto_ktp.mimes' => 'Format berkas harus JPG, PNG, atau PDF.',
            'foto_ktp.max' => 'Ukuran berkas melebihi 5 MB. Pilih foto yang lebih kecil.',
            // Muncul jika berkas melebihi batas upload_max_filesize di php.ini.
            'foto_ktp.uploaded' => 'Berkas gagal diunggah. Pastikan ukurannya tidak melebihi 5 MB.',
        ]);

        $ktp = [
            'ktp_path' => $hasStoredKtp ? $saved['ktp_path'] : null,
            'ktp_name' => $hasStoredKtp ? ($saved['ktp_name'] ?? null) : null,
            'ktp_size' => $hasStoredKtp ? ($saved['ktp_size'] ?? null) : null,
        ];

        // Buang berkas sementara lama jika diganti atau dihapus.
        if (! empty($saved['ktp_path']) && ($request->hasFile('foto_ktp') || ! $hasStoredKtp)) {
            Storage::delete($saved['ktp_path']);
        }

        if ($request->hasFile('foto_ktp')) {
            $file = $request->file('foto_ktp');
            $ktp = [
                'ktp_path' => $file->store('ktp-sementara'),
                'ktp_name' => $file->getClientOriginalName(),
                'ktp_size' => $file->getSize(),
            ];
        }

        $request->session()->put('register.data_diri', [
            'nama_lengkap' => $data['nama_lengkap'],
            'nik' => $data['nik'],
            'tanggal_lahir' => $data['tanggal_lahir'],
            'jenis_kelamin' => $data['jenis_kelamin'],
            'whatsapp' => $data['whatsapp'], // tanpa awalan +62
            'email' => $data['email'],       // email kontak, boleh beda dengan email login
        ] + $ktp);

        return redirect()->route('nasabah.register.alamat');
    }

    /* ----------------------------- Langkah 2: Alamat ----------------------------- */

    public function showAlamat(Request $request): View|RedirectResponse
    {
        if ($redirect = $this->guard($request, 'data_diri')) {
            return $redirect;
        }

        return view('auth.alamat', [
            'saved' => $request->session()->get('register.alamat', []),
            'wilayah' => config('wilayah'),
            'banks' => BankSampah::orderBy('nama')->get(['id', 'nama', 'alamat', 'jadwal', 'kelurahan']),
        ]);
    }

    public function storeAlamat(Request $request): RedirectResponse
    {
        if ($redirect = $this->guard($request, 'data_diri')) {
            return $redirect;
        }

        $wilayah = config('wilayah');

        // Rapikan RT/RW: angka saja.
        $request->merge([
            'rt' => preg_replace('/\D+/', '', (string) $request->input('rt')),
            'rw' => preg_replace('/\D+/', '', (string) $request->input('rw')),
        ]);

        $kota = (string) $request->input('kota');
        $kecamatan = (string) $request->input('kecamatan');
        $kelurahan = (string) $request->input('kelurahan');

        $data = $request->validate([
            'kota' => ['required', Rule::in(array_keys($wilayah))],
            'kecamatan' => ['required', Rule::in(array_keys($wilayah[$kota] ?? []))],
            'kelurahan' => ['required', Rule::in($wilayah[$kota][$kecamatan] ?? [])],
            'rt' => ['required', 'regex:/^\d{1,3}$/', 'not_regex:/^0+$/'],
            'rw' => ['required', 'regex:/^\d{1,3}$/', 'not_regex:/^0+$/'],
            'alamat_lengkap' => ['required', 'string', 'min:10', 'max:255'],
            'bank_sampah_id' => [
                'required',
                // Bank harus ada dan melayani kelurahan yang dipilih (kelurahan kosong = semua).
                Rule::exists('bank_sampah', 'id')->where(
                    fn ($query) => $query->where(
                        fn ($q) => $q->whereNull('kelurahan')->orWhere('kelurahan', $kelurahan)
                    )
                ),
            ],
        ], [
            'kota.required' => 'Pilih kota.',
            'kota.in' => 'Pilih kota dari daftar.',
            'kecamatan.required' => 'Pilih kecamatan.',
            'kecamatan.in' => 'Pilih kecamatan dari daftar.',
            'kelurahan.required' => 'Pilih kelurahan.',
            'kelurahan.in' => 'Pilih kelurahan dari daftar.',
            'rt.required' => 'RT wajib diisi.',
            'rt.regex' => 'RT berupa angka, maksimal 3 digit.',
            'rt.not_regex' => 'RT tidak boleh 0.',
            'rw.required' => 'RW wajib diisi.',
            'rw.regex' => 'RW berupa angka, maksimal 3 digit.',
            'rw.not_regex' => 'RW tidak boleh 0.',
            'alamat_lengkap.required' => 'Alamat lengkap wajib diisi.',
            'alamat_lengkap.min' => 'Alamat terlalu singkat. Tulis nama jalan dan nomor rumah.',
            'alamat_lengkap.max' => 'Alamat terlalu panjang (maksimal 255 karakter).',
            'bank_sampah_id.required' => 'Pilih bank sampah.',
            'bank_sampah_id.exists' => 'Bank sampah tidak tersedia untuk kelurahan ini. Pilih yang lain.',
        ]);

        $data['rt'] = str_pad($data['rt'], 3, '0', STR_PAD_LEFT);
        $data['rw'] = str_pad($data['rw'], 3, '0', STR_PAD_LEFT);

        $session = $request->session();
        $akun = $session->get('register.akun');
        $diri = $session->get('register.data_diri');

        try {
            $nasabah = DB::transaction(function () use ($akun, $diri, $data) {
                // forceFill: kolom role tidak perlu didaftarkan di $fillable model User.
                $user = (new User)->forceFill([
                    'name' => $diri['nama_lengkap'],
                    'email' => $akun['email'],
                    'password' => $akun['password'], // sudah di-hash
                    'role' => 'nasabah',
                ]);
                $user->save();

                return Nasabah::create([
                    'user_id' => $user->id,
                    'bank_sampah_id' => $data['bank_sampah_id'],
                    'nama_lengkap' => $diri['nama_lengkap'],
                    'nik' => $diri['nik'],
                    'tanggal_lahir' => $diri['tanggal_lahir'],
                    'jenis_kelamin' => $diri['jenis_kelamin'],
                    'whatsapp' => $diri['whatsapp'],
                    'email' => $diri['email'],
                    'kota' => $data['kota'],
                    'kecamatan' => $data['kecamatan'],
                    'kelurahan' => $data['kelurahan'],
                    'rt' => $data['rt'],
                    'rw' => $data['rw'],
                    'alamat_lengkap' => $data['alamat_lengkap'],
                    'status' => 'menunggu',
                ]);
            });
        } catch (UniqueConstraintViolationException) {
            // Email atau NIK ternyata dipakai orang lain sejak langkah sebelumnya (balapan data).
            $session->forget(['register.akun', 'register.data_diri', 'register.alamat']);

            return redirect()->route('nasabah.register')
                ->withErrors(['email' => 'Email atau NIK ini sudah terdaftar. Silakan login atau hubungi Helpdesk.']);
        }

        // Pindahkan KTP dari folder sementara ke folder tetap, bernama sesuai ID nasabah.
        $temp = $diri['ktp_path'] ?? null;
        if ($temp && Storage::exists($temp)) {
            $final = 'ktp/' . $nasabah->id . '.' . pathinfo($temp, PATHINFO_EXTENSION);
            Storage::move($temp, $final);
            $nasabah->update(['ktp_path' => $final]);
        }

        $bank = BankSampah::find($data['bank_sampah_id']);

        $session->forget(['register.akun', 'register.data_diri', 'register.alamat']);
        $session->put('register.berhasil', [
            'nama' => $nasabah->nama_lengkap,
            'email' => $akun['email'],
            'bank' => $bank?->only(['nama', 'alamat', 'jadwal']),
        ]);

        return redirect()->route('nasabah.register.berhasil');
    }

    /* ----------------------------- Langkah 3: Berhasil ----------------------------- */

    public function showBerhasil(Request $request): View|RedirectResponse
    {
        $done = $request->session()->get('register.berhasil');

        if (! $done) {
            return redirect()->route('home');
        }

        return view('auth.berhasil', [
            'nama' => $done['nama'],
            'firstName' => explode(' ', trim($done['nama']))[0],
            'email' => $done['email'],
            'bank' => $done['bank'],
        ]);
    }

    /* --------------------------------- Pembantu --------------------------------- */

    /**
     * Pastikan langkah sebelumnya sudah selesai. Jika belum, arahkan ke langkah yang kurang.
     * $needs: 'akun' (butuh langkah Akun) atau 'data_diri' (butuh Akun dan Data Diri).
     */
    private function guard(Request $request, string $needs): ?RedirectResponse
    {
        $session = $request->session();

        if (! $session->has('register.akun')) {
            return redirect()->route('nasabah.register');
        }

        if ($needs === 'data_diri') {
            $ktp = $session->get('register.data_diri.ktp_path');

            // Data Diri belum diisi, atau berkas KTP sementara sudah hilang.
            if (! $session->has('register.data_diri') || ! $ktp || ! Storage::exists($ktp)) {
                return redirect()->route('nasabah.register.data-diri');
            }
        }

        return null;
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