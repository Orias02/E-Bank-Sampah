@props(['current' => 1])

@php
    // Indikator langkah pendaftaran nasabah setelah membuat akun: Data Diri → Alamat → Berhasil.
    // Pakai di halaman wizard dengan atribut current (1, 2, atau 3). Isi 4 jika semua langkah selesai.
    $current = (int) $current;
    $labels = ['Data Diri', 'Alamat', 'Berhasil'];

    $circleClass = [
        'current' => 'bg-[#2e7d32] text-white',
        'done' => 'bg-[#2e7d32] text-white',
        'upcoming' => 'border border-neutral-300 bg-white text-neutral-500',
    ];
    $labelClass = [
        'current' => 'font-semibold text-[#2e7d32]',
        'done' => 'hidden font-medium text-[#2e7d32] sm:inline',
        'upcoming' => 'hidden text-neutral-500 sm:inline',
    ];
@endphp
<ol aria-label="Langkah pendaftaran" class="flex items-center gap-3 sm:gap-4">
    @foreach ($labels as $i => $label)
        @php
            $number = $i + 1;
            $state = $number < $current ? 'done' : ($number === $current ? 'current' : 'upcoming');
        @endphp
        <li @if ($state === 'current') aria-current="step" @endif class="flex items-center gap-3 sm:gap-4 {{ $loop->last ? '' : 'flex-1' }}">
            <span class="grid size-8 shrink-0 place-items-center rounded-full text-sm font-semibold transition-colors {{ $circleClass[$state] }}">
                @if ($state === 'done')
                    <x-icon name="check" class="size-4" />
                @else
                    {{ $number }}
                @endif
            </span>
            <span class="text-sm {{ $labelClass[$state] }}">{{ $label }}</span>
            @if (! $loop->last)
                <span aria-hidden="true" class="h-px flex-1 {{ $number < $current ? 'bg-[#2e7d32]' : 'bg-neutral-200' }}"></span>
            @endif
        </li>
    @endforeach
</ol>