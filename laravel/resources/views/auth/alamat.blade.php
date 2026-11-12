@extends('layouts.wizard')

@section('title', 'Pendaftaran Nasabah: Alamat | E-Bank Sampah FILKOM')

@section('card')
    @php
        // Dikirim controller: $saved (isian yang pernah disimpan), $wilayah (config/wilayah.php), $banks (tabel bank_sampah).
        $val = fn (string $key) => old($key, $saved[$key] ?? '');

        $kota = $val('kota');
        $kecamatan = $val('kecamatan');
        $kelurahan = $val('kelurahan');
        $bankId = (string) $val('bank_sampah_id');

        $kecamatanList = array_keys($wilayah[$kota] ?? []);
        $kelurahanList = $wilayah[$kota][$kecamatan] ?? [];

        $box = 'relative flex items-center rounded-xl border border-neutral-300 bg-white transition focus-within:border-[#2e7d32] focus-within:ring-4 focus-within:ring-[#2e7d32]/10 has-disabled:bg-neutral-50 group-data-[invalid=true]/field:border-red-400 group-data-[invalid=true]/field:bg-red-50/40 group-data-[invalid=true]/field:focus-within:border-red-500 group-data-[invalid=true]/field:focus-within:ring-red-500/10';
        $input = 'h-11 w-full min-w-0 bg-transparent text-sm text-neutral-900 placeholder:text-neutral-400 focus:outline-none';
        $select = $input . ' cursor-pointer appearance-none pl-3.5 pr-10 disabled:cursor-not-allowed disabled:text-neutral-400';

        $invalidCount = count($errors->keys());

        // Bank yang melayani kelurahan terpilih. Kelurahan kosong pada bank = melayani semua kelurahan.
        $bankVisible = fn ($bank) => $kelurahan !== '' && ($bank->kelurahan === null || $bank->kelurahan === $kelurahan);
        $visibleCount = $banks->filter($bankVisible)->count();
    @endphp

    <div class="flex items-center border-b border-neutral-200 pb-6">
        <div class="w-full">
            <x-wizard-steps :current="2" />
        </div>
    </div>

    <div class="mt-8 animate-fade-up">
        <p class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2e7d32]">Langkah 2 dari 3</p>
        <h1 class="mt-2 text-[1.9rem] font-normal leading-tight tracking-tight text-neutral-900">Di mana Anda tinggal?</h1>
        <p class="mt-3 text-sm text-neutral-600">Alamat dipakai untuk menghubungkan Anda dengan bank sampah terdekat.</p>
    </div>

    <form id="alamat-form" method="POST" action="{{ route('nasabah.register.alamat') }}" novalidate data-wizard-form="alamat" class="mt-6">
        @csrf

        {{-- Data wilayah untuk dropdown bertingkat (dibaca resources/js/wizard.js) --}}
        <script type="application/json" id="wilayah-data">{!! json_encode($wilayah, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) !!}</script>

        <div data-error-summary role="alert" tabindex="-1" @if (! $errors->any()) hidden @endif class="mb-6 flex gap-3 rounded-lg bg-red-50 px-4 py-3 text-red-700">
            <x-icon name="alert-circle" class="mt-0.5 size-5 shrink-0" />
            <div class="text-sm">
                <p data-error-summary-title class="font-semibold">Periksa {{ $invalidCount }} bagian yang ditandai.</p>
                <p>Perbaiki data berikut sebelum melanjutkan.</p>
            </div>
        </div>

        <p class="text-xs text-neutral-500"><span class="text-red-600">*</span> Wajib diisi</p>

        <div class="mt-4 grid gap-x-6 gap-y-5 sm:grid-cols-2">

            {{-- Kota --}}
            <div data-field="kota" data-invalid="{{ $errors->has('kota') ? 'true' : 'false' }}" class="group/field">
                <label for="kota" class="block text-sm font-medium text-neutral-900">Kota <span class="text-red-600" aria-hidden="true">*</span></label>
                <div class="mt-2 {{ $box }}">
                    <select id="kota" name="kota" required data-control aria-describedby="kota-error" class="{{ $select }}">
                        <option value="">Pilih kota</option>
                        @foreach (array_keys($wilayah) as $name)
                            <option value="{{ $name }}" @if ($kota === $name) selected @endif>{{ $name }}</option>
                        @endforeach
                    </select>
                    <x-icon name="chevron-down" class="pointer-events-none absolute right-3.5 size-4 text-neutral-500" />
                </div>
                <x-field-message name="kota" />
            </div>

            {{-- Kecamatan --}}
            <div data-field="kecamatan" data-invalid="{{ $errors->has('kecamatan') ? 'true' : 'false' }}" class="group/field">
                <label for="kecamatan" class="block text-sm font-medium text-neutral-900">Kecamatan <span class="text-red-600" aria-hidden="true">*</span></label>
                <div class="mt-2 {{ $box }}">
                    <select id="kecamatan" name="kecamatan" required data-control @if (! $kecamatanList) disabled @endif aria-describedby="kecamatan-error" class="{{ $select }}">
                        <option value="">{{ $kecamatanList ? 'Pilih kecamatan' : 'Pilih kota dulu' }}</option>
                        @foreach ($kecamatanList as $name)
                            <option value="{{ $name }}" @if ($kecamatan === $name) selected @endif>{{ $name }}</option>
                        @endforeach
                    </select>
                    <x-icon name="chevron-down" class="pointer-events-none absolute right-3.5 size-4 text-neutral-500" />
                </div>
                <x-field-message name="kecamatan" />
            </div>

            {{-- Kelurahan --}}
            <div data-field="kelurahan" data-invalid="{{ $errors->has('kelurahan') ? 'true' : 'false' }}" class="group/field">
                <label for="kelurahan" class="block text-sm font-medium text-neutral-900">Kelurahan <span class="text-red-600" aria-hidden="true">*</span></label>
                <div class="mt-2 {{ $box }}">
                    <select id="kelurahan" name="kelurahan" required data-control @if (! $kelurahanList) disabled @endif aria-describedby="kelurahan-error" class="{{ $select }}">
                        <option value="">{{ $kelurahanList ? 'Pilih kelurahan' : 'Pilih kecamatan dulu' }}</option>
                        @foreach ($kelurahanList as $name)
                            <option value="{{ $name }}" @if ($kelurahan === $name) selected @endif>{{ $name }}</option>
                        @endforeach
                    </select>
                    <x-icon name="chevron-down" class="pointer-events-none absolute right-3.5 size-4 text-neutral-500" />
                </div>
                <x-field-message name="kelurahan" />
            </div>

            {{-- RT dan RW --}}
            <div class="grid grid-cols-2 gap-3">
                @foreach (['rt' => 'RT', 'rw' => 'RW'] as $key => $label)
                    <div data-field="{{ $key }}" data-invalid="{{ $errors->has($key) ? 'true' : 'false' }}" class="group/field">
                        <label for="{{ $key }}" class="block text-sm font-medium text-neutral-900">{{ $label }} <span class="text-red-600" aria-hidden="true">*</span></label>
                        <div class="mt-2 {{ $box }}">
                            <input id="{{ $key }}" name="{{ $key }}" type="text" inputmode="numeric" placeholder="001" value="{{ $val($key) }}" required autocomplete="off" data-control data-rtrw aria-describedby="{{ $key }}-error" class="{{ $input }} px-3.5 tabular-nums">
                        </div>
                        <x-field-message :name="$key" />
                    </div>
                @endforeach
            </div>

            {{-- Alamat lengkap --}}
            <div data-field="alamat_lengkap" data-invalid="{{ $errors->has('alamat_lengkap') ? 'true' : 'false' }}" class="group/field sm:col-span-2">
                <label for="alamat_lengkap" class="block text-sm font-medium text-neutral-900">Alamat lengkap <span class="text-red-600" aria-hidden="true">*</span></label>
                <div class="mt-2 {{ $box }}">
                    <textarea id="alamat_lengkap" name="alamat_lengkap" rows="3" maxlength="255" required autocomplete="street-address" data-control aria-describedby="alamat_lengkap-error" placeholder="Nama jalan, nomor rumah, patokan" class="min-h-24 w-full min-w-0 resize-y bg-transparent px-3.5 py-3 text-sm text-neutral-900 placeholder:text-neutral-400 focus:outline-none">{{ $val('alamat_lengkap') }}</textarea>
                </div>
                <x-field-message name="alamat_lengkap" hint="Tulis nama jalan, nomor rumah, dan patokan terdekat." />
            </div>

            {{-- Bank sampah --}}
            <fieldset data-field="bank_sampah_id" data-invalid="{{ $errors->has('bank_sampah_id') ? 'true' : 'false' }}" class="group/field min-w-0 sm:col-span-2">
                <legend class="block text-sm font-medium text-neutral-900">Bank sampah terdekat <span class="text-red-600" aria-hidden="true">*</span></legend>

                {{-- Pesan sebelum kelurahan dipilih / jika tidak ada bank --}}
                <p data-bank-placeholder @if ($kelurahan !== '') hidden @endif class="mt-2 flex items-center gap-2 rounded-xl border border-dashed border-neutral-300 bg-[#f7f9f7] px-4 py-4 text-sm text-neutral-500">
                    <x-icon name="pin" class="size-5 shrink-0 text-neutral-400" />
                    Pilih kelurahan terlebih dahulu untuk melihat bank sampah terdekat.
                </p>
                <p data-bank-empty @if ($kelurahan === '' || $visibleCount > 0) hidden @endif class="mt-2 flex items-center gap-2 rounded-xl border border-dashed border-neutral-300 bg-[#f7f9f7] px-4 py-4 text-sm text-neutral-600">
                    <x-icon name="alert-circle" class="size-5 shrink-0 text-neutral-400" />
                    Belum ada bank sampah di kelurahan ini. Hubungi Helpdesk untuk bantuan.
                </p>

                <div data-bank-list class="mt-2 grid gap-3 sm:grid-cols-2">
                    @foreach ($banks as $bank)
                        <label data-bank data-kelurahan="{{ $bank->kelurahan }}" @if (! $bankVisible($bank)) hidden @endif class="group/radio flex cursor-pointer gap-3 rounded-xl border border-neutral-300 bg-white p-4 transition hover:border-neutral-400 has-checked:border-[#2e7d32] has-checked:bg-[#eaf3ea] has-focus-visible:ring-4 has-focus-visible:ring-[#2e7d32]/20 group-data-[invalid=true]/field:border-red-400">
                            <input type="radio" name="bank_sampah_id" value="{{ $bank->id }}" @if ($bankId === (string) $bank->id) checked @endif data-control class="sr-only">
                            <span class="mt-0.5 grid size-[18px] shrink-0 place-items-center rounded-full border border-neutral-300 bg-white transition group-has-checked/radio:border-[#2e7d32]">
                                <span class="size-2.5 rounded-full bg-[#2e7d32] opacity-0 transition group-has-checked/radio:opacity-100"></span>
                            </span>
                            <span class="min-w-0">
                                <span class="block text-sm font-semibold text-neutral-900">{{ $bank->nama }}</span>
                                <span class="mt-1.5 flex items-start gap-1.5 text-xs text-neutral-600">
                                    <x-icon name="pin" class="mt-px size-3.5 shrink-0 text-neutral-400" />
                                    {{ $bank->alamat }}
                                </span>
                                <span class="mt-1 flex items-start gap-1.5 text-xs text-neutral-600">
                                    <x-icon name="clock" class="mt-px size-3.5 shrink-0 text-neutral-400" />
                                    {{ $bank->jadwal }}
                                </span>
                            </span>
                        </label>
                    @endforeach
                </div>
                <x-field-message name="bank_sampah_id" />
            </fieldset>
        </div>

        {{-- Tombol --}}
        <div class="mt-8 flex items-center justify-between gap-4 border-t border-neutral-200 pt-6">
            <a href="{{ route('nasabah.register.data-diri') }}" class="group inline-flex h-12 items-center gap-2 rounded-lg border border-[#2e7d32] px-5 text-sm font-semibold text-[#2e7d32] transition hover:bg-[#2e7d32]/5">
                <x-icon name="arrow-left" class="size-4 transition-transform duration-300 group-hover:-translate-x-0.5" />
                Kembali
            </a>

            <button type="submit" data-loading="false" class="group/btn group relative inline-flex h-12 items-center justify-center gap-2 rounded-lg bg-[#2e7d32] px-6 text-sm font-semibold text-white shadow-lg shadow-[#2e7d32]/20 transition duration-300 hover:-translate-y-0.5 hover:bg-[#276a2b] active:translate-y-0 disabled:cursor-not-allowed focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#2e7d32]">
                <span class="inline-flex items-center gap-2 group-data-[loading=true]/btn:invisible">
                    Selesaikan Pendaftaran
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