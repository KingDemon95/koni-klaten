<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminCaborAssigned
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $user = $request->user();

        if (!$user || !$user->hasRole('admin_cabor')) {
            abort(403, 'Akses hanya untuk Admin Cabor.');
        }

        $cabor = $user->cabangOlahraga;

        if (!$cabor) {
            abort(
                403,
                'Akun belum terhubung dengan cabang olahraga.'
            );
        }

        if ($cabor->status !== 'aktif') {
            abort(
                403,
                'Cabang olahraga tidak aktif. Hubungi Admin KONI.'
            );
        }

        return $next($request);
    }
}
