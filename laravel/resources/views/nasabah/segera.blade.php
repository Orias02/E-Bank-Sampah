@extends('layouts.nasabah')

@section('title', $judul . ' | E-Bank Sampah FILKOM')

@section('breadcrumb')
    <nav aria-label="Breadcrumb" class="mb-4 flex items-center gap-2 text-sm">
        <a href="{{ route('dashboard') }}" class="text-neutral-500 transition-colors hover:text-neutral-900">Beranda</a>
        <x-icon name="chevron-right" class="size-4 text-neutral-400" />
        <span aria-current="page" class="font-medium text-neutral-900">{{ $judul }}</span>
    </nav>
@endsection

{{-- Halaman sementara untuk fitur yang desainnya belum dibuat. --}}
@section('card')
    <div class="flex flex-1 animate-fade-up flex-col items-center justify-center px-4 py-16 text-center">
        <span class="grid size-16 place-items-center rounded-full bg-[#eaf3ea] text-[#2e7d32]">
            <x-icon name="clock" class="size-8" />
        </span>
        <h1 class="mt-6 text-[1.6rem] font-normal tracking-tight text-neutral-900">{{ $judul }}</h1>
        <p class="mt-3 max-w-sm text-sm leading-relaxed text-neutral-600">Fitur ini sedang disiapkan dan akan segera tersedia.</p>
        <a href="{{ route('dashboard') }}" class="group mt-8 inline-flex h-12 items-center gap-2 rounded-lg border border-[#2e7d32] px-5 text-sm font-semibold text-[#2e7d32] transition hover:bg-[#2e7d32]/5">
            <x-icon name="arrow-left" class="size-4 transition-transform duration-300 group-hover:-translate-x-0.5" />
            Kembali ke Beranda
        </a>
    </div>
@endsection