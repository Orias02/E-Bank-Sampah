{{--
    Kerangka halaman admin: header (logo, menu admin, menu akun) dan konten selebar halaman.
    Halaman cukup @extends('layouts.admin') lalu mengisi section "title" dan "content".
    $antrean (jumlah nasabah/pengajuan menunggu) dan $namaBankAdmin dibagikan middleware EnsureAdmin.
--}}
@php
    $findImage = function (string $name, array $extensions) {
        foreach ($extensions as $extension) {
            if (file_exists(public_path("images/{$name}.{$extension}"))) {
                return asset("images/{$name}.{$extension}");
            }
        }
        return null;
    };

    $logos = [
        ['name' => 'Logo Kita',   'alt' => 'Logo E-Bank Sampah',          'src' => $findImage('logo-kita', ['svg', 'png', 'webp'])],
        ['name' => 'Logo UB',     'alt' => 'Logo Universitas Brawijaya',  'src' => $findImage('logo-ub', ['svg', 'png', 'webp'])],
        ['name' => 'Logo FILKOM', 'alt' => 'Logo Fakultas Ilmu Komputer', 'src' => $findImage('logo-filkom', ['svg', 'png', 'webp'])],
    ];

    $user = auth()->user();
    $initials = implode('', array_map(
        fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)),
        array_slice(preg_split('/\s+/', trim($user->name)), 0, 2)
    ));
    $antrean = $antrean ?? ['nasabah' => 0, 'pengajuan' => 0];
    $peranAdmin = ($namaBankAdmin ?? null) ? 'Admin ' . $namaBankAdmin : 'Admin';

    $menu = [
        ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'aktif' => request()->routeIs('admin.dashboard'), 'lencana' => 0],
        ['label' => 'Nasabah', 'route' => 'admin.nasabah.index', 'aktif' => request()->routeIs('admin.nasabah.*'), 'lencana' => $antrean['nasabah']],
        ['label' => 'Pengajuan', 'route' => 'admin.pengajuan.index', 'aktif' => request()->routeIs('admin.pengajuan.*'), 'lencana' => $antrean['pengajuan']],
        ['label' => 'Harga Sampah', 'route' => 'admin.harga.index', 'aktif' => request()->routeIs('admin.harga.*'), 'lencana' => 0],
    ];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('title', 'Admin | E-Bank Sampah FILKOM')</title>
    <meta name="theme-color" content="#ffffff">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-[#f4f7f4] font-sans text-slate-800 antialiased">

    {{-- ============================== HEADER ============================== --}}
    <header class="border-b border-neutral-200 bg-white">
        <div class="mx-auto flex max-w-[1440px] items-center justify-between gap-3 px-6 py-5 sm:px-12 sm:py-6">
            <a href="{{ route('admin.dashboard') }}" aria-label="E-Bank Sampah FILKOM, ke dashboard admin" class="flex items-center divide-x divide-neutral-200 rounded-[2rem] border border-neutral-200 bg-white p-2 text-neutral-900 transition duration-300 hover:border-neutral-300 hover:shadow-md hover:shadow-black/5">
                @foreach ($logos as $logo)
                    <span class="flex h-12 min-w-16 flex-col items-center justify-center px-3 sm:min-w-24 sm:px-5">
                        @if ($logo['src'])
                            <img src="{{ $logo['src'] }}" alt="{{ $logo['alt'] }}" class="h-8 w-auto max-w-24 object-contain sm:h-9">
                        @else
                            <span class="hidden text-[9px] font-medium uppercase tracking-wide text-neutral-500 sm:block">Placeholder</span>
                            <span class="text-[10px] font-bold uppercase leading-tight sm:text-[11px]">{{ $logo['name'] }}</span>
                        @endif
                    </span>
                @endforeach
            </a>

            <nav aria-label="Navigasi admin" class="hidden items-center gap-1 rounded-full border border-neutral-200 bg-[#f4f7f4] px-3 lg:flex">
                @foreach ($menu as $item)
                    <a href="{{ route($item['route']) }}" @if ($item['aktif']) aria-current="page" @endif class="flex h-14 items-center gap-2 px-3.5 text-sm font-medium transition-colors {{ $item['aktif'] ? 'text-[#2e7d32]' : 'text-neutral-800 hover:text-[#2e7d32]' }}">
                        {{ $item['label'] }}
                        @if ($item['lencana'] > 0)
                            <span class="grid h-5 min-w-5 place-items-center rounded-full bg-amber-500 px-1.5 text-[0.65rem] font-bold leading-none text-white">{{ $item['lencana'] }}</span>
                        @endif
                    </a>
                @endforeach
            </nav>

            <details data-menu class="group/menu relative">
                <summary aria-label="Menu akun" class="flex cursor-pointer list-none items-center gap-3 rounded-full py-1 pl-3 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#2e7d32] [&::-webkit-details-marker]:hidden">
                    <span class="hidden min-w-0 text-right sm:block">
                        <span class="block max-w-52 truncate text-sm font-semibold text-neutral-900">{{ $user->name }}</span>
                        <span class="block max-w-52 truncate text-xs text-neutral-500">{{ $peranAdmin }}</span>
                    </span>
                    <span class="grid size-10 shrink-0 place-items-center rounded-full bg-[#2e7d32] text-sm font-semibold text-white transition group-open/menu:ring-4 group-open/menu:ring-[#2e7d32]/20">{{ $initials }}</span>
                </summary>

                <div class="absolute right-0 top-full z-50 mt-3 w-60 rounded-xl border border-neutral-200 bg-white p-1.5 shadow-xl shadow-black/10">
                    <div class="border-b border-neutral-100 px-3 py-2.5 sm:hidden">
                        <p class="truncate text-sm font-semibold text-neutral-900">{{ $user->name }}</p>
                        <p class="truncate text-xs text-neutral-500">{{ $peranAdmin }}</p>
                    </div>
                    <div class="lg:hidden">
                        @foreach ($menu as $item)
                            <a href="{{ route($item['route']) }}" class="flex items-center justify-between gap-2.5 rounded-lg px-3 py-2.5 text-sm transition hover:bg-neutral-50 {{ $item['aktif'] ? 'font-semibold text-[#2e7d32]' : 'text-neutral-700' }}">
                                {{ $item['label'] }}
                                @if ($item['lencana'] > 0)
                                    <span class="grid h-5 min-w-5 place-items-center rounded-full bg-amber-500 px-1.5 text-[0.65rem] font-bold leading-none text-white">{{ $item['lencana'] }}</span>
                                @endif
                            </a>
                        @endforeach
                        <div class="my-1 h-px bg-neutral-100"></div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2.5 text-left text-sm text-red-600 transition hover:bg-red-50">
                            <x-icon name="log-out" class="size-4" />
                            Keluar
                        </button>
                    </form>
                </div>
            </details>
        </div>
    </header>

    {{-- ============================== KONTEN ============================== --}}
    <main class="mx-auto w-full max-w-[1440px] flex-1 px-6 pb-14 pt-6 sm:px-12">
        @yield('breadcrumb')

        {{-- Pesan berhasil dan galat umum (mis. aksi pada data yang sudah berubah) --}}
        @if (session('status'))
            <div role="status" class="mb-5 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                <x-icon name="circle-check" class="mt-0.5 size-5 shrink-0 text-[#2e7d32]" />
                <p>{{ session('status') }}</p>
            </div>
        @endif
        @error('aksi')
            <div role="alert" class="mb-5 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <x-icon name="alert-circle" class="mt-0.5 size-5 shrink-0 text-red-600" />
                <p>{{ $message }}</p>
            </div>
        @enderror

        <div class="animate-fade-up">
            @yield('content')
        </div>
    </main>

    <footer class="border-t border-neutral-200">
        <div class="mx-auto flex max-w-[1440px] items-center justify-between gap-4 px-6 py-8 text-sm text-neutral-500 sm:px-12">
            <p>&copy; 2026 E-Bank Sampah FILKOM</p>
            <p>Panel Admin</p>
        </div>
    </footer>

</body>
</html>