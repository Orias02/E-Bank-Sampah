{{--
    Form pencatatan hasil timbangan, dipakai halaman detail pengajuan dan setoran langsung.
    Variabel: $action, $jenisSampah, $batalUrl, $tombol,
              $perkiraan (array id => kg, boleh kosong), $pilihMetode (bool), $metodeAwal (antar|jemput).
    Server menghitung nilai = berat x harga per kg; angka di sini hanya pratinjau.
--}}
@php
    $perkiraan = $perkiraan ?? [];
    $pilihMetode = $pilihMetode ?? false;
    $metodeAwal = old('metode', $metodeAwal ?? '');
    $inputBerat = fn ($angka) => str_replace('.', ',', rtrim(rtrim(number_format((float) $angka, 2, '.', ''), '0'), '.'));
    $kgTampil = fn ($angka) => rtrim(rtrim(number_format($angka, 2, ',', '.'), '0'), ',');

    $awalKg = 0;
    $awalNilai = 0;
    foreach ($jenisSampah as $jenis) {
        $isi = old('berat.' . $jenis->id, isset($perkiraan[$jenis->id]) ? $inputBerat($perkiraan[$jenis->id]) : '');
        $n = (float) str_replace(',', '.', (string) $isi);
        if ($n > 0) {
            $awalKg += $n;
            $awalNilai += round($n * $jenis->harga_per_kg);
        }
    }
    $invalidCount = collect(['metode', 'berat'])->filter(fn ($k) => $errors->has($k))->count();

    $metodeList = [
        'antar' => ['judul' => 'Diantar ke bank sampah', 'teks' => 'Nasabah membawa sendiri sampahnya.'],
        'jemput' => ['judul' => 'Dijemput petugas', 'teks' => 'Sampah diambil dari alamat nasabah.'],
    ];
@endphp

<form method="POST" action="{{ $action }}" novalidate data-wizard-form="timbang" class="rounded-2xl border border-neutral-200 bg-white p-5 sm:p-6">
    @csrf

    <div data-error-summary role="alert" tabindex="-1" @if ($invalidCount === 0) hidden @endif class="mb-5 flex gap-3 rounded-lg bg-red-50 px-4 py-3 text-red-700">
        <x-icon name="alert-circle" class="mt-0.5 size-5 shrink-0" />
        <div class="text-sm">
            <p data-error-summary-title class="font-semibold">Periksa {{ $invalidCount }} bagian yang ditandai.</p>
            <p>Lengkapi data berikut sebelum menyimpan.</p>
        </div>
    </div>

    @if ($pilihMetode)
        <div data-field="metode" role="radiogroup" aria-labelledby="metode-judul" data-invalid="{{ $errors->has('metode') ? 'true' : 'false' }}" class="group/field mb-6">
            <p id="metode-judul" class="text-sm font-semibold text-neutral-800">Cara sampah diterima</p>
            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                @foreach ($metodeList as $kunci => $item)
                    <label class="group/method relative flex cursor-pointer flex-col rounded-xl border-2 border-neutral-200 bg-white p-4 transition hover:border-neutral-300 has-checked:border-[#2e7d32] has-checked:bg-[#eaf3ea] has-focus-visible:ring-4 has-focus-visible:ring-[#2e7d32]/20 group-data-[invalid=true]/field:border-red-300">
                        <input type="radio" name="metode" value="{{ $kunci }}" @if ($metodeAwal === $kunci) checked @endif data-control class="sr-only">
                        <x-icon name="check" class="absolute right-4 top-4 size-4 text-[#2e7d32] opacity-0 transition group-has-checked/method:opacity-100" />
                        <span class="text-sm font-semibold text-neutral-900">{{ $item['judul'] }}</span>
                        <span class="mt-1 text-xs leading-relaxed text-slate-500">{{ $item['teks'] }}</span>
                    </label>
                @endforeach
            </div>
            <x-field-message name="metode" />
        </div>
    @endif

    <div data-field="berat" data-invalid="{{ $errors->has('berat') ? 'true' : 'false' }}" class="group/field">
        <p id="berat-judul" class="text-sm font-semibold text-neutral-800">Berat hasil timbangan</p>
        <p class="mt-1 text-xs text-slate-500">Isi berat aktual (kg) untuk tiap kategori yang diterima. Kategori yang dikosongkan tidak dicatat.</p>

        <div class="mt-4 divide-y divide-neutral-100 rounded-xl border border-neutral-200">
            @foreach ($jenisSampah as $jenis)
                <div data-timbang-row data-nama="{{ $jenis->nama }}" data-harga="{{ $jenis->harga_per_kg }}" class="flex flex-wrap items-center gap-x-4 gap-y-2 px-4 py-3">
                    <label for="berat-{{ $jenis->id }}" class="flex min-w-0 flex-1 basis-48 items-center gap-2.5 text-sm text-neutral-800">
                        <x-icon :name="$jenis->ikon" class="size-5 shrink-0 text-neutral-600" />
                        <span class="min-w-0 leading-snug">
                            {{ $jenis->nama }}
                            @unless ($jenis->aktif)
                                <span class="ml-1 text-xs font-medium text-amber-600">(nonaktif)</span>
                            @endunless
                            <span class="block text-xs text-neutral-500">Rp {{ number_format($jenis->harga_per_kg, 0, ',', '.') }} / kg</span>
                        </span>
                    </label>
                    <div class="relative w-32 shrink-0">
                        <input id="berat-{{ $jenis->id }}" name="berat[{{ $jenis->id }}]" type="text" inputmode="decimal" autocomplete="off" placeholder="0" value="{{ old('berat.' . $jenis->id, isset($perkiraan[$jenis->id]) ? $inputBerat($perkiraan[$jenis->id]) : '') }}" data-control data-berat aria-describedby="berat-error" class="block h-10 w-full rounded-lg border border-neutral-300 bg-white pl-3.5 pr-10 text-right text-sm tabular-nums text-neutral-900 placeholder:text-neutral-400 transition focus:border-[#2e7d32] focus:outline-none focus:ring-4 focus:ring-[#2e7d32]/10 aria-invalid:border-red-400 aria-invalid:bg-red-50/40">
                        <span class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-xs text-neutral-500" aria-hidden="true">kg</span>
                    </div>
                    <p data-subtotal class="w-28 shrink-0 text-right text-sm font-semibold tabular-nums text-neutral-400">-</p>
                </div>
            @endforeach
        </div>

        <div class="mt-4 rounded-xl bg-[#f7f9f7] px-4 py-3.5 text-sm">
            <p class="flex items-center justify-between gap-4">
                <span class="text-neutral-600">Total berat</span>
                <span data-total-kg class="font-bold tabular-nums text-neutral-900">{{ $kgTampil($awalKg) }} kg</span>
            </p>
            <p class="mt-1.5 flex items-center justify-between gap-4">
                <span class="text-neutral-600">Ditambahkan ke saldo nasabah</span>
                <span data-total-nilai class="text-lg font-bold tabular-nums text-[#2e7d32]">Rp {{ number_format($awalNilai, 0, ',', '.') }}</span>
            </p>
        </div>

        <x-field-message name="berat" />
    </div>

    <div class="mt-6 flex flex-wrap items-center justify-between gap-4 border-t border-neutral-100 pt-5">
        <a href="{{ $batalUrl }}" class="inline-flex h-11 items-center rounded-lg border border-neutral-300 px-5 text-sm font-semibold text-neutral-700 transition hover:bg-neutral-50">Batal</a>

        <button type="submit" data-loading="false" class="group/btn relative inline-flex h-11 items-center justify-center rounded-lg bg-[#2e7d32] px-6 text-sm font-semibold text-white shadow-lg shadow-[#2e7d32]/20 transition duration-300 hover:-translate-y-0.5 hover:bg-[#276a2b] active:translate-y-0 disabled:cursor-not-allowed focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#2e7d32]">
            <span class="group-data-[loading=true]/btn:invisible">{{ $tombol }}</span>
            <span class="absolute inset-0 hidden place-items-center group-data-[loading=true]/btn:grid" aria-hidden="true">
                <svg class="size-5 animate-spin" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity=".3" stroke-width="3"/><path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
            </span>
        </button>
    </div>
</form>