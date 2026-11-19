@extends($routePrefix === 'admin' ? 'layouts.admin' : ($routePrefix === 'cabor' ? 'layouts.cabor' : ($routePrefix === 'staff' ? 'layouts.staff' : 'layouts.sekretaris')))

@section('title', 'Inventaris')
@section('heading', 'Inventaris')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h2 class="text-xl font-black text-gray-900">Inventaris KONI dan Cabang Olahraga</h2>
            <p class="mt-1 text-sm text-gray-500">Data aset, kondisi barang, lokasi penyimpanan, peminjaman, dan mutasi.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route($routePrefix.'.inventaris.kategori.index') }}" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-xs font-bold text-gray-700 hover:bg-gray-50">Kategori</a>
            <a href="{{ route($routePrefix.'.inventaris.laporan') }}" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-xs font-bold text-gray-700 hover:bg-gray-50">Laporan</a>
            @can('create', \App\Models\InventoryItem::class)
                <a href="{{ route($routePrefix.'.inventaris.create') }}" class="rounded-lg bg-[#D71920] px-4 py-2 text-xs font-black text-white hover:bg-red-800">+ Tambah Barang</a>
            @endcan
        </div>
    </div>

    @if(session('success')) <div role="status" class="rounded-xl border border-green-200 bg-green-50 p-3 text-sm text-green-800">{{ session('success') }}</div> @endif
    @if(session('error')) <div role="alert" class="rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-800">{{ session('error') }}</div> @endif

    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach([
            ['label' => 'Jenis barang', 'value' => $totalItems],
            ['label' => 'Total unit', 'value' => number_format($totalUnits, 0, ',', '.')],
            ['label' => 'Barang perlu perhatian', 'value' => $damagedItems],
            ['label' => 'Jenis barang sedang dipinjam', 'value' => $activeLoans],
        ] as $stat)
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm"><p class="text-xs font-semibold text-gray-500">{{ $stat['label'] }}</p><p class="mt-2 text-2xl font-black tabular-nums">{{ $stat['value'] }}</p></div>
        @endforeach
    </div>

    <form method="GET" action="{{ route($routePrefix.'.inventaris.index') }}" class="grid gap-3 rounded-xl border border-gray-200 bg-white p-4 md:grid-cols-5">
        <input type="search" name="q" value="{{ $validated['q'] ?? '' }}" placeholder="Cari kode, nama, lokasi" class="rounded-lg border-gray-300 text-sm md:col-span-2">
        <select name="category_id" class="rounded-lg border-gray-300 text-sm"><option value="">Semua kategori</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(($validated['category_id'] ?? '') == $category->id)>{{ $category->nama }}</option>@endforeach</select>
        <select name="kondisi" class="rounded-lg border-gray-300 text-sm"><option value="">Semua kondisi</option>@foreach(\App\Models\InventoryItem::CONDITIONS as $key => $label)<option value="{{ $key }}" @selected(($validated['kondisi'] ?? '') === $key)>{{ $label }}</option>@endforeach</select>
        @if($cabors->isNotEmpty())
            <select name="cabor_id" class="rounded-lg border-gray-300 text-sm"><option value="">Semua pemilik</option>@foreach($cabors as $cabor)<option value="{{ $cabor->id }}" @selected(($validated['cabor_id'] ?? '') == $cabor->id)>{{ $cabor->nama }}</option>@endforeach</select>
        @endif
        <div class="flex gap-2 md:col-span-5"><button class="rounded-lg bg-gray-900 px-4 py-2 text-xs font-bold text-white">Filter</button><a href="{{ route($routePrefix.'.inventaris.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-xs font-bold text-gray-600">Reset</a></div>
    </form>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500"><tr><th class="px-4 py-3">Barang</th><th class="px-4 py-3">Pemilik</th><th class="px-4 py-3">Kategori</th><th class="px-4 py-3">Jumlah</th><th class="px-4 py-3">Tersedia</th><th class="px-4 py-3">Kondisi</th><th class="px-4 py-3">Lokasi</th><th class="px-4 py-3">Status</th></tr></thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($items as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3"><a class="font-bold text-gray-900 hover:text-red-700" href="{{ route($routePrefix.'.inventaris.show', $item) }}">{{ $item->nama_barang }}</a><p class="mt-1 text-xs text-gray-500">{{ $item->kode_barang }}</p></td>
                            <td class="px-4 py-3 text-gray-600">{{ $item->cabangOlahraga?->nama ?? 'KONI Kabupaten' }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $item->category?->nama ?? '-' }}</td>
                            <td class="px-4 py-3 tabular-nums">{{ number_format($item->jumlah, 0, ',', '.') }} {{ $item->satuan }}</td>
                            <td class="px-4 py-3 font-bold tabular-nums">{{ max(0, $item->jumlah - (int) ($item->borrowed_quantity ?? 0)) }}</td>
                            <td class="px-4 py-3"><span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold">{{ \App\Models\InventoryItem::CONDITIONS[$item->kondisi] ?? $item->kondisi }}</span></td>
                            <td class="px-4 py-3 text-gray-600">{{ $item->lokasi }}</td>
                            <td class="px-4 py-3"><span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $item->status === 'aktif' ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-600' }}">{{ ucfirst($item->status) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-12 text-center text-sm text-gray-500">Belum ada data inventaris yang sesuai dengan filter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-gray-100 p-4">{{ $items->links() }}</div>
    </div>
</div>
@endsection
