@extends('layouts.admin')

@section('title', $nasabah->nama_lengkap . ' | Admin E-Bank Sampah FILKOM')

@section('breadcrumb')
    <nav aria-label="Breadcrumb" class="mb-4 flex items-center gap-2 text-sm">
        <a href="{{ route('admin.nasabah.index') }}" class="text-neutral-500 transition-colors hover:text-neutral-900">Nasabah</a>
        <x-icon name="chevron-right" class="size-4 text-neutral-400" />
        <span aria-current="page" class="font-medium text-neutral-900">{{ $nasabah->nama_lengkap }}</span>
    </nav>
@endsection

@section('content')
    @php
        // Dikirim Admin\NasabahController: $nasabah, $saldo, $totalBerat, $transaksi, $ktpPdf.
        $rupiah = fn ($angka) => 'Rp ' . number_format($angka, 0, ',', '.');
        $kg = fn ($angka) => rtrim(rtrim(number_format($angka, 2, ',', '.'), '0'), ',');
        $nada = ['menunggu' => 'proses', 'aktif' => 'selesai', 'ditolak' => 'gagal', 'nonaktif' => 'netral'];
        $labelStatus = ['menunggu' => 'Menunggu aktivasi', 'aktif' => 'Aktif', 'ditolak' => 'Ditolak', 'nonaktif' => 'Nonaktif'];
        $wa = preg_replace('/\D+/', '', $nasabah->whatsapp);
        if (str_starts_with($wa, '0')) {
            $wa = '62' . substr($wa, 1);
        }
        $jk = $nasabah->jenis_kelamin;
        $tombolUtama = 'inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-[#2e7d32] px-5 text-sm font-semibold text-white transition hover:bg-[#276b2b] focus-visible:ring-4 focus-visible:ring-[#2e7d32]/25 focus-visible:outline-none';
        $tombolBahaya = 'inline-flex h-11 items-center justify-center gap-2 rounded-lg border border-red-200 bg-white px-5 text-sm font-semibold text-red-600 transition hover:bg-red-50 focus-visible:ring-4 focus-visible:ring-red-500/15 focus-visible:outline-none';
        $tombolBiasa = 'inline-flex h-11 items-center justify-center gap-2 rounded-lg border border-neutral-300 bg-white px-5 text-sm font-semibold text-neutral-800 transition hover:bg-neutral-50 focus-visible:ring-4 focus-visible:ring-[#2e7d32]/15 focus-visible:outline-none';
    @endphp

    <header class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-[1.75rem] font-bold tracking-tight text-neutral-900">{{ $nasabah->nama_lengkap }}</h1>
                <x-pil :tone="$nada[$nasabah->status] ?? 'netral'">{{ $labelStatus[$nasabah->status] ?? ucfirst($nasabah->status) }}</x-pil>
            </div>
            <p class="mt-2 text-sm text-slate-500">
                <span class="font-mono text-xs tracking-wide">{{ $nasabah->kode() }}</span>
                · Mendaftar {{ $nasabah->created_at->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d F Y, H:i') }} WIB
                @if ($nasabah->bankSampah) · {{ $nasabah->bankSampah->nama }} @endif
            </p>
        </div>

        @if ($nasabah->status === 'aktif')
            <a href="{{ route('admin.nasabah.setoran', $nasabah->id) }}" class="{{ $tombolUtama }} self-start">
                <x-icon name="plus" class="size-4" />
                Catat Setoran
            </a>
        @endif
    </header>

    {{-- Panel keputusan untuk pendaftaran yang menunggu --}}
    @if ($nasabah->status === 'menunggu')
        <section aria-labelledby="judul-verifikasi" class="mb-6 rounded-2xl border border-amber-200 bg-amber-50/60 p-5 sm:p-6">
            <h2 id="judul-verifikasi" class="text-base font-semibold text-neutral-900">Verifikasi pendaftaran</h2>
            <p class="mt-1 text-sm text-neutral-600">Cocokkan data diri dengan foto KTP di bawah. Setelah diaktifkan, nasabah bisa langsung login.</p>

            <div class="mt-4 flex flex-wrap items-start gap-3">
                <form method="POST" action="{{ route('admin.nasabah.aktifkan', $nasabah->id) }}">
                    @csrf
                    <button type="submit" class="{{ $tombolUtama }}">
                        <x-icon name="check" class="size-4" />
                        Aktifkan Akun
                    </button>
                </form>

                <details class="group/tolak w-full sm:w-auto sm:min-w-[22rem] sm:flex-1" @if ($errors->has('alasan')) open @endif>
                    <summary class="{{ $tombolBahaya }} w-fit cursor-pointer list-none [&::-webkit-details-marker]:hidden">
                        <x-icon name="x" class="size-4" />
                        Tolak
                    </summary>
                    <form method="POST" action="{{ route('admin.nasabah.tolak', $nasabah->id) }}" class="mt-3 max-w-xl rounded-xl border border-red-200 bg-white p-4" novalidate>
                        @csrf
                        <label for="alasan-tolak" class="block text-sm font-medium text-neutral-800">Alasan penolakan</label>
                        <p class="mt-1 text-xs text-neutral-500">Alasan ini ditampilkan ke nasabah saat ia mencoba login.</p>
                        <textarea id="alasan-tolak" name="alasan" rows="3" maxlength="255" required aria-invalid="{{ $errors->has('alasan') ? 'true' : 'false' }}" placeholder="Contoh: Foto KTP buram, NIK tidak terbaca." class="mt-2 block w-full rounded-lg border border-neutral-300 px-3.5 py-2.5 text-sm text-neutral-900 placeholder:text-neutral-400 focus:border-[#2e7d32] focus:ring-4 focus:ring-[#2e7d32]/15 focus:outline-none aria-invalid:border-red-500 aria-invalid:focus:ring-red-500/15">{{ old('alasan') }}</textarea>
                        <x-field-message name="alasan" />
                        <button type="submit" class="mt-3 inline-flex h-10 items-center rounded-lg bg-red-600 px-4 text-sm font-semibold text-white transition hover:bg-red-700 focus-visible:ring-4 focus-visible:ring-red-500/25 focus-visible:outline-none">Tolak Pendaftaran</button>
                    </form>
                </details>
            </div>
        </section>
    @endif

    {{-- Alasan penolakan / penonaktifan --}}
    @if (in_array($nasabah->status, ['ditolak', 'nonaktif'], true) && $nasabah->alasan_status)
        <div class="mb-6 flex items-start gap-3 rounded-xl border border-neutral-200 bg-white px-4 py-3 text-sm text-neutral-700">
            <x-icon name="alert-circle" class="mt-0.5 size-5 shrink-0 text-neutral-400" />
            <p><span class="font-semibold text-neutral-900">{{ $nasabah->status === 'ditolak' ? 'Alasan penolakan:' : 'Alasan penonaktifan:' }}</span> {{ $nasabah->alasan_status }}</p>
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
        <div class="space-y-6">
            <section aria-labelledby="judul-diri" class="rounded-2xl border border-neutral-200 bg-white p-5 sm:p-6">
                <h2 id="judul-diri" class="text-base font-semibold text-neutral-900">Data diri</h2>
                <dl class="mt-4 grid gap-x-6 gap-y-4 text-sm sm:grid-cols-2">
                    <div><dt class="text-xs font-medium text-slate-500">Nama lengkap</dt><dd class="mt-1 font-medium text-neutral-900">{{ $nasabah->nama_lengkap }}</dd></div>
                    <div><dt class="text-xs font-medium text-slate-500">NIK</dt><dd class="mt-1 font-mono font-medium text-neutral-900">{{ $nasabah->nik }}</dd></div>
                    <div><dt class="text-xs font-medium text-slate-500">Tanggal lahir</dt><dd class="mt-1 font-medium text-neutral-900">{{ $nasabah->tanggal_lahir?->locale('id')->translatedFormat('d F Y') ?? '-' }}</dd></div>
                    <div><dt class="text-xs font-medium text-slate-500">Jenis kelamin</dt><dd class="mt-1 font-medium text-neutral-900">{{ $jk ?: '-' }}</dd></div>
                    <div>
                        <dt class="text-xs font-medium text-slate-500">WhatsApp</dt>
                        <dd class="mt-1 font-medium text-neutral-900">
                            <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 text-[#2e7d32] hover:underline">
                                <x-icon name="phone" class="size-4" />
                                {{ $nasabah->whatsapp }}
                            </a>
                        </dd>
                    </div>
                    <div><dt class="text-xs font-medium text-slate-500">Email</dt><dd class="mt-1 break-all font-medium text-neutral-900">{{ $nasabah->email }}</dd></div>
                </dl>
            </section>

            <section aria-labelledby="judul-alamat" class="rounded-2xl border border-neutral-200 bg-white p-5 sm:p-6">
                <h2 id="judul-alamat" class="text-base font-semibold text-neutral-900">Alamat</h2>
                <dl class="mt-4 grid gap-x-6 gap-y-4 text-sm sm:grid-cols-2">
                    <div class="sm:col-span-2"><dt class="text-xs font-medium text-slate-500">Alamat lengkap</dt><dd class="mt-1 font-medium text-neutral-900">{{ $nasabah->alamat_lengkap }}</dd></div>
                    <div><dt class="text-xs font-medium text-slate-500">RT / RW</dt><dd class="mt-1 font-medium text-neutral-900">{{ $nasabah->rt }} / {{ $nasabah->rw }}</dd></div>
                    <div><dt class="text-xs font-medium text-slate-500">Kelurahan</dt><dd class="mt-1 font-medium text-neutral-900">{{ $nasabah->kelurahan }}</dd></div>
                    <div><dt class="text-xs font-medium text-slate-500">Kecamatan</dt><dd class="mt-1 font-medium text-neutral-900">{{ $nasabah->kecamatan }}</dd></div>
                    <div><dt class="text-xs font-medium text-slate-500">Kota</dt><dd class="mt-1 font-medium text-neutral-900">{{ $nasabah->kota }}</dd></div>
                </dl>
            </section>
        </div>

        <div class="space-y-6">
            <section aria-labelledby="judul-ktp" class="rounded-2xl border border-neutral-200 bg-white p-5 sm:p-6">
                <h2 id="judul-ktp" class="flex items-center gap-2 text-base font-semibold text-neutral-900">
                    <x-icon name="id-card" class="size-5 text-[#2e7d32]" />
                    Foto KTP
                </h2>

                @if (! $nasabah->ktp_path)
                    <p class="mt-4 rounded-xl border border-dashed border-neutral-300 px-4 py-8 text-center text-sm text-slate-500">Nasabah belum mengunggah KTP.</p>
                @elseif ($ktpPdf)
                    <a href="{{ route('admin.nasabah.ktp', $nasabah->id) }}" target="_blank" rel="noopener" class="mt-4 flex items-center gap-3 rounded-xl border border-neutral-200 px-4 py-4 transition hover:bg-neutral-50">
                        <x-icon name="file-text" class="size-8 text-neutral-400" />
                        <span class="text-sm font-semibold text-neutral-900">Buka berkas KTP (PDF)</span>
                    </a>
                @else
                    <a href="{{ route('admin.nasabah.ktp', $nasabah->id) }}" target="_blank" rel="noopener" class="mt-4 block overflow-hidden rounded-xl border border-neutral-200 bg-neutral-50" aria-label="Buka foto KTP ukuran penuh">
                        <img src="{{ route('admin.nasabah.ktp', $nasabah->id) }}" alt="Foto KTP {{ $nasabah->nama_lengkap }}" class="max-h-80 w-full object-contain">
                    </a>
                    <p class="mt-2 text-xs text-slate-500">Klik gambar untuk membuka ukuran penuh.</p>
                @endif
            </section>

            @if ($nasabah->status === 'aktif' || $transaksi->isNotEmpty())
                <section aria-labelledby="judul-saldo" class="rounded-2xl border border-neutral-200 bg-white">
                    <div class="p-5 sm:p-6">
                        <h2 id="judul-saldo" class="text-base font-semibold text-neutral-900">Saldo &amp; setoran</h2>
                        <dl class="mt-4 grid grid-cols-2 gap-4">
                            <div class="rounded-xl bg-[#eaf3ea] px-4 py-3">
                                <dt class="text-xs font-medium text-[#2e7d32]">Saldo aktif</dt>
                                <dd class="mt-1 text-xl font-bold tabular-nums text-neutral-900">{{ $rupiah($saldo) }}</dd>
                            </div>
                            <div class="rounded-xl bg-[#f7f9fb] px-4 py-3">
                                <dt class="text-xs font-medium text-slate-500">Total sampah disetor</dt>
                                <dd class="mt-1 text-xl font-bold tabular-nums text-neutral-900">{{ $kg($totalBerat) }} kg</dd>
                            </div>
                        </dl>
                    </div>

                    @if ($transaksi->isNotEmpty())
                        <ul class="divide-y divide-neutral-100 border-t border-neutral-200">
                            @foreach ($transaksi as $trx)
                                <li class="flex items-center justify-between gap-4 px-5 py-3.5 text-sm sm:px-6">
                                    <span class="min-w-0">
                                        <span class="block truncate font-medium text-neutral-900">{{ $trx->tipe === 'setoran' ? 'Setor ' . ($trx->jenisSampah?->nama ?? 'sampah') : 'Penarikan saldo' }}</span>
                                        <span class="mt-0.5 block text-xs text-neutral-500">{{ $trx->tanggal->locale('id')->translatedFormat('d M Y') }}@if ($trx->tipe === 'setoran' && $trx->berat_kg) · {{ $kg($trx->berat_kg) }} kg @endif</span>
                                    </span>
                                    <span class="shrink-0 font-bold tabular-nums {{ $trx->tipe === 'setoran' ? 'text-[#2e7d32]' : 'text-neutral-800' }}">{{ $trx->tipe === 'setoran' ? '+' : '−' }}{{ $rupiah($trx->nilai) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>
            @endif
        </div>
    </div>

    {{-- Tindakan lain untuk akun aktif / ditolak / nonaktif --}}
    @if ($nasabah->status === 'aktif')
        <section aria-labelledby="judul-akun" class="mt-6 rounded-2xl border border-neutral-200 bg-white p-5 sm:p-6">
            <h2 id="judul-akun" class="text-base font-semibold text-neutral-900">Kelola akun</h2>
            <p class="mt-1 text-sm text-neutral-600">Akun yang dinonaktifkan tidak bisa login. Saldo dan riwayat tetap tersimpan.</p>
            <details class="mt-4" @if ($errors->has('alasan')) open @endif>
                <summary class="{{ $tombolBahaya }} w-fit cursor-pointer list-none [&::-webkit-details-marker]:hidden">Nonaktifkan Akun</summary>
                <form method="POST" action="{{ route('admin.nasabah.nonaktifkan', $nasabah->id) }}" class="mt-3 max-w-xl rounded-xl border border-red-200 bg-white p-4" novalidate>
                    @csrf
                    <label for="alasan-nonaktif" class="block text-sm font-medium text-neutral-800">Alasan <span class="font-normal text-neutral-500">(opsional)</span></label>
                    <textarea id="alasan-nonaktif" name="alasan" rows="2" maxlength="255" aria-invalid="{{ $errors->has('alasan') ? 'true' : 'false' }}" class="mt-2 block w-full rounded-lg border border-neutral-300 px-3.5 py-2.5 text-sm text-neutral-900 focus:border-[#2e7d32] focus:ring-4 focus:ring-[#2e7d32]/15 focus:outline-none aria-invalid:border-red-500">{{ old('alasan') }}</textarea>
                    <x-field-message name="alasan" />
                    <button type="submit" class="mt-3 inline-flex h-10 items-center rounded-lg bg-red-600 px-4 text-sm font-semibold text-white transition hover:bg-red-700 focus-visible:ring-4 focus-visible:ring-red-500/25 focus-visible:outline-none">Nonaktifkan</button>
                </form>
            </details>
        </section>
    @elseif (in_array($nasabah->status, ['ditolak', 'nonaktif'], true))
        <section class="mt-6 flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-neutral-200 bg-white p-5 sm:p-6">
            <p class="text-sm text-neutral-600">{{ $nasabah->status === 'ditolak' ? 'Pendaftaran ini ditolak.' : 'Akun ini nonaktif.' }} Aktifkan kembali jika sudah sesuai.</p>
            <form method="POST" action="{{ route('admin.nasabah.aktifkan', $nasabah->id) }}">
                @csrf
                <button type="submit" class="{{ $tombolBiasa }}">Aktifkan Akun</button>
            </form>
        </section>
    @endif
@endsection