<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\CabangOlahraga;
use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Support\Inventory\InventoryAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function __construct(private readonly InventoryAccess $access)
    {
    }

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', InventoryItem::class);

        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'kondisi' => ['nullable', Rule::in(array_keys(InventoryItem::CONDITIONS))],
            'status' => ['nullable', Rule::in(['aktif', 'nonaktif'])],
            'category_id' => ['nullable', 'integer', 'min:1'],
            'cabor_id' => ['nullable', 'integer', 'exists:cabang_olahraga,id'],
        ]);

        $user = $request->user();
        $routePrefix = $this->access->routePrefix($user);
        $cabor = $user->cabangOlahraga;
        $baseQuery = $this->access->visibleItemsQuery($user);

        if (!empty($validated['q'])) {
            $search = trim($validated['q']);
            $baseQuery->where(function ($query) use ($search): void {
                $query->where('nama_barang', 'like', "%{$search}%")
                    ->orWhere('kode_barang', 'like', "%{$search}%")
                    ->orWhere('lokasi', 'like', "%{$search}%");
            });
        }

        foreach (['kondisi', 'status'] as $filter) {
            if (!empty($validated[$filter])) {
                $baseQuery->where($filter, $validated[$filter]);
            }
        }

        if (!empty($validated['category_id'])) {
            $baseQuery->where('inventory_category_id', $validated['category_id']);
        }

        if (!empty($validated['cabor_id']) && !$user->hasRole('admin_cabor')) {
            $baseQuery->where('cabang_olahraga_id', $validated['cabor_id']);
        }

        $totalItems = (clone $baseQuery)->count();
        $totalUnits = (clone $baseQuery)->sum('jumlah');
        $damagedItems = (clone $baseQuery)->whereIn('kondisi', ['rusak_ringan', 'rusak_berat', 'tidak_layak'])->count();
        $activeLoans = (clone $baseQuery)->whereHas('activeLoans')->count();

        $items = $baseQuery
            ->with(['category', 'cabangOlahraga'])
            ->withSum('activeLoans as borrowed_quantity', 'jumlah')
            ->orderBy('nama_barang')
            ->paginate(15)
            ->withQueryString();

        $categories = $this->access->visibleCategoriesQuery($user)->orderBy('nama')->get();
        $cabors = $user->hasAnyRole(['admin_koni', 'ketua_koni', 'sekretaris_koni'])
            ? CabangOlahraga::query()->orderBy('nama')->get(['id', 'nama'])
            : collect();

        return view('inventaris.index', compact(
            'items', 'categories', 'cabors', 'routePrefix', 'totalItems', 'totalUnits',
            'damagedItems', 'activeLoans', 'validated', 'cabor'
        ));
    }

    public function create(Request $request): View|RedirectResponse
    {
        Gate::authorize('create', InventoryItem::class);

        $user = $request->user();
        $routePrefix = $this->access->routePrefix($user);
        $cabor = $user->cabangOlahraga;
        $categories = $this->access->visibleCategoriesQuery($user)->orderBy('nama')->get();
        $cabors = $user->hasAnyRole(['admin_koni', 'ketua_koni'])
            ? CabangOlahraga::query()->where('status', 'aktif')->orderBy('nama')->get(['id', 'nama'])
            : collect();

        if ($categories->isEmpty()) {
            return redirect()
                ->route($routePrefix.'.inventaris.kategori.index')
                ->with('error', 'Buat kategori inventaris terlebih dahulu sebelum menambahkan barang.');
        }

        return view('inventaris.create', compact('categories', 'cabors', 'routePrefix', 'cabor'));
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', InventoryItem::class);

        $user = $request->user();
        $validated = $request->validate($this->itemRules());
        $caborId = $this->resolveCaborId($request, $validated);
        $category = InventoryCategory::query()->findOrFail($validated['inventory_category_id']);

        abort_unless($this->access->categoryMatchesScope($category, $caborId), 422, 'Kategori tidak sesuai dengan kepemilikan inventaris.');

        $item = DB::transaction(function () use ($validated, $caborId, $user): InventoryItem {
            $item = InventoryItem::create([
                'inventory_category_id' => $validated['inventory_category_id'],
                'cabang_olahraga_id' => $caborId,
                'nama_barang' => $validated['nama_barang'],
                'deskripsi' => $validated['deskripsi'] ?? null,
                'jumlah' => $validated['jumlah'],
                'satuan' => $validated['satuan'],
                'kondisi' => $validated['kondisi'],
                'lokasi' => $validated['lokasi'],
                'tanggal_perolehan' => $validated['tanggal_perolehan'] ?? null,
                'harga_perolehan' => $validated['harga_perolehan'] ?? null,
                'status' => $validated['status'],
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            $ownerCode = $caborId === null
                ? 'KONI'
                : 'CABOR'.str_pad((string) $caborId, 3, '0', STR_PAD_LEFT);

            $item->update([
                'kode_barang' => 'INV-'.$ownerCode.'-'.now()->format('Y').'-'.str_pad((string) $item->id, 6, '0', STR_PAD_LEFT),
            ]);

            return $item;
        });

        return redirect()
            ->route($this->access->routePrefix($user).'.inventaris.show', $item)
            ->with('success', 'Data inventaris berhasil ditambahkan.');
    }

    public function show(Request $request, InventoryItem $inventoryItem): View
    {
        Gate::authorize('view', $inventoryItem);

        $inventoryItem->load([
            'category', 'cabangOlahraga', 'creator', 'updater',
            'movements.actor', 'loans.creator', 'loans.returner',
        ]);

        $user = $request->user();
        $routePrefix = $this->access->routePrefix($user);

        return view('inventaris.show', [
            'item' => $inventoryItem,
            'routePrefix' => $routePrefix,
            'cabor' => $user->cabangOlahraga,
            'canManage' => Gate::allows('update', $inventoryItem),
            'canMove' => Gate::allows('move', $inventoryItem),
            'canLoan' => Gate::allows('createLoan', $inventoryItem),
            'canReturnLoan' => Gate::allows('returnLoan', $inventoryItem),
            'availableQuantity' => $inventoryItem->availableQuantity(),
        ]);
    }

    public function edit(Request $request, InventoryItem $inventoryItem): View
    {
        Gate::authorize('update', $inventoryItem);

        $user = $request->user();
        $routePrefix = $this->access->routePrefix($user);
        $cabor = $user->cabangOlahraga;
        $categories = $this->access->visibleCategoriesQuery($user)->orderBy('nama')->get()
            ->filter(fn (InventoryCategory $category) => $this->access->categoryMatchesScope($category, $inventoryItem->cabang_olahraga_id));

        return view('inventaris.edit', compact('inventoryItem', 'categories', 'routePrefix', 'cabor'));
    }

    public function update(Request $request, InventoryItem $inventoryItem): RedirectResponse
    {
        Gate::authorize('update', $inventoryItem);

        $validated = $request->validate($this->itemRules(false));
        $category = InventoryCategory::query()->findOrFail($validated['inventory_category_id']);
        abort_unless($this->access->categoryMatchesScope($category, $inventoryItem->cabang_olahraga_id), 422, 'Kategori tidak sesuai dengan kepemilikan inventaris.');

        DB::transaction(function () use ($inventoryItem, $validated, $request): void {
            $lockedItem = InventoryItem::query()->lockForUpdate()->findOrFail($inventoryItem->id);
            $loanedQuantity = (int) $lockedItem->activeLoans()->sum('jumlah');
            abort_if((int) $validated['jumlah'] < $loanedQuantity, 422, 'Jumlah barang tidak boleh lebih kecil dari jumlah unit yang masih dipinjam.');

            $lockedItem->update([
                ...$validated,
                'updated_by' => $request->user()->id,
            ]);
        });

        return redirect()
            ->route($this->access->routePrefix($request->user()).'.inventaris.show', $inventoryItem)
            ->with('success', 'Data inventaris berhasil diperbarui.');
    }

    public function destroy(Request $request, InventoryItem $inventoryItem): RedirectResponse
    {
        Gate::authorize('delete', $inventoryItem);

        $archived = DB::transaction(function () use ($inventoryItem): bool {
            $lockedItem = InventoryItem::query()->lockForUpdate()->findOrFail($inventoryItem->id);

            if ($lockedItem->activeLoans()->exists()) {
                return false;
            }

            $lockedItem->delete();
            return true;
        });

        if (!$archived) {
            return back()->with('error', 'Barang tidak dapat diarsipkan selama masih dipinjam.');
        }

        return redirect()
            ->route($this->access->routePrefix($request->user()).'.inventaris.index')
            ->with('success', 'Barang berhasil diarsipkan. Riwayat transaksi tetap tersimpan.');
    }

    private function itemRules(bool $includeScope = true): array
    {
        $rules = [
            'inventory_category_id' => ['required', 'integer', 'exists:inventory_categories,id'],
            'nama_barang' => ['required', 'string', 'max:180'],
            'deskripsi' => ['nullable', 'string', 'max:3000'],
            'jumlah' => ['required', 'integer', 'min:1', 'max:1000000'],
            'satuan' => ['required', 'string', 'max:30'],
            'kondisi' => ['required', Rule::in(array_keys(InventoryItem::CONDITIONS))],
            'lokasi' => ['required', 'string', 'max:150'],
            'tanggal_perolehan' => ['nullable', 'date'],
            'harga_perolehan' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
        ];

        if ($includeScope) {
            $rules['scope'] = ['required', Rule::in(['koni', 'cabor'])];
            $rules['cabang_olahraga_id'] = ['nullable', 'integer', 'exists:cabang_olahraga,id'];
        }

        return $rules;
    }

    private function resolveCaborId(Request $request, array $validated): ?int
    {
        if ($request->user()->hasRole('admin_cabor')) {
            abort_unless($request->user()->cabang_olahraga_id !== null, 403);
            return (int) $request->user()->cabang_olahraga_id;
        }

        if (($validated['scope'] ?? 'koni') === 'koni') {
            return null;
        }

        $caborId = (int) ($validated['cabang_olahraga_id'] ?? 0);
        abort_if($caborId < 1, 422, 'Pilih cabang olahraga untuk inventaris tersebut.');
        abort_unless(CabangOlahraga::query()->whereKey($caborId)->where('status', 'aktif')->exists(), 422, 'Cabang olahraga tidak aktif.');

        return $caborId;
    }
}
