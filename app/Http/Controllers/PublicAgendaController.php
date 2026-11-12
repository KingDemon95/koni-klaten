<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicAgendaController extends Controller
{
    public function index(Request $request): View
    {
        $searchInput = $request->query('q', '');
        $jenisInput = $request->query('jenis', '');

        abort_unless(is_string($searchInput) && is_string($jenisInput), 422);

        $search = mb_substr(trim($searchInput), 0, 100);
        $jenis = $jenisInput;
        abort_unless(in_array($jenis, ['', 'agenda', 'kegiatan', 'kejuaraan'], true), 422);

        $agendas = Agenda::query()
            ->published()
            ->with('cabangOlahraga')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $nested) use ($search): void {
                    $nested->where('judul', 'like', "%{$search}%")
                        ->orWhere('deskripsi', 'like', "%{$search}%")
                        ->orWhere('lokasi', 'like', "%{$search}%")
                        ->orWhereHas('cabangOlahraga', fn (Builder $cabor) => $cabor->where('nama', 'like', "%{$search}%"));
                });
            })
            ->when($jenis !== '', fn (Builder $query) => $query->where('jenis', $jenis))
            ->orderByRaw('CASE WHEN tanggal_mulai >= ? THEN 0 ELSE 1 END', [now()->toDateString()])
            ->orderBy('tanggal_mulai')
            ->paginate(9)
            ->withQueryString();

        return view('public.agenda.index', compact('agendas', 'search', 'jenis'));
    }

    public function show(Agenda $agenda): View
    {
        abort_unless($agenda->status === Agenda::STATUS_PUBLISHED, 404);
        $agenda->load('cabangOlahraga');

        return view('public.agenda.show', compact('agenda'));
    }
}
