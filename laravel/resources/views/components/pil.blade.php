@props(['tone' => 'info'])

@php
    // Lencana status kecil, warnanya sama dengan yang dipakai di Riwayat nasabah.
    $warna = [
        'selesai' => 'border-emerald-200 bg-emerald-50 text-[#2e7d32]',
        'proses' => 'border-amber-200 bg-amber-50 text-amber-700',
        'info' => 'border-sky-200 bg-sky-50 text-sky-700',
        'gagal' => 'border-red-200 bg-red-50 text-red-600',
        'netral' => 'border-neutral-200 bg-neutral-100 text-neutral-600',
    ];
@endphp
<span {{ $attributes->merge(['class' => 'inline-block whitespace-nowrap rounded-full border px-3 py-1 text-xs font-semibold leading-snug ' . ($warna[$tone] ?? $warna['info'])]) }}>{{ $slot }}</span>