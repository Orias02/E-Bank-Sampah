@extends('layouts.nasabah')

@section('title', 'Setor Sampah Baru | E-Bank Sampah FILKOM')

@section('breadcrumb')
    <nav aria-label="Breadcrumb" class="mb-4 text-sm">
        <a href="{{ route('dashboard') }}" class="text-neutral-500 transition-colors hover:text-neutral-900">Beranda</a>
    </nav>
@endsection

@section('card')
    @php
        // Dikirim SetorController: $nasabah (dengan bankSampah), $jenisSampah, $tanggalMin, $tanggalMax, $tanggalDefault.
        $metode = old('metode', '');
        $dipilih = array_map('strval', (array) old('jenis_sampah', []));
        $tanggal = old('tanggal_rencana', $tanggalDefault);

        // Total perkiraan berat dari isian sebelumnya (jika form dikembalikan karena error).
        $totalAwal = 0;
        foreach ($jenisSampah as $j) {
            if (in_array((string) $j->id, $dipilih, true)) {
                $totalAwal += (float) str_replace(',', '.', (string) old('berat.' . $j->id, 0));
            }
        }

        $metodeList = [
            'antar' => ['emoji' => '🚶', 'judul' => 'Antar ke Bank Sampah', 'teks' => 'Bawa langsung sampahmu ke lokasi pengepul E-Bank Sampah FILKOM.'],
            'jemput' => ['emoji' => '🚚', 'judul' => 'Minta Dijemput', 'teks' => 'Admin akan datang menjemput ke alamatmu (Minimal total berat 10 Kg).'],
        ];

        $bank = $nasabah->bankSampah;
        $alamatJemput = implode(', ', array_filter([$nasabah->alamat_lengkap, $nasabah->kelurahan, $nasabah->kecamatan, $nasabah->kota]));

        $formKeys = ['metode', 'jenis_sampah', 'berat', 'tanggal_rencana', 'catatan'];
        $invalidCount = count(array_filter($errors->keys(), fn ($key) => in_array(explode('.', $key)[0], $formKeys, true)));

        $judulSeksi = 'border-b border-neutral-100 pb-3 text-[0.95rem] font-semibold text-neutral-900';
    @endphp

    <div class="h-px bg-neutral-200"></div>

    <div class="mx-auto mt-6 w-full max-w-[43rem] flex-1 animate-fade-up">
        <header class="pt-6">
            <h1 class="text-[1.75rem] font-bold tracking-tight text-neutral-900">Setor Sampah Baru</h1>
            <p class="mt-2 text-sm text-slate-500">Pilih metode penyetoran dan beri tahu admin sampah apa saja yang akan kamu bawa.</p>
        </header>

        @if (session('status'))
            <div role="status" class="mt-6 flex items-start gap-3 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-[#2e7d32]">
                <x-icon name="circle-check" class="mt-0.5 size-5 shrink-0" />
                <p>{{ session('status') }}</p>
            </div>
        @endif

        @error('pengajuan')
            <div role="alert" class="mt-6 flex items-start gap-3 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700">
                <x-icon name="alert-circle" class="mt-0.5 size-5 shrink-0" />
                <p>{{ $message }}</p>
            </div>
        @enderror

        <form id="setor-form" method="POST" action="{{ route('nasabah.setor.store') }}" novalidate data-wizard-form="setor" class="mt-6 rounded-2xl border border-neutral-200 bg-white p-5 sm:p-8">
            @csrf

            <div data-error-summary role="alert" tabindex="-1" @if ($invalidCount === 0) hidden @endif class="mb-6 flex gap-3 rounded-lg bg-red-50 px-4 py-3 text-red-700">
                <x-icon name="alert-circle" class="mt-0.5 size-5 shrink-0" />
                <div class="text-sm">
                    <p data-error-summary-title class="font-semibold">Periksa {{ $invalidCount }} bagian yang ditandai.</p>
                    <p>Lengkapi data berikut sebelum mengirim.</p>
                </div>
            </div>

            {{-- 1. Metode penyetoran --}}
            <h2 id="metode-judul" class="{{ $judulSeksi }}">1. Pilih Metode Penyetoran</h2>
            <div data-field="metode" role="radiogroup" aria-labelledby="metode-judul" data-invalid="{{ $errors->has('metode') ? 'true' : 'false' }}" class="group/field mt-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach ($metodeList as $key => $item)
                        <label class="group/method relative flex cursor-pointer flex-col rounded-xl border-2 border-neutral-200 bg-white p-4 transition hover:border-neutral-300 has-checked:border-[#2e7d32] has-checked:bg-[#eaf3ea] has-focus-visible:ring-4 has-focus-visible:ring-[#2e7d32]/20 group-data-[invalid=true]/field:border-red-300">
                            <input type="radio" name="metode" value="{{ $key }}" @if ($metode === $key) checked @endif data-control class="sr-only">
                            <x-icon name="check" class="absolute right-4 top-4 size-4 text-[#2e7d32] opacity-0 transition group-has-checked/method:opacity-100" />
                            <span class="text-2xl" aria-hidden="true">{{ $item['emoji'] }}</span>
                            <span class="mt-3 text-sm font-semibold text-neutral-900">{{ $item['judul'] }}</span>
                            <span class="mt-1.5 text-xs leading-relaxed text-slate-500">{{ $item['teks'] }}</span>
                        </label>
                    @endforeach
                </div>
                <x-field-message name="metode" />

                {{-- Keterangan lokasi sesuai metode yang dipilih --}}
                <p data-metode-info="antar" @if ($metode !== 'antar') hidden @endif class="mt-3 flex items-start gap-2 text-xs text-neutral-600">
                    <x-icon name="pin" class="mt-px size-4 shrink-0 text-[#2e7d32]" />
                    <span>
                        @if ($bank)
                            Bank sampah Anda: <strong class="font-semibold text-neutral-800">{{ $bank->nama }}</strong>, {{ $bank->alamat }} ({{ $bank->jadwal }}).
                        @else
                            Antar ke bank sampah terdekat.
                        @endif
                    </span>
                </p>
                <p data-metode-info="jemput" @if ($metode !== 'jemput') hidden @endif class="mt-3 flex items-start gap-2 text-xs text-neutral-600">
                    <x-icon name="pin" class="mt-px size-4 shrink-0 text-[#2e7d32]" />
                    <span>Dijemput di: <strong class="font-semibold text-neutral-800">{{ $alamatJemput }}</strong>. <a href="{{ route('nasabah.profil') }}" class="font-semibold text-[#2e7d32] hover:underline">Ubah di Profil</a></span>
                </p>
            </div>

            {{-- 2. Kategori sampah --}}
            <h2 id="kategori-judul" class="{{ $judulSeksi }} mt-8">2. Kategori Sampah (Pilih yang dibawa)</h2>
            <div data-field="jenis_sampah" role="group" aria-labelledby="kategori-judul" data-invalid="{{ $errors->has('jenis_sampah') ? 'true' : 'false' }}" class="group/field mt-4">
                @if ($jenisSampah->isEmpty())
                    <p class="rounded-xl border border-dashed border-neutral-300 bg-[#f7f9f7] px-4 py-4 text-sm text-neutral-600">Kategori sampah belum tersedia. Hubungi Helpdesk untuk bantuan.</p>
                @else
                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach ($jenisSampah as $jenis)
                            <label class="group/cat flex cursor-pointer items-center gap-3 rounded-xl border border-neutral-200 bg-white px-4 py-3.5 text-sm text-neutral-900 transition hover:border-neutral-300 has-checked:border-[#2e7d32] has-checked:bg-[#eaf3ea] has-focus-visible:ring-4 has-focus-visible:ring-[#2e7d32]/20 group-data-[invalid=true]/field:border-red-300">
                                <input type="checkbox" name="jenis_sampah[]" value="{{ $jenis->id }}" @if (in_array((string) $jenis->id, $dipilih, true)) checked @endif data-control class="sr-only">
                                <span class="grid size-[18px] shrink-0 place-items-center rounded border border-neutral-300 bg-white text-white transition group-has-checked/cat:border-[#2e7d32] group-has-checked/cat:bg-[#2e7d32]">
                                    <x-icon name="check" class="size-3 opacity-0 transition group-has-checked/cat:opacity-100" />
                                </span>
                                <x-icon :name="$jenis->ikon" class="size-5 shrink-0 text-neutral-700" />
                                <span class="min-w-0 font-medium leading-snug">{{ $jenis->nama }}</span>
                            </label>
                        @endforeach
                    </div>
                @endif
                <x-field-message name="jenis_sampah" />
            </div>


            {{-- Perkiraan berat per kategori yang dipilih --}}
            <div data-field="berat" data-invalid="{{ $errors->has('berat') ? 'true' : 'false' }}" class="group/field mt-6">
                <p id="berat-judul" class="text-sm font-semibold text-neutral-800">Perkiraan Berat</p>
                <p class="mt-1 text-xs text-slate-500">Isi perkiraan berat tiap kategori. Admin akan menimbang ulang saat sampah diterima.</p>

                <p data-berat-empty @if ($dipilih) hidden @endif class="mt-3 rounded-xl border border-dashed border-neutral-300 bg-[#f7f9f7] px-4 py-3.5 text-sm text-neutral-500">Pilih kategori di atas untuk mengisi perkiraan beratnya.</p>

                <div class="mt-3 space-y-3">
                    @foreach ($jenisSampah as $jenis)
                        <div data-berat-row data-nama="{{ $jenis->nama }}" @if (! in_array((string) $jenis->id, $dipilih, true)) hidden @endif class="flex items-center gap-3">
                            <label for="berat-{{ $jenis->id }}" class="flex min-w-0 flex-1 items-center gap-2.5 text-sm text-neutral-800">
                                <x-icon :name="$jenis->ikon" class="size-5 shrink-0 text-neutral-600" />
                                <span class="min-w-0 leading-snug">{{ $jenis->nama }}</span>
                            </label>
                            <div class="relative w-36 shrink-0">
                                <input id="berat-{{ $jenis->id }}" name="berat[{{ $jenis->id }}]" type="text" inputmode="decimal" autocomplete="off" placeholder="0" value="{{ old('berat.' . $jenis->id) }}" data-control data-berat aria-describedby="berat-error" class="block h-10 w-full rounded-lg border border-neutral-300 bg-white pl-3.5 pr-10 text-right text-sm tabular-nums text-neutral-900 placeholder:text-neutral-400 transition focus:border-[#2e7d32] focus:outline-none focus:ring-4 focus:ring-[#2e7d32]/10 aria-invalid:border-red-400 aria-invalid:bg-red-50/40">
                                <span class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-xs text-neutral-500" aria-hidden="true">kg</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div data-total-box @if (! $dipilih) hidden @endif class="mt-4 rounded-xl bg-[#f7f9f7] px-4 py-3.5 text-sm">
                    <p class="flex items-center justify-between gap-4">
                        <span class="text-neutral-600">Total perkiraan berat</span>
                        <span data-total-value class="font-bold tabular-nums text-neutral-900">{{ rtrim(rtrim(number_format($totalAwal, 2, ',', '.'), '0'), ',') }} kg</span>
                    </p>
                    <p data-jemput-note data-ok="false" @if ($metode !== 'jemput') hidden @endif class="group/note mt-1.5 flex items-center gap-1.5 text-xs text-amber-600 data-[ok=true]:text-[#2e7d32]">
                        <x-icon name="alert-circle" class="size-4 shrink-0 group-data-[ok=true]/note:hidden" />
                        <x-icon name="circle-check" class="hidden size-4 shrink-0 group-data-[ok=true]/note:block" />
                        <span>Penjemputan memerlukan total minimal 10 kg.</span>
                    </p>
                </div>

                <x-field-message name="berat" />
            </div>

            {{-- 3. Jadwal dan keterangan --}}
            <h2 class="{{ $judulSeksi }} mt-8">3. Jadwal &amp; Keterangan</h2>
            <div class="mt-4 space-y-5">
                <div data-field="tanggal_rencana" data-invalid="{{ $errors->has('tanggal_rencana') ? 'true' : 'false' }}" class="group/field">
                    <label for="tanggal_rencana" class="block text-sm font-semibold text-neutral-800">Rencana Tanggal Setor</label>
                    <div class="relative mt-2 flex h-11 items-center rounded-lg border border-neutral-300 bg-white transition focus-within:border-[#2e7d32] focus-within:ring-4 focus-within:ring-[#2e7d32]/10 group-data-[invalid=true]/field:border-red-400 group-data-[invalid=true]/field:bg-red-50/40">
                        <input id="tanggal_rencana" name="tanggal_rencana" type="date" min="{{ $tanggalMin }}" max="{{ $tanggalMax }}" value="{{ $tanggal }}" required data-control data-date-input aria-describedby="tanggal_rencana-error" class="absolute inset-0 z-10 size-full cursor-pointer opacity-0">
                        <span data-date-display data-placeholder="Pilih tanggal setor" data-empty="true" class="flex h-full flex-1 items-center px-3.5 text-sm text-neutral-900 data-[empty=true]:text-neutral-400">Pilih tanggal setor</span>
                        <x-icon name="calendar" class="mr-3.5 size-5 shrink-0 text-neutral-500" />
                    </div>
                    <x-field-message name="tanggal_rencana" />
                </div>

                <div data-field="catatan" data-invalid="{{ $errors->has('catatan') ? 'true' : 'false' }}" class="group/field">
                    <label for="catatan" class="block text-sm font-semibold text-neutral-800">Catatan Tambahan (Opsional)</label>
                    <textarea id="catatan" name="catatan" rows="3" maxlength="500" placeholder="Contoh: Sampah kardus sudah diikat rapi, ditaruh di depan kosan." data-control aria-describedby="catatan-error" class="mt-2 block min-h-24 w-full resize-y rounded-lg border border-neutral-300 bg-white px-3.5 py-3 text-sm text-neutral-900 placeholder:text-neutral-400 transition focus:border-[#2e7d32] focus:outline-none focus:ring-4 focus:ring-[#2e7d32]/10 group-data-[invalid=true]/field:border-red-400">{{ old('catatan') }}</textarea>
                    <x-field-message name="catatan" />
                </div>
            </div>

            {{-- Tombol --}}
            <div class="mt-8 flex flex-wrap items-center justify-between gap-4 border-t border-neutral-100 pt-6">
                <a href="{{ route('dashboard') }}" class="inline-flex h-11 items-center rounded-lg border border-neutral-300 px-5 text-sm font-semibold text-neutral-700 transition hover:bg-neutral-50">Batal</a>

                <button type="submit" data-loading="false" class="group/btn relative inline-flex h-11 items-center justify-center rounded-lg bg-[#2e7d32] px-6 text-sm font-semibold text-white shadow-lg shadow-[#2e7d32]/20 transition duration-300 hover:-translate-y-0.5 hover:bg-[#276a2b] active:translate-y-0 disabled:cursor-not-allowed focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#2e7d32]">
                    <span class="group-data-[loading=true]/btn:invisible">Kirim Pengajuan</span>
                    <span class="absolute inset-0 hidden place-items-center group-data-[loading=true]/btn:grid" aria-hidden="true">
                        <svg class="size-5 animate-spin" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity=".3" stroke-width="3"/><path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                    </span>
                </button>
            </div>
        </form>
    </div>
@endsection