<?php

namespace App\Http\Controllers;

use App\Models\JenisSampah;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/** Katalog harga sampah. Dilindungi middleware EnsureNasabahAktif. */
class KatalogController extends Controller
{
    public function __invoke(): View
    {
        // Urutan mengikuti urutan data dimasukkan (id), jadi admin bisa mengatur urutan tampil lewat urutan input.
        $jenisSampah = JenisSampah::orderBy('id')->get();

        $terakhir = $jenisSampah->max('harga_diperbarui_pada');

        return view('nasabah.harga', [
            'jenisSampah' => $jenisSampah,
            'kategori' => $jenisSampah->pluck('kategori')->unique()->values(),
            'diperbarui' => $terakhir ? Carbon::parse($terakhir) : null,
        ]);
    }
}