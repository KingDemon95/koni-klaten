<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Atlet;
use App\Models\CabangOlahraga;
use App\Models\Pelatih;
use App\Models\Pengurus;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StaffPanelController extends Controller
{
    /**
     * Dashboard rekap seluruh KONI Kabupaten Klaten.
     */
    public function dashboard(): View
    {
        $stats = [
            [
                'label' => 'Cabang Olahraga',
                'total' => CabangOlahraga::count(),
                'aktif' => CabangOlahraga::where('status', 'aktif')->count(),
                'route' => 'staff.cabor.index',
            ],
            [
                'label' => 'Atlet',
                'total' => Atlet::count(),
                'aktif' => Atlet::where('status', 'aktif')->count(),
                'route' => 'staff.atlet.index',
            ],
            [
                'label' => 'Pelatih',
                'total' => Pelatih::count(),
                'aktif' => Pelatih::where('status', 'aktif')->count(),
                'route' => 'staff.pelatih.index',
            ],
            [
                'label' => 'Pengurus',
                'total' => Pengurus::count(),
                'aktif' => Pengurus::where('status', 'aktif')->count(),
                'route' => 'staff.pengurus.index',
            ],
        ];

        return view('staff.dashboard', compact('stats'));
    }

    public function cabor(Request $request): View
    {
        return $this->daftar($request, 'cabor');
    }

    public function atlet(Request $request): View
    {
        return $this->daftar($request, 'atlet');
    }

    public function pelatih(Request $request): View
    {
        return $this->daftar($request, 'pelatih');
    }

    public function pengurus(Request $request): View
    {
        return $this->daftar($request, 'pengurus');
    }

    /**
     * Daftar read-only.
     *
     * Jenis model berasal dari method controller,
     * bukan dari input bebas pengguna.
     */
    private function daftar(
        Request $request,
        string $modul
    ): View {
        [$model, $judul, $orderBy] = match ($modul) {
            'cabor' => [
                CabangOlahraga::class,
                'Cabang Olahraga',
                'nama',
            ],
            'atlet' => [
                Atlet::class,
                'Atlet',
                'nama_lengkap',
            ],
            'pelatih' => [
                Pelatih::class,
                'Pelatih',
                'nama_lengkap',
            ],
            'pengurus' => [
                Pengurus::class,
                'Pengurus',
                'nama_lengkap',
            ],
            default => abort(404),
        };

        // Lindungi query dari parameter berbentuk array.
        $searchInput = $request->query('q', '');
        $statusInput = $request->query('status', '');
        $caborInput = $request->query('cabor_id', '');

        abort_unless(
            is_string($searchInput)
                && is_string($statusInput)
                && is_string($caborInput),
            422
        );

        $search = mb_substr(
            trim($searchInput),
            0,
            100
        );

        $status = $statusInput;
        $caborId = $caborInput;

        abort_unless(
            in_array($status, ['', 'aktif', 'nonaktif'], true),
            422
        );

        if ($modul !== 'cabor' && $caborId !== '') {
            abort_unless(ctype_digit($caborId), 422);
        }

        $query = $model::query();

        if ($modul !== 'cabor') {
            $query->with('cabangOlahraga');
        }

        if ($search !== '') {
            $query->where(function ($q) use ($modul, $search) {
                if ($modul === 'cabor') {
                    $q->where('nama', 'like', "%{$search}%")
                        ->orWhere(
                            'singkatan',
                            'like',
                            "%{$search}%"
                        );
                } else {
                    $q->where(
                        'nama_lengkap',
                        'like',
                        "%{$search}%"
                    );

                    if ($modul === 'pengurus') {
                        $q->orWhere(
                            'jabatan',
                            'like',
                            "%{$search}%"
                        );
                    }
                }
            });
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        if ($modul !== 'cabor' && $caborId !== '') {
            $query->where(
                'cabang_olahraga_id',
                (int) $caborId
            );
        }

        $items = $query
            ->orderBy($orderBy)
            ->paginate(10)
            ->withQueryString();

        // Statistik global, tidak dipengaruhi filter.
        $total = $model::count();

        $aktif = $model::where(
            'status',
            'aktif'
        )->count();

        $nonaktif = $model::where(
            'status',
            'nonaktif'
        )->count();

        $cabors = CabangOlahraga::query()
            ->orderBy('nama')
            ->get(['id', 'nama']);

        return view('staff.daftar', compact(
            'modul',
            'judul',
            'items',
            'search',
            'status',
            'caborId',
            'cabors',
            'total',
            'aktif',
            'nonaktif'
        ));
    }
}
