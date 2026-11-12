<?php

namespace App\Http\Middleware;

use App\Models\BankSampah;
use App\Models\Nasabah;
use App\Models\PengajuanSetoran;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Hanya akun ber-role 'admin' yang boleh membuka area admin.
 * Admin yang login tersedia di $request->attributes->get('admin').
 * Jumlah antrean (nasabah dan pengajuan menunggu) dibagikan ke semua view untuk lencana di menu.
 */
class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $admin = $request->user();

        abort_unless($admin && $admin->role === 'admin', 403, 'Halaman ini khusus admin.');

        $request->attributes->set('admin', $admin);

        View::share('antrean', [
            'nasabah' => Nasabah::diCakupan($admin)->where('status', 'menunggu')->count(),
            'pengajuan' => PengajuanSetoran::diCakupan($admin)->where('status', PengajuanSetoran::MENUNGGU)->count(),
        ]);
        View::share('namaBankAdmin', $admin->bank_sampah_id ? BankSampah::whereKey($admin->bank_sampah_id)->value('nama') : null);

        return $next($request);
    }
}