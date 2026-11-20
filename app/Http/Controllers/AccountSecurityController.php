<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AccountSecurityController extends Controller
{
    public function edit(Request $request): View
    {
        return view('account.security.password', [
            'requiresChange' => (bool) $request->user()->must_change_password,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => [
                'required',
                'string',
                'current_password:web',
            ],
            'password' => [
                'required',
                'string',
                'confirmed',
                'different:current_password',
                Password::min(10)->mixedCase()->numbers(),
            ],
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'current_password.current_password' => 'Password saat ini tidak sesuai.',
            'password.required' => 'Password baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi password baru tidak sama.',
            'password.different' => 'Password baru harus berbeda dari password saat ini.',
        ]);

        $user = $request->user();

        $user->forceFill([
            'password' => $validated['password'],
            'must_change_password' => false,
            'password_changed_at' => now(),
        ])->save();

        $request->session()->regenerate();

        return redirect()
            ->route('dashboard')
            ->with('success', 'Password berhasil diperbarui. Gunakan password baru pada login berikutnya.');
    }
}
