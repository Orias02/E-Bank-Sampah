@extends('layouts.nasabah')

@section('title', 'Katalog Harga Sampah | E-Bank Sampah FILKOM')

@section('breadcrumb')
    <nav aria-label="Breadcrumb" class="mb-4 flex items-center gap-2 text-sm">
        <a href="{{ route('dashboard') }}" class="text-neutral-500 transition-colors hover:text-neutral-900">Beranda</a>
        <x-icon name="chevron-right" class="size-4 text-neutral-400" />
        <span aria-current="page" class="font-medium text-neutral-900">Harga Sampah</span>
    </nav>
@endsection

@section('card')
    @php
        // Dikirim KatalogController: $jenisSampah (semua jenis), $kategori (daftar kategori unik), $diperbarui (Carbon atau null).
        $rupiah = fn ($angka) => number_format($angka, 0, ',', '.');
        $pill = 'inline-flex h-10 shrink-0 items-center rounded-full border border-transparent px-3 text-sm font-semibold text-slate-600 transition hover:text-neutral-900 focus-visible:ring-4 focus-visible:ring-[#2e7d32]/15 focus-visible:outline-none data-[active=true]:border-emerald-200 data-[active=true]:bg-[#eaf3ea] data-[active=true]:text-[#2e7d32]';
    @endphp

    <div class="h-px bg-neutral-200"></div>

    <div data-katalog class="mx-auto mt-6 w-full max-w-[43rem] flex-1 animate-fade-up">
        <header class="flex flex-col gap-3 pt-6 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-[1.75rem] font-bold tracking-tight text-neutral-900">Katalog Harga Sampah</h1>
                <p class="mt-2 text-sm text-slate-500">Daftar nilai tukar sampah terpilah per kilogram yang berlaku saat ini.</p>
            </div>

            @if ($diperbarui)
                <p class="inline-flex shrink-0 items-center gap-2 self-start rounded-full border border-emerald-200 bg-emerald-50/70 px-4 py-2 text-xs font-semibold text-[#2e7d32]">
                    <x-icon name="refresh" class="size-3.5" />
                    Diperbarui: {{ $diperbarui->locale('id')->translatedFormat('j M Y') }}
                </p>
            @endif
        </header>

        @if ($jenisSampah->isEmpty())
            <div class="mt-6 flex flex-col items-center rounded-2xl border border-neutral-200 bg-white px-6 py-14 text-center">
                <span class="grid size-12 place-items-center rounded-full bg-[#eaf3ea] text-[#2e7d32]">
                    <x-icon name="recycle" class="size-6" />
                </span>
                <p class="mt-4 text-sm font-semibold text-neutral-900">Katalog belum tersedia</p>
                <p class="mt-1 max-w-xs text-sm text-neutral-600">Daftar harga sampah akan muncul di sini setelah diisi pengelola bank sampah.</p>
            </div>
        @else
            {{-- Filter kategori dan pencarian --}}
            <div class="mt-6 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-neutral-200 bg-white p-3 sm:p-4">
                <div role="group" aria-label="Filter kategori" class="flex flex-wrap gap-1">
                    <button type="button" data-filter="" data-active="true" aria-pressed="true" class="{{ $pill }}">Semua Kategori ({{ $jenisSampah->count() }})</button>
                    @foreach ($kategori as $nama)
                        <button type="button" data-filter="{{ $nama }}" data-active="false" aria-pressed="false" class="{{ $pill }}">{{ $nama }}</button>
                    @endforeach
                </div>

                <div class="relative w-full sm:w-auto sm:min-w-[11.5rem] sm:flex-1 sm:max-w-64">
                    <label for="cari-sampah" class="sr-only">Cari jenis sampah</label>
                    <x-icon name="search" class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                    <input id="cari-sampah" type="search" data-cari autocomplete="off" placeholder="Cari jenis sampah..." class="block h-10 w-full rounded-lg border border-neutral-300 bg-white pl-10 pr-3 text-sm text-neutral-900 placeholder:text-neutral-400 transition focus:border-[#2e7d32] focus:outline-none focus:ring-4 focus:ring-[#2e7d32]/10">
                </div>
            </div>

            <p data-jumlah role="status" aria-live="polite" class="sr-only"></p>

            {{-- Kartu harga --}}
            <ul class="mt-4 grid gap-4 sm:grid-cols-2">
                @foreach ($jenisSampah as $jenis)
                    @php
                        $selisih = $jenis->perubahanHarga();
                        $cari = mb_strtolower($jenis->nama . ' ' . $jenis->kategori . ' ' . $jenis->deskripsi);
                    @endphp
                    <li data-item data-kategori="{{ $jenis->kategori }}" data-cari="{{ $cari }}" class="flex flex-col rounded-2xl border border-neutral-200 bg-white p-5 transition hover:border-neutral-300 hover:shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <span class="grid size-12 place-items-center rounded-xl bg-[#eaf3ea] text-neutral-900">
                                <x-icon :name="$jenis->ikon ?: 'recycle'" class="size-6" />
                            </span>
                            <span class="rounded-md bg-slate-100 px-2.5 py-1 text-[0.65rem] font-semibold uppercase tracking-wider text-slate-500">{{ $jenis->kategori }}</span>
                        </div>

                        <h2 class="mt-4 text-lg font-bold leading-snug text-neutral-900">{{ $jenis->nama }}</h2>
                        @if ($jenis->deskripsi)
                            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">{{ $jenis->deskripsi }}</p>
                        @endif

                        <div class="mt-auto flex items-end justify-between gap-3 border-t border-dashed border-neutral-200 pt-4">
                            <div class="mt-5">
                                <p class="text-[0.7rem] text-slate-400">Harga Beli</p>
                                <p class="text-2xl font-bold tabular-nums tracking-tight text-[#2e7d32]">Rp {{ $rupiah($jenis->harga_per_kg) }} <span class="text-sm font-medium text-slate-500">/ kg</span></p>
                            </div>

                            @if ($selisih > 0)
                                <span class="mb-1 inline-flex shrink-0 items-center gap-1 rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-[#2e7d32]"><span aria-hidden="true">▲</span><span class="sr-only">Naik</span> +Rp {{ $rupiah($selisih) }}</span>
                            @elseif ($selisih < 0)
                                <span class="mb-1 inline-flex shrink-0 items-center gap-1 rounded-md bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-600"><span aria-hidden="true">▼</span><span class="sr-only">Turun</span> −Rp {{ $rupiah(abs($selisih)) }}</span>
                            @else
                                <span class="mb-1 inline-flex shrink-0 items-center gap-1.5 rounded-md bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500"><span aria-hidden="true" class="size-1.5 rounded-full bg-slate-400"></span>Stabil</span>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>

            <div data-kosong hidden class="mt-4 flex flex-col items-center rounded-2xl border border-dashed border-neutral-300 bg-white px-6 py-12 text-center">
                <span class="grid size-12 place-items-center rounded-full bg-slate-100 text-slate-500">
                    <x-icon name="search" class="size-6" />
                </span>
                <p class="mt-4 text-sm font-semibold text-neutral-900">Tidak ada sampah yang cocok</p>
                <p class="mt-1 text-sm text-neutral-600">Coba kata kunci lain atau ganti kategori.</p>
                <button type="button" data-reset class="mt-4 inline-flex h-10 items-center rounded-lg border border-neutral-300 px-4 text-sm font-semibold text-neutral-800 transition hover:bg-neutral-50">Tampilkan Semua</button>
            </div>

            <p class="mt-5 text-xs leading-relaxed text-slate-500">Harga dapat berubah sewaktu-waktu mengikuti pasar. Nilai pasti setoran dihitung dari berat hasil timbangan admin dan harga yang berlaku saat setoran diterima.</p>
        @endif
    </div>
@endsection