<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Support\Inventory\InventoryAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InventoryReportController extends Controller
{
    public function __construct(private readonly InventoryAccess $access)
    {
    }

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', InventoryItem::class);
        [$query, $filters] = $this->filteredQuery($request);
        $routePrefix = $this->access->routePrefix($request->user());
        $cabor = $request->user()->cabangOlahraga;

        $conditionSummary = (clone $query)
            ->selectRaw('kondisi, COUNT(*) as jumlah_barang, SUM(jumlah) as total_unit')
            ->groupBy('kondisi')
            ->orderBy('kondisi')
            ->get();

        $categorySummary = (clone $query)
            ->join('inventory_categories', 'inventory_items.inventory_category_id', '=', 'inventory_categories.id')
            ->selectRaw('inventory_categories.nama as kategori, COUNT(inventory_items.id) as jumlah_barang, SUM(inventory_items.jumlah) as total_unit')
            ->groupBy('inventory_categories.id', 'inventory_categories.nama')
            ->orderBy('inventory_categories.nama')
            ->get();

        $ownerSummary = (clone $query)
            ->leftJoin('cabang_olahraga', 'inventory_items.cabang_olahraga_id', '=', 'cabang_olahraga.id')
            ->selectRaw("COALESCE(cabang_olahraga.nama, 'KONI Kabupaten') as pemilik, COUNT(inventory_items.id) as jumlah_barang, SUM(inventory_items.jumlah) as total_unit")
            ->groupBy('cabang_olahraga.id', 'cabang_olahraga.nama')
            ->orderBy('pemilik')
            ->get();

        $items = (clone $query)
            ->with(['category', 'cabangOlahraga'])
            ->orderBy('nama_barang')
            ->paginate(20)
            ->withQueryString();

        $totalItems = (clone $query)->count();
        $totalUnits = (clone $query)->sum('jumlah');
        $totalValue = (clone $query)->sum('harga_perolehan');

        return view('inventaris.report', compact(
            'routePrefix', 'filters', 'conditionSummary', 'categorySummary', 'ownerSummary',
            'items', 'totalItems', 'totalUnits', 'totalValue', 'cabor'
        ));
    }

    public function export(Request $request): StreamedResponse
    {
        Gate::authorize('viewAny', InventoryItem::class);
        [$query] = $this->filteredQuery($request);
        $items = $query->with(['category', 'cabangOlahraga'])->orderBy('nama_barang')->get();

        return response()->streamDownload(function () use ($items): void {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, [
                'Kode Barang', 'Nama Barang', 'Kategori', 'Pemilik', 'Jumlah', 'Satuan',
                'Kondisi', 'Lokasi', 'Tanggal Perolehan', 'Harga Perolehan', 'Status',
            ]);

            foreach ($items as $item) {
                fputcsv($output, [
                    $this->safeCsv($item->kode_barang),
                    $this->safeCsv($item->nama_barang),
                    $this->safeCsv($item->category?->nama),
                    $this->safeCsv($item->cabangOlahraga?->nama ?? 'KONI Kabupaten'),
                    $item->jumlah,
                    $this->safeCsv($item->satuan),
                    $this->safeCsv(InventoryItem::CONDITIONS[$item->kondisi] ?? $item->kondisi),
                    $this->safeCsv($item->lokasi),
                    $item->tanggal_perolehan?->format('Y-m-d'),
                    $item->harga_perolehan,
                    $this->safeCsv($item->status),
                ]);
            }

            fclose($output);
        }, 'laporan-inventaris-'.now()->format('Ymd-His').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function filteredQuery(Request $request): array
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'kondisi' => ['nullable', 'string', 'in:baik,rusak_ringan,rusak_berat,tidak_layak'],
            'cabor_id' => ['nullable', 'integer', 'exists:cabang_olahraga,id'],
            'category_id' => ['nullable', 'integer', 'exists:inventory_categories,id'],
        ]);

        $query = $this->access->visibleItemsQuery($request->user());

        if (!empty($filters['q'])) {
            $search = trim($filters['q']);

            $query->where(function ($builder) use ($search): void {
                $builder->where('inventory_items.nama_barang', 'like', "%{$search}%")
                    ->orWhere('inventory_items.kode_barang', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['kondisi'])) {
            $query->where('inventory_items.kondisi', $filters['kondisi']);
        }

        if (!empty($filters['cabor_id']) && !$request->user()->hasRole('admin_cabor')) {
            $query->where('inventory_items.cabang_olahraga_id', $filters['cabor_id']);
        }

        if (!empty($filters['category_id'])) {
            $query->where('inventory_items.inventory_category_id', $filters['category_id']);
        }

        return [$query, $filters];
    }

    private function safeCsv(?string $value): string
    {
        $value = trim((string) $value);
        return preg_match('/^[=+@\-\t\r]/', $value) ? "'".$value : $value;
    }
}
