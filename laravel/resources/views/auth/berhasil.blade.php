@extends('layouts.wizard')

@section('title', 'Pendaftaran Berhasil | E-Bank Sampah FILKOM')

{{-- Judul panel kiri khusus halaman ini --}}
@section('panel-heading')
    <h2 class="text-[2.75rem] font-semibold leading-[1.15] tracking-tight">
        <span class="block">Selamat datang!</span>
        <span class="block">Malang bersih,</span>
        <span class="block">mulai dari Anda.</span>
    </h2>
@endsection

@section('card')
    @php
        // Dikirim controller: $nama, $firstName, $email, $bank (nama, alamat, jadwal) atau null.
        $helpdeskLink = 'https://wa.me/' . config('helpdesk.whatsapp') . '?text=' . rawurlencode(
            "Halo Helpdesk E-Bank Sampah FILKOM, saya {$nama} baru mendaftar sebagai nasabah dengan email {$email}. Mohon bantu verifikasi akun saya."
        );

        $nextSteps = [
            'Hubungi Helpdesk lewat WhatsApp untuk konfirmasi pendaftaran.',
            'Admin memeriksa data dan mengaktifkan akun Anda.',
            'Login dengan email dan kata sandi yang Anda buat tadi.',
        ];
    @endphp

    <div class="flex items-center border-b border-neutral-200 pb-6">
        <div class="w-full">
            <x-wizard-steps :current="4" />
        </div>
    </div>

    <div class="mx-auto mt-10 flex w-full max-w-xl flex-1 animate-fade-up flex-col items-center text-center">
        <span class="grid size-20 place-items-center rounded-full bg-[#eaf3ea] text-[#2e7d32] ring-8 ring-[#2e7d32]/5">
            <x-icon name="circle-check" class="size-10" />
        </span>

        <h1 class="mt-6 text-[1.9rem] font-normal leading-tight tracking-tight text-neutral-900">Pendaftaran berhasil, {{ $firstName }}!</h1>
        <p class="mt-3 text-sm leading-relaxed text-neutral-600">Akun Anda sudah dibuat dan sedang menunggu verifikasi admin. Hubungi Helpdesk agar akun bisa segera diaktifkan.</p>

        {{-- Ringkasan --}}
        <div class="mt-8 w-full rounded-2xl border border-neutral-200 bg-[#f7f9f7] p-5 text-left">
            <dl class="space-y-4 text-sm">
                <div class="flex items-start justify-between gap-4">
                    <dt class="shrink-0 text-neutral-500">Nama</dt>
                    <dd class="text-right font-medium text-neutral-900">{{ $nama }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4">
                    <dt class="shrink-0 text-neutral-500">Email login</dt>
                    <dd class="break-all text-right font-medium text-neutral-900">{{ $email }}</dd>
                </div>
                @if ($bank)
                    <div class="border-t border-neutral-200 pt-4">
                        <dt class="text-neutral-500">Bank sampah Anda</dt>
                        <dd class="mt-2">
                            <p class="font-semibold text-neutral-900">{{ $bank['nama'] }}</p>
                            <p class="mt-1.5 flex items-start gap-1.5 text-xs text-neutral-600">
                                <x-icon name="pin" class="mt-px size-3.5 shrink-0 text-neutral-400" />
                                {{ $bank['alamat'] }}
                            </p>
                            <p class="mt-1 flex items-start gap-1.5 text-xs text-neutral-600">
                                <x-icon name="clock" class="mt-px size-3.5 shrink-0 text-neutral-400" />
                                {{ $bank['jadwal'] }}
                            </p>
                        </dd>
                    </div>
                @endif
            </dl>
        </div>

        {{-- Langkah berikutnya --}}
        <div class="mt-6 w-full text-left">
            <h2 class="text-sm font-semibold text-neutral-900">Langkah berikutnya</h2>
            <ol class="mt-3 space-y-3">
                @foreach ($nextSteps as $step)
                    <li class="flex items-start gap-3 text-sm text-neutral-600">
                        <span class="grid size-6 shrink-0 place-items-center rounded-full bg-[#eaf3ea] text-xs font-semibold text-[#2e7d32]">{{ $loop->index + 1 }}</span>
                        <span class="pt-0.5">{{ $step }}</span>
                    </li>
                @endforeach
            </ol>
        </div>

        <div class="mt-8 flex w-full flex-col gap-3 border-t border-neutral-200 pt-6 sm:flex-row sm:justify-center">
            <a href="{{ $helpdeskLink }}" target="_blank" rel="noopener" class="group inline-flex h-12 items-center justify-center gap-2 rounded-lg bg-[#2e7d32] px-6 text-sm font-semibold text-white shadow-lg shadow-[#2e7d32]/20 transition duration-300 hover:-translate-y-0.5 hover:bg-[#276a2b] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#2e7d32]">
                Hubungi Helpdesk
                <x-icon name="arrow-right" class="size-4 transition-transform duration-300 group-hover:translate-x-0.5" />
            </a>
            <a href="{{ route('home') }}" class="inline-flex h-12 items-center justify-center rounded-lg border border-[#2e7d32] px-6 text-sm font-semibold text-[#2e7d32] transition hover:bg-[#2e7d32]/5">Kembali ke Beranda</a>
        </div>
    </div>
@endsection