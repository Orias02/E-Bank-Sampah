@extends('layouts.admin')

@php
    // Dikirim Admin\JenisSampahController: $jenis (model, baru atau lama), $daftarIkon, $daftarKategori.
    $ubah = $jenis->exists;
    $judul = $ubah ? 'Ubah Jenis Sampah' : 'Tambah Jenis Sampah';
    $invalidCount = collect(['nama', 'kategori', 'deskripsi', 'harga_per_kg', 'ikon'])->filter(fn ($k) => $errors->has($k))->count();
    $hargaAwal = old('harga_per_kg', $jenis->harga_per_kg);
    $hargaAwal = $hargaAwal !== null && $hargaAwal !== '' ? number_format((int) preg_replace('/\D+/', '', (string) $hargaAwal), 0, ',', '.') : '';
    $ikonAwal = old('ikon', $jenis->ikon ?: 'recycle');
    $kotak = 'mt-2 block h-11 w-full rounded-lg border border-neutral-300 bg-white px-3.5 text-sm text-neutral-900 placeholder:text-neutral-400 transition focus:border-[#2e7d32] focus:outline-none focus:ring-4 focus:ring-[#2e7d32]/10 group-data-[invalid=true]/field:border-red-400';
    $label = 'block text-sm font-semibold text-neutral-800';
@endphp

@section('title', $judul . ' | Admin E-Bank Sampah FILKOM')

@section('breadcrumb')
    <nav aria-label="Breadcrumb" class="mb-4 flex items-center gap-2 text-sm">
        <a href="{{ route('admin.harga.index') }}" class="text-neutral-500 transition-colors hover:text-neutral-900">Harga Sampah</a>
        <x-icon name="chevron-right" class="size-4 text-neutral-400" />
        <span aria-current="page" class="font-medium text-neutral-900">{{ $judul }}</span>
    </nav>
@endsection

@section('content')
    <div class="mx-auto max-w-2xl">
        <header class="mb-6">
            <h1 class="text-[1.75rem] font-bold tracking-tight text-neutral-900">{{ $judul }}</h1>
            <p class="mt-2 text-sm text-slate-500">
                @if ($ubah)
                    Perubahan harga hanya berlaku untuk setoran berikutnya. Setoran yang sudah tercatat tidak berubah.
                @else
                    Jenis baru langsung tampil di katalog dan form setor nasabah.
                @endif
            </p>
        </header>

        <form method="POST" action="{{ $ubah ? route('admin.harga.update', $jenis->id) : route('admin.harga.store') }}" novalidate data-wizard-form="harga" class="rounded-2xl border border-neutral-200 bg-white p-5 sm:p-8">
            @csrf
            @if ($ubah) @method('PUT') @endif

            <div data-error-summary role="alert" tabindex="-1" @if ($invalidCount === 0) hidden @endif class="mb-6 flex gap-3 rounded-lg bg-red-50 px-4 py-3 text-red-700">
                <x-icon name="alert-circle" class="mt-0.5 size-5 shrink-0" />
                <div class="text-sm">
                    <p data-error-summary-title class="font-semibold">Periksa {{ $invalidCount }} bagian yang ditandai.</p>
                    <p>Lengkapi data berikut sebelum menyimpan.</p>
                </div>
            </div>

            <div class="space-y-6">
                <div data-field="nama" data-invalid="{{ $errors->has('nama') ? 'true' : 'false' }}" class="group/field">
                    <label for="nama" class="{{ $label }}">Nama jenis sampah</label>
                    <input id="nama" name="nama" type="text" maxlength="100" required value="{{ old('nama', $jenis->nama) }}" placeholder="Contoh: Botol Plastik PET Bening" data-control aria-describedby="nama-error" class="{{ $kotak }}">
                    <x-field-message name="nama" />
                </div>

                <div data-field="kategori" data-invalid="{{ $errors->has('kategori') ? 'true' : 'false' }}" class="group/field">
                    <label for="kategori" class="{{ $label }}">Kategori</label>
                    <input id="kategori" name="kategori" type="text" maxlength="30" required list="daftar-kategori" value="{{ old('kategori', $jenis->kategori) }}" placeholder="Contoh: Plastik" data-control aria-describedby="kategori-error" class="{{ $kotak }}">
                    <datalist id="daftar-kategori">
                        @foreach ($daftarKategori as $kategori)
                            <option value="{{ $kategori }}"></option>
                        @endforeach
                    </datalist>
                    <x-field-message name="kategori" hint="Pilih kategori yang sudah ada atau ketik kategori baru. Dipakai sebagai filter di katalog." />
                </div>

                <div data-field="harga_per_kg" data-invalid="{{ $errors->has('harga_per_kg') ? 'true' : 'false' }}" class="group/field">
                    <label for="harga_per_kg" class="{{ $label }}">Harga per kg</label>
                    <div class="relative mt-2">
                        <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm font-medium text-neutral-500" aria-hidden="true">Rp</span>
                        <input id="harga_per_kg" name="harga_per_kg" type="text" inputmode="numeric" autocomplete="off" required value="{{ $hargaAwal }}" placeholder="0" data-control aria-describedby="harga_per_kg-error" class="{{ str_replace('mt-2', 'mt-0', $kotak) }} pl-10 tabular-nums">
                    </div>
                    @if ($ubah && $jenis->harga_sebelumnya)
                        <x-field-message name="harga_per_kg" :hint="'Harga sebelumnya Rp ' . number_format($jenis->harga_sebelumnya, 0, ',', '.') . '.'" />
                    @else
                        <x-field-message name="harga_per_kg" />
                    @endif
                </div>

                <div data-field="deskripsi" data-invalid="{{ $errors->has('deskripsi') ? 'true' : 'false' }}" class="group/field">
                    <label for="deskripsi" class="{{ $label }}">Deskripsi <span class="font-normal text-neutral-500">(opsional)</span></label>
                    <textarea id="deskripsi" name="deskripsi" rows="3" maxlength="500" data-control placeholder="Contoh: Botol air mineral bening, sudah dibilas dan dilepas tutupnya." aria-describedby="deskripsi-error" class="mt-2 block min-h-24 w-full resize-y rounded-lg border border-neutral-300 bg-white px-3.5 py-3 text-sm text-neutral-900 placeholder:text-neutral-400 transition focus:border-[#2e7d32] focus:outline-none focus:ring-4 focus:ring-[#2e7d32]/10 group-data-[invalid=true]/field:border-red-400">{{ old('deskripsi', $jenis->deskripsi) }}</textarea>
                    <x-field-message name="deskripsi" hint="Tampil di katalog sebagai panduan kondisi sampah yang diterima." />
                </div>

                <div data-field="ikon" role="radiogroup" aria-labelledby="ikon-judul" data-invalid="{{ $errors->has('ikon') ? 'true' : 'false' }}" class="group/field">
                    <p id="ikon-judul" class="{{ $label }}">Ikon</p>
                    <div class="mt-3 grid grid-cols-3 gap-3 sm:grid-cols-6">
                        @foreach ($daftarIkon as $nama => $namaIkon)
                            <label class="group/ikon relative flex cursor-pointer flex-col items-center gap-2 rounded-xl border-2 border-neutral-200 bg-white px-2 py-3 text-center transition hover:border-neutral-300 has-checked:border-[#2e7d32] has-checked:bg-[#eaf3ea] has-focus-visible:ring-4 has-focus-visible:ring-[#2e7d32]/20">
                                <input type="radio" name="ikon" value="{{ $nama }}" @if ($ikonAwal === $nama) checked @endif data-control class="sr-only">
                                <x-icon :name="$nama" class="size-6 text-neutral-600 group-has-checked/ikon:text-[#2e7d32]" />
                                <span class="text-[0.7rem] font-medium leading-tight text-neutral-600">{{ $namaIkon }}</span>
                            </label>
                        @endforeach
                    </div>
                    <x-field-message name="ikon" />
                </div>
            </div>

            <div class="mt-8 flex flex-wrap items-center justify-between gap-4 border-t border-neutral-100 pt-6">
                <a href="{{ route('admin.harga.index') }}" class="inline-flex h-11 items-center rounded-lg border border-neutral-300 px-5 text-sm font-semibold text-neutral-700 transition hover:bg-neutral-50">Batal</a>

                <button type="submit" data-loading="false" class="group/btn relative inline-flex h-11 items-center justify-center rounded-lg bg-[#2e7d32] px-6 text-sm font-semibold text-white shadow-lg shadow-[#2e7d32]/20 transition duration-300 hover:-translate-y-0.5 hover:bg-[#276a2b] active:translate-y-0 disabled:cursor-not-allowed focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#2e7d32]">
                    <span class="group-data-[loading=true]/btn:invisible">{{ $ubah ? 'Simpan Perubahan' : 'Tambah ke Katalog' }}</span>
                    <span class="absolute inset-0 hidden place-items-center group-data-[loading=true]/btn:grid" aria-hidden="true">
                        <svg class="size-5 animate-spin" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity=".3" stroke-width="3"/><path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                    </span>
                </button>
            </div>
        </form>
    </div>
@endsection