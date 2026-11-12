@extends('layouts.nasabah')

@section('title', 'Tarik Saldo | E-Bank Sampah FILKOM')

@section('breadcrumb')
    <nav aria-label="Breadcrumb" class="mb-4 flex items-center gap-2 text-sm">
        <a href="{{ route('dashboard') }}" class="text-neutral-500 transition-colors hover:text-neutral-900">Beranda</a>
        <x-icon name="chevron-right" class="size-4 text-neutral-400" />
        <span aria-current="page" class="font-medium text-neutral-900">Tarik Saldo</span>
    </nav>
@endsection

@section('card')
    @php
        // Dikirim TarikController@show: $nasabah, $saldo (rupiah), $minTarik, $pilihanCepat (daftar nominal), $metodePencairan.
        $rupiah = fn ($angka) => number_format($angka, 0, ',', '.');
        $digits = fn ($teks) => preg_replace('/\D+/', '', (string) $teks);

        $nominalAwal = $digits(old('nominal'));
        $nominalTampil = $nominalAwal !== '' ? $rupiah((int) $nominalAwal) : '';
        $tujuanAwal = old('tujuan', $nasabah->metode_pencairan);
        $nomorAwal = trim(chunk_split($digits(old('nomor_tujuan', $nasabah->nomor_rekening)), 4, ' '));
        $namaAwal = old('nama_pemilik', $nasabah->nama_lengkap);

        $saldoCukup = $saldo >= $minTarik;
        $sisaAwal = $nominalAwal !== '' && (int) $nominalAwal <= $saldo ? $saldo - (int) $nominalAwal : $saldo;

        $formKeys = ['nominal', 'tujuan', 'nomor_tujuan', 'nama_pemilik'];
        $invalidCount = count(array_intersect($errors->keys(), $formKeys));

        $label = 'block text-sm font-semibold text-neutral-800';
        $input = 'mt-2 block h-12 w-full rounded-lg border border-neutral-300 bg-white px-4 text-sm text-neutral-900 placeholder:text-neutral-400 transition focus:border-[#2e7d32] focus:outline-none focus:ring-4 focus:ring-[#2e7d32]/10 group-data-[invalid=true]/field:border-red-400 group-data-[invalid=true]/field:bg-red-50/40 group-data-[invalid=true]/field:focus:border-red-500 group-data-[invalid=true]/field:focus:ring-red-500/10';
        $chip = 'inline-flex h-9 items-center rounded-full border px-4 text-xs font-semibold transition focus-visible:ring-4 focus-visible:ring-[#2e7d32]/15 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-40';
    @endphp

    <div class="h-px bg-neutral-200"></div>

    <div class="mx-auto mt-6 w-full max-w-[43rem] flex-1 animate-fade-up">
        <header class="pt-6">
            <h1 class="text-[1.75rem] font-bold tracking-tight text-neutral-900">Tarik Saldo</h1>
            <p class="mt-2 text-sm text-slate-500">Cairkan saldo hasil tabungan sampahmu ke rekening bank atau e-wallet.</p>
        </header>

        <form id="tarik-form" method="POST" action="{{ route('nasabah.tarik.store') }}" novalidate data-wizard-form="tarik" data-saldo="{{ $saldo }}" data-min="{{ $minTarik }}" class="mt-6">
            @csrf

            <div data-error-summary role="alert" tabindex="-1" @if ($invalidCount === 0) hidden @endif class="mb-4 flex gap-3 rounded-lg bg-red-50 px-4 py-3 text-red-700">
                <x-icon name="alert-circle" class="mt-0.5 size-5 shrink-0" />
                <div class="text-sm">
                    <p data-error-summary-title class="font-semibold">Periksa {{ $invalidCount }} bagian yang ditandai.</p>
                    <p>Lengkapi data berikut sebelum menarik saldo.</p>
                </div>
            </div>

            <div class="rounded-2xl border border-neutral-200 bg-white p-5 sm:p-8">
                {{-- Saldo tersedia --}}
                <div class="flex items-center justify-between gap-4 rounded-lg border border-emerald-200 bg-emerald-50/70 px-5 py-4">
                    <p class="text-sm font-medium text-[#1b5e20]">Total Saldo Tersedia</p>
                    <p class="text-2xl font-bold tabular-nums tracking-tight text-[#2e7d32]">Rp {{ $rupiah($saldo) }}</p>
                </div>

                @unless ($saldoCukup)
                    <div role="status" class="mt-4 flex items-start gap-3 rounded-lg bg-amber-50 px-4 py-3 text-sm text-amber-800">
                        <x-icon name="alert-circle" class="mt-0.5 size-5 shrink-0" />
                        <p>Saldo Anda belum cukup untuk ditarik. Minimal penarikan Rp {{ $rupiah($minTarik) }}.</p>
                    </div>
                @endunless

                <div class="mt-6 space-y-6">
                    {{-- Nominal --}}
                    <div data-field="nominal" data-invalid="{{ $errors->has('nominal') ? 'true' : 'false' }}" class="group/field">
                        <label for="nominal" class="{{ $label }}">Nominal Penarikan <span class="text-red-500" aria-hidden="true">*</span></label>
                        <div class="relative">
                            <span class="pointer-events-none absolute left-4 top-1/2 mt-1 -translate-y-1/2 text-sm font-semibold text-neutral-500" aria-hidden="true">Rp</span>
                            <input id="nominal" name="nominal" type="text" inputmode="numeric" autocomplete="off" placeholder="0" value="{{ $nominalTampil }}" required data-control aria-describedby="nominal-error" class="{{ $input }} pl-11 font-semibold tabular-nums">
                        </div>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($pilihanCepat as $jumlah)
                                <button type="button" data-chip="{{ $jumlah }}" @disabled($jumlah > $saldo) class="{{ $chip }} border-neutral-200 bg-[#f4f7fa] text-neutral-700 hover:border-neutral-300 hover:bg-white data-[active=true]:border-[#2e7d32]/40 data-[active=true]:bg-[#eaf3ea] data-[active=true]:text-[#2e7d32]">{{ $rupiah($jumlah) }}</button>
                            @endforeach
                            <button type="button" data-chip="all" @disabled(! $saldoCukup) class="{{ $chip }} border-emerald-200 bg-emerald-50 text-[#2e7d32] hover:bg-emerald-100 data-[active=true]:border-[#2e7d32]/50">Tarik Semua</button>
                        </div>
                        <x-field-message name="nominal" :hint="'Minimal penarikan Rp ' . $rupiah($minTarik) . '.'" />
                    </div>

                    {{-- Tujuan --}}
                    <div data-field="tujuan" data-invalid="{{ $errors->has('tujuan') ? 'true' : 'false' }}" class="group/field">
                        <label for="tujuan" class="{{ $label }}">Tujuan Pencairan <span class="text-red-500" aria-hidden="true">*</span></label>
                        <div class="relative">
                            <select id="tujuan" name="tujuan" required data-control aria-describedby="tujuan-error" class="{{ $input }} cursor-pointer appearance-none pr-11">
                                <option value="">Pilih bank atau e-wallet</option>
                                @foreach ($metodePencairan as $metode)
                                    <option value="{{ $metode }}" @selected($tujuanAwal === $metode)>{{ $metode }}</option>
                                @endforeach
                            </select>
                            <x-icon name="chevron-down" class="pointer-events-none absolute right-4 top-1/2 mt-1 size-4 -translate-y-1/2 text-neutral-500" />
                        </div>
                        <x-field-message name="tujuan" />
                    </div>

                    {{-- Nomor --}}
                    <div data-field="nomor_tujuan" data-invalid="{{ $errors->has('nomor_tujuan') ? 'true' : 'false' }}" class="group/field">
                        <label for="nomor_tujuan" class="{{ $label }}">Nomor Rekening / No. HP <span class="text-red-500" aria-hidden="true">*</span></label>
                        <input id="nomor_tujuan" name="nomor_tujuan" type="text" inputmode="numeric" autocomplete="off" value="{{ $nomorAwal }}" required data-control aria-describedby="nomor_tujuan-error" class="{{ $input }} tabular-nums">
                        <x-field-message name="nomor_tujuan" hint="Periksa kembali nomornya. Nomor yang salah membuat pencairan gagal." />
                    </div>

                    {{-- Nama pemilik --}}
                    <div data-field="nama_pemilik" data-invalid="{{ $errors->has('nama_pemilik') ? 'true' : 'false' }}" class="group/field">
                        <label for="nama_pemilik" class="{{ $label }}">Nama Pemilik Rekening <span class="text-red-500" aria-hidden="true">*</span></label>
                        <input id="nama_pemilik" name="nama_pemilik" type="text" maxlength="100" autocomplete="off" value="{{ $namaAwal }}" required data-control aria-describedby="nama_pemilik-error" class="{{ $input }}">
                        <x-field-message name="nama_pemilik" />
                    </div>
                </div>
            </div>

            {{-- Ringkasan --}}
            <section aria-labelledby="ringkasan-judul" class="mt-4 rounded-2xl border border-neutral-200 bg-white p-5 sm:p-8">
                <h2 id="ringkasan-judul" class="border-b border-dashed border-neutral-200 pb-4 text-base font-bold text-neutral-900">Ringkasan Penarikan</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-slate-500">Nominal Tarik</dt>
                        <dd data-sum-nominal class="font-semibold tabular-nums text-neutral-900">Rp {{ $nominalAwal !== '' ? $rupiah((int) $nominalAwal) : '0' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-slate-500">Saldo Saat Ini</dt>
                        <dd class="font-semibold tabular-nums text-neutral-900">Rp {{ $rupiah($saldo) }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4 border-t border-neutral-100 pt-3">
                        <dt class="font-semibold text-neutral-900">Sisa Saldo</dt>
                        <dd data-sum-sisa data-over="false" class="text-base font-bold tabular-nums text-[#2e7d32] data-[over=true]:text-red-600">Rp {{ $rupiah($sisaAwal) }}</dd>
                    </div>
                </dl>
                <p class="mt-4 text-xs leading-relaxed text-slate-500">Saldo Anda langsung berkurang sebesar nominal yang ditarik. Pastikan tujuan pencairan di atas sudah benar sebelum melanjutkan.</p>

                <div class="mt-6 flex flex-wrap items-center justify-between gap-4 border-t border-neutral-100 pt-6">
                    <a href="{{ route('dashboard') }}" class="inline-flex h-11 items-center rounded-lg border border-neutral-300 px-5 text-sm font-semibold text-neutral-700 transition hover:bg-neutral-50">Batal</a>

                    <button type="submit" data-submit data-loading="false" @disabled(! $saldoCukup) class="group/btn relative inline-flex h-11 items-center justify-center rounded-lg bg-[#2e7d32] px-6 text-sm font-semibold text-white shadow-lg shadow-[#2e7d32]/20 transition duration-300 hover:-translate-y-0.5 hover:bg-[#276a2b] active:translate-y-0 disabled:cursor-not-allowed disabled:opacity-50 disabled:shadow-none disabled:hover:translate-y-0 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#2e7d32]">
                        <span class="group-data-[loading=true]/btn:invisible">Tarik Saldo</span>
                        <span class="absolute inset-0 hidden place-items-center group-data-[loading=true]/btn:grid" aria-hidden="true">
                            <svg class="size-5 animate-spin" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity=".3" stroke-width="3"/><path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                        </span>
                    </button>
                </div>
            </section>
        </form>
    </div>
@endsection