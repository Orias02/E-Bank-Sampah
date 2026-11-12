@extends('layouts.auth')

@section('title', 'Pendaftaran Nasabah | E-Bank Sampah FILKOM')

@section('header-action')
    <a href="{{ route('login') }}" class="inline-flex h-12 items-center rounded-full border border-neutral-200 bg-white px-6 text-sm font-semibold text-neutral-800 transition duration-300 hover:-translate-y-0.5 hover:border-neutral-300 hover:shadow-md hover:shadow-black/5 sm:h-14 sm:px-8">Login</a>
@endsection

@section('content')
    <div class="mx-auto max-w-[720px]">
        <h1 class="text-center text-[1.75rem] font-bold tracking-tight text-slate-900">Buat Akun Baru</h1>
        <p class="mt-2 text-center text-slate-500">Langkah awal menjadi pahlawan lingkungan.</p>

        <form method="POST" action="{{ route('nasabah.register') }}" data-auth-form class="mt-8">
            @csrf

            {{-- Email --}}
            <div>
                <label for="email" class="block text-sm font-medium text-slate-800">Email Aktif</label>
                <input id="email" name="email" type="email" value="{{ old('email', session('register.akun.email')) }}" required autocomplete="email" placeholder="Contoh: nasabah@email.com"
                    @error('email') aria-invalid="true" aria-describedby="email-error" @enderror
                    class="mt-2 h-[46px] w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 transition placeholder:text-slate-400 focus:border-[#2e7d32] focus:bg-white focus:outline-none focus:ring-4 focus:ring-[#2e7d32]/10 aria-invalid:border-red-400 aria-invalid:bg-red-50/50">
                @error('email')
                    <p id="email-error" class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Kata sandi --}}
            <div class="mt-5">
                <label for="password" class="block text-sm font-medium text-slate-800">Buat Kata Sandi</label>
                <div class="relative mt-2">
                    <input id="password" name="password" type="password" required minlength="8" autocomplete="new-password" placeholder="Minimal 8 karakter"
                        @error('password') aria-invalid="true" aria-describedby="password-error" @enderror
                        class="h-[46px] w-full rounded-xl border border-slate-200 bg-slate-50 pl-4 pr-12 text-sm text-slate-900 transition placeholder:text-slate-400 focus:border-[#2e7d32] focus:bg-white focus:outline-none focus:ring-4 focus:ring-[#2e7d32]/10 aria-invalid:border-red-400 aria-invalid:bg-red-50/50">
                    <button type="button" data-password-toggle="password" data-visible="false" aria-label="Tampilkan kata sandi" class="group/eye absolute right-1.5 top-1/2 grid size-9 -translate-y-1/2 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                        <x-icon name="eye" class="size-5 group-data-[visible=true]/eye:hidden" />
                        <x-icon name="eye-off" class="hidden size-5 group-data-[visible=true]/eye:block" />
                    </button>
                </div>
                @error('password')
                    <p id="password-error" class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Konfirmasi kata sandi --}}
            <div class="mt-5">
                <label for="password_confirmation" class="block text-sm font-medium text-slate-800">Konfirmasi Kata Sandi</label>
                <div class="relative mt-2">
                    <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" placeholder="Ulangi kata sandi"
                        class="h-[46px] w-full rounded-xl border border-slate-200 bg-slate-50 pl-4 pr-12 text-sm text-slate-900 transition placeholder:text-slate-400 focus:border-[#2e7d32] focus:bg-white focus:outline-none focus:ring-4 focus:ring-[#2e7d32]/10">
                    <button type="button" data-password-toggle="password_confirmation" data-visible="false" aria-label="Tampilkan kata sandi" class="group/eye absolute right-1.5 top-1/2 grid size-9 -translate-y-1/2 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                        <x-icon name="eye" class="size-5 group-data-[visible=true]/eye:hidden" />
                        <x-icon name="eye-off" class="hidden size-5 group-data-[visible=true]/eye:block" />
                    </button>
                </div>
                {{-- Petunjuk cocok/tidak cocok, diisi oleh resources/js/auth.js --}}
                <p id="confirm-hint" hidden aria-live="polite" class="mt-1.5 text-sm text-amber-600 data-[ok=true]:text-emerald-600"></p>
            </div>

            <button type="submit" data-loading="false" class="group/btn relative mt-6 flex h-[50px] w-full items-center justify-center rounded-xl bg-[#2e7d32] text-sm font-semibold text-white shadow-lg shadow-[#2e7d32]/20 transition duration-300 hover:-translate-y-0.5 hover:bg-[#276a2b] active:translate-y-0 disabled:cursor-not-allowed focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#2e7d32]">
                <span class="group-data-[loading=true]/btn:invisible">Lanjut Isi Data Diri</span>
                <span class="absolute inset-0 hidden place-items-center group-data-[loading=true]/btn:grid" aria-hidden="true">
                    <svg class="size-5 animate-spin" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity=".3" stroke-width="3"/><path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                </span>
            </button>
        </form>

        <p class="mt-6 text-center text-slate-500">Sudah punya akun? <a href="{{ route('login') }}" class="font-semibold text-emerald-600 transition-colors hover:text-emerald-700 hover:underline">Masuk</a></p>
    </div>
@endsection