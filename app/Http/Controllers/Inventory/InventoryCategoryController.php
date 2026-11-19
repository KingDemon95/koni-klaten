<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\CabangOlahraga;
use App\Models\InventoryCategory;
use App\Support\Inventory\InventoryAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class InventoryCategoryController extends Controller
{
    public function __construct(private readonly InventoryAccess $access)
    {
    }

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', InventoryCategory::class);

        $user = $request->user();
        $categories = $this->access->visibleCategoriesQuery($user)
            ->with('cabangOlahraga')
            ->withCount('items')
            ->orderBy('nama')
            ->paginate(20);

        $cabors = $user->hasAnyRole(['admin_koni', 'ketua_koni'])
            ? CabangOlahraga::query()->where('status', 'aktif')->orderBy('nama')->get(['id', 'nama'])
            : collect();

        return view('inventaris.categories', [
            'categories' => $categories,
            'cabors' => $cabors,
            'routePrefix' => $this->access->routePrefix($user),
            'cabor' => $user->cabangOlahraga,
            'canCreate' => Gate::allows('create', InventoryCategory::class),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', InventoryCategory::class);

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
            'cabang_olahraga_id' => ['nullable', 'integer', 'exists:cabang_olahraga,id'],
        ]);

        $caborId = $request->user()->hasRole('admin_cabor')
            ? (int) $request->user()->cabang_olahraga_id
            : (isset($validated['cabang_olahraga_id']) ? (int) $validated['cabang_olahraga_id'] : null);

        if ($caborId !== null) {
            abort_unless(CabangOlahraga::query()->whereKey($caborId)->where('status', 'aktif')->exists(), 422, 'Cabang olahraga tidak aktif.');
        }

        $this->ensureUniqueName($validated['nama'], $caborId);

        InventoryCategory::create([
            'cabang_olahraga_id' => $caborId,
            'nama' => trim($validated['nama']),
            'deskripsi' => $validated['deskripsi'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route($this->access->routePrefix($request->user()).'.inventaris.kategori.index')
            ->with('success', 'Kategori inventaris berhasil ditambahkan.');
    }

    public function update(Request $request, InventoryCategory $category): RedirectResponse
    {
        Gate::authorize('update', $category);

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->ensureUniqueName($validated['nama'], $category->cabang_olahraga_id, $category->id);
        $category->update([
            'nama' => trim($validated['nama']),
            'deskripsi' => $validated['deskripsi'] ?? null,
        ]);

        return redirect()->route($this->access->routePrefix($request->user()).'.inventaris.kategori.index')
            ->with('success', 'Kategori inventaris berhasil diperbarui.');
    }

    public function destroy(Request $request, InventoryCategory $category): RedirectResponse
    {
        Gate::authorize('delete', $category);

        if ($category->items()->withTrashed()->exists()) {
            return back()->with('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh data barang.');
        }

        $category->delete();

        return redirect()->route($this->access->routePrefix($request->user()).'.inventaris.kategori.index')
            ->with('success', 'Kategori inventaris berhasil dihapus.');
    }

    private function ensureUniqueName(string $name, ?int $caborId, ?int $ignoreId = null): void
    {
        $query = InventoryCategory::query()->whereRaw('LOWER(nama) = ?', [mb_strtolower(trim($name))]);
        $caborId === null
            ? $query->whereNull('cabang_olahraga_id')
            : $query->where('cabang_olahraga_id', $caborId);

        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        abort_if($query->exists(), 422, 'Nama kategori sudah digunakan pada ruang lingkup tersebut.');
    }
}
