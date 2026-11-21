@extends($layout)

@section('title', 'Absensi')
@section('heading', 'Absensi QR dan Selfie')

@section('content')
@php
    $roleLabels = [
        'admin_koni' => 'Admin KONI',
        'staff_koni' => 'Staff KONI',
        'ketua_koni' => 'Ketua KONI',
        'sekretaris_koni' => 'Sekretaris KONI',
        'admin_cabor' => 'Admin Cabor',
        'atlet' => 'Atlet',
        'pelatih' => 'Pelatih',
        'atlet_pelatih' => 'Atlet dan Pelatih',
    ];
@endphp

<div class="mx-auto max-w-7xl space-y-6">
    @if(session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">{{ session('success') }}</div>
    @endif

    @if($canManage)
        <section class="flex flex-col justify-between gap-4 rounded-2xl bg-gradient-to-r from-[#8F0010] to-[#E60012] p-6 text-white shadow-lg sm:flex-row sm:items-center sm:p-8">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-yellow-200">Kehadiran kegiatan</p>
                <h2 class="mt-2 text-2xl font-black">Kelola Sesi Absensi</h2>
                <p class="mt-2 max-w-2xl text-sm text-red-100">Buat kegiatan, pilih role peserta, bagikan QR harian, lalu catat check-in dan check-out dengan selfie.</p>
            </div>
            <a href="{{ route($routePrefix.'.absensi.create') }}" class="shrink-0 rounded-lg bg-white px-5 py-3 text-sm font-black text-red-700 hover:bg-red-50">+ Buat Sesi Absensi</a>
        </section>
    @else
        <section class="rounded-2xl bg-gradient-to-r from-[#8F0010] to-[#E60012] p-6 text-white shadow-lg">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-yellow-200">Kehadiran pribadi</p>
            <h2 class="mt-2 text-2xl font-black">Absensi QR dan Selfie</h2>
            <p class="mt-2 max-w-2xl text-sm text-red-100">Untuk melakukan absensi, scan QR kegiatan dari pengelola. Sistem mewajibkan selfie saat check-in dan check-out.</p>
        </section>
    @endif

    @if($canViewRecords)
        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="mb-4">
                <h3 class="font-black">Filter rekap kehadiran</h3>
                <p class="mt-1 text-xs text-gray-500">Laporan dapat disaring berdasarkan role, cabang olahraga, dan tanggal check-in.</p>
            </div>
            <form method="GET" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                <div>
                    <label for="filter-role" class="mb-1 block text-xs font-bold text-gray-600">Role</label>
                    <select id="filter-role" name="role" class="w-full rounded-lg border-gray-300 text-sm">
                        <option value="">Semua role</option>
                        @foreach($roleLabels as $key => $label)
                            <option value="{{ $key }}" @selected(($filters['role'] ?? '') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                @if(!auth()->user()->hasRole('admin_cabor'))
                    <div>
                        <label for="filter-cabor" class="mb-1 block text-xs font-bold text-gray-600">Cabang olahraga</label>
                        <select id="filter-cabor" name="cabor_id" class="w-full rounded-lg border-gray-300 text-sm">
                            <option value="">Semua cabor</option>
                            @foreach($cabors as $cabor)
                                <option value="{{ $cabor->id }}" @selected((string)($filters['cabor_id'] ?? '') === (string)$cabor->id)>{{ $cabor->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div>
                    <label for="date-from" class="mb-1 block text-xs font-bold text-gray-600">Dari tanggal</label>
                    <input id="date-from" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="w-full rounded-lg border-gray-300 text-sm">
                </div>
                <div>
                    <label for="date-to" class="mb-1 block text-xs font-bold text-gray-600">Sampai tanggal</label>
                    <input id="date-to" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="w-full rounded-lg border-gray-300 text-sm">
                </div>
                <div class="flex items-end gap-2">
                    <button class="flex-1 rounded-lg bg-[#E60012] px-4 py-2.5 text-sm font-bold text-white">Terapkan</button>
                    <a href="{{ route($routePrefix.'.absensi.index') }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-bold text-gray-600">Reset</a>
                </div>
            </form>
        </section>

        <div class="grid gap-5 lg:grid-cols-2">
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <h3 class="font-black">Rekap berdasarkan role</h3>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-xs text-gray-500"><tr><th class="py-2">Role</th><th class="py-2 text-right">Check-in</th><th class="py-2 text-right">Check-out</th></tr></thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($summaryByRole as $summary)
                                <tr><td class="py-3 font-semibold">{{ $roleLabels[$summary->role_snapshot] ?? $summary->role_snapshot }}</td><td class="py-3 text-right tabular-nums">{{ number_format($summary->total, 0, ',', '.') }}</td><td class="py-3 text-right tabular-nums">{{ number_format($summary->completed, 0, ',', '.') }}</td></tr>
                            @empty
                                <tr><td colspan="3" class="py-6 text-center text-gray-500">Belum ada data absensi.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <h3 class="font-black">Rekap berdasarkan cabang olahraga</h3>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-xs text-gray-500"><tr><th class="py-2">Cabang olahraga</th><th class="py-2 text-right">Jumlah hadir</th></tr></thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($summaryByCabor as $summary)
                                <tr><td class="py-3 font-semibold">{{ $summary->cabor }}</td><td class="py-3 text-right tabular-nums">{{ number_format($summary->total, 0, ',', '.') }}</td></tr>
                            @empty
                                <tr><td colspan="2" class="py-6 text-center text-gray-500">Belum ada data absensi.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    @endif

    <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4 sm:px-6">
            <h3 class="font-black">{{ $canManage || $canViewRecords ? 'Daftar Sesi Absensi' : 'Kegiatan Absensi yang Tersedia' }}</h3>
            <p class="mt-1 text-xs text-gray-500">
                @if($canManage)
                    Sesi yang dibuat sesuai kewenangan akun Anda.
                @elseif($canViewRecords)
                    Sesi kegiatan dan rekap yang dapat Anda lihat.
                @else
                    Scan QR dari pengelola kegiatan untuk membuka formulir absensi.
                @endif
            </p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                    <tr><th class="px-5 py-3">Kegiatan</th><th class="px-5 py-3">Cabang</th><th class="px-5 py-3">Jadwal</th>@if($canViewRecords)<th class="px-5 py-3">Hadir</th>@endif<th class="px-5 py-3">Status</th>@if($canViewRecords)<th class="px-5 py-3">Aksi</th>@endif</tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($sessions as $session)
                        @php($sessionEnded = $session->ends_at->isPast())
                        <tr>
                            <td class="px-5 py-4"><p class="font-bold text-gray-900">{{ $session->title }}</p><p class="mt-1 text-xs text-gray-500">{{ collect($session->target_roles ?? [])->map(fn($role) => $roleLabels[$role] ?? $role)->join(', ') ?: ($session->target_role === 'all' ? 'Atlet dan Pelatih' : ucfirst($session->target_role)) }}</p></td>
                            <td class="px-5 py-4 text-gray-600">{{ $session->cabangOlahraga?->nama ?? 'Semua cabor' }}</td>
                            <td class="px-5 py-4 text-xs text-gray-600">{{ $session->starts_at->format('d/m/Y H:i') }}<br>s.d. {{ $session->ends_at->format('d/m/Y H:i') }}</td>
                            @if($canViewRecords)<td class="px-5 py-4 font-black tabular-nums">{{ number_format($session->records_count, 0, ',', '.') }}</td>@endif
                            <td class="px-5 py-4"><span class="rounded-full px-3 py-1 text-xs font-bold {{ $session->status === 'closed' ? 'bg-gray-100 text-gray-600' : ($sessionEnded ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700') }}">{{ $session->status === 'closed' ? 'Ditutup' : ($sessionEnded ? 'Berakhir' : 'Dibuka') }}</span></td>
                            @if($canViewRecords)<td class="px-5 py-4"><a href="{{ route($routePrefix.'.absensi.show', $session) }}" class="font-bold text-red-600 hover:underline">Detail dan rekap</a></td>@endif
                        </tr>
                    @empty
                        <tr><td colspan="{{ $canViewRecords ? 6 : 4 }}" class="px-5 py-12 text-center text-sm text-gray-500">Belum ada sesi absensi yang sesuai dengan akun Anda.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($sessions instanceof \Illuminate\Contracts\Pagination\Paginator || $sessions instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
            <div class="border-t border-gray-100 px-5 py-4">{{ $sessions->links() }}</div>
        @endif
    </section>

    <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4 sm:px-6">
            <h3 class="font-black">{{ $canViewRecords ? 'Riwayat Kehadiran' : 'Riwayat Absensi Saya' }}</h3>
            <p class="mt-1 text-xs text-gray-500">Setiap sesi menyimpan waktu check-in dan check-out secara terpisah.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                    <tr><th class="px-5 py-3">Peserta</th><th class="px-5 py-3">Role</th><th class="px-5 py-3">Cabor</th><th class="px-5 py-3">Kegiatan</th><th class="px-5 py-3">Check-in</th><th class="px-5 py-3">Check-out</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($records as $record)
                        <tr>
                            <td class="px-5 py-4 font-semibold">{{ $record->participant_name }}</td>
                            <td class="px-5 py-4">{{ $roleLabels[$record->role_snapshot] ?? $record->role_snapshot }}</td>
                            <td class="px-5 py-4">{{ $record->cabangOlahraga?->nama ?? 'Non-Cabor' }}</td>
                            <td class="px-5 py-4">{{ $record->session?->title ?? '-' }}</td>
                            <td class="px-5 py-4 text-xs">{{ $record->checked_in_at?->format('d/m/Y H:i') ?? '-' }}</td>
                            <td class="px-5 py-4 text-xs">{{ $record->checked_out_at?->format('d/m/Y H:i') ?? 'Belum check-out' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-12 text-center text-sm text-gray-500">Belum ada riwayat kehadiran.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-gray-100 px-5 py-4">{{ $records->links() }}</div>
    </section>
</div>
@endsection
