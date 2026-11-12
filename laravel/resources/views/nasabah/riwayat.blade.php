@extends('layouts.nasabah')

@section('title', 'Riwayat Transaksi | E-Bank Sampah FILKOM')

@section('breadcrumb')
    <nav aria-label="Breadcrumb" class="mb-4 flex items-center gap-2 text-sm">
        <a href="{{ route('dashboard') }}" class="text-neutral-500 transition-colors hover:text-neutral-900">Beranda</a>
        <x-icon name="chevron-right" class="size-4 text-neutral-400" />
        <span aria-current="page" class="font-medium text-neutral-900">Riwayat Transaksi</span>
    </nav>
@endsection

@section('card')
    @php
        // Dikirim RiwayatController: $riwayat (LengthAwarePaginator berisi baris siap tampil), $nomorHalaman (angka atau null untuk "…").
        $rupiah = fn ($angka) => 'Rp ' . number_format($angka, 0, ',', '.');

        $pil = [
            'selesai' => 'border-emerald-200 bg-emerald-50 text-[#2e7d32]',
            'proses' => 'border-amber-200 bg-amber-50 text-amber-700',
            'info' => 'border-sky-200 bg-sky-50 text-sky-700',
            'gagal' => 'border-red-200 bg-red-50 text-red-600',
        ];
        $tombolHalaman = 'grid h-8 min-w-8 place-items-center rounded-md border px-2 text-xs font-semibold transition';
    @endphp

    <div class="h-px bg-neutral-200"></div>

    <div class="mx-auto mt-6 w-full max-w-[43rem] flex-1 animate-fade-up">
        <header class="flex flex-col gap-4 pt-6 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-[1.75rem] font-bold tracking-tight text-neutral-900">Riwayat Transaksi</h1>
                <p class="mt-2 text-sm text-slate-500">Pantau seluruh aktivitas setoran sampah dan penarikan saldomu.</p>
            </div>

            @if ($riwayat->total() > 0)
                <a href="{{ route('nasabah.riwayat.laporan') }}" target="_blank" rel="noopener" class="inline-flex h-10 shrink-0 items-center justify-center gap-2 self-start rounded-lg border border-neutral-300 bg-white px-4 text-sm font-semibold text-neutral-800 transition hover:bg-neutral-50 focus-visible:ring-4 focus-visible:ring-[#2e7d32]/15 focus-visible:outline-none">
                    <x-icon name="download" class="size-4" />
                    Unduh Laporan PDF
                </a>
            @endif
        </header>

        <section aria-label="Daftar transaksi" class="mt-6 overflow-hidden rounded-2xl border border-neutral-200 bg-white">
            @if ($riwayat->isEmpty())
                <div class="flex flex-col items-center px-6 py-14 text-center">
                    <span class="grid size-12 place-items-center rounded-full bg-[#eaf3ea] text-[#2e7d32]">
                        <x-icon name="recycle" class="size-6" />
                    </span>
                    <p class="mt-4 text-sm font-semibold text-neutral-900">Belum ada transaksi</p>
                    <p class="mt-1 max-w-xs text-sm text-neutral-600">Setoran sampah dan penarikan saldo Anda akan tercatat di sini.</p>
                    <a href="{{ route('nasabah.setor') }}" class="mt-5 inline-flex h-10 items-center gap-2 rounded-lg bg-[#2e7d32] px-4 text-sm font-semibold text-white transition hover:bg-[#276b2b]">
                        <x-icon name="plus" class="size-4" />
                        Setor Sampah
                    </a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[40rem] text-left text-sm">
                        <thead>
                            <tr class="border-b border-neutral-200 bg-[#f7f9fb] text-[0.7rem] font-semibold uppercase tracking-wider text-slate-500">
                                <th scope="col" class="px-4 py-4">Transaksi</th>
                                <th scope="col" class="px-2.5 py-4">Tanggal &amp; Waktu</th>
                                <th scope="col" class="px-2.5 py-4">Metode / Keterangan</th>
                                <th scope="col" class="px-2.5 py-4">Status</th>
                                <th scope="col" class="px-4 py-4 text-right">Nilai (Rp)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100">
                            @foreach ($riwayat as $item)
                                <tr>
                                    <td class="max-w-[11.5rem] px-4 py-4 align-middle">
                                        <p class="font-semibold leading-snug text-neutral-900">{{ $item->judul }}</p>
                                        <p class="mt-1 font-mono text-[0.7rem] tracking-wide text-neutral-400">{{ $item->kode }}</p>
                                    </td>
                                    <td class="whitespace-nowrap px-2.5 py-4 align-middle">
                                        <p class="font-medium text-neutral-800">{{ $item->waktu->locale('id')->translatedFormat('d M Y') }}</p>
                                        <p class="mt-0.5 text-xs text-neutral-500">{{ $item->waktu->format('H:i') }} WIB</p>
                                    </td>
                                    <td class="max-w-[10rem] px-2.5 py-4 align-middle {{ $item->gagal ? 'text-red-600' : 'text-neutral-700' }}">
                                        <p class="leading-snug">{{ $item->keterangan }}</p>
                                        @if ($item->keterangan_kecil)
                                            <p class="mt-0.5 text-xs text-neutral-500">{{ $item->keterangan_kecil }}</p>
                                        @endif
                                    </td>
                                    <td class="px-2.5 py-4 align-middle">
                                        <span class="inline-block rounded-full border px-3 py-1 text-xs font-semibold leading-snug {{ $pil[$item->status] }}">{{ $item->status_label }}</span>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-4 text-right align-middle font-bold tabular-nums">
                                        @if ($item->nilai === null)
                                            <span class="font-medium text-neutral-400">-</span>
                                        @elseif ($item->nilai_tanda === 'batal')
                                            <span class="text-neutral-400 line-through">{{ $rupiah($item->nilai) }}</span>
                                        @elseif ($item->nilai_tanda === 'estimasi')
                                            <span class="text-amber-600">≈ {{ $rupiah($item->nilai) }}</span>
                                        @elseif ($item->nilai_tanda === 'keluar')
                                            <span class="text-neutral-800">−{{ $rupiah($item->nilai) }}</span>
                                        @else
                                            <span class="text-[#2e7d32]">+{{ $rupiah($item->nilai) }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-col gap-3 border-t border-neutral-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-xs text-slate-500 sm:text-sm">Menampilkan {{ $riwayat->firstItem() }}-{{ $riwayat->lastItem() }} dari {{ $riwayat->total() }} transaksi</p>

                    @if ($riwayat->lastPage() > 1)
                        <nav aria-label="Halaman riwayat" class="flex flex-wrap items-center gap-1 sm:gap-1.5">
                            @if ($riwayat->onFirstPage())
                                <span aria-disabled="true" class="{{ $tombolHalaman }} border-transparent px-2 text-neutral-300">Sebelumnya</span>
                            @else
                                <a href="{{ $riwayat->previousPageUrl() }}" rel="prev" class="{{ $tombolHalaman }} border-transparent px-2 text-neutral-700 hover:bg-neutral-100">Sebelumnya</a>
                            @endif

                            @foreach ($nomorHalaman as $nomor)
                                @if ($nomor === null)
                                    <span aria-hidden="true" class="px-1 text-neutral-400">…</span>
                                @elseif ($nomor === $riwayat->currentPage())
                                    <span aria-current="page" class="{{ $tombolHalaman }} border-[#2e7d32]/30 bg-[#eaf3ea] text-[#2e7d32]">{{ $nomor }}</span>
                                @else
                                    <a href="{{ $riwayat->url($nomor) }}" aria-label="Halaman {{ $nomor }}" class="{{ $tombolHalaman }} border-neutral-200 text-neutral-700 hover:bg-neutral-50">{{ $nomor }}</a>
                                @endif
                            @endforeach

                            @if ($riwayat->hasMorePages())
                                <a href="{{ $riwayat->nextPageUrl() }}" rel="next" class="{{ $tombolHalaman }} border-neutral-300 px-2.5 text-neutral-800 hover:bg-neutral-50">Selanjutnya</a>
                            @else
                                <span aria-disabled="true" class="{{ $tombolHalaman }} border-transparent px-2 text-neutral-300">Selanjutnya</span>
                            @endif
                        </nav>
                    @endif
                </div>
            @endif
        </section>
    </div>
@endsection