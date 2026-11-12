{{--
    Kerangka halaman nasabah setelah login: navigasi + menu profil di header.
    Halaman cukup memakai @extends('layouts.nasabah') lalu mengisi section "title", "breadcrumb", dan "card".
--}}
@extends('layouts.auth')

@php
    $user = auth()->user();
    $initials = implode('', array_map(
        fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)),
        array_slice(preg_split('/\s+/', trim($user->name)), 0, 2)
    ));

    // Data nasabah disiapkan middleware EnsureNasabahAktif. Foto profil dilayani lewat route privat.
    $me = request()->attributes->get('nasabah');
    $fotoUrl = $me?->foto_path ? route('nasabah.foto', ['v' => $me->updated_at?->timestamp]) : null;
@endphp

@section('header-nav')
    <nav aria-label="Navigasi utama" class="hidden items-center gap-1 rounded-full border border-neutral-200 bg-[#f4f7f4] px-3 md:flex">
        <a href="{{ route('dashboard') }}" aria-current="page" class="flex h-14 items-center px-3.5 text-sm font-medium text-[#2e7d32]">Beranda</a>
        @foreach ([['Tentang', 'tentang'], ['Lokasi', 'lokasi'], ['Helpdesk', 'helpdesk']] as $item)
            <a href="{{ route('home') }}#{{ $item[1] }}" class="flex h-14 items-center px-3.5 text-sm font-medium text-neutral-800 transition-colors hover:text-[#2e7d32]">{{ $item[0] }}</a>
        @endforeach
    </nav>
@endsection

@section('header-action')
    <details data-menu class="group/menu relative">
        <summary aria-label="Menu akun" class="flex cursor-pointer list-none items-center gap-3 rounded-full py-1 pl-3 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#2e7d32] [&::-webkit-details-marker]:hidden">
            <span class="hidden min-w-0 text-right sm:block">
                <span class="block max-w-52 truncate text-sm font-semibold text-neutral-900">{{ $user->name }}</span>
                <span class="block text-xs text-neutral-500">Nasabah Aktif</span>
            </span>
            @if ($fotoUrl)
                <img src="{{ $fotoUrl }}" alt="" class="size-10 shrink-0 rounded-full object-cover transition group-open/menu:ring-4 group-open/menu:ring-[#2e7d32]/20">
            @else
                <span class="grid size-10 shrink-0 place-items-center rounded-full bg-[#2e7d32] text-sm font-semibold text-white transition group-open/menu:ring-4 group-open/menu:ring-[#2e7d32]/20">{{ $initials }}</span>
            @endif
        </summary>

        <div class="absolute right-0 top-full z-50 mt-3 w-60 rounded-xl border border-neutral-200 bg-white p-1.5 shadow-xl shadow-black/10">
            <div class="border-b border-neutral-100 px-3 py-2.5 sm:hidden">
                <p class="truncate text-sm font-semibold text-neutral-900">{{ $user->name }}</p>
                <p class="text-xs text-neutral-500">Nasabah Aktif</p>
            </div>
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 rounded-lg px-3 py-2.5 text-sm text-neutral-700 transition hover:bg-neutral-50 md:hidden">
                <x-icon name="home" class="size-4 text-neutral-500" />
                Beranda
            </a>
            <a href="{{ route('nasabah.profil') }}" class="flex items-center gap-2.5 rounded-lg px-3 py-2.5 text-sm text-neutral-700 transition hover:bg-neutral-50">
                <x-icon name="user" class="size-4 text-neutral-500" />
                Profil
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2.5 text-left text-sm text-red-600 transition hover:bg-red-50">
                    <x-icon name="log-out" class="size-4" />
                    Keluar
                </button>
            </form>
        </div>
    </details>
@endsection