<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\Atlet;
use App\Models\CabangOlahraga;
use App\Models\Pelatih;
use App\Models\Pengurus;
use App\Models\Prestasi;
use App\Models\ProfilKoni;
use App\Models\Publikasi;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicPortalController extends Controller
{
    public function home(): View
    {
        $upcomingAgendas = Agenda::query()
            ->published()
            ->with('cabangOlahraga')
            ->whereDate('tanggal_mulai', '>=', now()->toDateString())
            ->orderBy('tanggal_mulai')
            ->limit(3)
            ->get();

        $cabors = CabangOlahraga::query()
            ->where('status', 'aktif')
            ->withCount([
                'atlets as atlet_aktif_count' => fn (Builder $query) => $query->where('status', 'aktif'),
                'pelatihs as pelatih_aktif_count' => fn (Builder $query) => $query->where('status', 'aktif'),
            ])
            ->orderBy('nama')
            ->limit(6)
            ->get();

        $pengurusKoni = Pengurus::query()
            ->whereNull('cabang_olahraga_id')
            ->where('status', 'aktif')
            ->orderBy('urutan')
            ->orderBy('nama_lengkap')
            ->limit(6)
            ->get([
                'id',
                'nama_lengkap',
                'jabatan',
                'bidang',
                'foto_path',
                'periode_mulai',
                'periode_selesai',
            ]);

        $verifiedPrestasis = Prestasi::query()
            ->where('status', 'terverifikasi')
            ->whereHas('cabangOlahraga', fn (Builder $query) => $query->where('status', 'aktif'));

        $statistics = [
            'cabor' => CabangOlahraga::query()->where('status', 'aktif')->count(),
            'atlet' => Atlet::query()
                ->where('status', 'aktif')
                ->whereHas('cabangOlahraga', fn (Builder $query) => $query->where('status', 'aktif'))
                ->count(),
            'pelatih' => Pelatih::query()
                ->where('status', 'aktif')
                ->whereHas('cabangOlahraga', fn (Builder $query) => $query->where('status', 'aktif'))
                ->count(),
            'pengurus' => Pengurus::query()
                ->whereNull('cabang_olahraga_id')
                ->where('status', 'aktif')
                ->count(),
            'prestasi' => (clone $verifiedPrestasis)->count(),
            'medali' => (clone $verifiedPrestasis)->whereIn('jenis_medali', ['emas', 'perak', 'perunggu'])->count(),
        ];

        $latestBerita = Publikasi::query()->published()->where('jenis', Publikasi::JENIS_BERITA)->orderByDesc('published_at')->limit(3)->get();
        $latestDokumentasi = Publikasi::query()->published()->whereIn('jenis', [Publikasi::JENIS_FOTO, Publikasi::JENIS_VIDEO])->orderByDesc('published_at')->limit(3)->get();

        $featuredPrestasis = (clone $verifiedPrestasis)
            ->with([
                'atlet:id,nama_lengkap',
                'pelatih:id,nama_lengkap',
                'cabangOlahraga:id,nama',
            ])
            ->orderByDesc('tanggal_prestasi')
            ->orderByDesc('id')
            ->limit(3)
            ->get();

        $profilKoni = ProfilKoni::query()->first();

        return view('public.home', compact('cabors', 'pengurusKoni', 'statistics', 'upcomingAgendas', 'featuredPrestasis', 'latestBerita', 'latestDokumentasi', 'profilKoni'));
    }

    public function caborIndex(Request $request): View
    {
        $query = $request->query('q', '');
        $search = is_string($query) ? trim($query) : '';

        $cabors = CabangOlahraga::query()
            ->where('status', 'aktif')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $nested) use ($search): void {
                    $nested->where('nama', 'like', "%{$search}%")
                        ->orWhere('singkatan', 'like', "%{$search}%");
                });
            })
            ->withCount([
                'atlets as atlet_aktif_count' => fn (Builder $query) => $query->where('status', 'aktif'),
                'pelatihs as pelatih_aktif_count' => fn (Builder $query) => $query->where('status', 'aktif'),
            ])
            ->orderBy('nama')
            ->paginate(12)
            ->withQueryString();

        return view('public.cabor.index', compact('cabors', 'search'));
    }

    public function caborShow(CabangOlahraga $cabangOlahraga): View
    {
        abort_unless($cabangOlahraga->status === 'aktif', 404);

        $atlets = $cabangOlahraga->atlets()
            ->where('status', 'aktif')
            ->orderBy('nama_lengkap')
            ->paginate(12, ['id', 'cabang_olahraga_id', 'nama_lengkap', 'foto_path'], 'atlet_page');

        $pelatihs = $cabangOlahraga->pelatihs()
            ->where('status', 'aktif')
            ->orderBy('nama_lengkap')
            ->paginate(8, ['id', 'cabang_olahraga_id', 'nama_lengkap', 'foto_path', 'spesialisasi', 'pengalaman_tahun'], 'pelatih_page');

        $penguruses = $cabangOlahraga->pengurus()
            ->where('status', 'aktif')
            ->orderBy('urutan')
            ->orderBy('nama_lengkap')
            ->get([
                'id',
                'cabang_olahraga_id',
                'nama_lengkap',
                'jabatan',
                'bidang',
                'foto_path',
                'periode_mulai',
                'periode_selesai',
            ]);

        // Hanya prestasi yang sudah diverifikasi Admin Cabor yang dipublikasikan.
        $prestasis = $cabangOlahraga->prestasis()
            ->where('status', 'terverifikasi')
            ->with(['atlet:id,nama_lengkap', 'pelatih:id,nama_lengkap'])
            ->orderByDesc('tanggal_prestasi')
            ->paginate(10, ['*'], 'prestasi_page');

        return view('public.cabor.show', compact(
            'cabangOlahraga',
            'atlets',
            'pelatihs',
            'penguruses',
            'prestasis'
        ));
    }

    public function strukturIndex(): View
    {
        $penguruses = Pengurus::query()
            ->whereNull('cabang_olahraga_id')
            ->where('status', 'aktif')
            ->orderBy('urutan')
            ->orderBy('nama_lengkap')
            ->get([
                'id',
                'nama_lengkap',
                'jabatan',
                'bidang',
                'foto_path',
                'periode_mulai',
                'periode_selesai',
            ]);

        return view('public.struktur.index', compact('penguruses'));
    }
}
