@extends($layout)

@section('title', 'Dokumentasi & Publikasi')
@section('heading', 'Dokumentasi & Publikasi')

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"><p class="font-bold">Periksa kembali data yang dimasukkan.</p><ul class="mt-2 list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h2 class="text-xl font-black text-slate-900">Kelola berita dan dokumentasi KONI</h2>
            <p class="mt-1 max-w-2xl text-sm text-slate-500">Staff KONI dan Admin KONI dapat membuat, mengedit, menghapus, serta langsung memublikasikan berita, foto, dan video.</p>
        </div>
        <a href="{{ route($routePrefix.'.publikasi.create') }}" class="inline-flex items-center justify-center rounded-xl bg-[#D71920] px-5 py-3 text-sm font-bold text-white hover:bg-[#B9151B]">+ Buat Konten</a>
    </div>

    <div class="grid gap-3 sm:grid-cols-3">
        @foreach([['Total Konten', $stats['total']], ['Draft', $stats['draft']], ['Dipublikasikan', $stats['published']]] as [$label, $value])
            <div class="rounded-xl border border-slate-200 bg-white p-5"><p class="text-xs font-bold uppercase tracking-wide text-slate-500">{{ $label }}</p><p class="mt-2 text-3xl font-black text-slate-900">{{ $value }}</p></div>
        @endforeach
    </div>

    <form method="GET" class="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 sm:grid-cols-[1fr_180px_180px_auto]">
        <input type="search" name="q" value="{{ $search }}" maxlength="100" placeholder="Cari judul atau kategori..." class="rounded-lg border-slate-300 text-sm focus:border-red-500 focus:ring-red-500">
        <select name="jenis" class="rounded-lg border-slate-300 text-sm focus:border-red-500 focus:ring-red-500"><option value="">Semua jenis</option>@foreach(\App\Models\Publikasi::jenisLabels() as $key => $label)<option value="{{ $key }}" @selected($jenis === $key)>{{ $label }}</option>@endforeach</select>
        <select name="status" class="rounded-lg border-slate-300 text-sm focus:border-red-500 focus:ring-red-500"><option value="">Semua status</option>@foreach(\App\Models\Publikasi::statusLabels() as $key => $label)<option value="{{ $key }}" @selected($status === $key)>{{ $label }}</option>@endforeach</select>
        <div class="flex gap-2"><button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-bold text-white">Filter</button><a href="{{ route($routePrefix.'.publikasi.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-bold text-slate-600">Reset</a></div>
    </form>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-3">Konten</th><th class="px-5 py-3">Jenis / Kategori</th><th class="px-5 py-3">Status</th><th class="px-5 py-3">Diperbarui</th><th class="px-5 py-3 text-right">Aksi</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($items as $item)
                        <tr class="align-top">
                            <td class="px-5 py-4"><p class="font-bold text-slate-900">{{ $item->judul }}</p><p class="mt-1 max-w-xl text-xs text-slate-500">{{ $item->ringkasan ?: \Illuminate\Support\Str::limit(strip_tags($item->konten ?? ''), 110) }}</p></td>
                            <td class="px-5 py-4"><span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">{{ \App\Models\Publikasi::jenisLabels()[$item->jenis] ?? $item->jenis }}</span><p class="mt-2 text-xs text-slate-500">{{ $item->kategori ?: 'Tanpa kategori' }}</p></td>
                            <td class="px-5 py-4"><span class="rounded-full px-3 py-1 text-xs font-bold {{ $item->status === 'published' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">{{ \App\Models\Publikasi::statusLabels()[$item->status] ?? $item->status }}</span>@if($item->published_at)<p class="mt-2 text-xs text-slate-500">{{ $item->published_at->format('d M Y H:i') }}</p>@endif</td>
                            <td class="whitespace-nowrap px-5 py-4 text-xs text-slate-500">{{ $item->updated_at?->format('d M Y H:i') }}</td>
                            <td class="px-5 py-4"><div class="flex min-w-32 flex-col items-end gap-2"><a href="{{ route($routePrefix.'.publikasi.show', $item) }}" class="text-xs font-bold text-slate-700 hover:underline">Detail</a><a href="{{ route($routePrefix.'.publikasi.edit', $item) }}" class="text-xs font-bold text-blue-700 hover:underline">Edit</a>@if($item->status !== 'published')<form method="POST" action="{{ route($routePrefix.'.publikasi.publish', $item) }}" onsubmit="return confirm('Publikasikan konten ini ke website publik?')">@csrf<button class="text-xs font-bold text-green-700 hover:underline">Publikasikan</button></form>@endif<form method="POST" action="{{ route($routePrefix.'.publikasi.destroy', $item) }}" onsubmit="return confirm('Hapus konten dan file terkait?')">@csrf @method('DELETE')<button class="text-xs font-bold text-red-700 hover:underline">Hapus</button></form></div></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-12 text-center"><p class="font-bold text-slate-700">Belum ada konten.</p><p class="mt-1 text-sm text-slate-500">Buat berita atau dokumentasi pertama untuk website KONI Klaten.</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-5 py-4">{{ $items->links() }}</div>
    </div>
</div>
@endsection
