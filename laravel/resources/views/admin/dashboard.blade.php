@extends('layouts.admin')

@section('title', 'Dashboard Admin | E-Bank Sampah FILKOM')

@section('content')
    @php
        // Dikirim Admin\DashboardController.
        $rupiah = fn ($angka) => 'Rp ' . number_format($angka, 0, ',', '.');
        $kg = fn ($angka) => rtrim(rtrim(number_format($angka, 2, ',', '.'), '0'), ',');
        $tanggalHariIni = \Illuminate\Support\Carbon::parse($hariIni, 'Asia/Jakarta');

        $kartu = [
            ['label' => 'Nasabah menunggu aktivasi', 'nilai' => $jumlahNasabahMenunggu, 'ikon' => 'users', 'url' => route('admin.nasabah.index', ['status' => 'menunggu']), 'sorot' => $jumlahNasabahMenunggu > 0],
            ['label' => 'Pengajuan setoran menunggu', 'nilai' => $jumlahPengajuanMenunggu, 'ikon' => 'package', 'url' => route('admin.pengajuan.index', ['status' => 'menunggu']), 'sorot' => $jumlahPengajuanMenunggu > 0],
            ['label' => 'Sampah masuk bulan ini', 'nilai' => $kg($sampahBulanIni) . ' kg', 'ikon' => 'scale', 'url' => null, 'sorot' => false],
            ['label' => 'Total saldo nasabah', 'nilai' => $rupiah($totalSaldo), 'ikon' => 'wallet', 'url' => null, 'sorot' => false],
        ];
    @endphp

    <header class="mb-6">
        <h1 class="text-[1.75rem] font-bold tracking-tight text-neutral-900">Dashboard</h1>
        <p class="mt-2 text-sm text-slate-500">{{ $tanggalHariIni->locale('id')->translatedFormat('l, d F Y') }}@if ($namaBankAdmin ?? null) · {{ $namaBankAdmin }}@else · Semua bank sampah @endif</p>
    </header>

    <section aria-label="Ringkasan" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($kartu as $item)
            @php
                $kelas = 'flex items-center gap-4 rounded-2xl border bg-white p-5 transition ' . ($item['sorot'] ? 'border-amber-200' : 'border-neutral-200') . ($item['url'] ? ' hover:border-[#2e7d32]/40 hover:shadow-md hover:shadow-black/5' : '');
            @endphp
            @if ($item['url'])
                <a href="{{ $item['url'] }}" class="{{ $kelas }}">
            @else
                <div class="{{ $kelas }}">
            @endif
                <span class="grid size-12 shrink-0 place-items-center rounded-xl {{ $item['sorot'] ? 'bg-amber-50 text-amber-600' : 'bg-[#eaf3ea] text-[#2e7d32]' }}">
                    <x-icon :name="$item['ikon']" class="size-6" />
                </span>
                <span class="min-w-0">
                    <span class="block text-xs font-medium text-slate-500">{{ $item['label'] }}</span>
                    <span class="mt-1 block truncate text-2xl font-bold tracking-tight text-neutral-900 tabular-nums">{{ $item['nilai'] }}</span>
                </span>
            @if ($item['url'])
                </a>
            @else
                </div>
            @endif
        @endforeach
    </section>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">

        {{-- Jadwal jemput hari ini dan besok --}}
        <section aria-labelledby="judul-jemput" class="rounded-2xl border border-neutral-200 bg-white xl:col-span-2">
            <header class="flex items-center justify-between gap-3 border-b border-neutral-200 px-5 py-4">
                <h2 id="judul-jemput" class="flex items-center gap-2 text-base font-semibold text-neutral-900">
                    <x-icon name="truck" class="size-5 text-[#2e7d32]" />
                    Jadwal jemput hari ini &amp; besok
                </h2>
                <a href="{{ route('admin.pengajuan.index', ['status' => 'semua', 'metode' => 'jemput']) }}" class="text-sm font-semibold text-[#2e7d32] hover:underline">Lihat semua jemputan</a>
            </header>

            @if ($jadwalJemput->isEmpty())
                <p class="px-5 py-10 text-center text-sm text-slate-500">Tidak ada jadwal jemput untuk hari ini maupun besok.</p>
            @else
                <ul class="divide-y divide-neutral-100">
                    @foreach ($jadwalJemput as $item)
                        @php $hariIniJadwal = $item->tanggal_rencana->toDateString() === $hariIni; @endphp
                        <li>
                            <a href="{{ route('admin.pengajuan.show', $item->id) }}" class="flex flex-wrap items-center gap-x-4 gap-y-2 px-5 py-4 transition hover:bg-neutral-50">
                                <span class="w-20 shrink-0">
                                    <x-pil :tone="$hariIniJadwal ? 'proses' : 'info'">{{ $hariIniJadwal ? 'Hari ini' : 'Besok' }}</x-pil>
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-semibold text-neutral-900">{{ $item->nasabah->nama_lengkap }}</span>
                                    <span class="mt-0.5 block truncate text-xs text-neutral-500">{{ $item->nasabah->alamat_lengkap }}, {{ $item->nasabah->kelurahan }}</span>
                                </span>
                                <span class="text-xs font-medium text-neutral-500">{{ $item->kode() }}</span>
                                <x-pil :tone="$item->status === 'diterima' ? 'selesai' : 'proses'">{{ $item->status === 'diterima' ? 'Diterima' : 'Menunggu' }}</x-pil>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        {{-- Nasabah menunggu aktivasi --}}
        <section aria-labelledby="judul-nasabah" class="rounded-2xl border border-neutral-200 bg-white">
            <header class="flex items-center justify-between gap-3 border-b border-neutral-200 px-5 py-4">
                <h2 id="judul-nasabah" class="flex items-center gap-2 text-base font-semibold text-neutral-900">
                    <x-icon name="users" class="size-5 text-[#2e7d32]" />
                    Menunggu aktivasi
                </h2>
                <a href="{{ route('admin.nasabah.index', ['status' => 'menunggu']) }}" class="text-sm font-semibold text-[#2e7d32] hover:underline">Lihat semua</a>
            </header>

            @if ($nasabahTerbaru->isEmpty())
                <p class="px-5 py-10 text-center text-sm text-slate-500">Tidak ada pendaftaran yang menunggu. Semua sudah diproses.</p>
            @else
                <ul class="divide-y divide-neutral-100">
                    @foreach ($nasabahTerbaru as $item)
                        <li>
                            <a href="{{ route('admin.nasabah.show', $item->id) }}" class="flex items-center gap-4 px-5 py-4 transition hover:bg-neutral-50">
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-semibold text-neutral-900">{{ $item->nama_lengkap }}</span>
                                    <span class="mt-0.5 block truncate text-xs text-neutral-500">{{ $item->kelurahan }} · mendaftar {{ $item->created_at->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d M Y, H:i') }}</span>
                                </span>
                                <span class="shrink-0 text-sm font-semibold text-[#2e7d32]">Periksa</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        {{-- Pengajuan setoran menunggu --}}
        <section aria-labelledby="judul-pengajuan" class="rounded-2xl border border-neutral-200 bg-white">
            <header class="flex items-center justify-between gap-3 border-b border-neutral-200 px-5 py-4">
                <h2 id="judul-pengajuan" class="flex items-center gap-2 text-base font-semibold text-neutral-900">
                    <x-icon name="package" class="size-5 text-[#2e7d32]" />
                    Pengajuan setoran menunggu
                </h2>
                <a href="{{ route('admin.pengajuan.index', ['status' => 'menunggu']) }}" class="text-sm font-semibold text-[#2e7d32] hover:underline">Lihat semua</a>
            </header>

            @if ($pengajuanTerbaru->isEmpty())
                <p class="px-5 py-10 text-center text-sm text-slate-500">Tidak ada pengajuan yang menunggu.</p>
            @else
                <ul class="divide-y divide-neutral-100">
                    @foreach ($pengajuanTerbaru as $item)
                        <li>
                            <a href="{{ route('admin.pengajuan.show', $item->id) }}" class="flex items-center gap-4 px-5 py-4 transition hover:bg-neutral-50">
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-semibold text-neutral-900">{{ $item->nasabah->nama_lengkap }}</span>
                                    <span class="mt-0.5 block truncate text-xs text-neutral-500">
                                        {{ $item->metode === 'jemput' ? 'Dijemput' : 'Diantar' }} {{ $item->tanggal_rencana->locale('id')->translatedFormat('d M Y') }}
                                        · ≈ {{ $kg($item->perkiraanBerat()) }} kg
                                    </span>
                                </span>
                                <span class="shrink-0 text-sm font-bold tabular-nums text-amber-600">≈ {{ $rupiah($item->perkiraanNilai()) }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
@endsection