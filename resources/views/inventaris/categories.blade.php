@extends($routePrefix === 'admin' ? 'layouts.admin' : ($routePrefix === 'cabor' ? 'layouts.cabor' : ($routePrefix === 'staff' ? 'layouts.staff' : 'layouts.sekretaris')))

@section('title', 'Kategori Inventaris')
@section('heading', 'Kategori Inventaris')

@section('content')
<div class="space-y-5">
    <div class="flex flex-wrap items-start justify-between gap-3"><div><h2 class="text-xl font-black">Kategori inventaris</h2><p class="mt-1 text-sm text-gray-500">Kategori umum dapat dipakai semua cabang. Kategori khusus hanya berlaku untuk cabang pembuatnya.</p></div><a href="{{ route($routePrefix.'.inventaris.index') }}" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-xs font-bold">Kembali ke Inventaris</a></div>
    @if(session('success')) <div role="status" class="rounded-xl bg-green-50 p-3 text-sm text-green-800">{{ session('success') }}</div> @endif
    @if(session('error')) <div role="alert" class="rounded-xl bg-red-50 p-3 text-sm text-red-800">{{ session('error') }}</div> @endif
    @if($errors->any()) <div role="alert" class="rounded-xl bg-red-50 p-3 text-sm text-red-800">{{ $errors->first() }}</div> @endif

    @if($canCreate)
        <form method="POST" action="{{ route($routePrefix.'.inventaris.kategori.store') }}" class="grid gap-3 rounded-xl border border-gray-200 bg-white p-5 md:grid-cols-4">@csrf
            <label class="block text-sm font-semibold md:col-span-2">Nama kategori<input name="nama" required maxlength="100" class="mt-1 w-full rounded-lg border-gray-300 text-sm"></label>
            @if($routePrefix === 'admin')<label class="block text-sm font-semibold">Ruang lingkup<select name="cabang_olahraga_id" class="mt-1 w-full rounded-lg border-gray-300 text-sm"><option value="">Kategori umum KONI</option>@foreach($cabors as $cabor)<option value="{{ $cabor->id }}">{{ $cabor->nama }}</option>@endforeach</select></label>@endif
            <label class="block text-sm font-semibold">Deskripsi<input name="deskripsi" maxlength="2000" class="mt-1 w-full rounded-lg border-gray-300 text-sm"></label>
            <div class="md:col-span-4"><button class="rounded-lg bg-[#D71920] px-4 py-2 text-xs font-bold text-white">Tambah Kategori</button></div>
        </form>
    @endif

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white"><div class="overflow-x-auto"><table class="min-w-full text-sm"><thead class="bg-gray-50 text-left text-xs uppercase text-gray-500"><tr><th class="px-4 py-3">Kategori</th><th class="px-4 py-3">Ruang lingkup</th><th class="px-4 py-3">Jumlah barang</th><th class="px-4 py-3">Deskripsi</th><th class="px-4 py-3">Tindakan</th></tr></thead><tbody class="divide-y divide-gray-100">
        @forelse($categories as $category)
            <tr><td class="px-4 py-3 font-bold">{{ $category->nama }}</td><td class="px-4 py-3">{{ $category->cabangOlahraga?->nama ?? 'Umum KONI' }}</td><td class="px-4 py-3">{{ $category->items_count }}</td><td class="px-4 py-3">{{ $category->deskripsi ?? '-' }}</td><td class="px-4 py-3">@can('update', $category)<details class="text-xs"><summary class="cursor-pointer font-bold text-red-700">Kelola</summary><div class="mt-2 min-w-64 space-y-2"><form method="POST" action="{{ route($routePrefix.'.inventaris.kategori.update', $category) }}">@csrf @method('PUT')<input name="nama" value="{{ $category->nama }}" required class="mb-2 w-full rounded-lg border-gray-300 text-xs"><textarea name="deskripsi" rows="2" class="mb-2 w-full rounded-lg border-gray-300 text-xs">{{ $category->deskripsi }}</textarea><button class="rounded bg-gray-900 px-3 py-2 font-bold text-white">Simpan perubahan</button></form><form method="POST" action="{{ route($routePrefix.'.inventaris.kategori.destroy', $category) }}" onsubmit="return confirm('Hapus kategori ini?')">@csrf @method('DELETE')<button class="rounded border border-red-200 px-3 py-2 font-bold text-red-700">Hapus kategori</button></form></div></details>@else<span class="text-xs text-gray-400">Hanya lihat</span>@endcan</td></tr>
        @empty <tr><td colspan="5" class="px-4 py-10 text-center text-sm text-gray-500">Belum ada kategori inventaris.</td></tr> @endforelse
    </tbody></table></div><div class="border-t border-gray-100 p-4">{{ $categories->links() }}</div></div>
</div>
@endsection
