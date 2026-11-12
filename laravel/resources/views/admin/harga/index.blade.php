@extends('layouts.admin')

@section('title', 'Harga Sampah | Admin E-Bank Sampah FILKOM')

@section('content')
    @php
        // Dikirim Admin\JenisSampahController: $jenisSampah (Collection).
        $rupiah = fn ($angka) => 'Rp ' . number_format($angka, 0, ',', '.');
        $jumlahAktif = $jenisSampah->where('aktif', true)->count();
    @endphp

    <header class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-[1.75rem] font-bold tracking-tight text-neutral-900">Harga Sampah</h1>
            <p class="mt-2 text-sm text-slate-500">Atur harga per kg, kategori, dan jenis sampah yang diterima. Perubahan harga otomatis tampil sebagai naik/turun di katalog nasabah.</p>
        </div>
        <a href="{{ route('admin.harga.create') }}" class="inline-flex h-11 shrink-0 items-center justify-center gap-2 self-start rounded-lg bg-[#2e7d32] px-5 text-sm font-semibold text-white transition hover:bg-[#276b2b] focus-visible:ring-4 focus-visible:ring-[#2e7d32]/25 focus-visible:outline-none">
            <x-icon name="plus" class="size-4" />
            Tambah Jenis Sampah
        </a>
    </header>

    <section class="overflow-hidden rounded-2xl border border-neutral-200 bg-white">
        @if ($jenisSampah->isEmpty())
            <div class="flex flex-col items-center px-6 py-14 text-center">
                <span class="grid size-12 place-items-center rounded-full bg-[#eaf3ea] text-[#2e7d32]">
                    <x-icon name="recycle" class="size-6" />
                </span>
                <p class="mt-4 text-sm font-semibold text-neutral-900">Belum ada jenis sampah</p>
                <p class="mt-1 max-w-xs text-sm text-neutral-600">Tambahkan jenis sampah pertama agar nasabah bisa mengajukan setoran.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[48rem] text-left text-sm">
                    <thead>
                        <tr class="border-b border-neutral-200 bg-[#f7f9fb] text-[0.7rem] font-semibold uppercase tracking-wider text-slate-500">
                            <th scope="col" class="px-5 py-4">Jenis sampah</th>
                            <th scope="col" class="px-3 py-4">Kategori</th>
                            <th scope="col" class="px-3 py-4 text-right">Harga / kg</th>
                            <th scope="col" class="px-3 py-4">Status</th>
                            <th scope="col" class="px-5 py-4 text-right"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100">
                        @foreach ($jenisSampah as $jenis)
                            @php $selisih = $jenis->perubahanHarga(); @endphp
                            <tr class="{{ $jenis->aktif ? '' : 'bg-neutral-50/70' }}">
                                <td class="px-5 py-4 align-middle">
                                    <div class="flex items-center gap-3">
                                        <span class="grid size-10 shrink-0 place-items-center rounded-xl {{ $jenis->aktif ? 'bg-[#eaf3ea] text-[#2e7d32]' : 'bg-neutral-100 text-neutral-400' }}">
                                            <x-icon :name="$jenis->ikon" class="size-5" />
                                        </span>
                                        <span class="min-w-0">
                                            <span class="block font-semibold {{ $jenis->aktif ? 'text-neutral-900' : 'text-neutral-500' }}">{{ $jenis->nama }}</span>
                                            @if ($jenis->deskripsi)
                                                <span class="mt-0.5 block max-w-xs truncate text-xs text-neutral-500">{{ $jenis->deskripsi }}</span>
                                            @endif
                                        </span>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 align-middle text-neutral-700">{{ $jenis->kategori }}</td>
                                <td class="whitespace-nowrap px-3 py-4 text-right align-middle">
                                    <p class="font-bold tabular-nums text-neutral-900">{{ $rupiah($jenis->harga_per_kg) }}</p>
                                    @if ($selisih > 0)
                                        <p class="mt-0.5 text-xs font-medium text-[#2e7d32]">▲ naik {{ $rupiah($selisih) }}</p>
                                    @elseif ($selisih < 0)
                                        <p class="mt-0.5 text-xs font-medium text-red-600">▼ turun {{ $rupiah(abs($selisih)) }}</p>
                                    @endif
                                </td>
                                <td class="px-3 py-4 align-middle">
                                    <x-pil :tone="$jenis->aktif ? 'selesai' : 'netral'">{{ $jenis->aktif ? 'Aktif' : 'Nonaktif' }}</x-pil>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-right align-middle">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.harga.edit', $jenis->id) }}" class="inline-flex h-9 items-center gap-1.5 rounded-lg border border-neutral-300 bg-white px-3.5 text-sm font-semibold text-neutral-800 transition hover:bg-neutral-50 focus-visible:ring-4 focus-visible:ring-[#2e7d32]/15 focus-visible:outline-none">
                                            <x-icon name="pencil" class="size-3.5" />
                                            Ubah
                                        </a>
                                        <form method="POST" action="{{ route('admin.harga.toggle', $jenis->id) }}">
                                            @csrf
                                            <button type="submit" class="inline-flex h-9 items-center rounded-lg px-3 text-sm font-semibold transition focus-visible:ring-4 focus-visible:outline-none {{ $jenis->aktif ? 'text-red-600 hover:bg-red-50 focus-visible:ring-red-500/15' : 'text-[#2e7d32] hover:bg-[#eaf3ea] focus-visible:ring-[#2e7d32]/15' }}">{{ $jenis->aktif ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <p class="border-t border-neutral-200 px-5 py-4 text-xs text-slate-500 sm:text-sm">{{ $jumlahAktif }} dari {{ $jenisSampah->count() }} jenis sampah aktif. Jenis yang nonaktif tidak tampil di katalog dan form setor, tetapi riwayat lamanya tetap utuh.</p>
        @endif
    </section>
@endsection