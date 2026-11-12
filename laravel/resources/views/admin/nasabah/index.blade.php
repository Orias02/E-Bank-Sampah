@extends('layouts.admin')

@section('title', 'Nasabah | Admin E-Bank Sampah FILKOM')

@section('content')
    @php
        // Dikirim Admin\NasabahController: $nasabah (paginator), $status, $cari, $jumlah, $totalSemua, $daftarStatus.
        $nada = ['menunggu' => 'proses', 'aktif' => 'selesai', 'ditolak' => 'gagal', 'nonaktif' => 'netral'];
        $tab = 'inline-flex h-9 items-center gap-2 whitespace-nowrap rounded-full border px-4 text-sm font-medium transition';
    @endphp

    <header class="mb-6">
        <h1 class="text-[1.75rem] font-bold tracking-tight text-neutral-900">Nasabah</h1>
        <p class="mt-2 text-sm text-slate-500">Periksa pendaftaran baru, aktifkan akun, dan lihat data nasabah.</p>
    </header>

    <section class="overflow-hidden rounded-2xl border border-neutral-200 bg-white">
        <div class="flex flex-col gap-4 border-b border-neutral-200 p-5 lg:flex-row lg:items-center lg:justify-between">
            <nav aria-label="Filter status" class="flex flex-wrap gap-2">
                <a href="{{ route('admin.nasabah.index', array_filter(['status' => 'semua', 'q' => $cari])) }}" @if ($status === 'semua') aria-current="true" @endif class="{{ $tab }} {{ $status === 'semua' ? 'border-[#2e7d32] bg-[#2e7d32] text-white' : 'border-neutral-200 text-neutral-700 hover:bg-neutral-50' }}">
                    Semua <span class="text-xs opacity-80">{{ $totalSemua }}</span>
                </a>
                @foreach ($daftarStatus as $kunci => $label)
                    <a href="{{ route('admin.nasabah.index', array_filter(['status' => $kunci, 'q' => $cari])) }}" @if ($status === $kunci) aria-current="true" @endif class="{{ $tab }} {{ $status === $kunci ? 'border-[#2e7d32] bg-[#2e7d32] text-white' : 'border-neutral-200 text-neutral-700 hover:bg-neutral-50' }}">
                        {{ $label }} <span class="text-xs opacity-80">{{ $jumlah[$kunci] ?? 0 }}</span>
                    </a>
                @endforeach
            </nav>

            <form method="GET" action="{{ route('admin.nasabah.index') }}" role="search" class="flex gap-2">
                <input type="hidden" name="status" value="{{ $status }}">
                <label class="relative block">
                    <span class="sr-only">Cari nasabah</span>
                    <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-neutral-400" />
                    <input type="search" name="q" value="{{ $cari }}" placeholder="Nama, NIK, WhatsApp, atau NAS-…" class="h-10 w-full rounded-lg border border-neutral-300 bg-white pl-9 pr-3 text-sm text-neutral-900 placeholder:text-neutral-400 focus:border-[#2e7d32] focus:ring-4 focus:ring-[#2e7d32]/15 focus:outline-none sm:w-80">
                </label>
                <button type="submit" class="h-10 rounded-lg bg-[#2e7d32] px-4 text-sm font-semibold text-white transition hover:bg-[#276b2b] focus-visible:ring-4 focus-visible:ring-[#2e7d32]/25 focus-visible:outline-none">Cari</button>
            </form>
        </div>

        @if ($nasabah->isEmpty())
            <div class="flex flex-col items-center px-6 py-14 text-center">
                <span class="grid size-12 place-items-center rounded-full bg-[#eaf3ea] text-[#2e7d32]">
                    <x-icon name="users" class="size-6" />
                </span>
                <p class="mt-4 text-sm font-semibold text-neutral-900">{{ $cari !== '' ? 'Tidak ada nasabah yang cocok' : 'Belum ada nasabah di daftar ini' }}</p>
                <p class="mt-1 max-w-xs text-sm text-neutral-600">{{ $cari !== '' ? 'Coba kata kunci lain atau ubah filter status.' : 'Pendaftaran baru akan muncul di sini.' }}</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[46rem] text-left text-sm">
                    <thead>
                        <tr class="border-b border-neutral-200 bg-[#f7f9fb] text-[0.7rem] font-semibold uppercase tracking-wider text-slate-500">
                            <th scope="col" class="px-5 py-4">Nasabah</th>
                            <th scope="col" class="px-3 py-4">Wilayah</th>
                            <th scope="col" class="px-3 py-4">Mendaftar</th>
                            <th scope="col" class="px-3 py-4">Status</th>
                            <th scope="col" class="px-5 py-4 text-right"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100">
                        @foreach ($nasabah as $item)
                            <tr class="transition hover:bg-neutral-50/70">
                                <td class="px-5 py-4 align-middle">
                                    <a href="{{ route('admin.nasabah.show', $item->id) }}" class="font-semibold text-neutral-900 hover:text-[#2e7d32] hover:underline">{{ $item->nama_lengkap }}</a>
                                    <p class="mt-1 font-mono text-[0.7rem] tracking-wide text-neutral-400">{{ $item->kode() }}</p>
                                </td>
                                <td class="px-3 py-4 align-middle text-neutral-700">
                                    <p>{{ $item->kelurahan }}</p>
                                    <p class="mt-0.5 text-xs text-neutral-500">{{ $item->kecamatan }}</p>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 align-middle">
                                    <p class="font-medium text-neutral-800">{{ $item->created_at->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d M Y') }}</p>
                                    <p class="mt-0.5 text-xs text-neutral-500">{{ $item->created_at->timezone('Asia/Jakarta')->format('H:i') }} WIB</p>
                                </td>
                                <td class="px-3 py-4 align-middle">
                                    <x-pil :tone="$nada[$item->status] ?? 'netral'">{{ $daftarStatus[$item->status] ?? ucfirst($item->status) }}</x-pil>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-right align-middle">
                                    <a href="{{ route('admin.nasabah.show', $item->id) }}" class="inline-flex h-9 items-center rounded-lg border border-neutral-300 bg-white px-3.5 text-sm font-semibold text-neutral-800 transition hover:bg-neutral-50 focus-visible:ring-4 focus-visible:ring-[#2e7d32]/15 focus-visible:outline-none">{{ $item->status === 'menunggu' ? 'Periksa' : 'Detail' }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <x-pagination :paginator="$nasabah" satuan="nasabah" />
        @endif
    </section>
@endsection