<?php

namespace App\Http\Controllers\Atlet;

use App\Http\Controllers\Controller;
use App\Models\Atlet;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AtletPanelController extends Controller
{
    /**
     * Profil atlet hanya boleh berasal dari akun
     * yang sedang login.
     */
    private function profilMilikAkun(Request $request): ?Atlet
    {
        return $request->user()
            ->atlet()
            ->with('cabangOlahraga')
            ->first();
    }

    /**
     * Dashboard pribadi atlet.
     */
    public function dashboard(Request $request): View
    {
        $atlet = $this->profilMilikAkun($request);

        return view('atlet.dashboard', [
            'atlet' => $atlet,
            'user' => $request->user(),
        ]);
    }

    /**
     * Profil pribadi atlet (baca saja).
     */
    public function profil(Request $request): View
    {
        $atlet = $this->profilMilikAkun($request);

        return view('atlet.profil', [
            'atlet' => $atlet,
            'user' => $request->user(),
        ]);
    }
}
