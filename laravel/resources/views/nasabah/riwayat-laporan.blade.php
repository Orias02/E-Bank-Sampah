<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan Riwayat Transaksi {{ $nasabah->kode() }}</title>
    <meta name="robots" content="noindex">
    @vite(['resources/css/app.css'])
    <style>
        @page { size: A4; margin: 14mm; }
        @media print { html, body { background: #fff !important; } }
    </style>
</head>
<body class="bg-neutral-100 font-sans text-neutral-900 antialiased">
    @php
        // Dikirim RiwayatController@laporan: $nasabah, $baris (semua), $saldo, $totalBerat, $totalSetoran, $totalPenarikan, $dicetak.
        $rupiah = fn ($angka) => 'Rp ' . number_format($angka, 0, ',', '.');
        $kg = fn ($angka) => rtrim(rtrim(number_format($angka, 2, ',', '.'), '0'), ',') . ' Kg';
    @endphp

    <div class="print:hidden border-b border-neutral-200 bg-white">
        <div class="mx-auto flex max-w-4xl flex-wrap items-center justify-between gap-3 px-5 py-3">
            <p class="text-sm text-neutral-600">Di dialog cetak, pilih <strong class="font-semibold text-neutral-900">Simpan sebagai PDF</strong> sebagai printer.</p>
            <div class="flex gap-2">
                <a href="{{ route('nasabah.riwayat') }}" class="inline-flex h-10 items-center rounded-lg border border-neutral-300 px-4 text-sm font-semibold text-neutral-800 hover:bg-neutral-50">Kembali</a>
                <button type="button" onclick="window.print()" class="inline-flex h-10 items-center gap-2 rounded-lg bg-[#2e7d32] px-4 text-sm font-semibold text-white hover:bg-[#276b2b]">
                    <x-icon name="printer" class="size-4" />
                    Cetak / Simpan PDF
                </button>
            </div>
        </div>
    </div>

    <main class="mx-auto my-6 max-w-4xl bg-white p-8 shadow-sm print:my-0 print:max-w-none print:p-0 print:shadow-none">
        <header class="flex items-start justify-between gap-6 border-b-2 border-[#2e7d32] pb-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-[#2e7d32]">E-Bank Sampah FILKOM</p>
                <h1 class="mt-1 text-2xl font-bold">Laporan Riwayat Transaksi</h1>
            </div>
            <p class="text-right text-xs text-neutral-500">Dicetak<br><span class="font-medium text-neutral-800">{{ $dicetak->locale('id')->translatedFormat('j F Y, H:i') }} WIB</span></p>
        </header>

        <dl class="mt-5 grid grid-cols-2 gap-x-8 gap-y-2 text-sm">
            <div class="flex gap-2"><dt class="w-28 shrink-0 text-neutral-500">Nama</dt><dd class="font-semibold">{{ $nasabah->nama_lengkap }}</dd></div>
            <div class="flex gap-2"><dt class="w-28 shrink-0 text-neutral-500">Kode Nasabah</dt><dd class="font-semibold">{{ $nasabah->kode() }}</dd></div>
            <div class="flex gap-2"><dt class="w-28 shrink-0 text-neutral-500">Bank Sampah</dt><dd class="font-semibold">{{ $nasabah->bankSampah?->nama ?? '-' }}</dd></div>
            <div class="flex gap-2"><dt class="w-28 shrink-0 text-neutral-500">Kelurahan</dt><dd class="font-semibold">{{ $nasabah->kelurahan }}, {{ $nasabah->kecamatan }}</dd></div>
        </dl>

        <section aria-label="Ringkasan" class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
            @foreach ([['Saldo Saat Ini', $rupiah($saldo)], ['Total Setoran Selesai', $rupiah($totalSetoran)], ['Total Penarikan', $rupiah($totalPenarikan)], ['Total Berat Disetor', $kg($totalBerat)]] as [$label, $isi])
                <div class="rounded-lg border border-neutral-200 px-3 py-2.5">
                    <p class="text-[0.7rem] text-neutral-500">{{ $label }}</p>
                    <p class="mt-0.5 text-sm font-bold tabular-nums">{{ $isi }}</p>
                </div>
            @endforeach
        </section>

        <table class="mt-6 w-full text-left text-xs">
            <thead>
                <tr class="border-y border-neutral-300 bg-neutral-50 text-[0.65rem] font-semibold uppercase tracking-wider text-neutral-500">
                    <th class="px-2 py-2.5">Transaksi</th>
                    <th class="px-2 py-2.5">Tanggal &amp; Waktu</th>
                    <th class="px-2 py-2.5">Keterangan</th>
                    <th class="px-2 py-2.5">Status</th>
                    <th class="px-2 py-2.5 text-right">Nilai (Rp)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-200">
                @forelse ($baris as $item)
                    <tr class="break-inside-avoid align-top">
                        <td class="px-2 py-2.5"><p class="font-semibold">{{ $item->judul }}</p><p class="font-mono text-[0.65rem] text-neutral-400">{{ $item->kode }}</p></td>
                        <td class="whitespace-nowrap px-2 py-2.5">{{ $item->waktu->locale('id')->translatedFormat('d M Y') }}<br><span class="text-neutral-500">{{ $item->waktu->format('H:i') }} WIB</span></td>
                        <td class="px-2 py-2.5 {{ $item->gagal ? 'text-red-600' : '' }}">{{ $item->keterangan }}@if ($item->keterangan_kecil)<br><span class="text-neutral-500">{{ $item->keterangan_kecil }}</span>@endif</td>
                        <td class="px-2 py-2.5 font-semibold">{{ $item->status_label }}</td>
                        <td class="whitespace-nowrap px-2 py-2.5 text-right font-semibold tabular-nums">
                            @if ($item->nilai === null)
                                -
                            @elseif ($item->nilai_tanda === 'batal')
                                <span class="text-neutral-400 line-through">{{ $rupiah($item->nilai) }}</span>
                            @else
                                {{ ['estimasi' => '≈ ', 'keluar' => '−', 'masuk' => '+'][$item->nilai_tanda] }}{{ $rupiah($item->nilai) }}
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-2 py-8 text-center text-neutral-500">Belum ada transaksi.</td></tr>
                @endforelse
            </tbody>
        </table>

        <p class="mt-6 text-[0.7rem] leading-relaxed text-neutral-500">Tanda “≈” berarti estimasi dari perkiraan berat pada pengajuan; nilai pasti ditentukan setelah admin menimbang sampah. Dokumen ini dibuat otomatis oleh sistem E-Bank Sampah FILKOM.</p>
    </main>

    <script>
        window.addEventListener('load', function () { setTimeout(function () { window.print(); }, 400); });
    </script>
</body>
</html>