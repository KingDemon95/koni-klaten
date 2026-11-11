<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {

        $user = $request->user();

        if ($user && !$user->is_active) {

            // Logout pengguna.
            Auth::logout();

            // Hapus sesi login.
            $request->session()->invalidate();

            // Regenerasi token CSRF.
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Akun Anda telah dinonaktifkan. Silakan hubungi Administrator KONI.',
                ]);
        }

        return $next($request);
    }
}
