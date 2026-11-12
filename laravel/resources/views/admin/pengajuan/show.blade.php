@extends('layouts.admin')

@section('title', $pengajuan->kode() . ' | Admin E-Bank Sampah FILKOM')

@section('breadcrumb')
    <nav aria-label="Breadcrumb" class="mb-4 flex items-center gap-2 text-sm">
        <a href="{{ route('admin.pengajuan.index') }}" class="text-neutral-500 transition-colors hover:text-neutral-900">Pengajuan Setoran</a>
        <x-icon name="chevron-right" class="size-4 text-neutral-400" />
        <span aria-current="page" class="font-medium text-neutral-900">{{ $pengajuan->kode() }}</span>
    </nav>
@endsection

@section('content')
    @php
        // Dikirim Admin\PengajuanController: $pengajuan, $nasabah, $bisaDitimbang, $jenisSampah, $perkiraan.
        $rupiah = fn ($angka) => 'Rp ' . number_format($angka, 0, ',', '.');
        $kg = fn ($angka) => rtrim(rtrim(number_format($angka, 2, ',', '.'), '0'), ',');
        $nada = ['menunggu' => 'proses', 'diterima' => 'info', 'selesai' => 'selesai', 'ditolak' => 'gagal', 'dibatalkan' => 'netral'];
        $labelStatus = ['menunggu' => 'Menunggu', 'diterima' => 'Diterima', 'selesai' => 'Selesai', 'ditolak' => 'Ditolak', 'dibatalkan' => 'Dibatalkan'];
        $jemput = $pengajuan->metode === 'jemput';
        $wa = preg_replace('/\D+/', '', $nasabah->whatsapp);
        if (str_starts_with($wa, '0')) {
            $wa = '62' . substr($wa, 1);
        }
        $tombolUtama = 'inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-[#2e7d32] px-5 text-sm font-semibold text-white transition hover:bg-[#276b2b] focus-visible:ring-4 focus-visible:ring-[#2e7d32]/25 focus-visible:outline-none';
        $tombolBahaya = 'inline-flex h-11 items-center justify-center gap-2 rounded-lg border border-red-200 bg-white px-5 text-sm font-semibold text-red-600 transition hover:bg-red-50 focus-visible:ring-4 focus-visible:ring-red-500/15 focus-visible:outline-none';
        $timbangan = $pengajuan->transaksi;
    @endphp

    <header class="mb-6">
        <div class="flex flex-wrap items-center gap-3">
            <h1 class="text-[1.75rem] font-bold tracking-tight text-neutral-900">Pengajuan {{ $pengajuan->kode() }}</h1>
            <x-pil :tone="$nada[$pengajuan->status] ?? 'netral'">{{ $labelStatus[$pengajuan->status] ?? ucfirst($pengajuan->status) }}</x-pil>
        </div>
        <p class="mt-2 text-sm text-slate-500">Diajukan {{ $pengajuan->created_at->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d F Y, H:i') }} WIB oleh
            <a href="{{ route('admin.nasabah.show', $nasabah->id) }}" class="font-semibold text-[#2e7d32] hover:underline">{{ $nasabah->nama_lengkap }}</a></p>
    </header>

    @if ($pengajuan->status === 'ditolak' && $pengajuan->alasan)
        <div class="mb-6 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <x-icon name="alert-circle" class="mt-0.5 size-5 shrink-0 text-red-600" />
            <p><span class="font-semibold">Alasan penolakan:</span> {{ $pengajuan->alasan }}</p>
        </div>
    @endif

    @if ($nasabah->status !== 'aktif' && $bisaDitimbang)
        <div class="mb-6 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            <x-icon name="alert-circle" class="mt-0.5 size-5 shrink-0 text-amber-600" />
            <p>Akun nasabah ini berstatus <strong>{{ $nasabah->status }}</strong>, sehingga setoran tidak bisa dicatat sampai akunnya aktif kembali.</p>
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.1fr)]">

        {{-- Kiri: informasi pengajuan --}}
        <div class="space-y-6">
            <section aria-labelledby="judul-info" class="rounded-2xl border border-neutral-200 bg-white p-5 sm:p-6">
                <h2 id="judul-info" class="text-base font-semibold text-neutral-900">Informasi pengajuan</h2>
                <dl class="mt-4 grid gap-x-6 gap-y-4 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-medium text-slate-500">Metode</dt>
                        <dd class="mt-1 flex items-center gap-1.5 font-medium text-neutral-900">
                            <x-icon :name="$jemput ? 'truck' : 'pin'" class="size-4 text-[#2e7d32]" />
                            {{ $jemput ? 'Dijemput petugas' : 'Diantar ke bank sampah' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-slate-500">Tanggal rencana</dt>
                        <dd class="mt-1 font-medium text-neutral-900">{{ $pengajuan->tanggal_rencana->locale('id')->translatedFormat('l, d F Y') }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-medium text-slate-500">{{ $jemput ? 'Alamat penjemputan' : 'Alamat nasabah' }}</dt>
                        <dd class="mt-1 font-medium text-neutral-900">
                            {{ $nasabah->alamat_lengkap }}, RT {{ $nasabah->rt }}/RW {{ $nasabah->rw }}, {{ $nasabah->kelurahan }}, {{ $nasabah->kecamatan }}, {{ $nasabah->kota }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-slate-500">WhatsApp nasabah</dt>
                        <dd class="mt-1 font-medium">
                            <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 text-[#2e7d32] hover:underline">
                                <x-icon name="phone" class="size-4" />
                                {{ $nasabah->whatsapp }}
                            </a>
                        </dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-medium text-slate-500">Catatan nasabah</dt>
                        <dd class="mt-1 text-neutral-900">{{ $pengajuan->catatan ?: '-' }}</dd>
                    </div>
                </dl>
            </section>

            <section aria-labelledby="judul-perkiraan" class="overflow-hidden rounded-2xl border border-neutral-200 bg-white">
                <div class="p-5 pb-3 sm:p-6 sm:pb-3">
                    <h2 id="judul-perkiraan" class="text-base font-semibold text-neutral-900">Perkiraan dari nasabah</h2>
                    <p class="mt-1 text-xs text-slate-500">Hanya perkiraan. Nilai sebenarnya dihitung dari hasil timbangan.</p>
                </div>
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-y border-neutral-200 bg-[#f7f9fb] text-[0.7rem] font-semibold uppercase tracking-wider text-slate-500">
                            <th scope="col" class="px-5 py-3 sm:px-6">Kategori</th>
                            <th scope="col" class="px-3 py-3 text-right">Berat</th>
                            <th scope="col" class="px-5 py-3 text-right sm:px-6">Nilai</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100">
                        @foreach ($pengajuan->jenisSampah as $jenis)
                            <tr>
                                <td class="px-5 py-3 sm:px-6">
                                    <p class="font-medium text-neutral-900">{{ $jenis->nama }}</p>
                                    <p class="text-xs text-neutral-500">{{ $rupiah($jenis->harga_per_kg) }} / kg</p>
                                </td>
                                <td class="whitespace-nowrap px-3 py-3 text-right tabular-nums text-neutral-800">≈ {{ $kg($jenis->pivot->perkiraan_berat_kg) }} kg</td>
                                <td class="whitespace-nowrap px-5 py-3 text-right font-semibold tabular-nums text-amber-600 sm:px-6">≈ {{ $rupiah(round($jenis->pivot->perkiraan_berat_kg * $jenis->harga_per_kg)) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-neutral-200 bg-[#f7f9f7] font-bold">
                            <th scope="row" class="px-5 py-3 text-left sm:px-6">Total</th>
                            <td class="whitespace-nowrap px-3 py-3 text-right tabular-nums">≈ {{ $kg($pengajuan->perkiraanBerat()) }} kg</td>
                            <td class="whitespace-nowrap px-5 py-3 text-right tabular-nums text-amber-600 sm:px-6">≈ {{ $rupiah($pengajuan->perkiraanNilai()) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </section>
        </div>

        {{-- Kanan: keputusan dan penimbangan --}}
        <div class="space-y-6">
            @if ($bisaDitimbang)
                <section aria-labelledby="judul-keputusan" class="rounded-2xl border border-neutral-200 bg-white p-5 sm:p-6">
                    <h2 id="judul-keputusan" class="text-base font-semibold text-neutral-900">Keputusan</h2>
                    <p class="mt-1 text-sm text-neutral-600">
                        @if ($pengajuan->status === 'menunggu')
                            Terima pengajuan agar nasabah tahu setorannya akan diproses, atau tolak dengan alasan.
                        @else
                            Pengajuan sudah diterima. Catat timbangan di bawah setelah sampah tiba.
                        @endif
                    </p>

                    <div class="mt-4 flex flex-wrap items-start gap-3">
                        @if ($pengajuan->status === 'menunggu')
                            <form method="POST" action="{{ route('admin.pengajuan.terima', $pengajuan->id) }}">
                                @csrf
                                <button type="submit" class="{{ $tombolUtama }}">
                                    <x-icon name="check" class="size-4" />
                                    Terima Pengajuan
                                </button>
                            </form>
                        @endif

                        <details class="w-full sm:w-auto sm:min-w-[20rem] sm:flex-1" @if ($errors->has('alasan')) open @endif>
                            <summary class="{{ $tombolBahaya }} w-fit cursor-pointer list-none [&::-webkit-details-marker]:hidden">
                                <x-icon name="x" class="size-4" />
                                Tolak
                            </summary>
                            <form method="POST" action="{{ route('admin.pengajuan.tolak', $pengajuan->id) }}" class="mt-3 rounded-xl border border-red-200 bg-white p-4" novalidate>
                                @csrf
                                <label for="alasan-tolak" class="block text-sm font-medium text-neutral-800">Alasan penolakan</label>
                                <p class="mt-1 text-xs text-neutral-500">Alasan ini ditampilkan ke nasabah di riwayatnya.</p>
                                <textarea id="alasan-tolak" name="alasan" rows="3" maxlength="255" required aria-invalid="{{ $errors->has('alasan') ? 'true' : 'false' }}" placeholder="Contoh: Di luar jangkauan penjemputan." class="mt-2 block w-full rounded-lg border border-neutral-300 px-3.5 py-2.5 text-sm text-neutral-900 placeholder:text-neutral-400 focus:border-[#2e7d32] focus:ring-4 focus:ring-[#2e7d32]/15 focus:outline-none aria-invalid:border-red-500 aria-invalid:focus:ring-red-500/15">{{ old('alasan') }}</textarea>
                                <x-field-message name="alasan" />
                                <button type="submit" class="mt-3 inline-flex h-10 items-center rounded-lg bg-red-600 px-4 text-sm font-semibold text-white transition hover:bg-red-700 focus-visible:ring-4 focus-visible:ring-red-500/25 focus-visible:outline-none">Tolak Pengajuan</button>
                            </form>
                        </details>
                    </div>
                </section>

                @if ($nasabah->status === 'aktif')
                    <section aria-labelledby="judul-timbang">
                        <h2 id="judul-timbang" class="mb-3 text-base font-semibold text-neutral-900">Catat penimbangan</h2>
                        @include('admin._timbang', [
                            'action' => route('admin.pengajuan.timbang', $pengajuan->id),
                            'batalUrl' => route('admin.pengajuan.index'),
                            'tombol' => 'Simpan & Selesaikan',
                            'pilihMetode' => false,
                            'jenisSampah' => $jenisSampah,
                            'perkiraan' => $perkiraan,
                        ])
                    </section>
                @endif
            @elseif ($pengajuan->status === 'selesai')
                <section aria-labelledby="judul-hasil" class="overflow-hidden rounded-2xl border border-neutral-200 bg-white">
                    <div class="p-5 pb-3 sm:p-6 sm:pb-3">
                        <h2 id="judul-hasil" class="flex items-center gap-2 text-base font-semibold text-neutral-900">
                            <x-icon name="circle-check" class="size-5 text-[#2e7d32]" />
                            Hasil penimbangan
                        </h2>
                        <p class="mt-1 text-xs text-slate-500">Sudah masuk ke saldo {{ $nasabah->nama_lengkap }}.</p>
                    </div>
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-y border-neutral-200 bg-[#f7f9fb] text-[0.7rem] font-semibold uppercase tracking-wider text-slate-500">
                                <th scope="col" class="px-5 py-3 sm:px-6">Kategori</th>
                                <th scope="col" class="px-3 py-3 text-right">Berat</th>
                                <th scope="col" class="px-5 py-3 text-right sm:px-6">Nilai</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100">
                            @foreach ($timbangan as $trx)
                                <tr>
                                    <td class="px-5 py-3 font-medium text-neutral-900 sm:px-6">{{ $trx->jenisSampah?->nama ?? 'Sampah' }}</td>
                                    <td class="whitespace-nowrap px-3 py-3 text-right tabular-nums text-neutral-800">{{ $kg($trx->berat_kg) }} kg</td>
                                    <td class="whitespace-nowrap px-5 py-3 text-right font-semibold tabular-nums text-[#2e7d32] sm:px-6">+{{ $rupiah($trx->nilai) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="border-t border-neutral-200 bg-[#f7f9f7] font-bold">
                                <th scope="row" class="px-5 py-3 text-left sm:px-6">Total</th>
                                <td class="whitespace-nowrap px-3 py-3 text-right tabular-nums">{{ $kg($timbangan->sum('berat_kg')) }} kg</td>
                                <td class="whitespace-nowrap px-5 py-3 text-right tabular-nums text-[#2e7d32] sm:px-6">+{{ $rupiah($timbangan->sum('nilai')) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </section>
            @else
                <section class="rounded-2xl border border-neutral-200 bg-white p-5 text-sm text-neutral-600 sm:p-6">
                    Pengajuan ini sudah {{ $pengajuan->status === 'ditolak' ? 'ditolak' : 'dibatalkan' }} dan tidak bisa diproses lagi.
                </section>
            @endif
        </div>
    </div>
@endsection