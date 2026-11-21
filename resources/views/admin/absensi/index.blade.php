@extends('layouts.admin')

@section('title', 'Absensi')
@section('heading', 'Absensi QR dan Selfie')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    @if(session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">{{ session('success') }}</div>
    @endif

    <section class="flex flex-col justify-between gap-4 rounded-2xl bg-gradient-to-r from-[#8F0010] to-[#E60012] p-6 text-white shadow-lg sm:flex-row sm:items-center sm:p-8">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-yellow-200">Kehadiran kegiatan</p>
            <h2 class="mt-2 text-2xl font-black">Kelola Sesi Absensi</h2>
            <p class="mt-2 max-w-2xl text-sm text-red-100">Buat sesi, bagikan QR Code, lalu peserta melakukan check-in dengan foto selfie dari kamera perangkat.</p>
        </div>
        <a href="{{ route('admin.absensi.create') }}" class="shrink-0 rounded-lg bg-white px-5 py-3 text-sm font-black text-red-700 hover:bg-red-50">+ Buat Sesi Absensi</a>
    </section>

    <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4 sm:px-6">
            <h3 class="font-black">Riwayat Sesi</h3>
            <p class="mt-1 text-xs text-gray-500">Daftar sesi absensi yang pernah dibuat.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                    <tr><th class="px-5 py-3">Kegiatan</th><th class="px-5 py-3">Cabang</th><th class="px-5 py-3">Jadwal</th><th class="px-5 py-3">Hadir</th><th class="px-5 py-3">Status</th><th class="px-5 py-3">Aksi</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($sessions as $session)
                        <tr>
                            <td class="px-5 py-4"><p class="font-bold text-gray-900">{{ $session->title }}</p><p class="mt-1 text-xs text-gray-500">{{ $session->target_role === 'all' ? 'Atlet dan pelatih' : ucfirst($session->target_role) }}</p></td>
                            <td class="px-5 py-4 text-gray-600">{{ $session->cabangOlahraga?->nama ?? 'Semua cabor' }}</td>
                            <td class="px-5 py-4 text-xs text-gray-600">{{ $session->starts_at->format('d/m/Y H:i') }}<br>s.d. {{ $session->ends_at->format('d/m/Y H:i') }}</td>
                            <td class="px-5 py-4 font-black tabular-nums">{{ number_format($session->records_count, 0, ',', '.') }}</td>
                            @php($sessionEnded = $session->ends_at->isPast())
                            <td class="px-5 py-4"><span class="rounded-full px-3 py-1 text-xs font-bold {{ $session->status === 'closed' ? 'bg-gray-100 text-gray-600' : ($sessionEnded ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700') }}">{{ $session->status === 'closed' ? 'Ditutup' : ($sessionEnded ? 'Berakhir' : 'Dibuka') }}</span></td>
                            <td class="px-5 py-4"><a href="{{ route('admin.absensi.show', $session) }}" class="font-bold text-red-600 hover:underline">Detail dan QR</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-12 text-center text-sm text-gray-500">Belum ada sesi absensi. Buat sesi pertama untuk mulai mencatat kehadiran.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-gray-100 px-5 py-4">{{ $sessions->links() }}</div>
    </section>
</div>
@endsection
