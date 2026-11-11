<?php

namespace App\Http\Controllers\Pelatih;

use App\Http\Controllers\Controller;
use App\Models\Pelatih;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PelatihPanelController extends Controller
{
    /**
     * Mengambil profil yang terhubung dengan
     * akun pengguna yang sedang login.
     */
    private function profilMilikAkun(Request $request): ?Pelatih
    {
        return $request->user()
            ->pelatih()
            ->with('cabangOlahraga')
            ->first();
    }

    /**
     * Dashboard pribadi Pelatih.
     */
    public function dashboard(Request $request): View
    {
        return view('pelatih.dashboard', [
            'user' => $request->user(),
            'pelatih' => $this->profilMilikAkun($request),
        ]);
    }

    /**
     * Profil pribadi Pelatih, baca saja.
     */
    public function profil(Request $request): View
    {
        return view('pelatih.profil', [
            'user' => $request->user(),
            'pelatih' => $this->profilMilikAkun($request),
        ]);
    }
}
