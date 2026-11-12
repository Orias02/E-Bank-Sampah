@extends('layouts.admin')

@section('title', 'Pengajuan Setoran | Admin E-Bank Sampah FILKOM')

@section('content')
    @php
        // Dikirim Admin\PengajuanController: $pengajuan (paginator), $status, $metode, $cari, $jumlah, $totalSemua, $daftarStatus.
        $rupiah = fn ($angka) => 'Rp ' . number_format($angka, 0, ',', '.');
        $kg = fn ($angka) => rtrim(rtrim(number_format($angka, 2, ',', '.'), '0'), ',');
        $nada = ['menunggu' => 'proses', 'diterima' => 'info', 'selesai' => 'selesai', 'ditolak' => 'gagal', 'dibatalkan' => 'netral'];
        $labelStatus = $daftarStatus + ['dibatalkan' => 'Dibatalkan'];
        $tab = 'inline-flex h-9 items-center gap-2 whitespace-nowrap rounded-full border px-4 text-sm font-medium transition';
        $query = fn (array $ubah) => array_filter(array_merge(['status' => $status, 'metode' => $metode, 'q' => $cari], $ubah));
    @endphp

    <header class="mb-6">
        <h1 class="text-[1.75rem] font-bold tracking-tight text-neutral-900">Pengajuan Setoran</h1>
        <p class="mt-2 text-sm text-slate-500">Terima pengajuan dari nasabah, lalu catat hasil timbangan saat sampah tiba.</p>
    </header>

    <section class="overflow-hidden rounded-2xl border border-neutral-200 bg-white">
        <div class="space-y-4 border-b border-neutral-200 p-5">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <nav aria-label="Filter status" class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.pengajuan.index', $query(['status' => 'semua'])) }}" @if ($status === 'semua') aria-current="true" @endif class="{{ $tab }} {{ $status === 'semua' ? 'border-[#2e7d32] bg-[#2e7d32] text-white' : 'border-neutral-200 text-neutral-700 hover:bg-neutral-50' }}">
                        Semua <span class="text-xs opacity-80">{{ $totalSemua }}</span>
                    </a>
                    @foreach ($daftarStatus as $kunci => $label)
                        <a href="{{ route('admin.pengajuan.index', $query(['status' => $kunci])) }}" @if ($status === $kunci) aria-current="true" @endif class="{{ $tab }} {{ $status === $kunci ? 'border-[#2e7d32] bg-[#2e7d32] text-white' : 'border-neutral-200 text-neutral-700 hover:bg-neutral-50' }}">
                            {{ $label }} <span class="text-xs opacity-80">{{ $jumlah[$kunci] ?? 0 }}</span>
                        </a>
                    @endforeach
                </nav>

                <form method="GET" action="{{ route('admin.pengajuan.index') }}" role="search" class="flex gap-2">
                    <input type="hidden" name="status" value="{{ $status }}">
                    @if ($metode !== '') <input type="hidden" name="metode" value="{{ $metode }}"> @endif
                    <label class="relative block">
                        <span class="sr-only">Cari pengajuan</span>
                        <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-neutral-400" />
                        <input type="search" name="q" value="{{ $cari }}" placeholder="Nama nasabah, kelurahan, atau PGJ-…" class="h-10 w-full rounded-lg border border-neutral-300 bg-white pl-9 pr-3 text-sm text-neutral-900 placeholder:text-neutral-400 focus:border-[#2e7d32] focus:ring-4 focus:ring-[#2e7d32]/15 focus:outline-none sm:w-80">
                    </label>
                    <button type="submit" class="h-10 rounded-lg bg-[#2e7d32] px-4 text-sm font-semibold text-white transition hover:bg-[#276b2b] focus-visible:ring-4 focus-visible:ring-[#2e7d32]/25 focus-visible:outline-none">Cari</button>
                </form>
            </div>

            <nav aria-label="Filter metode" class="flex flex-wrap items-center gap-2 text-sm">
                <span class="text-xs font-medium text-slate-500">Metode:</span>
                @foreach (['' => 'Semua', 'antar' => 'Diantar', 'jemput' => 'Dijemput'] as $kunci => $label)
                    <a href="{{ route('admin.pengajuan.index', $query(['metode' => $kunci])) }}" @if ($metode === $kunci) aria-current="true" @endif class="inline-flex h-8 items-center rounded-full border px-3 text-xs font-semibold transition {{ $metode === $kunci ? 'border-[#2e7d32]/30 bg-[#eaf3ea] text-[#2e7d32]' : 'border-neutral-200 text-neutral-600 hover:bg-neutral-50' }}">{{ $label }}</a>
                @endforeach
            </nav>
        </div>

        @if ($pengajuan->isEmpty())
            <div class="flex flex-col items-center px-6 py-14 text-center">
                <span class="grid size-12 place-items-center rounded-full bg-[#eaf3ea] text-[#2e7d32]">
                    <x-icon name="package" class="size-6" />
                </span>
                <p class="mt-4 text-sm font-semibold text-neutral-900">{{ $cari !== '' ? 'Tidak ada pengajuan yang cocok' : 'Tidak ada pengajuan di daftar ini' }}</p>
                <p class="mt-1 max-w-xs text-sm text-neutral-600">{{ $cari !== '' ? 'Coba kata kunci lain atau ubah filter.' : 'Pengajuan baru dari nasabah akan muncul di sini.' }}</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[52rem] text-left text-sm">
                    <thead>
                        <tr class="border-b border-neutral-200 bg-[#f7f9fb] text-[0.7rem] font-semibold uppercase tracking-wider text-slate-500">
                            <th scope="col" class="px-5 py-4">Nasabah</th>
                            <th scope="col" class="px-3 py-4">Jadwal &amp; Metode</th>
                            <th scope="col" class="px-3 py-4">Kategori</th>
                            <th scope="col" class="px-3 py-4 text-right">Perkiraan</th>
                            <th scope="col" class="px-3 py-4">Status</th>
                            <th scope="col" class="px-5 py-4 text-right"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100">
                        @foreach ($pengajuan as $item)
                            <tr class="transition hover:bg-neutral-50/70">
                                <td class="px-5 py-4 align-middle">
                                    <a href="{{ route('admin.pengajuan.show', $item->id) }}" class="font-semibold text-neutral-900 hover:text-[#2e7d32] hover:underline">{{ $item->nasabah->nama_lengkap }}</a>
                                    <p class="mt-1 font-mono text-[0.7rem] tracking-wide text-neutral-400">{{ $item->kode() }}</p>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 align-middle">
                                    <p class="font-medium text-neutral-800">{{ $item->tanggal_rencana->locale('id')->translatedFormat('d M Y') }}</p>
                                    <p class="mt-0.5 flex items-center gap-1 text-xs text-neutral-500">
                                        <x-icon :name="$item->metode === 'jemput' ? 'truck' : 'pin'" class="size-3.5" />
                                        {{ $item->metode === 'jemput' ? 'Dijemput' : 'Diantar' }}
                                    </p>
                                </td>
                                <td class="max-w-[14rem] px-3 py-4 align-middle text-neutral-700">
                                    <p class="leading-snug">{{ $item->jenisSampah->pluck('nama')->take(2)->implode(', ') }}@if ($item->jenisSampah->count() > 2) <span class="text-neutral-400">+{{ $item->jenisSampah->count() - 2 }}</span>@endif</p>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-right align-middle">
                                    <p class="font-semibold tabular-nums text-neutral-900">≈ {{ $kg($item->perkiraanBerat()) }} kg</p>
                                    <p class="mt-0.5 text-xs tabular-nums text-amber-600">≈ {{ $rupiah($item->perkiraanNilai()) }}</p>
                                </td>
                                <td class="px-3 py-4 align-middle">
                                    <x-pil :tone="$nada[$item->status] ?? 'netral'">{{ $labelStatus[$item->status] ?? ucfirst($item->status) }}</x-pil>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-right align-middle">
                                    <a href="{{ route('admin.pengajuan.show', $item->id) }}" class="inline-flex h-9 items-center rounded-lg border border-neutral-300 bg-white px-3.5 text-sm font-semibold text-neutral-800 transition hover:bg-neutral-50 focus-visible:ring-4 focus-visible:ring-[#2e7d32]/15 focus-visible:outline-none">{{ in_array($item->status, ['menunggu', 'diterima'], true) ? 'Proses' : 'Detail' }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <x-pagination :paginator="$pengajuan" satuan="pengajuan" />
        @endif
    </section>
@endsection