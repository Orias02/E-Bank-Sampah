<?php

namespace App\Http\Middleware;

use App\Models\Nasabah;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Hanya nasabah berstatus 'aktif' yang boleh membuka halaman area nasabah.
 * Data nasabah yang sedang login tersedia di $request->attributes->get('nasabah').
 */
class EnsureNasabahAktif
{
    public function handle(Request $request, Closure $next): Response
    {
        $nasabah = Nasabah::where('user_id', $request->user()->id)->first();

        // Akun login yang bukan nasabah (mis. admin) tidak punya akses ke area ini.
        abort_if($nasabah === null, 403, 'Halaman ini khusus untuk nasabah.');

        // Akun dinonaktifkan admin setelah pengguna login: keluarkan.
        if ($nasabah->status !== 'aktif') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => $nasabah->pesanStatus()]);
        }

        $request->attributes->set('nasabah', $nasabah);

        return $next($request);
    }
}