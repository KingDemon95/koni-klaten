<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Support\Inventory\InventoryAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class InventoryMovementController extends Controller
{
    public function __construct(private readonly InventoryAccess $access)
    {
    }

    public function store(Request $request, InventoryItem $inventoryItem): RedirectResponse
    {
        Gate::authorize('move', $inventoryItem);

        $validated = $request->validate([
            'lokasi_tujuan' => ['required', 'string', 'max:150'],
            'tanggal_mutasi' => ['required', 'date'],
            'catatan' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($request, $inventoryItem, $validated): void {
            $lockedItem = InventoryItem::query()->lockForUpdate()->findOrFail($inventoryItem->id);
            abort_if(mb_strtolower(trim($validated['lokasi_tujuan'])) === mb_strtolower(trim($lockedItem->lokasi)), 422, 'Lokasi tujuan harus berbeda dari lokasi saat ini.');
            abort_if($lockedItem->activeLoans()->exists(), 422, 'Lokasi barang tidak dapat dimutasi selama masih ada unit yang dipinjam.');

            InventoryMovement::create([
                'inventory_item_id' => $lockedItem->id,
                'lokasi_asal' => $lockedItem->lokasi,
                'lokasi_tujuan' => trim($validated['lokasi_tujuan']),
                'jumlah' => $lockedItem->jumlah,
                'tanggal_mutasi' => $validated['tanggal_mutasi'],
                'catatan' => $validated['catatan'] ?? null,
                'moved_by' => $request->user()->id,
            ]);

            $lockedItem->update([
                'lokasi' => trim($validated['lokasi_tujuan']),
                'updated_by' => $request->user()->id,
            ]);
        });

        return redirect()->route($this->access->routePrefix($request->user()).'.inventaris.show', $inventoryItem)
            ->with('success', 'Mutasi barang berhasil dicatat dan lokasi inventaris diperbarui.');
    }
}
