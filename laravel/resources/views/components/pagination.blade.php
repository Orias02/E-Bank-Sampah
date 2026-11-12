@props(['paginator', 'satuan' => 'data'])

@php
    // Nomor halaman dengan jendela di sekitar halaman aktif; null = "…".
    $akhir = $paginator->lastPage();
    $kini = $paginator->currentPage();
    $nomor = [];
    if ($akhir <= 7) {
        $nomor = range(1, $akhir);
    } else {
        $nomor = [1];
        $awal = max(2, $kini - 1);
        $ujung = min($akhir - 1, $kini + 1);
        if ($awal > 2) {
            $nomor[] = null;
        }
        foreach (range($awal, $ujung) as $n) {
            $nomor[] = $n;
        }
        if ($ujung < $akhir - 1) {
            $nomor[] = null;
        }
        $nomor[] = $akhir;
    }
    $tombol = 'grid h-8 min-w-8 place-items-center rounded-md border px-2 text-xs font-semibold transition';
@endphp

@if ($paginator->total() > 0)
    <div class="flex flex-col gap-3 border-t border-neutral-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-xs text-slate-500 sm:text-sm">Menampilkan {{ $paginator->firstItem() }}-{{ $paginator->lastItem() }} dari {{ $paginator->total() }} {{ $satuan }}</p>

        @if ($akhir > 1)
            <nav aria-label="Halaman" class="flex flex-wrap items-center gap-1 sm:gap-1.5">
                @if ($paginator->onFirstPage())
                    <span aria-disabled="true" class="{{ $tombol }} border-transparent px-2 text-neutral-300">Sebelumnya</span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="{{ $tombol }} border-transparent px-2 text-neutral-700 hover:bg-neutral-100">Sebelumnya</a>
                @endif

                @foreach ($nomor as $n)
                    @if ($n === null)
                        <span aria-hidden="true" class="px-1 text-neutral-400">…</span>
                    @elseif ($n === $kini)
                        <span aria-current="page" class="{{ $tombol }} border-[#2e7d32]/30 bg-[#eaf3ea] text-[#2e7d32]">{{ $n }}</span>
                    @else
                        <a href="{{ $paginator->url($n) }}" aria-label="Halaman {{ $n }}" class="{{ $tombol }} border-neutral-200 text-neutral-700 hover:bg-neutral-50">{{ $n }}</a>
                    @endif
                @endforeach

                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="{{ $tombol }} border-neutral-300 px-2.5 text-neutral-800 hover:bg-neutral-50">Selanjutnya</a>
                @else
                    <span aria-disabled="true" class="{{ $tombol }} border-transparent px-2 text-neutral-300">Selanjutnya</span>
                @endif
            </nav>
        @endif
    </div>
@endif