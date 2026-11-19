<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\InventoryLoan;
use App\Support\Inventory\InventoryAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class InventoryLoanController extends Controller
{
    public function __construct(private readonly InventoryAccess $access)
    {
    }

    public function store(Request $request, InventoryItem $inventoryItem): RedirectResponse
    {
        Gate::authorize('createLoan', $inventoryItem);

        $validated = $request->validate([
            'peminjam' => ['required', 'string', 'max:150'],
            'kontak_peminjam' => ['nullable', 'string', 'max:60'],
            'unit_peminjam' => ['nullable', 'string', 'max:150'],
            'jumlah' => ['required', 'integer', 'min:1', 'max:1000000'],
            'tanggal_pinjam' => ['required', 'date'],
            'batas_pengembalian' => ['required', 'date', 'after_or_equal:tanggal_pinjam'],
            'catatan' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($request, $inventoryItem, $validated): void {
            $lockedItem = InventoryItem::query()->lockForUpdate()->findOrFail($inventoryItem->id);
            abort_unless($lockedItem->status === 'aktif', 422, 'Barang nonaktif tidak dapat dipinjam.');
            $loaned = $lockedItem->activeLoans()->sum('jumlah');
            $available = max(0, (int) $lockedItem->jumlah - (int) $loaned);

            abort_if((int) $validated['jumlah'] > $available, 422, 'Jumlah pinjaman melebihi stok yang tersedia. Stok tersedia: '.$available.'.');

            InventoryLoan::create([
                'inventory_item_id' => $lockedItem->id,
                'peminjam' => trim($validated['peminjam']),
                'kontak_peminjam' => $validated['kontak_peminjam'] ?? null,
                'unit_peminjam' => $validated['unit_peminjam'] ?? null,
                'jumlah' => $validated['jumlah'],
                'tanggal_pinjam' => $validated['tanggal_pinjam'],
                'batas_pengembalian' => $validated['batas_pengembalian'],
                'status' => 'dipinjam',
                'catatan' => $validated['catatan'] ?? null,
                'created_by' => $request->user()->id,
            ]);
        });

        return redirect()->route($this->access->routePrefix($request->user()).'.inventaris.show', $inventoryItem)
            ->with('success', 'Peminjaman barang berhasil dicatat.');
    }

    public function return(Request $request, InventoryLoan $inventoryLoan): RedirectResponse
    {
        $inventoryLoan->loadMissing('item');
        Gate::authorize('returnLoan', $inventoryLoan->item);

        abort_unless($inventoryLoan->status === 'dipinjam', 422, 'Peminjaman ini sudah ditutup.');

        $validated = $request->validate([
            'kondisi_pengembalian' => ['required', Rule::in(array_keys(InventoryItem::CONDITIONS))],
            'catatan_pengembalian' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($request, $inventoryLoan, $validated): void {
            $lockedItem = InventoryItem::withTrashed()->lockForUpdate()->findOrFail($inventoryLoan->inventory_item_id);
            $loan = InventoryLoan::query()->lockForUpdate()->findOrFail($inventoryLoan->id);
            abort_unless($loan->status === 'dipinjam', 422, 'Peminjaman ini sudah ditutup.');

            $loan->update([
                'status' => 'dikembalikan',
                'tanggal_dikembalikan' => now(),
                'kondisi_pengembalian' => $validated['kondisi_pengembalian'],
                'catatan_pengembalian' => $validated['catatan_pengembalian'] ?? null,
                'returned_by' => $request->user()->id,
            ]);

            $lockedItem->update([
                'kondisi' => $validated['kondisi_pengembalian'],
                'updated_by' => $request->user()->id,
            ]);
        });

        return redirect()->route($this->access->routePrefix($request->user()).'.inventaris.show', $inventoryLoan->item)
            ->with('success', 'Pengembalian barang berhasil dicatat.');
    }
}
