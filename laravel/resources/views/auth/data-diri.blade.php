@extends('layouts.wizard')

@section('title', 'Pendaftaran Nasabah: Data Diri | E-Bank Sampah FILKOM')

@section('card')
    @php
        // $saved dikirim controller: data langkah ini yang sudah pernah diisi (untuk tombol "Kembali").
        $val = fn (string $key) => old($key, $saved[$key] ?? '');

        // Kotak kolom: border berubah hijau saat fokus dan merah saat tidak valid.
        $box = 'relative flex items-center rounded-xl border border-neutral-300 bg-white transition focus-within:border-[#2e7d32] focus-within:ring-4 focus-within:ring-[#2e7d32]/10 group-data-[invalid=true]/field:border-red-400 group-data-[invalid=true]/field:bg-red-50/40 group-data-[invalid=true]/field:focus-within:border-red-500 group-data-[invalid=true]/field:focus-within:ring-red-500/10';
        $input = 'h-11 w-full min-w-0 bg-transparent text-sm text-neutral-900 placeholder:text-neutral-400 focus:outline-none';

        // Berkas KTP yang sudah tersimpan sementara (jika pengguna kembali ke langkah ini).
        $ktpName = $saved['ktp_name'] ?? null;
        $ktpBytes = (int) ($saved['ktp_size'] ?? 0);
        $ktpSize = $ktpBytes >= 1048576
            ? number_format($ktpBytes / 1048576, 1, ',', '.') . ' MB'
            : number_format(max(1, $ktpBytes / 1024), 0, ',', '.') . ' KB';
        $showStoredKtp = $ktpName && ! $errors->has('foto_ktp');
        $invalidCount = count($errors->keys());
    @endphp

    <div class="flex items-center border-b border-neutral-200 pb-6">
        <div class="w-full">
            <x-wizard-steps :current="1" />
        </div>
    </div>

    <div class="mt-8 animate-fade-up">
        <p class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2e7d32]">Langkah 1 dari 3</p>
        <h1 class="mt-2 text-[1.9rem] font-normal leading-tight tracking-tight text-neutral-900">Kenalan dulu, yuk.</h1>
        <p class="mt-3 text-sm text-neutral-600">Isi data diri Anda dengan benar sesuai KTP untuk mulai menjadi nasabah.</p>
    </div>

    <form id="data-diri-form" method="POST" action="{{ route('nasabah.register.data-diri') }}" enctype="multipart/form-data" novalidate data-wizard-form="data-diri" class="mt-6">
        @csrf

        {{-- Ringkasan error. Ditampilkan server (jika ada error) atau oleh wizard.js. --}}
        <div data-error-summary role="alert" tabindex="-1" @if (! $errors->any()) hidden @endif class="mb-6 flex gap-3 rounded-lg bg-red-50 px-4 py-3 text-red-700">
            <x-icon name="alert-circle" class="mt-0.5 size-5 shrink-0" />
            <div class="text-sm">
                <p data-error-summary-title class="font-semibold">Periksa {{ $invalidCount }} bagian yang ditandai.</p>
                <p>Perbaiki data berikut sebelum melanjutkan.</p>
            </div>
        </div>

        <p class="text-xs text-neutral-500"><span class="text-red-600">*</span> Wajib diisi</p>

        <div class="mt-4 grid gap-x-6 gap-y-5 sm:grid-cols-2">

            {{-- Nama lengkap --}}
            <div data-field="nama_lengkap" data-invalid="{{ $errors->has('nama_lengkap') ? 'true' : 'false' }}" class="group/field">
                <label for="nama_lengkap" class="block text-sm font-medium text-neutral-900">Nama lengkap <span class="text-red-600" aria-hidden="true">*</span></label>
                <div class="mt-2 {{ $box }}">
                    <input id="nama_lengkap" name="nama_lengkap" type="text" value="{{ $val('nama_lengkap') }}" required autocomplete="name" data-control aria-describedby="nama_lengkap-error" class="{{ $input }} px-3.5">
                    <x-icon name="alert-circle" class="mr-3 hidden size-5 shrink-0 text-red-500 group-data-[invalid=true]/field:block" />
                </div>
                <x-field-message name="nama_lengkap" hint="Sesuai nama pada KTP." />
            </div>

            {{-- NIK --}}
            <div data-field="nik" data-invalid="{{ $errors->has('nik') ? 'true' : 'false' }}" class="group/field">
                <label for="nik" class="block text-sm font-medium text-neutral-900">NIK <span class="text-red-600" aria-hidden="true">*</span></label>
                <div class="mt-2 {{ $box }}">
                    <input id="nik" name="nik" type="text" inputmode="numeric" value="{{ $val('nik') }}" required autocomplete="off" data-control aria-describedby="nik-error" class="{{ $input }} px-3.5 tabular-nums">
                    <x-icon name="alert-circle" class="mr-3 hidden size-5 shrink-0 text-red-500 group-data-[invalid=true]/field:block" />
                </div>
                <x-field-message name="nik" hint="16 digit nomor pada KTP." />
            </div>

            {{-- Tanggal lahir: tampil "12 Mei 1998", memakai pemilih tanggal bawaan browser --}}
            <div data-field="tanggal_lahir" data-invalid="{{ $errors->has('tanggal_lahir') ? 'true' : 'false' }}" class="group/field">
                <label for="tanggal_lahir" class="block text-sm font-medium text-neutral-900">Tanggal lahir <span class="text-red-600" aria-hidden="true">*</span></label>
                <div class="mt-2 {{ $box }}">
                    <input id="tanggal_lahir" name="tanggal_lahir" type="date" min="1900-01-01" value="{{ $val('tanggal_lahir') }}" required data-control data-date-input aria-describedby="tanggal_lahir-error" class="absolute inset-0 z-10 size-full cursor-pointer opacity-0">
                    <span data-date-display data-placeholder="Pilih tanggal lahir" data-empty="true" class="flex h-11 flex-1 items-center px-3.5 text-sm text-neutral-900 data-[empty=true]:text-neutral-400">Pilih tanggal lahir</span>
                    <x-icon name="calendar" class="mr-3.5 size-5 shrink-0 text-neutral-500" />
                </div>
                <x-field-message name="tanggal_lahir" />
            </div>

            {{-- Jenis kelamin --}}
            <fieldset data-field="jenis_kelamin" data-invalid="{{ $errors->has('jenis_kelamin') ? 'true' : 'false' }}" class="group/field min-w-0">
                <legend class="block text-sm font-medium text-neutral-900">Jenis kelamin <span class="text-red-600" aria-hidden="true">*</span></legend>
                <div class="mt-2 grid grid-cols-2 gap-3">
                    @foreach (['Laki-laki', 'Perempuan'] as $gender)
                        <label class="group/radio flex h-[46px] cursor-pointer items-center gap-2.5 rounded-xl border border-neutral-300 bg-white px-3.5 text-sm text-neutral-900 transition hover:border-neutral-400 has-checked:border-[#2e7d32] has-checked:bg-[#eaf3ea] has-focus-visible:ring-4 has-focus-visible:ring-[#2e7d32]/20 group-data-[invalid=true]/field:border-red-400">
                            <input type="radio" name="jenis_kelamin" value="{{ $gender }}" @if ($val('jenis_kelamin') === $gender) checked @endif data-control class="sr-only">
                            <span class="grid size-[18px] shrink-0 place-items-center rounded-full border border-neutral-300 bg-white transition group-has-checked/radio:border-[#2e7d32]">
                                <span class="size-2.5 rounded-full bg-[#2e7d32] opacity-0 transition group-has-checked/radio:opacity-100"></span>
                            </span>
                            {{ $gender }}
                        </label>
                    @endforeach
                </div>
                <x-field-message name="jenis_kelamin" />
            </fieldset>

            {{-- Nomor WhatsApp --}}
            <div data-field="whatsapp" data-invalid="{{ $errors->has('whatsapp') ? 'true' : 'false' }}" class="group/field">
                <label for="whatsapp" class="block text-sm font-medium text-neutral-900">Nomor WhatsApp <span class="text-red-600" aria-hidden="true">*</span></label>
                <div class="mt-2 {{ $box }}">
                    <span class="pl-3.5 pr-2 text-sm text-neutral-500" aria-hidden="true">+62</span>
                    <input id="whatsapp" name="whatsapp" type="tel" inputmode="numeric" placeholder="812 3456 7890" value="{{ $val('whatsapp') }}" required autocomplete="tel-national" data-control aria-describedby="whatsapp-error" class="{{ $input }} pr-3.5 tabular-nums">
                    <x-icon name="alert-circle" class="mr-3 hidden size-5 shrink-0 text-red-500 group-data-[invalid=true]/field:block" />
                </div>
                <x-field-message name="whatsapp" hint="Gunakan nomor yang aktif." />
            </div>

            {{-- Email --}}
            <div data-field="email" data-invalid="{{ $errors->has('email') ? 'true' : 'false' }}" class="group/field">
                <label for="email" class="block text-sm font-medium text-neutral-900">Email <span class="text-red-600" aria-hidden="true">*</span></label>
                <div class="mt-2 {{ $box }}">
                    <input id="email" name="email" type="email" value="{{ $val('email') }}" required autocomplete="email" data-control aria-describedby="email-error" class="{{ $input }} px-3.5">
                    <x-icon name="alert-circle" class="mr-3 hidden size-5 shrink-0 text-red-500 group-data-[invalid=true]/field:block" />
                </div>
                <x-field-message name="email" hint="Untuk informasi dan pemulihan akun." />
            </div>

            {{-- Foto KTP --}}
            <div data-field="foto_ktp" data-invalid="{{ $errors->has('foto_ktp') ? 'true' : 'false' }}" data-stored="{{ $showStoredKtp ? 'true' : 'false' }}" class="group/field sm:col-span-2">
                <div class="flex flex-wrap items-end justify-between gap-x-4">
                    <p class="text-sm font-medium text-neutral-900" id="foto_ktp-label">Foto KTP <span class="text-red-600" aria-hidden="true">*</span></p>
                    <p class="text-xs text-neutral-500">JPG, PNG, atau PDF &bull; Maks. 5 MB</p>
                </div>

                <label data-drop-area class="mt-2 flex cursor-pointer flex-col items-center gap-1.5 rounded-xl border border-dashed border-[#2e7d32]/40 bg-[#f7f9f7] px-6 py-5 text-center transition hover:border-[#2e7d32]/70 hover:bg-[#eef4ee] has-focus-visible:ring-4 has-focus-visible:ring-[#2e7d32]/20 data-[dragging=true]:border-[#2e7d32] data-[dragging=true]:bg-[#e3efe3] group-data-[invalid=true]/field:border-red-400 group-data-[invalid=true]/field:bg-red-50/40">
                    <input id="foto_ktp" name="foto_ktp" type="file" accept=".jpg,.jpeg,.png,.pdf,image/jpeg,image/png,application/pdf" data-control data-file-input aria-labelledby="foto_ktp-label" aria-describedby="foto_ktp-error" class="sr-only">
                    <x-icon name="cloud-upload" class="size-6 text-[#2e7d32]" />
                    <span class="text-sm text-neutral-600"><span class="font-semibold text-[#2e7d32]">Pilih berkas</span> atau tarik berkas ke sini</span>
                    <span class="text-xs text-neutral-500">Pastikan seluruh KTP terlihat jelas dan terbaca.</span>
                </label>
                <input type="hidden" name="ktp_removed" value="0" data-ktp-removed>

                <div data-file-item data-state="ok" @if (! $showStoredKtp) hidden @endif class="group/file mt-3 flex items-center gap-3 rounded-lg bg-[#eef4ee] px-4 py-3 data-[state=error]:bg-red-50">
                    <x-icon name="file-check" class="size-5 shrink-0 text-[#2e7d32] group-data-[state=error]/file:hidden" />
                    <x-icon name="file-warning" class="hidden size-5 shrink-0 text-red-600 group-data-[state=error]/file:block" />
                    <div class="min-w-0 flex-1">
                        <p data-file-name class="truncate text-sm font-medium text-neutral-900">{{ $ktpName }}</p>
                        <p data-file-meta class="text-xs text-[#2e7d32] group-data-[state=error]/file:text-red-600">{{ $ktpSize }} &bull; Berhasil diunggah</p>
                    </div>
                    <button type="button" data-file-remove aria-label="Hapus berkas" class="grid size-8 shrink-0 place-items-center rounded-lg text-neutral-500 transition hover:bg-black/5 hover:text-neutral-900">
                        <x-icon name="x" class="size-4" />
                    </button>
                </div>

                <x-field-message name="foto_ktp" />
            </div>
        </div>

        {{-- Tombol --}}
        <div class="mt-8 flex items-center justify-between gap-4 border-t border-neutral-200 pt-6">
            <a href="{{ route('nasabah.register') }}" class="group inline-flex h-12 items-center gap-2 rounded-lg border border-[#2e7d32] px-5 text-sm font-semibold text-[#2e7d32] transition hover:bg-[#2e7d32]/5">
                <x-icon name="arrow-left" class="size-4 transition-transform duration-300 group-hover:-translate-x-0.5" />
                Kembali
            </a>

            <button type="submit" data-loading="false" class="group/btn group relative inline-flex h-12 items-center justify-center gap-2 rounded-lg bg-[#2e7d32] px-6 text-sm font-semibold text-white shadow-lg shadow-[#2e7d32]/20 transition duration-300 hover:-translate-y-0.5 hover:bg-[#276a2b] active:translate-y-0 disabled:cursor-not-allowed focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#2e7d32]">
                <span class="inline-flex items-center gap-2 group-data-[loading=true]/btn:invisible">
                    Lanjut
                    <x-icon name="arrow-right" class="size-4 transition-transform duration-300 group-hover:translate-x-0.5" />
                </span>
                <span class="absolute inset-0 hidden place-items-center group-data-[loading=true]/btn:grid" aria-hidden="true">
                    <svg class="size-5 animate-spin" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity=".3" stroke-width="3"/><path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                </span>
            </button>
        </div>

        <p class="mt-6 flex items-center justify-center gap-2 text-xs text-neutral-500">
            <x-icon name="shield-check" class="size-4" />
            Data Anda hanya digunakan untuk layanan bank sampah.
        </p>
    </form>
@endsection