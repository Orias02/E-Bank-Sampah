@php
    // Gambar: sama dengan landing page, ambil dari public/images/ (placeholder jika belum ada).
    $findImage = function (string $name, array $extensions) {
        foreach ($extensions as $extension) {
            if (file_exists(public_path("images/{$name}.{$extension}"))) {
                return asset("images/{$name}.{$extension}");
            }
        }
        return null;
    };

    $heroImage = $findImage('hero', ['jpg', 'jpeg', 'webp', 'png']);

    // Nomor WhatsApp Helpdesk: atur di config/helpdesk.php.
    $helpdeskWhatsapp = 'https://wa.me/' . config('helpdesk.whatsapp') . '?text=' . rawurlencode('Halo Helpdesk E-Bank Sampah FILKOM, saya butuh bantuan.');

    $logos = [
        ['name' => 'Logo Kita',   'alt' => 'Logo E-Bank Sampah',          'src' => $findImage('logo-kita', ['svg', 'png', 'webp'])],
        ['name' => 'Logo UB',     'alt' => 'Logo Universitas Brawijaya',  'src' => $findImage('logo-ub', ['svg', 'png', 'webp'])],
        ['name' => 'Logo FILKOM', 'alt' => 'Logo Fakultas Ilmu Komputer', 'src' => $findImage('logo-filkom', ['svg', 'png', 'webp'])],
    ];

    $panelFeatures = [
        ['icon' => 'wallet', 'title' => 'Sampah jadi tabungan', 'text' => 'Setor sampah terpilah, kumpulkan saldo.'],
        ['icon' => 'pin', 'title' => 'Dekat dengan rumah', 'text' => 'Terhubung ke bank sampah di lingkungan Anda.'],
        ['icon' => 'sprout', 'title' => 'Dampak nyata untuk lingkungan', 'text' => 'Mulai kebiasaan baik untuk kota lebih bersih.'],
    ];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'E-Bank Sampah FILKOM')</title>
    <meta name="theme-color" content="#ffffff">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-[#f4f7f4] font-sans text-slate-800 antialiased">

    {{-- ============================== HEADER ============================== --}}
    <header class="border-b border-neutral-200 bg-white">
        <div class="mx-auto flex max-w-[1440px] items-center justify-between gap-3 px-6 py-5 sm:px-12 sm:py-8">
            <a href="{{ route('home') }}" aria-label="E-Bank Sampah FILKOM, ke halaman utama" class="flex items-center divide-x divide-neutral-200 rounded-[2rem] border border-neutral-200 bg-white p-2 text-neutral-900 transition duration-300 hover:border-neutral-300 hover:shadow-md hover:shadow-black/5">
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

            @yield('header-nav')

            @yield('header-action')
        </div>
    </header>

    {{-- ============================== KONTEN ============================== --}}
    <main class="mx-auto w-full max-w-[1440px] flex-1 px-6 pb-14 pt-6 sm:px-12">
        @yield('breadcrumb')

        <div class="grid gap-8 lg:grid-cols-[minmax(0,456fr)_minmax(0,854fr)]">

            {{-- Panel kiri (hanya tampil di layar lebar) --}}
            <aside class="relative isolate hidden min-h-[44rem] animate-fade-up flex-col justify-between overflow-hidden rounded-2xl bg-[#1b4a31] p-9 text-white lg:flex">
                <div class="absolute inset-0 -z-20 bg-[linear-gradient(135deg,#1b4a31,#0f2e1f)] bg-cover bg-center" @if ($heroImage) style="background-image: url('{{ $heroImage }}')" @endif></div>
                <div class="absolute inset-0 -z-10 bg-linear-to-b from-[#1b4a31] via-[#1b4a31]/90 to-[#1b4a31]/75"></div>

                <p class="flex items-center gap-3 text-xs font-semibold uppercase tracking-[0.15em]">
                    <x-icon name="recycle" class="size-5" />
                    E-Bank Sampah FILKOM
                </p>

                <div>
                    {{-- Halaman bisa mengganti judul panel lewat section "panel-heading" (mis. halaman Berhasil). --}}
                    @hasSection('panel-heading')
                        @yield('panel-heading')
                    @else
                        <h2 class="text-[2.75rem] font-semibold leading-[1.15] tracking-tight">
                            <span class="block">Sampah bernilai.</span>
                            <span class="block">Langkah kecil,</span>
                            <span class="block">dampak berarti.</span>
                        </h2>
                    @endif
                    <p class="mt-6 max-w-sm leading-relaxed text-white/85">Jadi bagian dari perubahan. Kelola sampah dari rumah dan mulai tabungan Anda bersama kami.</p>

                    <ul class="mt-9 space-y-6">
                        @foreach ($panelFeatures as $feature)
                            <li class="flex items-center gap-4">
                                <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-white/10 ring-1 ring-white/10">
                                    <x-icon :name="$feature['icon']" class="size-5" />
                                </span>
                                <div>
                                    <p class="font-medium">{{ $feature['title'] }}</p>
                                    <p class="mt-0.5 text-sm text-white/70">{{ $feature['text'] }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <p class="border-t border-white/20 pt-6 text-sm leading-relaxed text-white/80">Dari rumah Anda, untuk lingkungan yang lebih bersih dan berkelanjutan.</p>
            </aside>

            {{-- Kartu form --}}
            <section class="flex min-w-0 flex-col rounded-2xl border border-neutral-200 bg-white p-5 sm:p-9">
                {{-- Halaman bisa mengisi seluruh kartu lewat section "card" (dipakai wizard pendaftaran),
                     atau cukup section "content" untuk kartu form biasa (login, akun). --}}
                @hasSection('card')
                    @yield('card')
                @else
                    <div class="h-px bg-neutral-200"></div>
                    <div class="mt-6 flex-1 animate-fade-up rounded-[1.75rem] bg-white p-5 shadow-[0_10px_40px_-12px_rgba(15,23,42,0.14)] [animation-delay:100ms] sm:p-8">
                        @yield('content')
                    </div>
                @endif
            </section>
        </div>

        <p class="mt-8 flex items-center justify-center gap-2 text-sm text-neutral-500">
            <x-icon name="help" class="size-4" />
            <span>Butuh bantuan? <a href="{{ $helpdeskWhatsapp }}" target="_blank" rel="noopener" class="font-semibold text-[#2e7d32] hover:underline">Hubungi Helpdesk</a></span>
        </p>
    </main>

    {{-- ============================== FOOTER ============================== --}}
    <footer class="border-t border-neutral-200">
        <div class="mx-auto flex max-w-[1440px] items-center justify-between gap-4 px-6 py-8 text-sm text-neutral-500 sm:px-12">
            <p>&copy; 2026 E-Bank Sampah FILKOM</p>
            <a href="#" class="transition-colors hover:text-neutral-800">Kebijakan Privasi</a>
        </div>
    </footer>

</body>
</html>