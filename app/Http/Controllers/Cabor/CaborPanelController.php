<?php

namespace App\Http\Controllers\Cabor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CaborPanelController extends Controller
{
    /**
     * Dashboard Admin Cabor.
     */
    public function dashboard(Request $request): View
    {
        $cabor = $request->user()->cabangOlahraga;

        $totalAtlet = $cabor->atlets()->count();

        $atletAktif = $cabor->atlets()
            ->where('status', 'aktif')
            ->count();

        $totalPelatih = $cabor->pelatihs()->count();

        $pelatihAktif = $cabor->pelatihs()
            ->where('status', 'aktif')
            ->count();

        $totalPengurus = $cabor->pengurus()->count();

        return view('cabor.dashboard', compact(
            'cabor',
            'totalAtlet',
            'atletAktif',
            'totalPelatih',
            'pelatihAktif',
            'totalPengurus'
        ));
    }

    /**
     * Daftar atlet milik cabor.
     */
    public function atlet(Request $request): View
    {
        return $this->daftar($request, 'atlet');
    }

    /**
     * Daftar pelatih milik cabor.
     */
    public function pelatih(Request $request): View
    {
        return $this->daftar($request, 'pelatih');
    }

    /**
     * Pengambilan data selalu dibatasi ke cabor akun.
     */
    private function daftar(
        Request $request,
        string $jenis
    ): View {
        $cabor = $request->user()->cabangOlahraga;

        $searchInput = $request->query('q', '');
        $search = is_string($searchInput)
            ? mb_substr(trim($searchInput), 0, 100)
            : '';

        $statusInput = $request->query('status', '');
        $status = in_array(
            $statusInput,
            ['aktif', 'nonaktif'],
            true
        ) ? $statusInput : '';

        $relasi = $jenis === 'atlet'
            ? 'atlets'
            : 'pelatihs';

        // Query dimulai dari relasi cabor pengguna.
        // Bukan dari seluruh tabel atlet/pelatih.
        $query = $cabor->{$relasi}();

        if ($search !== '') {
            $query->where(
                'nama_lengkap',
                'like',
                "%{$search}%"
            );
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        $items = $query
            ->orderBy('nama_lengkap')
            ->paginate(10)
            ->withQueryString();

        $total = $cabor->{$relasi}()->count();

        $aktif = $cabor->{$relasi}()
            ->where('status', 'aktif')
            ->count();

        $nonaktif = $cabor->{$relasi}()
            ->where('status', 'nonaktif')
            ->count();

        return view('cabor.daftar', compact(
            'cabor',
            'items',
            'jenis',
            'search',
            'status',
            'total',
            'aktif',
            'nonaktif'
        ));
    }
}
