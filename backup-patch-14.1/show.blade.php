@extends('layouts.admin')

@section('title', 'Detail Absensi')
@section('heading', 'Detail Absensi')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    @if(session('success'))<div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800">{{ session('error') }}</div>@endif

    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('admin.absensi.index') }}" class="text-sm font-semibold text-gray-500 hover:text-red-600">← Kembali ke daftar sesi</a>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.absensi.export', $attendanceSession) }}" class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-xs font-bold text-gray-700">Ekspor CSV</a>
            @if($attendanceSession->status === 'open')
                <form method="POST" action="{{ route('admin.absensi.close', $attendanceSession) }}" onsubmit="return confirm('Tutup sesi absensi ini? QR tidak akan dapat digunakan lagi.');">
                    @csrf
                    <button class="rounded-lg bg-red-600 px-4 py-2.5 text-xs font-bold text-white">Tutup Sesi</button>
                </form>
            @endif
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_340px]">
        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-7">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div><p class="text-xs font-bold uppercase tracking-[0.18em] text-red-600">Sesi absensi</p><h2 class="mt-2 text-2xl font-black">{{ $attendanceSession->title }}</h2><p class="mt-2 text-sm text-gray-500">{{ $attendanceSession->description ?: 'Tidak ada keterangan tambahan.' }}</p></div>
                <span class="rounded-full px-3 py-1 text-xs font-bold {{ $attendanceSession->status === 'closed' ? 'bg-gray-100 text-gray-600' : ($attendanceSession->ends_at->isPast() ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700') }}">{{ $attendanceSession->status === 'closed' ? 'Ditutup' : ($attendanceSession->ends_at->isPast() ? 'Berakhir' : 'Dibuka') }}</span>
            </div>
            <dl class="mt-6 grid gap-4 border-t border-gray-100 pt-5 sm:grid-cols-2">
                <div><dt class="text-xs font-semibold text-gray-500">Cabang olahraga</dt><dd class="mt-1 text-sm font-bold">{{ $attendanceSession->cabangOlahraga?->nama ?? 'Semua cabang olahraga' }}</dd></div>
                <div><dt class="text-xs font-semibold text-gray-500">Peserta</dt><dd class="mt-1 text-sm font-bold">{{ $attendanceSession->target_role === 'all' ? 'Atlet dan pelatih' : ucfirst($attendanceSession->target_role) }}</dd></div>
                <div><dt class="text-xs font-semibold text-gray-500">Mulai</dt><dd class="mt-1 text-sm font-bold">{{ $attendanceSession->starts_at->format('d/m/Y H:i') }}</dd></div>
                <div><dt class="text-xs font-semibold text-gray-500">Selesai</dt><dd class="mt-1 text-sm font-bold">{{ $attendanceSession->ends_at->format('d/m/Y H:i') }}</dd></div>
            </dl>
        </section>

        <aside class="rounded-2xl border border-gray-200 bg-white p-5 text-center shadow-sm">
            <h3 class="font-black">QR Code Kehadiran</h3>
            <p class="mt-1 text-xs text-gray-500">Peserta scan QR lalu login dan ambil selfie.</p>
            <div class="mx-auto my-4 max-w-[240px] rounded-xl border border-gray-100 bg-white p-3">{!! $qrSvg !!}</div>
            <p class="break-all rounded-lg bg-gray-50 p-3 text-[11px] text-gray-600">{{ route('attendance.check-in.show', $attendanceSession->token) }}</p>
            <button type="button" id="copy-attendance-url" class="mt-3 w-full rounded-lg border border-gray-300 px-4 py-2.5 text-xs font-bold text-gray-700" data-url="{{ route('attendance.check-in.show', $attendanceSession->token) }}">Salin tautan absensi</button>
            <p id="copy-feedback" class="mt-2 text-xs text-green-700" aria-live="polite"></p>
            @if($attendanceSession->status !== 'open' || $attendanceSession->ends_at->isPast())
                <p class="mt-3 text-xs font-semibold text-red-600">Sesi sudah ditutup atau berakhir. QR tidak dapat digunakan.</p>
            @elseif(now()->lt($attendanceSession->starts_at))
                <p class="mt-3 text-xs font-semibold text-amber-700">QR akan aktif pada {{ $attendanceSession->starts_at->format('d/m/Y H:i') }}.</p>
            @endif
        </aside>
    </div>

    <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-5 py-4 sm:px-6">
            <div><h3 class="font-black">Daftar Kehadiran</h3><p class="mt-1 text-xs text-gray-500">Data masuk saat peserta menyelesaikan pengambilan selfie.</p></div>
            <span class="rounded-full bg-green-50 px-3 py-1 text-xs font-black text-green-700">{{ number_format($attendanceSession->records()->count(), 0, ',', '.') }} hadir</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500"><tr><th class="px-5 py-3">Peserta</th><th class="px-5 py-3">Peran</th><th class="px-5 py-3">Cabang</th><th class="px-5 py-3">Waktu Hadir</th><th class="px-5 py-3">Selfie</th></tr></thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($records as $record)
                        <tr>
                            <td class="px-5 py-4"><p class="font-bold text-gray-900">{{ $record->participant_name }}</p><p class="mt-1 text-xs text-gray-500">{{ $record->participant_email ?: '-' }}</p></td>
                            <td class="px-5 py-4 text-gray-600">{{ ucfirst($record->role_snapshot) }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ $record->cabangOlahraga?->nama ?? '-' }}</td>
                            <td class="px-5 py-4 text-xs text-gray-600">{{ $record->checked_in_at->format('d/m/Y H:i:s') }}</td>
                            <td class="px-5 py-4"><a target="_blank" rel="noopener" href="{{ route('admin.absensi.selfie', $record) }}" class="font-bold text-red-600 hover:underline">Lihat foto</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-12 text-center text-sm text-gray-500">Belum ada peserta yang melakukan absensi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-gray-100 px-5 py-4">{{ $records->links() }}</div>
    </section>
</div>

<script>
    document.getElementById('copy-attendance-url')?.addEventListener('click', async (event) => {
        const button = event.currentTarget;
        try {
            await navigator.clipboard.writeText(button.dataset.url);
            document.getElementById('copy-feedback').textContent = 'Tautan berhasil disalin.';
        } catch (error) {
            document.getElementById('copy-feedback').textContent = 'Browser tidak mengizinkan penyalinan otomatis. Salin tautan dari teks di atas.';
        }
    });
</script>
@endsection
