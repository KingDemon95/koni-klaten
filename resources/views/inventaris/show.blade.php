@extends($routePrefix === 'admin' ? 'layouts.admin' : ($routePrefix === 'cabor' ? 'layouts.cabor' : ($routePrefix === 'staff' ? 'layouts.staff' : 'layouts.sekretaris')))

@section('title', $item->nama_barang)
@section('heading', 'Detail Inventaris')

@section('content')
<div class="space-y-6">
    @if(session('success')) <div role="status" class="rounded-xl border border-green-200 bg-green-50 p-3 text-sm text-green-800">{{ session('success') }}</div> @endif
    @if(session('error')) <div role="alert" class="rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-800">{{ session('error') }}</div> @endif

    <div class="flex flex-wrap items-start justify-between gap-3">
        <div><p class="text-xs font-bold uppercase tracking-wide text-red-600">{{ $item->kode_barang }}</p><h2 class="mt-1 text-2xl font-black">{{ $item->nama_barang }}</h2><p class="mt-1 text-sm text-gray-500">{{ $item->cabangOlahraga?->nama ?? 'KONI Kabupaten' }} · {{ $item->category?->nama }}</p></div>
        <div class="flex flex-wrap gap-2"><a href="{{ route($routePrefix.'.inventaris.index') }}" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-xs font-bold">Kembali</a>@if($canManage)<a href="{{ route($routePrefix.'.inventaris.edit', $item) }}" class="rounded-lg bg-gray-900 px-4 py-2 text-xs font-bold text-white">Edit</a><form method="POST" action="{{ route($routePrefix.'.inventaris.destroy', $item) }}" onsubmit="return confirm('Arsipkan barang ini?')">@csrf @method('DELETE')<button class="rounded-lg border border-red-200 px-4 py-2 text-xs font-bold text-red-700">Arsipkan</button></form>@endif</div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach([['Jumlah tercatat', number_format($item->jumlah,0,',','.').' '.$item->satuan],['Tersedia', number_format($availableQuantity,0,',','.')],['Kondisi', \App\Models\InventoryItem::CONDITIONS[$item->kondisi] ?? $item->kondisi],['Lokasi', $item->lokasi]] as [$label,$value])
            <div class="rounded-xl border border-gray-200 bg-white p-4"><p class="text-xs text-gray-500">{{ $label }}</p><p class="mt-2 text-lg font-black">{{ $value }}</p></div>
        @endforeach
    </div>

    <div class="grid gap-5 xl:grid-cols-2">
        @if($canMove)
            <section class="rounded-xl border border-gray-200 bg-white p-5"><h3 class="font-black">Catat mutasi lokasi</h3><p class="mt-1 text-xs text-gray-500">Lokasi saat ini: {{ $item->lokasi }}. Sistem menyimpan lokasi asal dan tujuan pada riwayat.</p>
                <form method="POST" action="{{ route($routePrefix.'.inventaris.mutasi', $item) }}" class="mt-4 space-y-3">@csrf
                    <label class="block text-sm font-semibold">Lokasi tujuan<input name="lokasi_tujuan" required maxlength="150" class="mt-1 w-full rounded-lg border-gray-300 text-sm"></label>
                    <label class="block text-sm font-semibold">Tanggal mutasi<input type="datetime-local" name="tanggal_mutasi" value="{{ now()->format('Y-m-d\TH:i') }}" required class="mt-1 w-full rounded-lg border-gray-300 text-sm"></label>
                    <label class="block text-sm font-semibold">Catatan<textarea name="catatan" rows="2" class="mt-1 w-full rounded-lg border-gray-300 text-sm"></textarea></label>
                    <button class="rounded-lg bg-gray-900 px-4 py-2 text-xs font-bold text-white">Simpan Mutasi</button>
                </form>
            </section>
        @endif
        @if($canLoan)
            <section class="rounded-xl border border-gray-200 bg-white p-5"><h3 class="font-black">Catat peminjaman</h3><p class="mt-1 text-xs text-gray-500">Stok tersedia: {{ $availableQuantity }} {{ $item->satuan }}.</p>
                <form method="POST" action="{{ route($routePrefix.'.inventaris.peminjaman.store', $item) }}" class="mt-4 grid gap-3 sm:grid-cols-2">@csrf
                    <label class="block text-sm font-semibold sm:col-span-2">Nama peminjam<input name="peminjam" required maxlength="150" class="mt-1 w-full rounded-lg border-gray-300 text-sm"></label>
                    <label class="block text-sm font-semibold">Kontak<input name="kontak_peminjam" maxlength="60" class="mt-1 w-full rounded-lg border-gray-300 text-sm"></label>
                    <label class="block text-sm font-semibold">Unit / Cabor<input name="unit_peminjam" maxlength="150" class="mt-1 w-full rounded-lg border-gray-300 text-sm"></label>
                    <label class="block text-sm font-semibold">Jumlah<input type="number" name="jumlah" min="1" max="{{ $availableQuantity }}" value="1" required class="mt-1 w-full rounded-lg border-gray-300 text-sm"></label>
                    <label class="block text-sm font-semibold">Tanggal pinjam<input type="date" name="tanggal_pinjam" value="{{ now()->toDateString() }}" required class="mt-1 w-full rounded-lg border-gray-300 text-sm"></label>
                    <label class="block text-sm font-semibold">Batas pengembalian<input type="date" name="batas_pengembalian" min="{{ now()->toDateString() }}" required class="mt-1 w-full rounded-lg border-gray-300 text-sm"></label>
                    <label class="block text-sm font-semibold sm:col-span-2">Catatan<textarea name="catatan" rows="2" class="mt-1 w-full rounded-lg border-gray-300 text-sm"></textarea></label>
                    <div class="sm:col-span-2"><button class="rounded-lg bg-[#D71920] px-4 py-2 text-xs font-bold text-white" @disabled($availableQuantity < 1)>Simpan Peminjaman</button></div>
                </form>
            </section>
        @endif
    </div>

    <section class="overflow-hidden rounded-xl border border-gray-200 bg-white"><div class="border-b border-gray-100 p-4"><h3 class="font-black">Riwayat peminjaman</h3></div><div class="overflow-x-auto"><table class="min-w-full text-sm"><thead class="bg-gray-50 text-left text-xs text-gray-500"><tr><th class="px-4 py-3">Peminjam</th><th class="px-4 py-3">Jumlah</th><th class="px-4 py-3">Tanggal pinjam</th><th class="px-4 py-3">Batas kembali</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Tindakan</th></tr></thead><tbody class="divide-y divide-gray-100">
        @forelse($item->loans as $loan)
            <tr><td class="px-4 py-3"><strong>{{ $loan->peminjam }}</strong><p class="text-xs text-gray-500">{{ $loan->unit_peminjam }}</p></td><td class="px-4 py-3">{{ $loan->jumlah }} {{ $item->satuan }}</td><td class="px-4 py-3">{{ $loan->tanggal_pinjam?->format('d/m/Y') }}</td><td class="px-4 py-3">{{ $loan->batas_pengembalian?->format('d/m/Y') }}</td><td class="px-4 py-3"><span class="rounded-full px-2 py-1 text-xs font-bold {{ $loan->status === 'dipinjam' ? 'bg-amber-50 text-amber-700' : 'bg-green-50 text-green-700' }}">{{ ucfirst($loan->status) }}</span></td><td class="px-4 py-3">@if($canReturnLoan && $loan->status === 'dipinjam')<form method="POST" action="{{ route($routePrefix.'.inventaris.peminjaman.return', $loan) }}" class="flex flex-wrap items-center gap-2">@csrf<select name="kondisi_pengembalian" required class="rounded-lg border-gray-300 text-xs">@foreach(\App\Models\InventoryItem::CONDITIONS as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select><button class="rounded-lg bg-green-700 px-3 py-2 text-xs font-bold text-white">Tandai kembali</button></form>@elseif($loan->tanggal_dikembalikan)<span class="text-xs text-gray-500">{{ $loan->tanggal_dikembalikan->format('d/m/Y H:i') }}</span>@else<span class="text-xs text-gray-400">-</span>@endif</td></tr>
        @empty <tr><td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada riwayat peminjaman.</td></tr> @endforelse
    </tbody></table></div></section>

    <section class="overflow-hidden rounded-xl border border-gray-200 bg-white"><div class="border-b border-gray-100 p-4"><h3 class="font-black">Riwayat mutasi lokasi</h3></div><div class="overflow-x-auto"><table class="min-w-full text-sm"><thead class="bg-gray-50 text-left text-xs text-gray-500"><tr><th class="px-4 py-3">Tanggal</th><th class="px-4 py-3">Dari</th><th class="px-4 py-3">Ke</th><th class="px-4 py-3">Jumlah</th><th class="px-4 py-3">Petugas</th><th class="px-4 py-3">Catatan</th></tr></thead><tbody class="divide-y divide-gray-100">
        @forelse($item->movements as $movement)<tr><td class="px-4 py-3">{{ $movement->tanggal_mutasi?->format('d/m/Y H:i') }}</td><td class="px-4 py-3">{{ $movement->lokasi_asal }}</td><td class="px-4 py-3 font-semibold">{{ $movement->lokasi_tujuan }}</td><td class="px-4 py-3">{{ $movement->jumlah }}</td><td class="px-4 py-3">{{ $movement->actor?->name ?? '-' }}</td><td class="px-4 py-3">{{ $movement->catatan ?? '-' }}</td></tr>
        @empty <tr><td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada riwayat mutasi.</td></tr> @endforelse
    </tbody></table></div></section>
</div>
@endsection
