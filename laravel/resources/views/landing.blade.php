@php
    /*
    |--------------------------------------------------------------------------
    | Konten yang bisa Anda ubah
    |--------------------------------------------------------------------------
    | Semua teks/data yang masih contoh dikumpulkan di sini supaya mudah diganti.
    */
    $whatsapp = [
        'display' => config('helpdesk.whatsapp_display'),   // atur di config/helpdesk.php
        'link' => 'https://wa.me/' . config('helpdesk.whatsapp') . '?text=' . rawurlencode('Halo Helpdesk E-Bank Sampah FILKOM, saya butuh bantuan.'),
    ];
    $email = config('helpdesk.email');                                                         // atur di config/helpdesk.php
    $address = 'Fakultas Ilmu Komputer, Universitas Brawijaya, Jl. Veteran No. 8, Ketawanggede, Lowokwaru, Kota Malang, Jawa Timur 65145'; // TODO: pastikan alamat bank sampah
    $hours = 'Senin – Jumat, 08.00 – 16.00 WIB';                                               // TODO: jam operasional asli
    $mapQuery = 'Fakultas Ilmu Komputer Universitas Brawijaya';

    // Gambar: taruh file di public/images/. Jika file belum ada, halaman memakai placeholder.
    //   hero.jpg (atau .jpeg/.webp/.png), logo-kita.png, logo-ub.png, logo-filkom.png (atau .svg/.webp)
    $findImage = function (string $name, array $extensions) {
        foreach ($extensions as $extension) {
            if (file_exists(public_path("images/{$name}.{$extension}"))) {
                return asset("images/{$name}.{$extension}");
            }
        }
        return null;
    };

    $heroImage = $findImage('hero', ['jpg', 'jpeg', 'webp', 'png']);

    $logos = [
        ['name' => 'Logo Kita',   'alt' => 'Logo E-Bank Sampah',           'src' => $findImage('logo-kita', ['svg', 'png', 'webp'])],
        ['name' => 'Logo UB',     'alt' => 'Logo Universitas Brawijaya',   'src' => $findImage('logo-ub', ['svg', 'png', 'webp'])],
        ['name' => 'Logo FILKOM', 'alt' => 'Logo Fakultas Ilmu Komputer',  'src' => $findImage('logo-filkom', ['svg', 'png', 'webp'])],
    ];

    $navItems = [
        ['id' => 'beranda', 'label' => 'Beranda'],
        ['id' => 'tentang', 'label' => 'Tentang'],
        ['id' => 'lokasi', 'label' => 'Lokasi'],
        ['id' => 'helpdesk', 'label' => 'Helpdesk'],
    ];

    $features = [
        ['icon' => 'database', 'title' => 'Data terpusat', 'text' => 'Nasabah, jenis sampah, harga, dan riwayat transaksi tersimpan dalam satu database terintegrasi, mudah dicari dan dikelola admin.'],
        ['icon' => 'calculator', 'title' => 'Nilai dihitung otomatis', 'text' => 'Admin cukup memasukkan jenis dan berat sampah. Nilai setoran langsung dihitung dari harga per kilogram, tanpa hitung manual.'],
        ['icon' => 'history', 'title' => 'Harga tercatat per transaksi', 'text' => 'Harga saat setoran disimpan bersama transaksinya, sehingga perubahan harga di kemudian hari tidak mengubah riwayat lama.'],
    ];

    $steps = [
        ['title' => 'Daftar online', 'text' => 'Isi formulir pendaftaran nasabah di website dalam tiga langkah: data diri, alamat, lalu akun. Siapkan foto KTP (JPG, PNG, atau PDF, maksimal 5 MB).'],
        ['title' => 'Konfirmasi ke Helpdesk', 'text' => 'Setelah mendaftar, hubungi Helpdesk untuk konfirmasi. Admin memeriksa dan memverifikasi data pendaftaran sebelum akun dinyatakan aktif.'],
        ['title' => 'Setor sampah', 'text' => 'Bawa sampah ke bank sampah. Admin mencatat jenis dan berat sampah, lalu nilai setoran dihitung otomatis berdasarkan harga per kilogram.'],
        ['title' => 'Pantau dan tarik saldo', 'text' => 'Saldo Anda adalah total nilai setoran dikurangi total penarikan. Penarikan saldo diajukan lewat admin, yang mencatat dan memvalidasi jumlahnya.'],
    ];

    // Harga CONTOH untuk simulasi saja. Harga asli nanti dari database (Manajemen Harga Sampah).
    $wastes = [
        ['name' => 'Botol plastik (PET)', 'price' => 3500],
        ['name' => 'Kardus', 'price' => 2000],
        ['name' => 'Kertas HVS', 'price' => 2500],
        ['name' => 'Kaleng / logam', 'price' => 5000],
        ['name' => 'Botol kaca', 'price' => 500],
    ];
    $simWeight = 5;
    $simTotal = number_format($simWeight * $wastes[0]['price'], 0, ',', '.');

    $faqs = [
        ['q' => 'Bagaimana cara menjadi nasabah?', 'a' => 'Klik tombol Pendaftaran Nasabah, isi data diri, alamat, dan akun, lalu hubungi Helpdesk untuk konfirmasi. Akun aktif setelah datanya diverifikasi admin.'],
        ['q' => 'Kenapa akun saya belum aktif?', 'a' => 'Akun baru aktif setelah admin memeriksa dan memverifikasi data pendaftaran Anda. Jika belum ada kabar, hubungi Helpdesk dan sebutkan nama serta email yang Anda daftarkan.'],
        ['q' => 'Siapa yang mencatat setoran dan penarikan saldo?', 'a' => 'Seluruh pencatatan dilakukan oleh admin bank sampah. Nasabah menyerahkan sampah atau mengajukan penarikan, lalu admin yang mengisi transaksinya di sistem.'],
        ['q' => 'Apakah perubahan harga memengaruhi setoran lama?', 'a' => 'Tidak. Harga per kilogram saat setoran disimpan bersama transaksinya, jadi riwayat lama tetap bernilai sama walau harga berubah kemudian.'],
    ];

    // Ikon (gaya garis, 24x24)
    $icons = [
        'arrow-up-right' => '<path d="M7 17 17 7"/><path d="M7 7h10v10"/>',
        'arrow-up' => '<path d="m5 12 7-7 7 7"/><path d="M12 19V5"/>',
        'chevron-down' => '<path d="m6 9 6 6 6-6"/>',
        'database' => '<ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14c0 1.7 4 3 9 3s9-1.3 9-3V5"/><path d="M3 12c0 1.7 4 3 9 3s9-1.3 9-3"/>',
        'calculator' => '<rect width="16" height="20" x="4" y="2" rx="2"/><path d="M8 6h8"/><path d="M16 14v4"/><path d="M8 10h.01M12 10h.01M16 10h.01M8 14h.01M12 14h.01M8 18h.01M12 18h.01"/>',
        'history' => '<path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M12 7v5l4 2"/>',
        'leaf' => '<path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"/><path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"/>',
        'pin' => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
        'clock' => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
        'message' => '<path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/>',
        'mail' => '<rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>',
        'copy' => '<rect width="14" height="14" x="8" y="8" rx="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/>',
    ];
    $icon = fn (string $name, string $class = 'size-5') =>
        '<svg class="' . $class . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $icons[$name] . '</svg>';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>E-Bank Sampah FILKOM</title>
    <meta name="description" content="E-Bank Sampah FILKOM: sistem informasi administrasi bank sampah untuk mengelola nasabah, harga sampah, setoran, dan saldo secara digital.">
    <meta name="theme-color" content="#0a0a0a">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <script>document.documentElement.classList.add('js');</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-neutral-950 font-sans text-white antialiased">

    {{-- ============================== HEADER ============================== --}}
    <header id="site-header" data-scrolled="false" data-menu="closed" class="group/header fixed inset-x-0 top-0 z-50 px-4 pt-4 sm:px-8 sm:pt-6 lg:px-12 lg:pt-8">
        <div class="mx-auto flex max-w-[1440px] items-center justify-between gap-3">

            {{-- Logo (Kita · UB · FILKOM) --}}
            <a href="#beranda" aria-label="E-Bank Sampah FILKOM, kembali ke Beranda" class="flex items-center divide-x divide-neutral-200 rounded-[2rem] bg-white p-2 text-neutral-900 shadow-lg shadow-black/10 transition duration-300 hover:shadow-xl">
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

            {{-- Menu desktop --}}
            <nav id="main-nav" aria-label="Navigasi utama" class="relative hidden items-center gap-1 rounded-full border border-white/20 bg-black/30 px-3 backdrop-blur-md transition-colors duration-300 group-data-[scrolled=true]/header:bg-neutral-950/75 md:flex">
                @foreach ($navItems as $item)
                    <a href="#{{ $item['id'] }}" data-nav-link data-active="{{ $loop->first ? 'true' : 'false' }}" class="flex h-14 items-center px-3.5 text-sm font-medium text-white/65 transition-colors hover:text-white focus-visible:text-white data-[active=true]:text-white">{{ $item['label'] }}</a>
                @endforeach
                <span id="nav-indicator" aria-hidden="true" class="pointer-events-none absolute bottom-3 left-0 h-0.5 w-4 rounded-full bg-white opacity-0 transition-[transform,opacity] duration-300 ease-out"></span>
            </nav>

            <div class="flex items-center gap-3">
                <a href="{{ route('login') }}" class="hidden h-14 items-center rounded-full bg-white px-8 text-sm font-semibold text-neutral-900 shadow-lg shadow-black/10 transition duration-300 hover:-translate-y-0.5 hover:bg-neutral-200 active:translate-y-0 md:inline-flex">Login</a>

                {{-- Tombol menu mobile --}}
                <button id="menu-toggle" type="button" aria-expanded="false" aria-controls="mobile-menu" aria-label="Buka menu" class="relative size-12 rounded-full bg-white text-neutral-900 shadow-lg shadow-black/10 md:hidden">
                    <span class="absolute left-1/2 top-[19px] h-0.5 w-5 -translate-x-1/2 rounded bg-current transition duration-300 group-data-[menu=open]/header:translate-y-1 group-data-[menu=open]/header:rotate-45"></span>
                    <span class="absolute left-1/2 top-[27px] h-0.5 w-5 -translate-x-1/2 rounded bg-current transition duration-300 group-data-[menu=open]/header:-translate-y-1 group-data-[menu=open]/header:-rotate-45"></span>
                </button>
            </div>
        </div>

        {{-- Panel menu mobile --}}
        <div id="mobile-menu" inert class="pointer-events-none absolute inset-x-4 top-full mt-2 origin-top scale-95 rounded-3xl border border-white/15 bg-neutral-950/90 p-3 opacity-0 backdrop-blur-xl transition duration-300 group-data-[menu=open]/header:pointer-events-auto group-data-[menu=open]/header:scale-100 group-data-[menu=open]/header:opacity-100 sm:inset-x-8 md:hidden">
            @foreach ($navItems as $item)
                <a href="#{{ $item['id'] }}" data-nav-link data-active="{{ $loop->first ? 'true' : 'false' }}" class="flex h-12 items-center rounded-2xl px-4 text-base font-medium text-white/70 transition-colors hover:bg-white/5 hover:text-white data-[active=true]:bg-white/10 data-[active=true]:text-white">{{ $item['label'] }}</a>
            @endforeach
            <a href="{{ route('login') }}" class="mt-2 flex h-12 items-center justify-center rounded-2xl bg-white text-base font-semibold text-neutral-900 transition hover:bg-neutral-200">Login</a>
        </div>
    </header>

    <main>

        {{-- ============================== BERANDA (HERO) ============================== --}}
        <section id="beranda" data-section class="relative isolate flex min-h-screen items-center overflow-hidden bg-neutral-950">
            <div id="hero-bg" class="absolute -inset-[3%] -z-20 bg-[linear-gradient(135deg,#2a2f2c,#0a0b0a)] bg-cover bg-center will-change-transform" @if ($heroImage) style="background-image: url('{{ $heroImage }}')" @endif></div>
            <div class="absolute inset-0 -z-10 bg-black/55"></div>
            <div class="absolute inset-x-0 bottom-0 -z-10 h-1/3 bg-linear-to-t from-neutral-950/80 to-transparent"></div>

            <div class="mx-auto w-full max-w-[1440px] px-6 pb-28 pt-36 sm:px-12 lg:px-20">
                <h1 class="text-[clamp(2.75rem,6.1vw,5.5rem)] font-bold leading-[0.95] tracking-[-0.045em] text-white">
                    <span class="block animate-fade-up">E-Bank Sampah</span>
                    <span class="block animate-fade-up [animation-delay:120ms]">FILKOM</span>
                </h1>
                <p class="mt-8 animate-fade-up text-lg text-white/80 [animation-delay:260ms] sm:text-xl">Kelompok 1</p>

                <a href="{{ route('nasabah.register') }}" class="group mt-10 inline-flex h-[60px] animate-fade-up items-center gap-5 rounded-full border border-white/70 px-7 text-base font-medium text-white backdrop-blur-[2px] transition-colors duration-300 [animation-delay:400ms] hover:bg-white hover:text-neutral-900 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-white">
                    Pendaftaran Nasabah
                    {!! $icon('arrow-up-right', 'size-4 transition-transform duration-300 group-hover:-translate-y-0.5 group-hover:translate-x-0.5') !!}
                </a>
            </div>

            <a href="#tentang" aria-label="Gulir ke bagian Tentang" class="absolute bottom-8 left-1/2 flex -translate-x-1/2 flex-col items-center gap-2 text-xs font-medium uppercase tracking-[0.25em] text-white/60 transition-colors hover:text-white">
                Gulir
                {!! $icon('chevron-down', 'size-5 animate-float') !!}
            </a>
        </section>

        {{-- ============================== TENTANG ============================== --}}
        <section id="tentang" data-section class="bg-neutral-950 py-24 sm:py-32">
            <div class="mx-auto max-w-6xl px-6 sm:px-10">

                <div class="reveal">
                    <p class="inline-flex items-center gap-2 rounded-full border border-emerald-300/30 bg-emerald-300/10 px-3.5 py-1.5 text-xs font-medium text-emerald-200">
                        {!! $icon('leaf', 'size-3.5') !!}
                        Mendukung SDG 12: Konsumsi dan Produksi yang Bertanggung Jawab
                    </p>
                    <h2 class="mt-6 max-w-3xl text-3xl font-semibold tracking-tight sm:text-5xl sm:leading-[1.05]">Administrasi bank sampah yang rapi, otomatis, dan terpusat.</h2>
                    <p class="mt-6 max-w-2xl text-lg leading-relaxed text-white/65">E-Bank Sampah FILKOM menggantikan pencatatan manual dengan sistem digital. Data nasabah, jenis dan harga sampah, serta setoran dan penarikan saldo dikelola admin dalam satu platform.</p>
                </div>

                {{-- Keunggulan --}}
                <div class="mt-14 grid gap-4 md:grid-cols-3">
                    @foreach ($features as $i => $feature)
                        <div class="reveal" style="--reveal-delay: {{ $i * 100 }}ms">
                            <article class="spotlight relative h-full overflow-hidden rounded-3xl border border-white/10 bg-white/[0.04] p-7 transition duration-300 hover:-translate-y-1 hover:border-white/25">
                                <span class="grid size-12 place-items-center rounded-2xl bg-white/10 text-emerald-300">{!! $icon($feature['icon'], 'size-6') !!}</span>
                                <h3 class="mt-6 text-xl font-semibold tracking-tight">{{ $feature['title'] }}</h3>
                                <p class="mt-3 leading-relaxed text-white/60">{{ $feature['text'] }}</p>
                            </article>
                        </div>
                    @endforeach
                </div>

                {{-- Cara menjadi nasabah --}}
                <div class="reveal mt-24 grid gap-8 lg:grid-cols-[minmax(0,5fr)_minmax(0,7fr)] lg:gap-14">
                    <div>
                        <p class="text-sm font-medium uppercase tracking-[0.2em] text-emerald-300">Cara bergabung</p>
                        <h3 class="mt-4 text-2xl font-semibold tracking-tight sm:text-4xl">Empat langkah menjadi nasabah</h3>
                        <div role="tablist" aria-label="Langkah menjadi nasabah" class="mt-8 flex flex-col gap-1">
                            @foreach ($steps as $i => $step)
                                <button type="button" role="tab" data-step id="step-tab-{{ $i }}" aria-controls="step-panel-{{ $i }}" aria-selected="{{ $i === 0 ? 'true' : 'false' }}" class="group/step flex w-full items-center gap-4 rounded-2xl px-4 py-3.5 text-left text-white/60 transition hover:bg-white/5 hover:text-white aria-selected:bg-white/10 aria-selected:text-white">
                                    <span class="grid size-9 shrink-0 place-items-center rounded-full border border-white/20 text-sm font-semibold transition group-aria-selected/step:border-white group-aria-selected/step:bg-white group-aria-selected/step:text-neutral-900">{{ $i + 1 }}</span>
                                    <span class="font-medium">{{ $step['title'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex flex-col rounded-3xl border border-white/10 bg-white/[0.04] p-8 sm:p-10">
                        <div class="flex-1">
                            @foreach ($steps as $i => $step)
                                <div role="tabpanel" data-step-panel id="step-panel-{{ $i }}" aria-labelledby="step-tab-{{ $i }}" data-active="{{ $i === 0 ? 'true' : 'false' }}" class="hidden data-[active=true]:block data-[active=true]:animate-fade-up">
                                    <span class="text-7xl font-bold tracking-tighter text-white/10 sm:text-8xl">0{{ $i + 1 }}</span>
                                    <h4 class="mt-2 text-2xl font-semibold tracking-tight sm:text-3xl">{{ $step['title'] }}</h4>
                                    <p class="mt-4 text-lg leading-relaxed text-white/65">{{ $step['text'] }}</p>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-10 h-1 overflow-hidden rounded-full bg-white/10">
                            <div id="step-progress" class="h-full rounded-full bg-emerald-300 transition-[width] duration-500 ease-out" style="width: 25%"></div>
                        </div>
                        <div class="mt-6 flex flex-wrap items-center gap-3">
                            <button type="button" data-step-next class="inline-flex h-11 items-center rounded-full border border-white/20 px-5 text-sm font-medium text-white transition hover:bg-white/10">Langkah berikutnya</button>
                            <a href="{{ route('nasabah.register') }}" class="group inline-flex h-11 items-center gap-2 rounded-full bg-white px-5 text-sm font-semibold text-neutral-900 transition hover:bg-neutral-200">
                                Mulai pendaftaran
                                {!! $icon('arrow-up-right', 'size-4 transition-transform duration-300 group-hover:-translate-y-0.5 group-hover:translate-x-0.5') !!}
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Simulasi nilai setoran --}}
                <div id="simulator" class="reveal mt-24 overflow-hidden rounded-[2rem] border border-white/10 bg-linear-to-br from-white/[0.07] to-white/[0.02]">
                    <div class="grid lg:grid-cols-2">
                        <div class="p-8 sm:p-10">
                            <p class="text-sm font-medium uppercase tracking-[0.2em] text-emerald-300">Simulasi</p>
                            <h3 class="mt-4 text-2xl font-semibold tracking-tight sm:text-4xl">Berapa nilai setoran Anda?</h3>
                            <p class="mt-4 text-white/60">Pilih jenis sampah dan geser beratnya. Begitulah cara sistem menghitung nilai setoran secara otomatis.</p>

                            <div class="mt-8 flex flex-wrap gap-2" role="group" aria-label="Jenis sampah">
                                @foreach ($wastes as $i => $waste)
                                    <button type="button" data-waste data-price="{{ $waste['price'] }}" aria-pressed="{{ $i === 0 ? 'true' : 'false' }}" class="rounded-full border border-white/15 px-4 py-2 text-sm text-white/80 transition hover:border-white/40 hover:text-white aria-pressed:border-white aria-pressed:bg-white aria-pressed:text-neutral-900">
                                        {{ $waste['name'] }}
                                        <span class="ml-1 opacity-60">Rp {{ number_format($waste['price'], 0, ',', '.') }}/kg</span>
                                    </button>
                                @endforeach
                            </div>

                            <div class="mt-8">
                                <div class="flex items-center justify-between gap-4">
                                    <label for="sim-weight" class="text-sm font-medium text-white/80">Berat sampah</label>
                                    <div class="flex items-center gap-2">
                                        <input id="sim-weight-number" type="number" min="0" max="500" step="0.5" inputmode="decimal" value="{{ $simWeight }}" aria-label="Berat dalam kilogram" class="w-24 rounded-xl border border-white/15 bg-white/5 px-3 py-2 text-right text-sm tabular-nums text-white focus:border-white/50 focus:outline-none">
                                        <span class="text-sm text-white/60">kg</span>
                                    </div>
                                </div>
                                <input id="sim-weight" type="range" min="0" max="50" step="0.5" value="{{ $simWeight }}" class="mt-5 w-full cursor-pointer accent-emerald-300">
                                <div class="mt-1 flex justify-between text-xs text-white/40"><span>0 kg</span><span>50 kg</span></div>
                            </div>
                        </div>

                        <div class="flex flex-col justify-center border-t border-white/10 bg-black/25 p-8 sm:p-10 lg:border-l lg:border-t-0">
                            <p class="text-sm text-white/60">Perkiraan nilai setoran</p>
                            <p class="mt-3 text-5xl font-semibold tracking-tight sm:text-6xl">
                                <span class="mr-1 text-2xl font-medium text-white/50 sm:text-3xl">Rp</span><span id="sim-total" data-value="{{ $simWeight * $wastes[0]['price'] }}" class="tabular-nums">{{ $simTotal }}</span>
                            </p>
                            <p id="sim-formula" class="mt-4 text-white/60">{{ $simWeight }} kg × Rp {{ number_format($wastes[0]['price'], 0, ',', '.') }}/kg</p>
                            <p class="mt-10 text-xs leading-relaxed text-white/40">Simulasi memakai harga contoh. Harga sebenarnya ditetapkan admin, dan nilai dihitung otomatis saat setoran Anda dicatat.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ============================== LOKASI ============================== --}}
        <section id="lokasi" data-section class="bg-neutral-900 py-24 sm:py-32">
            <div class="mx-auto max-w-6xl px-6 sm:px-10">
                <div class="reveal">
                    <p class="text-sm font-medium uppercase tracking-[0.2em] text-emerald-300">Lokasi</p>
                    <h2 class="mt-4 max-w-3xl text-3xl font-semibold tracking-tight sm:text-5xl sm:leading-[1.05]">Datang dan setor sampah Anda.</h2>
                    <p class="mt-6 max-w-2xl text-lg leading-relaxed text-white/65">Setoran dan penarikan saldo dicatat langsung oleh admin di lokasi bank sampah.</p>
                </div>

                <div class="mt-12 grid gap-4 lg:grid-cols-5">
                    <div class="reveal lg:col-span-2">
                        <div class="flex h-full flex-col gap-7 rounded-3xl border border-white/10 bg-white/[0.04] p-8">
                            <div class="flex gap-4">
                                <span class="grid size-11 shrink-0 place-items-center rounded-2xl bg-white/10 text-emerald-300">{!! $icon('pin') !!}</span>
                                <div>
                                    <p class="text-sm text-white/50">Alamat</p>
                                    <p class="mt-1 leading-relaxed text-white/90">{{ $address }}</p>
                                </div>
                            </div>
                            <div class="flex gap-4">
                                <span class="grid size-11 shrink-0 place-items-center rounded-2xl bg-white/10 text-emerald-300">{!! $icon('clock') !!}</span>
                                <div>
                                    <p class="text-sm text-white/50">Jam layanan</p>
                                    <p class="mt-1 text-white/90">{{ $hours }}</p>
                                </div>
                            </div>
                            <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($mapQuery) }}" target="_blank" rel="noopener" class="group mt-auto inline-flex h-12 items-center justify-center gap-2 rounded-full bg-white px-6 text-sm font-semibold text-neutral-900 transition hover:bg-neutral-200">
                                Buka di Google Maps
                                {!! $icon('arrow-up-right', 'size-4 transition-transform duration-300 group-hover:-translate-y-0.5 group-hover:translate-x-0.5') !!}
                            </a>
                        </div>
                    </div>

                    <div class="reveal lg:col-span-3" style="--reveal-delay: 120ms">
                        <div class="group relative h-80 overflow-hidden rounded-3xl border border-white/10 bg-neutral-800 sm:h-[26rem] lg:h-full lg:min-h-[26rem]">
                            <iframe title="Peta lokasi E-Bank Sampah FILKOM" src="https://www.google.com/maps?q={{ urlencode($mapQuery) }}&output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade" class="absolute inset-0 size-full border-0 transition duration-500 [filter:grayscale(1)_invert(.92)_contrast(.9)] group-hover:[filter:none]"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ============================== HELPDESK ============================== --}}
        <section id="helpdesk" data-section class="bg-neutral-950 py-24 sm:py-32">
            <div class="mx-auto grid max-w-6xl gap-14 px-6 sm:px-10 lg:grid-cols-2 lg:gap-20">

                <div class="reveal">
                    <p class="text-sm font-medium uppercase tracking-[0.2em] text-emerald-300">Helpdesk</p>
                    <h2 class="mt-4 text-3xl font-semibold tracking-tight sm:text-5xl sm:leading-[1.05]">Butuh bantuan atau konfirmasi pendaftaran?</h2>
                    <p class="mt-6 text-lg leading-relaxed text-white/65">Setelah mendaftar, hubungi Helpdesk agar admin dapat memverifikasi data Anda dan mengaktifkan akun.</p>

                    <div class="mt-10 space-y-3">
                        <div class="flex flex-wrap items-center gap-4 rounded-2xl border border-white/10 bg-white/[0.04] p-4 transition hover:border-white/25">
                            <span class="grid size-11 shrink-0 place-items-center rounded-2xl bg-white/10 text-emerald-300">{!! $icon('message') !!}</span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm text-white/50">WhatsApp</p>
                                <p class="break-all font-medium">{{ $whatsapp['display'] }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" data-copy="{{ $whatsapp['display'] }}" class="inline-flex items-center gap-1.5 rounded-full border border-white/15 px-3.5 py-2 text-xs font-medium text-white/80 transition hover:border-white/40 hover:text-white data-[copied=true]:border-emerald-300 data-[copied=true]:text-emerald-300">
                                    {!! $icon('copy', 'size-3.5') !!}<span data-copy-label>Salin</span>
                                </button>
                                <a href="{{ $whatsapp['link'] }}" target="_blank" rel="noopener" class="inline-flex items-center rounded-full bg-white px-4 py-2 text-xs font-semibold text-neutral-900 transition hover:bg-neutral-200">Chat</a>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-4 rounded-2xl border border-white/10 bg-white/[0.04] p-4 transition hover:border-white/25">
                            <span class="grid size-11 shrink-0 place-items-center rounded-2xl bg-white/10 text-emerald-300">{!! $icon('mail') !!}</span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm text-white/50">Email</p>
                                <p class="break-all font-medium">{{ $email }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" data-copy="{{ $email }}" class="inline-flex items-center gap-1.5 rounded-full border border-white/15 px-3.5 py-2 text-xs font-medium text-white/80 transition hover:border-white/40 hover:text-white data-[copied=true]:border-emerald-300 data-[copied=true]:text-emerald-300">
                                    {!! $icon('copy', 'size-3.5') !!}<span data-copy-label>Salin</span>
                                </button>
                                <a href="mailto:{{ $email }}" class="inline-flex items-center rounded-full bg-white px-4 py-2 text-xs font-semibold text-neutral-900 transition hover:bg-neutral-200">Kirim</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="reveal" style="--reveal-delay: 120ms">
                    <h3 class="text-xl font-semibold tracking-tight">Pertanyaan yang sering diajukan</h3>
                    <div class="mt-6 divide-y divide-white/10 border-y border-white/10">
                        @foreach ($faqs as $i => $faq)
                            <div data-faq data-open="{{ $i === 0 ? 'true' : 'false' }}">
                                <h4>
                                    <button type="button" data-faq-trigger id="faq-trigger-{{ $i }}" aria-expanded="{{ $i === 0 ? 'true' : 'false' }}" aria-controls="faq-panel-{{ $i }}" class="group/faq flex w-full items-center justify-between gap-6 py-5 text-left text-base font-medium text-white/85 transition-colors hover:text-white aria-expanded:text-white sm:text-lg">
                                        {{ $faq['q'] }}
                                        {!! $icon('chevron-down', 'size-5 shrink-0 text-white/50 transition-transform duration-300 group-aria-expanded/faq:rotate-180') !!}
                                    </button>
                                </h4>
                                <div id="faq-panel-{{ $i }}" role="region" aria-labelledby="faq-trigger-{{ $i }}" class="faq-panel">
                                    <div><p class="pb-5 leading-relaxed text-white/60">{{ $faq['a'] }}</p></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    </main>

    {{-- ============================== FOOTER ============================== --}}
    <footer class="border-t border-white/10 bg-neutral-950 py-10">
        <div class="mx-auto flex max-w-6xl flex-col gap-4 px-6 text-sm text-white/50 sm:px-10 md:flex-row md:items-center md:justify-between">
            <p>&copy; 2026 E-Bank Sampah FILKOM &middot; Kelompok 1</p>
            <p>Pengembangan Aplikasi Web &middot; Fakultas Ilmu Komputer, Universitas Brawijaya</p>
        </div>
    </footer>

    <button id="to-top" type="button" data-visible="false" aria-label="Kembali ke atas" class="pointer-events-none fixed bottom-6 right-6 z-40 grid size-12 translate-y-4 place-items-center rounded-full bg-white text-neutral-900 opacity-0 shadow-lg shadow-black/30 transition duration-300 hover:bg-neutral-200 data-[visible=true]:pointer-events-auto data-[visible=true]:translate-y-0 data-[visible=true]:opacity-100">
        {!! $icon('arrow-up') !!}
    </button>

</body>
</html>