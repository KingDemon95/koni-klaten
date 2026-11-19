@extends($layout)

@section('title', 'Riwayat Aktivitas')
@section('heading', 'Riwayat Aktivitas')

@section('content')
<div class="space-y-6">
    <section class="rounded-2xl bg-gradient-to-r from-[#171717] to-[#3A3A3A] p-6 text-white shadow-sm sm:p-8">
        <p class="text-xs font-bold tracking-[0.22em] text-yellow-300">AKUNTABILITAS SISTEM</p>
        <h2 class="mt-3 text-2xl font-black sm:text-3xl">Riwayat Aktivitas</h2>
        <p class="mt-2 max-w-3xl text-sm leading-6 text-gray-200">Catatan perubahan data dan keputusan yang tercatat di Portal KONI Kabupaten Klaten. Riwayat ini bersifat baca saja dan tidak menyediakan tindakan untuk mengubah atau menghapus catatan.</p>
    </section>

    <section class="grid gap-4 sm:grid-cols-2">
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold text-gray-500">Total aktivitas dalam cakupan akses</p>
            <p class="mt-2 text-3xl font-black tabular-nums text-gray-900">{{ number_format($total) }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold text-gray-500">Aktivitas hari ini</p>
            <p class="mt-2 text-3xl font-black tabular-nums text-gray-900">{{ number_format($today) }}</p>
        </div>
    </section>

    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="mb-4">
            <h3 class="font-black text-gray-900">Filter riwayat</h3>
            <p class="mt-1 text-xs text-gray-500">
                @if(auth()->user()->hasRole('admin_cabor'))
                    Data hanya mencakup aktivitas yang terkait dengan cabang olahraga {{ $cabor->nama }}.
                @elseif(auth()->user()->hasRole('staff_koni'))
                    Data hanya menampilkan aktivitas yang dilakukan oleh akun Staff yang sedang digunakan.
                @else
                    Tampilkan aktivitas berdasarkan pengguna, modul, jenis tindakan, atau tanggal.
                @endif
            </p>
        </div>

        <form method="GET" action="{{ route($routeName) }}" class="grid gap-3 md:grid-cols-2 xl:grid-cols-5">
            <label class="xl:col-span-2">
                <span class="mb-1 block text-xs font-semibold text-gray-600">Kata kunci</span>
                <input type="search" name="q" value="{{ $search }}" maxlength="100" placeholder="Nama pengguna atau data" class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
            </label>
            <label>
                <span class="mb-1 block text-xs font-semibold text-gray-600">Jenis aktivitas</span>
                <select name="event" class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                    <option value="">Semua aktivitas</option>
                    @foreach($eventLabels as $key => $label)
                        <option value="{{ $key }}" @selected($event === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                <span class="mb-1 block text-xs font-semibold text-gray-600">Dari tanggal</span>
                <input type="date" name="from" value="{{ $from }}" class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
            </label>
            <label>
                <span class="mb-1 block text-xs font-semibold text-gray-600">Sampai tanggal</span>
                <input type="date" name="to" value="{{ $to }}" min="{{ $from }}" class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
            </label>
            <div class="flex flex-wrap gap-2 md:col-span-2 xl:col-span-5">
                <button type="submit" class="rounded-lg bg-[#D71920] px-4 py-2.5 text-xs font-bold text-white transition hover:bg-red-800">Terapkan filter</button>
                <a href="{{ route($routeName) }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-xs font-bold text-gray-700 transition hover:bg-gray-50">Reset</a>
            </div>
        </form>
    </section>

    <section class="space-y-3">
        <div class="flex flex-wrap items-end justify-between gap-2">
            <div>
                <h3 class="text-lg font-black text-gray-900">Catatan aktivitas</h3>
                <p class="mt-1 text-xs text-gray-500">Menampilkan {{ $items->firstItem() ?? 0 }}–{{ $items->lastItem() ?? 0 }} dari {{ $items->total() }} catatan.</p>
            </div>
            <span class="rounded-full bg-blue-50 px-3 py-1 text-[10px] font-bold uppercase tracking-wide text-blue-700">Baca saja</span>
        </div>

        @forelse($items as $item)
            <article class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-gray-100 px-3 py-1 text-[10px] font-bold text-gray-700">{{ $item->event_label }}</span>
                            <span class="text-xs font-semibold text-[#B71920]">{{ $item->module_label }}</span>
                        </div>
                        <h4 class="mt-2 break-words text-sm font-black text-gray-900">{{ $item->subject_label ?? 'Aktivitas sistem' }}</h4>
                        <p class="mt-1 text-xs text-gray-600">
                            Oleh <span class="font-semibold">{{ $item->actor_name ?? 'Pengguna tidak tersedia' }}</span>
                            @if($item->actor_role)
                                <span class="text-gray-400">· {{ str_replace('_', ' ', $item->actor_role) }}</span>
                            @endif
                        </p>
                    </div>
                    <div class="shrink-0 text-left sm:text-right">
                        <time class="text-xs font-semibold tabular-nums text-gray-700">{{ $item->created_at?->format('d M Y, H:i') }}</time>
                        <p class="mt-1 text-[10px] text-gray-400">{{ $item->method ?: 'SYSTEM' }} {{ $item->route_name ? '· '.$item->route_name : '' }}</p>
                    </div>
                </div>

                @php
                    $oldValues = $item->old_values ?? [];
                    $newValues = $item->new_values ?? [];
                    $changedFields = array_values(array_unique(array_merge(array_keys($oldValues), array_keys($newValues))));
                @endphp

                @if(count($changedFields))
                    <details class="mt-4 border-t border-gray-100 pt-3">
                        <summary class="cursor-pointer text-xs font-bold text-gray-700 hover:text-[#D71920]">Lihat detail perubahan ({{ count($changedFields) }} field)</summary>
                        <div class="mt-3 overflow-x-auto">
                            <table class="w-full min-w-[420px] text-left text-xs">
                                <thead class="bg-gray-50 text-gray-500">
                                    <tr>
                                        <th class="px-3 py-2 font-bold">Field</th>
                                        <th class="px-3 py-2 font-bold">Sebelum</th>
                                        <th class="px-3 py-2 font-bold">Sesudah</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($changedFields as $field)
                                        <tr>
                                            <td class="px-3 py-2 font-semibold text-gray-700">{{ str_replace('_', ' ', $field) }}</td>
                                            <td class="max-w-xs break-words px-3 py-2 text-gray-500">{{ array_key_exists($field, $oldValues) ? (is_scalar($oldValues[$field]) || $oldValues[$field] === null ? (string) $oldValues[$field] : json_encode($oldValues[$field])) : '—' }}</td>
                                            <td class="max-w-xs break-words px-3 py-2 text-gray-800">{{ array_key_exists($field, $newValues) ? (is_scalar($newValues[$field]) || $newValues[$field] === null ? (string) $newValues[$field] : json_encode($newValues[$field])) : '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </details>
                @endif
            </article>
        @empty
            <div class="rounded-xl border border-dashed border-gray-300 bg-white p-10 text-center">
                <p class="font-bold text-gray-700">Belum ada aktivitas yang tercatat.</p>
                <p class="mt-1 text-xs text-gray-500">Aktivitas akan muncul setelah pengguna membuat, memperbarui, menyetujui, atau menghapus data.</p>
            </div>
        @endforelse

        @if($items->hasPages())
            <div class="pt-2">{{ $items->links() }}</div>
        @endif
    </section>
</div>
@endsection
