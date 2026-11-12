<?php

namespace App\Http\Controllers;

use App\Models\Nasabah;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        $nasabah = Nasabah::where('user_id', $request->user()->id)->first();

        // Akun login yang bukan nasabah (mis. admin) belum punya dashboard di sini.
        abort_if($nasabah === null, 403, 'Halaman ini khusus untuk nasabah.');

        // Akun dinonaktifkan admin setelah pengguna login: keluarkan.
        if ($nasabah->status !== 'aktif') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => 'Akun Anda belum aktif. Hubungi Helpdesk untuk konfirmasi.']);
        }

        return view('nasabah.dashboard', [
            'nasabah' => $nasabah,
            'saldo' => $nasabah->saldo(),
            'totalBerat' => $nasabah->totalBeratDisetor(),
            'aktivitas' => $nasabah->transaksi()
                ->with('jenisSampah')
                ->orderByDesc('tanggal')
                ->orderByDesc('id')
                ->limit(5)
                ->get(),
        ]);
    }
}