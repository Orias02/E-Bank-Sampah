@extends('layouts.nasabah')

@section('title', 'Beranda Nasabah | E-Bank Sampah FILKOM')

@section('breadcrumb')
    <nav aria-label="Breadcrumb" class="mb-4 text-sm">
        <span aria-current="page" class="text-neutral-500">Beranda</span>
    </nav>
@endsection

@section('card')
    @php
        // Dikirim DashboardController: $nasabah, $saldo (rupiah), $totalBerat (kg), $aktivitas (5 aktivitas terakhir: transaksi + pengajuan setoran yang belum selesai).
        $rupiah = fn ($angka) => 'Rp ' . number_format($angka, 0, ',', '.');
        $kg = fn ($angka) => rtrim(rtrim(number_format($angka, 2, ',', '.'), '0'), ',') . ' kg';

        $statusClass = [
            'selesai' => 'bg-emerald-50 text-[#2e7d32]',
            'proses' => 'bg-amber-50 text-amber-600',
            'dibatalkan' => 'bg-red-50 text-red-600',
            'menunggu' => 'bg-amber-50 text-amber-600',
            'diterima' => 'bg-sky-50 text-sky-700',
            'ditolak' => 'bg-red-50 text-red-600',
        ];
        $statusLabel = [
            'selesai' => 'Selesai', 'proses' => 'Proses', 'dibatalkan' => 'Dibatalkan',
            'menunggu' => 'Menunggu', 'diterima' => 'Diterima', 'ditolak' => 'Ditolak',
        ];
    @endphp

    <div class="h-px bg-neutral-200"></div>

    <div class="mx-auto mt-6 w-full max-w-[43rem] flex-1 animate-fade-up space-y-3.5">

        {{-- Sambutan + saldo --}}
        <section aria-label="Saldo" class="rounded-2xl bg-[#213b2d] p-6 text-white shadow-xl shadow-black/10 sm:p-8">
            <div class="flex flex-col gap-6 sm:flex-row sm:items-start sm:justify-between">
                <div class="min-w-0">
                    <p class="text-sm text-white/75">Selamat datang kembali,</p>
                    <h1 class="mt-1 break-words text-base font-semibold">{{ $nasabah->nama_lengkap }}!</h1>

                    <p class="mt-6 text-[0.68rem] font-semibold uppercase tracking-[0.14em] text-white/60">Total saldo aktif</p>
                    <p class="mt-1 text-[1.75rem] font-bold leading-tight tabular-nums text-emerald-500">{{ $rupiah($saldo) }}</p>
                </div>

                <div class="flex flex-wrap gap-3 sm:justify-end">
                    <a href="{{ route('nasabah.setor') }}" class="inline-flex h-11 items-center gap-2.5 rounded-lg bg-[#2e7d32] px-5 text-sm font-semibold text-white transition duration-300 hover:-translate-y-0.5 hover:bg-[#35903a] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                        <x-icon name="plus" class="size-4" />
                        Setor Sampah Baru
                    </a>
                    <a href="{{ route('nasabah.tarik') }}" class="inline-flex h-11 items-center gap-2.5 rounded-lg border border-white/15 bg-white/10 px-5 text-sm font-semibold text-white transition duration-300 hover:-translate-y-0.5 hover:bg-white/15 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                        <x-icon name="wallet" class="size-4" />
                        Tarik Saldo
                    </a>
                </div>
            </div>
        </section>

        {{-- Ringkasan --}}
        <div class="grid gap-3.5 sm:grid-cols-[3fr_2fr]">
            <section aria-label="Total sampah disetor" class="flex items-center gap-4 rounded-2xl border border-neutral-200 bg-white p-5">
                <span class="grid size-14 shrink-0 place-items-center rounded-xl bg-[#eaf3ea] text-[#2e7d32]">
                    <x-icon name="scale" class="size-7" />
                </span>
                <div>
                    <p class="text-xs text-neutral-600">Total Sampah Disetor</p>
                    <p class="mt-0.5 text-2xl font-bold tabular-nums text-neutral-900">{{ $kg($totalBerat) }}</p>
                </div>
            </section>

            <a href="{{ route('nasabah.harga') }}" class="group flex items-center gap-4 rounded-2xl bg-[#2e7d32] p-5 text-white transition duration-300 hover:-translate-y-0.5 hover:bg-[#276a2b] hover:shadow-lg hover:shadow-[#2e7d32]/25 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#2e7d32]">
                <span class="grid size-14 shrink-0 place-items-center rounded-xl bg-[#fff8e8] text-[#2e7d32]">
                    <x-icon name="tag" class="size-6" />
                </span>
                <div>
                    <p class="text-xs text-white/85">Katalog</p>
                    <p class="mt-0.5 text-xl font-bold leading-tight">Harga Sampah</p>
                </div>
            </a>
        </div>

        {{-- Aktivitas terakhir --}}
        <section aria-labelledby="aktivitas-judul" class="rounded-2xl border border-neutral-200 bg-white p-5 sm:p-6">
            <div class="flex items-center justify-between gap-4">
                <h2 id="aktivitas-judul" class="text-lg font-bold text-neutral-900">Aktivitas Terakhir</h2>
                <a href="{{ route('nasabah.riwayat') }}" class="text-xs font-semibold text-[#2e7d32] hover:underline">Lihat Semua Riwayat</a>
            </div>

            @if ($aktivitas->isEmpty())
                <div class="mt-6 flex flex-col items-center rounded-xl bg-[#f7f9f7] px-6 py-10 text-center">
                    <span class="grid size-12 place-items-center rounded-full bg-[#eaf3ea] text-[#2e7d32]">
                        <x-icon name="recycle" class="size-6" />
                    </span>
                    <p class="mt-4 text-sm font-semibold text-neutral-900">Belum ada aktivitas</p>
                    <p class="mt-1 max-w-xs text-sm text-neutral-600">Setoran sampah pertama Anda akan muncul di sini.</p>
                </div>
            @else
                <div class="mt-5 overflow-x-auto">
                    <table class="w-full min-w-[34rem] text-left text-sm">
                        <thead>
                            <tr class="bg-[#f4f7fa] text-xs font-semibold text-slate-500">
                                <th scope="col" class="rounded-l-lg px-4 py-3.5">Jenis Sampah</th>
                                <th scope="col" class="px-3 py-3.5">Tanggal</th>
                                <th scope="col" class="px-3 py-3.5">Berat</th>
                                <th scope="col" class="px-3 py-3.5">Status</th>
                                <th scope="col" class="rounded-r-lg px-4 py-3.5">Nilai (Rp)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100">
                            @foreach ($aktivitas as $item)
                                @php
                                    $penarikan = $item->tipe === 'penarikan';
                                    $pengajuan = $item->tipe === 'pengajuan';
                                    $nilaiClass = $item->status === 'ditolak' ? 'text-neutral-400 line-through' : ($pengajuan || $item->status === 'proses' ? 'text-amber-600' : ($penarikan ? 'text-neutral-800' : 'text-[#2e7d32]'));
                                @endphp
                                <tr>
                                    <td class="px-4 py-4">
                                        <span class="flex items-center gap-3 font-semibold text-neutral-900">
                                            <x-icon :name="$item->ikon" class="size-5 shrink-0" />
                                            {{ $item->nama }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-neutral-700">{{ $item->tanggal->locale('id')->translatedFormat('j M Y') }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-neutral-700">
                                        @if ($item->berat === null)
                                            -
                                        @else
                                            {{ $item->berat_perkiraan ? '≈ ' : '' }}{{ $kg($item->berat) }}
                                        @endif
                                    </td>
                                    <td class="px-3 py-4">
                                        <span class="inline-block rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass[$item->status] ?? 'bg-neutral-100 text-neutral-600' }}">{{ $statusLabel[$item->status] ?? ucfirst($item->status) }}</span>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-4 font-bold tabular-nums {{ $nilaiClass }}">
                                        @if ($item->nilai === null)
                                            <span class="font-medium text-neutral-400">-</span>
                                        @else
                                            {{ $item->berat_perkiraan ? '≈ ' : ($penarikan ? '−' : '+') }}{{ $rupiah($item->nilai) }}
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
@endsection