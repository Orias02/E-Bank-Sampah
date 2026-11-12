@extends('layouts.nasabah')

@section('title', 'Penarikan Berhasil | E-Bank Sampah FILKOM')

@section('breadcrumb')
    <nav aria-label="Breadcrumb" class="mb-4 flex items-center gap-2 text-sm">
        <a href="{{ route('dashboard') }}" class="text-neutral-500 transition-colors hover:text-neutral-900">Beranda</a>
        <x-icon name="chevron-right" class="size-4 text-neutral-400" />
        <span aria-current="page" class="font-medium text-neutral-900">Tarik Saldo</span>
    </nav>
@endsection

@section('card')
    @php
        // Dikirim TarikController@berhasil: $nasabah dan $penarikan (kode, nominal, tujuan, nomor, sisa).
        $rupiah = fn ($angka) => 'Rp ' . number_format($angka, 0, ',', '.');
        $namaDepan = explode(' ', trim($nasabah->nama_lengkap))[0];
        $akhir = $penarikan['nomor'] ? ' (••' . substr($penarikan['nomor'], -4) . ')' : '';
    @endphp

    <div class="mx-auto flex w-full max-w-[43rem] flex-1 animate-fade-up flex-col items-center justify-center px-2 py-14 text-center">
        <span class="grid size-[4.5rem] place-items-center rounded-full bg-[#eaf3ea]">
            <span class="grid size-12 place-items-center rounded-full bg-[#2e7d32] text-white">
                <x-icon name="check" class="size-6" />
            </span>
        </span>

        <h1 class="mt-6 text-[1.9rem] font-normal tracking-tight text-neutral-900">Penarikan Berhasil!</h1>
        <p class="mt-3 text-sm leading-relaxed text-neutral-600">Selamat, {{ $namaDepan }}! Penarikan Anda sudah tercatat dan saldo sudah dikurangi.<br>Ayo setor sampah lebih banyak!</p>

        <dl class="mt-8 w-full max-w-sm space-y-3 rounded-xl border border-neutral-200 bg-[#f7f9f7] px-5 py-4 text-left text-sm">
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Nominal</dt><dd class="font-semibold tabular-nums text-neutral-900">{{ $rupiah($penarikan['nominal']) }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Tujuan</dt><dd class="font-semibold text-neutral-900">{{ $penarikan['tujuan'] }}{{ $akhir }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Sisa saldo</dt><dd class="font-semibold tabular-nums text-[#2e7d32]">{{ $rupiah($penarikan['sisa']) }}</dd></div>
            <div class="flex justify-between gap-4 border-t border-neutral-200 pt-3"><dt class="text-slate-500">Kode transaksi</dt><dd class="font-mono text-xs text-neutral-700">{{ $penarikan['kode'] }}</dd></div>
        </dl>

        <div class="mt-8 w-full border-t border-neutral-200 pt-6">
            <a href="{{ route('dashboard') }}" class="text-sm font-semibold text-[#2e7d32] hover:underline">Kembali ke Beranda</a>
            <span class="mx-3 text-neutral-300" aria-hidden="true">|</span>
            <a href="{{ route('nasabah.riwayat') }}" class="text-sm font-semibold text-[#2e7d32] hover:underline">Lihat Riwayat</a>
        </div>
    </div>
@endsection