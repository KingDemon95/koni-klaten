<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequirePasswordChange
{
    private const ALLOWED_ROUTES = [
        'account.password.edit',
        'account.password.update',
        'logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->must_change_password) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();

        if (in_array($routeName, self::ALLOWED_ROUTES, true)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Anda harus mengganti password awal sebelum menggunakan aplikasi.',
                'password_change_required' => true,
            ], 423);
        }

        return redirect()
            ->route('account.password.edit')
            ->with('warning', 'Ganti password awal terlebih dahulu untuk melanjutkan.');
    }
}
