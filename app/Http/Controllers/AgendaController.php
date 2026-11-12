<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\CabangOlahraga;
use App\Support\WorkflowNotifier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AgendaController extends Controller
{
    public function index(Request $request): View
    {
        $searchInput = $request->query('q', '');
        $statusInput = $request->query('status', '');
        $jenisInput = $request->query('jenis', '');

        abort_unless(
            is_string($searchInput) && is_string($statusInput) && is_string($jenisInput),
            422
        );

        $search = mb_substr(trim($searchInput), 0, 100);
        $status = $statusInput;
        $jenis = $jenisInput;

        abort_unless(in_array($status, ['', 'draft', 'diajukan', 'revisi', 'published'], true), 422);
        abort_unless(in_array($jenis, ['', 'agenda', 'kegiatan', 'kejuaraan'], true), 422);

        $baseQuery = $this->queryForUser($request);
        $query = clone $baseQuery;

        if ($search !== '') {
            $query->where(function (Builder $nested) use ($search): void {
                $nested->where('judul', 'like', "%{$search}%")
                    ->orWhere('lokasi', 'like', "%{$search}%")
                    ->orWhereHas('cabangOlahraga', fn (Builder $cabor) => $cabor->where('nama', 'like', "%{$search}%"));
            });
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        if ($jenis !== '') {
            $query->where('jenis', $jenis);
        }

        $items = $query
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'diajukan' => (clone $baseQuery)->where('status', Agenda::STATUS_DIAJUKAN)->count(),
            'published' => (clone $baseQuery)->where('status', Agenda::STATUS_PUBLISHED)->count(),
        ];

        return view('agenda.index', $this->viewData($request) + compact(
            'items', 'search', 'status', 'jenis', 'stats'
        ));
    }

    public function create(Request $request): View
    {
        return view('agenda.create', $this->viewData($request) + [
            'agenda' => new Agenda(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);
        $posterPath = $request->file('poster')?->store('agenda', 'public');
        unset($validated['poster']);

        $caborId = $this->isCaborUser($request)
            ? $request->user()->cabang_olahraga_id
            : ($validated['cabang_olahraga_id'] ?? null);

        $judul = $validated['judul'];
        $agenda = Agenda::create($validated + [
            'cabang_olahraga_id' => $caborId,
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
            'slug' => $this->uniqueSlug($judul),
            'poster_path' => $posterPath,
            'status' => Agenda::STATUS_DRAFT,
        ]);

        return redirect()
            ->route($this->routePrefix($request).'.agenda.index')
            ->with('success', 'Agenda berhasil dibuat sebagai draft.');
    }

    public function edit(Request $request, Agenda $agenda): View
    {
        $agenda = $this->findScopedAgenda($request, $agenda);
        abort_if($this->isCaborUser($request) && $agenda->status === Agenda::STATUS_PUBLISHED, 403);

        return view('agenda.edit', $this->viewData($request) + compact('agenda'));
    }

    public function update(Request $request, Agenda $agenda): RedirectResponse
    {
        $agenda = $this->findScopedAgenda($request, $agenda);
        abort_if($this->isCaborUser($request) && $agenda->status === Agenda::STATUS_PUBLISHED, 403);

        $validated = $this->validatedData($request, $agenda);
        $posterPath = $agenda->poster_path;

        if ($request->hasFile('poster')) {
            if ($posterPath) {
                Storage::disk('public')->delete($posterPath);
            }
            $posterPath = $request->file('poster')->store('agenda', 'public');
        }

        unset($validated['poster']);

        if ($this->isCaborUser($request)) {
            unset($validated['cabang_olahraga_id']);
        }

        if ($agenda->judul !== $validated['judul']) {
            $validated['slug'] = $this->uniqueSlug($validated['judul'], $agenda->id);
        }

        $validated['poster_path'] = $posterPath;
        $validated['updated_by'] = $request->user()->id;

        // Perubahan oleh Admin Cabor atas pengajuan yang sedang ditinjau
        // mengembalikannya menjadi draft agar dapat diajukan ulang.
        if ($this->isCaborUser($request) && $agenda->status === Agenda::STATUS_DIAJUKAN) {
            $validated['status'] = Agenda::STATUS_DRAFT;
            $validated['catatan_revisi'] = null;
        }

        $agenda->update($validated);

        return redirect()
            ->route($this->routePrefix($request).'.agenda.index')
            ->with('success', 'Agenda berhasil diperbarui.');
    }

    public function destroy(Request $request, Agenda $agenda): RedirectResponse
    {
        $agenda = $this->findScopedAgenda($request, $agenda);
        abort_if($this->isCaborUser($request) && $agenda->status === Agenda::STATUS_PUBLISHED, 403);

        if ($agenda->poster_path) {
            Storage::disk('public')->delete($agenda->poster_path);
        }

        $agenda->delete();

        return redirect()
            ->route($this->routePrefix($request).'.agenda.index')
            ->with('success', 'Agenda berhasil dihapus.');
    }

    public function submit(Request $request, Agenda $agenda): RedirectResponse
    {
        abort_unless($this->isCaborUser($request), 403);
        $agenda = $this->findScopedAgenda($request, $agenda);
        abort_unless(in_array($agenda->status, [Agenda::STATUS_DRAFT, Agenda::STATUS_REVISI], true), 422);

        $agenda->update([
            'status' => Agenda::STATUS_DIAJUKAN,
            'catatan_revisi' => null,
            'updated_by' => $request->user()->id,
        ]);

        WorkflowNotifier::toRole(
            'staff_koni',
            'Agenda baru menunggu pemeriksaan',
            'Agenda "'.$agenda->judul.'" diajukan oleh Admin Cabor dan siap diperiksa.',
            route('staff.agenda.index', [], false),
            'agenda',
        );

        return redirect()
            ->route('cabor.agenda.index')
            ->with('success', 'Agenda berhasil diajukan kepada Staff KONI.');
    }

    public function publish(Request $request, Agenda $agenda): RedirectResponse
    {
        abort_unless($this->canPublish($request), 403);
        $agenda = $this->findScopedAgenda($request, $agenda);

        $agenda->update([
            'status' => Agenda::STATUS_PUBLISHED,
            'catatan_revisi' => null,
            'published_at' => now(),
            'published_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        $agenda->loadMissing('creator');
        WorkflowNotifier::toUser(
            $agenda->creator,
            'Agenda telah dipublikasikan',
            'Agenda "'.$agenda->judul.'" telah dipublikasikan ke website publik.',
            route('public.agenda.show', $agenda->slug, false),
            'agenda',
            $request->user()->id,
        );

        return redirect()
            ->route($this->routePrefix($request).'.agenda.index')
            ->with('success', 'Agenda berhasil dipublikasikan ke website Public.');
    }

    public function requestRevision(Request $request, Agenda $agenda): RedirectResponse
    {
        abort_unless($this->canPublish($request), 403);
        $agenda = $this->findScopedAgenda($request, $agenda);
        abort_unless($agenda->status === Agenda::STATUS_DIAJUKAN, 422);

        $validated = $request->validate([
            'catatan_revisi' => ['required', 'string', 'max:2000'],
        ]);

        $agenda->update([
            'status' => Agenda::STATUS_REVISI,
            'catatan_revisi' => $validated['catatan_revisi'],
            'updated_by' => $request->user()->id,
        ]);

        $agenda->loadMissing('creator');
        WorkflowNotifier::toUser(
            $agenda->creator,
            'Agenda perlu diperbaiki',
            'Agenda "'.$agenda->judul.'" dikembalikan untuk revisi. Catatan: '.$validated['catatan_revisi'],
            route('cabor.agenda.index', [], false),
            'agenda',
            $request->user()->id,
        );

        return redirect()
            ->route($this->routePrefix($request).'.agenda.index')
            ->with('success', 'Catatan revisi berhasil dikirim kepada Admin Cabor.');
    }

    private function validatedData(Request $request, ?Agenda $agenda = null): array
    {
        $caborRules = $this->isCaborUser($request)
            ? ['prohibited']
            : ['nullable', 'integer', Rule::exists('cabang_olahraga', 'id')->where(fn ($query) => $query->where('status', 'aktif'))];

        return $request->validate([
            'judul' => ['required', 'string', 'max:180'],
            'jenis' => ['required', Rule::in(['agenda', 'kegiatan', 'kejuaraan'])],
            'deskripsi' => ['nullable', 'string', 'max:10000'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'waktu_mulai' => ['nullable', 'date_format:H:i'],
            'waktu_selesai' => ['nullable', 'date_format:H:i'],
            'lokasi' => ['nullable', 'string', 'max:180'],
            'cabang_olahraga_id' => $caborRules,
            'poster' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ]);
    }

    private function queryForUser(Request $request): Builder
    {
        $query = Agenda::query()->with(['cabangOlahraga', 'creator', 'publisher']);

        if ($this->isCaborUser($request)) {
            $query->where('cabang_olahraga_id', $request->user()->cabang_olahraga_id);
        }

        return $query;
    }

    private function findScopedAgenda(Request $request, Agenda $agenda): Agenda
    {
        return $this->queryForUser($request)->whereKey($agenda->getKey())->firstOrFail();
    }

    private function viewData(Request $request): array
    {
        $isCabor = $this->isCaborUser($request);
        $cabor = $isCabor ? $request->user()->cabangOlahraga : null;
        $cabors = $isCabor
            ? collect([$cabor])->filter()
            : CabangOlahraga::query()->where('status', 'aktif')->orderBy('nama')->get(['id', 'nama']);

        return [
            'layout' => $this->layoutName($request),
            'routePrefix' => $this->routePrefix($request),
            'cabors' => $cabors,
            'cabor' => $cabor,
            'isCaborUser' => $isCabor,
            'canPublish' => $this->canPublish($request),
        ];
    }

    private function isCaborUser(Request $request): bool
    {
        return $this->routePrefix($request) === 'cabor';
    }

    private function canPublish(Request $request): bool
    {
        return in_array($this->routePrefix($request), ['admin', 'staff'], true);
    }

    private function routePrefix(Request $request): string
    {
        $routeName = $request->route()?->getName() ?? '';

        foreach (['admin', 'staff', 'cabor'] as $prefix) {
            if (str_starts_with($routeName, $prefix.'.agenda.')) {
                return $prefix;
            }
        }

        abort(403);
    }

    private function layoutName(Request $request): string
    {
        return match ($this->routePrefix($request)) {
            'admin' => 'layouts.admin',
            'staff' => 'layouts.staff',
            'cabor' => 'layouts.cabor',
        };
    }

    private function uniqueSlug(string $judul, ?int $ignoreId = null): string
    {
        $base = Str::slug($judul) ?: 'agenda';
        $slug = $base;
        $suffix = 2;

        while (Agenda::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn (Builder $query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
