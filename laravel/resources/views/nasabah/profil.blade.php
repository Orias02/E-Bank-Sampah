@extends('layouts.nasabah')

@section('title', 'Profil | E-Bank Sampah FILKOM')

@section('breadcrumb')
    <nav aria-label="Breadcrumb" class="mb-4 flex items-center gap-2 text-sm">
        <a href="{{ route('dashboard') }}" class="text-neutral-500 transition-colors hover:text-neutral-900">Beranda</a>
        <x-icon name="chevron-right" class="size-4 text-neutral-400" />
        <span aria-current="page" class="font-medium text-neutral-900">Profil</span>
    </nav>
@endsection

@section('card')
    @php
        // Dikirim ProfilController: $nasabah dan $metodePencairan (daftar pilihan).
        $val = fn (string $key, $default = '') => old($key, $default);

        $initials = implode('', array_map(
            fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)),
            array_slice(preg_split('/\s+/', trim($nasabah->nama_lengkap)), 0, 2)
        ));
        $fotoUrl = $nasabah->foto_path ? route('nasabah.foto', ['v' => $nasabah->updated_at?->timestamp]) : null;

        // Nomor WhatsApp tersimpan tanpa awalan; tampil seperti 0812 3456 7890.
        $waAwal = '0' . $nasabah->whatsapp;
        $waTampil = trim(substr($waAwal, 0, 4) . ' ' . substr($waAwal, 4, 4) . ' ' . substr($waAwal, 8));

        $wilayah = "RT {$nasabah->rt} / RW {$nasabah->rw}, Kelurahan {$nasabah->kelurahan}, Kecamatan {$nasabah->kecamatan}, {$nasabah->kota}";

        $formKeys = ['whatsapp', 'email', 'alamat_lengkap', 'metode_pencairan', 'nomor_rekening', 'password', 'password_confirmation'];
        $invalidCount = count(array_intersect($errors->keys(), $formKeys));

        $label = 'block text-xs font-semibold text-neutral-800';
        $input = 'mt-1.5 block h-10 w-full rounded-lg border border-neutral-300 bg-white px-3.5 text-sm text-neutral-900 placeholder:text-neutral-400 transition focus:border-[#2e7d32] focus:outline-none focus:ring-4 focus:ring-[#2e7d32]/10 group-data-[invalid=true]/field:border-red-400 group-data-[invalid=true]/field:bg-red-50/40 group-data-[invalid=true]/field:focus:border-red-500 group-data-[invalid=true]/field:focus:ring-red-500/10';
    @endphp

    <div class="h-px bg-neutral-200"></div>

    <div class="mt-6 flex-1 animate-fade-up rounded-2xl border border-neutral-200 bg-white p-5 sm:p-8">

        @if (session('status'))
            <div role="status" class="mb-6 flex items-center gap-3 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-[#2e7d32]">
                <x-icon name="circle-check" class="size-5 shrink-0" />
                {{ session('status') }}
            </div>
        @endif

        @error('foto')
            <div role="alert" class="mb-6 flex items-center gap-3 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700">
                <x-icon name="alert-circle" class="size-5 shrink-0" />
                {{ $message }}
            </div>
        @enderror

        {{-- Pesan dari pemeriksaan foto di browser (wizard.js / auth.js) --}}
        <div data-photo-error role="alert" hidden class="mb-6 flex items-center gap-3 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700">
            <x-icon name="alert-circle" class="size-5 shrink-0" />
            <span data-photo-error-text></span>
        </div>

        {{-- Kepala profil --}}
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-neutral-200 pb-6">
            <div class="flex min-w-0 items-center gap-4">
                @if ($fotoUrl)
                    <img src="{{ $fotoUrl }}" alt="Foto profil {{ $nasabah->nama_lengkap }}" class="size-16 shrink-0 rounded-full object-cover">
                @else
                    <span class="grid size-16 shrink-0 place-items-center rounded-full bg-[#213b2d] text-xl font-bold text-white" aria-hidden="true">{{ $initials }}</span>
                @endif
                <div class="min-w-0">
                    <h1 class="break-words text-xl font-bold text-neutral-900">{{ $nasabah->nama_lengkap }}</h1>
                    <p class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-neutral-500">
                        <span>ID: {{ $nasabah->kode() }}</span>
                        <span aria-hidden="true">&bull;</span>
                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 font-medium text-[#2e7d32]">
                            <x-icon name="check" class="size-3" />
                            Terverifikasi
                        </span>
                    </p>
                </div>
            </div>

            {{-- Form foto terpisah: otomatis terkirim setelah memilih berkas --}}
            <form id="foto-form" method="POST" action="{{ route('nasabah.profil.foto') }}" enctype="multipart/form-data" data-photo-form>
                @csrf
                <input type="file" name="foto" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" data-photo-input class="sr-only" tabindex="-1" aria-label="Pilih foto profil">
                <button type="button" data-photo-trigger class="inline-flex h-10 items-center rounded-lg border border-neutral-300 bg-white px-4 text-sm font-semibold text-neutral-800 transition hover:border-neutral-400 hover:bg-neutral-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#2e7d32]">Ubah Foto</button>
            </form>
        </div>

        <form id="profil-form" method="POST" action="{{ route('nasabah.profil.update') }}" novalidate data-wizard-form="profil" class="mt-6">
            @csrf

            <div data-error-summary role="alert" tabindex="-1" @if ($invalidCount === 0) hidden @endif class="mb-6 flex gap-3 rounded-lg bg-red-50 px-4 py-3 text-red-700">
                <x-icon name="alert-circle" class="mt-0.5 size-5 shrink-0" />
                <div class="text-sm">
                    <p data-error-summary-title class="font-semibold">Periksa {{ $invalidCount }} bagian yang ditandai.</p>
                    <p>Perbaiki data berikut sebelum menyimpan.</p>
                </div>
            </div>

            {{-- Data diri dan alamat --}}
            <h2 class="text-base font-bold text-neutral-900">Data Diri &amp; Alamat</h2>

            <div class="mt-4 grid gap-x-6 gap-y-4 sm:grid-cols-2">
                <div>
                    <label for="nama_lengkap" class="{{ $label }}">Nama Lengkap (Terkunci)</label>
                    <input id="nama_lengkap" type="text" value="{{ $nasabah->nama_lengkap }}" disabled class="mt-1.5 block h-10 w-full cursor-not-allowed rounded-lg border border-neutral-200 bg-slate-50 px-3.5 text-sm text-neutral-400">
                </div>

                <div>
                    <label for="nik" class="{{ $label }}">NIK (Terkunci)</label>
                    <input id="nik" type="text" value="{{ $nasabah->nik }}" disabled class="mt-1.5 block h-10 w-full cursor-not-allowed rounded-lg border border-neutral-200 bg-slate-50 px-3.5 text-sm tabular-nums text-neutral-400">
                </div>

                <div data-field="whatsapp" data-invalid="{{ $errors->has('whatsapp') ? 'true' : 'false' }}" class="group/field">
                    <label for="whatsapp" class="{{ $label }}">Nomor WhatsApp</label>
                    <input id="whatsapp" name="whatsapp" type="tel" inputmode="numeric" value="{{ $val('whatsapp', $waTampil) }}" required autocomplete="tel" data-control aria-describedby="whatsapp-error" class="{{ $input }} tabular-nums">
                    <x-field-message name="whatsapp" />
                </div>

                <div data-field="email" data-invalid="{{ $errors->has('email') ? 'true' : 'false' }}" class="group/field">
                    <label for="email" class="{{ $label }}">Email</label>
                    <input id="email" name="email" type="email" value="{{ $val('email', $nasabah->email) }}" required autocomplete="email" data-control aria-describedby="email-error" class="{{ $input }}">
                    <x-field-message name="email" :hint="'Email kontak. Email login Anda: ' . auth()->user()->email" />
                </div>

                <div data-field="alamat_lengkap" data-invalid="{{ $errors->has('alamat_lengkap') ? 'true' : 'false' }}" class="group/field sm:col-span-2">
                    <label for="alamat_lengkap" class="{{ $label }}">Alamat Penjemputan</label>
                    <input id="alamat_lengkap" name="alamat_lengkap" type="text" maxlength="255" value="{{ $val('alamat_lengkap', $nasabah->alamat_lengkap) }}" required autocomplete="street-address" data-control aria-describedby="alamat_lengkap-error" class="{{ $input }}">
                    <x-field-message name="alamat_lengkap" :hint="$wilayah . '. Untuk mengubah wilayah, hubungi Helpdesk.'" />
                </div>
            </div>

            <div class="my-8 h-px bg-neutral-100"></div>

            {{-- Rekening pencairan dan kata sandi --}}
            <h2 class="text-base font-bold text-neutral-900">Rekening Pencairan &amp; Kata Sandi</h2>

            <div class="mt-4 grid gap-x-6 gap-y-4 sm:grid-cols-2">
                <div data-field="metode_pencairan" data-invalid="{{ $errors->has('metode_pencairan') ? 'true' : 'false' }}" class="group/field">
                    <label for="metode_pencairan" class="{{ $label }}">Metode Pencairan Utama</label>
                    <div class="relative">
                        <select id="metode_pencairan" name="metode_pencairan" data-control aria-describedby="metode_pencairan-error" class="{{ $input }} cursor-pointer appearance-none pr-10">
                            <option value="">Pilih metode</option>
                            @foreach ($metodePencairan as $metode)
                                <option value="{{ $metode }}" @if ($val('metode_pencairan', $nasabah->metode_pencairan) === $metode) selected @endif>{{ $metode }}</option>
                            @endforeach
                        </select>
                        <x-icon name="chevron-down" class="pointer-events-none absolute right-3.5 top-1/2 mt-[3px] size-4 -translate-y-1/2 text-neutral-500" />
                    </div>
                    <x-field-message name="metode_pencairan" />
                </div>

                <div data-field="nomor_rekening" data-invalid="{{ $errors->has('nomor_rekening') ? 'true' : 'false' }}" class="group/field">
                    <label for="nomor_rekening" class="{{ $label }}">Nomor Rekening / E-Wallet</label>
                    <input id="nomor_rekening" name="nomor_rekening" type="text" inputmode="numeric" value="{{ $val('nomor_rekening', $nasabah->nomor_rekening) }}" autocomplete="off" data-control aria-describedby="nomor_rekening-error" class="{{ $input }} tabular-nums">
                    <x-field-message name="nomor_rekening" />
                </div>

                <div data-field="password" data-invalid="{{ $errors->has('password') ? 'true' : 'false' }}" class="group/field">
                    <label for="password" class="{{ $label }}">Kata Sandi Baru (Opsional)</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" placeholder="Kosongkan jika tidak diubah" data-control aria-describedby="password-error" class="{{ $input }}">
                    <x-field-message name="password" />
                </div>

                <div data-field="password_confirmation" data-invalid="false" class="group/field">
                    <label for="password_confirmation" class="{{ $label }}">Konfirmasi Kata Sandi</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" placeholder="Ulangi kata sandi baru" data-control aria-describedby="password_confirmation-error" class="{{ $input }}">
                    <x-field-message name="password_confirmation" />
                </div>
            </div>

            <div class="mt-8 flex flex-wrap items-center justify-between gap-4 border-t border-neutral-100 pt-6">
                {{-- Keluar memakai form terpisah di bawah (form tidak boleh bersarang) --}}
                <button type="submit" form="logout-form" class="inline-flex h-11 items-center rounded-lg border border-red-300 bg-white px-5 text-sm font-semibold text-red-600 transition hover:bg-red-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-500">Keluar Akun</button>

                <button type="submit" data-submit data-loading="false" class="group/btn relative inline-flex h-11 items-center justify-center rounded-lg bg-[#2e7d32] px-6 text-sm font-semibold text-white shadow-lg shadow-[#2e7d32]/20 transition duration-300 hover:-translate-y-0.5 hover:bg-[#276a2b] active:translate-y-0 disabled:cursor-not-allowed focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#2e7d32]">
                    <span class="group-data-[loading=true]/btn:invisible">Simpan Perubahan</span>
                    <span class="absolute inset-0 hidden place-items-center group-data-[loading=true]/btn:grid" aria-hidden="true">
                        <svg class="size-5 animate-spin" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity=".3" stroke-width="3"/><path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                    </span>
                </button>
            </div>
        </form>

        <form id="logout-form" method="POST" action="{{ route('logout') }}" class="hidden">
            @csrf
        </form>
    </div>
@endsection