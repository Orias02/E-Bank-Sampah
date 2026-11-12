{{--
    Kerangka halaman wizard pendaftaran nasabah (Data Diri, Alamat, Berhasil).
    Halaman wizard cukup memakai @extends('layouts.wizard') lalu mengisi section "title" dan "card".
--}}
@extends('layouts.auth')

@section('header-nav')
    <nav aria-label="Navigasi utama" class="hidden items-center gap-1 rounded-full border border-neutral-200 bg-[#f4f7f4] px-3 md:flex">
        @foreach ([['Beranda', 'beranda'], ['Tentang', 'tentang'], ['Lokasi', 'lokasi'], ['Helpdesk', 'helpdesk']] as $item)
            <a href="{{ route('home') }}#{{ $item[1] }}" class="flex h-14 items-center px-3.5 text-sm font-medium text-neutral-800 transition-colors hover:text-[#2e7d32]">{{ $item[0] }}</a>
        @endforeach
    </nav>
@endsection

@section('header-action')
    <a href="{{ route('login') }}" class="inline-flex h-12 items-center rounded-full border border-neutral-200 bg-white px-6 text-sm font-semibold text-neutral-800 transition duration-300 hover:-translate-y-0.5 hover:border-neutral-300 hover:shadow-md hover:shadow-black/5 sm:h-14 sm:px-8">Login</a>
@endsection

@section('breadcrumb')
    <nav aria-label="Breadcrumb" class="mb-4 flex items-center gap-2 text-sm">
        <a href="{{ route('home') }}" class="text-neutral-500 transition-colors hover:text-neutral-900">Beranda</a>
        <x-icon name="chevron-right" class="size-4 text-neutral-400" />
        <span aria-current="page" class="font-medium text-neutral-900">Pendaftaran Nasabah</span>
    </nav>
@endsection