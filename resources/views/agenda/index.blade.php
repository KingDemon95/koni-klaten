@extends($layout)

@section('title', 'Agenda & Kejuaraan')
@section('heading', 'Agenda & Kejuaraan')

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800">{{ session('error') }}</div>
    @endif

    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h2 class="text-xl font-black text-slate-900">Pengelolaan agenda dan kejuaraan</h2>
            <p class="mt-1 text-sm text-slate-500">
                @if($isCaborUser)
                    Kelola agenda {{ $cabor?->nama }} dan ajukan ke Staff KONI untuk dipublikasikan.
                @else
                    Kelola agenda seluruh KONI Klaten. Staff KONI dan Admin KONI dapat langsung mempublikasikan agenda.
                @endif
            </p>
        </div>
        <a href="{{ route($routePrefix.'.agenda.create') }}" class="inline-flex items-center justify-center rounded-xl bg-[#D71920] px-5 py-3 text-sm font-bold text-white shadow-sm hover:bg-[#B9151B]">+ Tambah Agenda</a>
    </div>

    <div class="grid gap-3 sm:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-5"><p class="text-xs font-bold uppercase tracking-wide text-slate-500">Total Agenda</p><p class="mt-2 text-3xl font-black">{{ $stats['total'] }}</p></div>
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-5"><p class="text-xs font-bold uppercase tracking-wide text-amber-700">Menunggu Tinjauan</p><p class="mt-2 text-3xl font-black text-amber-900">{{ $stats['diajukan'] }}</p></div>
        <div class="rounded-xl border border-green-200 bg-green-50 p-5"><p class="text-xs font-bold uppercase tracking-wide text-green-700">Sudah Dipublikasikan</p><p class="mt-2 text-3xl font-black text-green-900">{{ $stats['published'] }}</p></div>
    </div>

    <form method="GET" action="{{ route($routePrefix.'.agenda.index') }}" class="grid gap-3 rounded-xl border border-slate-200 bg-white p-4 sm:grid-cols-2 lg:grid-cols-4">
        <input type="search" name="q" value="{{ $search }}" placeholder="Cari judul, lokasi, cabor..." class="rounded-lg border-slate-300 text-sm focus:border-red-500 focus:ring-red-500">
        <select name="jenis" class="rounded-lg border-slate-300 text-sm focus:border-red-500 focus:ring-red-500">
            <option value="">Semua jenis</option>
            <option value="agenda" @selected($jenis === 'agenda')>Agenda</option>
            <option value="kegiatan" @selected($jenis === 'kegiatan')>Kegiatan</option>
            <option value="kejuaraan" @selected($jenis === 'kejuaraan')>Kejuaraan</option>
        </select>
        <select name="status" class="rounded-lg border-slate-300 text-sm focus:border-red-500 focus:ring-red-500">
            <option value="">Semua status</option>
            <option value="draft" @selected($status === 'draft')>Draft</option>
            <option value="diajukan" @selected($status === 'diajukan')>Diajukan</option>
            <option value="revisi" @selected($status === 'revisi')>Perlu Revisi</option>
            <option value="published" @selected($status === 'published')>Published</option>
        </select>
        <div class="flex gap-2">
            <button class="flex-1 rounded-lg bg-slate-900 px-4 py-2 text-sm font-bold text-white">Filter</button>
            <a href="{{ route($routePrefix.'.agenda.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-bold text-slate-600">Reset</a>
        </div>
    </form>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr><th class="px-5 py-4">Agenda</th><th class="px-5 py-4">Cabang Olahraga</th><th class="px-5 py-4">Tanggal</th><th class="px-5 py-4">Status</th><th class="px-5 py-4 text-right">Tindakan</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($items as $item)
                        <tr class="align-top">
                            <td class="px-5 py-4">
                                <p class="font-bold text-slate-900">{{ $item->judul }}</p>
                                <p class="mt-1 text-xs capitalize text-slate-500">{{ $item->jenis }} @if($item->lokasi) · {{ $item->lokasi }} @endif</p>
                                @if($item->catatan_revisi && $item->status === 'revisi')
                                    <p class="mt-2 max-w-xs rounded-lg bg-amber-50 p-2 text-xs text-amber-800"><strong>Catatan revisi:</strong> {{ $item->catatan_revisi }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-slate-600">{{ $item->cabangOlahraga?->nama ?? 'Kegiatan KONI' }}</td>
                            <td class="whitespace-nowrap px-5 py-4 text-slate-600">
                                {{ $item->tanggal_mulai?->translatedFormat('d M Y') }}
                                @if($item->tanggal_selesai && !$item->tanggal_mulai->isSameDay($item->tanggal_selesai))
                                    <span class="block text-xs text-slate-400">s.d. {{ $item->tanggal_selesai->translatedFormat('d M Y') }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                @php($statusClass = ['draft' => 'bg-slate-100 text-slate-700', 'diajukan' => 'bg-amber-100 text-amber-800', 'revisi' => 'bg-orange-100 text-orange-800', 'published' => 'bg-green-100 text-green-800'][$item->status] ?? 'bg-slate-100 text-slate-700')
                                <span class="rounded-full px-3 py-1 text-xs font-bold {{ $statusClass }}">{{ ucfirst($item->status) }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex min-w-40 flex-col items-end gap-2">
                                    @if(!$isCaborUser || $item->status !== 'published')
                                        <a href="{{ route($routePrefix.'.agenda.edit', $item) }}" class="text-xs font-bold text-blue-700 hover:underline">Edit</a>
                                    @endif
                                    @if($isCaborUser && in_array($item->status, ['draft', 'revisi'], true))
                                        <form method="POST" action="{{ route('cabor.agenda.submit', $item) }}">@csrf<button class="text-xs font-bold text-amber-700 hover:underline">Ajukan Publikasi</button></form>
                                    @endif
                                    @if($canPublish && $item->status !== 'published')
                                        <form method="POST" action="{{ route($routePrefix.'.agenda.publish', $item) }}" onsubmit="return confirm('Publikasikan agenda ini ke website Public?')">@csrf<button class="text-xs font-bold text-green-700 hover:underline">Publikasikan</button></form>
                                        @if($item->status === 'diajukan')
                                        <details class="text-right">
                                            <summary class="cursor-pointer text-xs font-bold text-orange-700 hover:underline">Minta Revisi</summary>
                                            <form method="POST" action="{{ route($routePrefix.'.agenda.revisi', $item) }}" class="mt-2 w-64 rounded-lg border border-slate-200 bg-slate-50 p-3 text-left">@csrf<textarea name="catatan_revisi" required maxlength="2000" rows="3" placeholder="Tuliskan catatan revisi..." class="w-full rounded-lg border-slate-300 text-xs"></textarea><button class="mt-2 rounded-lg bg-orange-600 px-3 py-2 text-xs font-bold text-white">Kirim Revisi</button></form>
                                        </details>
                                        @endif
                                    @endif
                                    @if(!$isCaborUser || $item->status !== 'published')
                                        <form method="POST" action="{{ route($routePrefix.'.agenda.destroy', $item) }}" onsubmit="return confirm('Hapus agenda ini?')">@csrf @method('DELETE')<button class="text-xs font-bold text-red-700 hover:underline">Hapus</button></form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-12 text-center"><p class="font-bold text-slate-700">Belum ada agenda.</p><p class="mt-1 text-sm text-slate-500">Tambahkan agenda atau ubah filter pencarian.</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-5 py-4">{{ $items->links() }}</div>
    </div>
</div>
@endsection
