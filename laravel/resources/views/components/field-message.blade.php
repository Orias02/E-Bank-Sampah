@props(['name', 'hint' => null])

@php
    // Teks bantuan + pesan error di bawah sebuah kolom. Saat ada error, pesan error
    // menggantikan teks bantuan. resources/js/wizard.js memakai elemen yang sama
    // (data-hint dan data-error) untuk validasi di sisi browser.
    $message = $errors->first($name);
@endphp
@if ($hint)
    <p data-hint @if ($message) hidden @endif class="mt-1.5 text-xs text-neutral-500">{{ $hint }}</p>
@endif
<p data-error id="{{ $name }}-error" @if (! $message) hidden @endif class="mt-1.5 text-xs text-red-600">{{ $message }}</p>