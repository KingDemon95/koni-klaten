<?php

namespace App\Http\Controllers;

use App\Models\Surat;
use App\Models\SuratMessage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SuratMessageController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $role = match (true) {
            $user->hasRole('admin_cabor') => 'cabor',
            $user->hasRole('staff_koni') => 'staff',
            $user->hasRole('ketua_koni') => 'ketua',
            $user->hasRole('sekretaris_koni') => 'sekretaris',
            $user->hasRole('admin_koni') => 'admin',
            default => abort(403, 'Role ini tidak memiliki akses ke pesan persuratan.'),
        };

        $query = SuratMessage::query()
            ->with(['surat.cabangOlahraga', 'sender']);

        if ($role === 'cabor') {
            $query->whereHas('surat', function (Builder $suratQuery) use ($user): void {
                $suratQuery
                    ->where('cabang_olahraga_id', $user->cabang_olahraga_id)
                    ->where('status', '!=', Surat::STATUS_PERLU_REVISI_STAFF);
            });
        } elseif ($role === 'staff') {
            $query->whereHas('surat', fn (Builder $suratQuery) => $suratQuery->where('status', '!=', Surat::STATUS_DRAFT));
        } else {
            $query->whereHas('surat', function (Builder $suratQuery): void {
                $suratQuery->whereIn('status', [
                    Surat::STATUS_DITERUSKAN,
                    Surat::STATUS_PERLU_REVISI_STAFF,
                    Surat::STATUS_MENUNGGU_PENYAMPAIAN,
                    Surat::STATUS_REVISI,
                    Surat::STATUS_DITERIMA,
                    Surat::STATUS_DITOLAK,
                ]);
            });
        }

        $messages = $query->latest()->paginate(20)->withQueryString();

        $layout = match ($role) {
            'admin', 'ketua' => 'layouts.admin',
            'sekretaris' => 'layouts.sekretaris',
            'staff' => 'layouts.staff',
            'cabor' => 'layouts.cabor',
        };

        $routeBase = match ($role) {
            'admin', 'ketua' => 'admin.persuratan',
            'sekretaris' => 'sekretaris.surat',
            'staff' => 'staff.surat',
            'cabor' => 'cabor.surat',
        };

        return view('messages.index', compact('messages', 'layout', 'routeBase', 'role'));
    }
}
