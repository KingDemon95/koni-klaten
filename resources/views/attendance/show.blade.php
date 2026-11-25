@extends($layout)

@section('title', 'Detail Absensi')
@section('heading', 'Detail Absensi')

@section('content')
<style>
    .attendance-page {
        box-sizing: border-box;
        width: 100%;
        max-width: 100%;
        min-width: 0;
    }
    .attendance-page .attendance-qr-frame {
        box-sizing: border-box;
        display: flex;
        align-items: center;
        justify-content: center;
        width: min(100%, 15rem);
        max-width: 100%;
        min-width: 0;
        aspect-ratio: 1 / 1;
        margin: 1rem auto;
        padding: .5rem;
        overflow: hidden;
        border: 1px solid #f3f4f6;
        border-radius: .75rem;
        background: #fff;
    }
    .attendance-page .attendance-qr-frame svg {
        display: block;
        width: 100% !important;
        height: auto !important;
        max-width: 100% !important;
        flex: 0 1 auto;
    }
    .attendance-page .attendance-qr-url {
        box-sizing: border-box;
        width: 100%;
        max-width: 100%;
        min-width: 0;
        overflow-wrap: anywhere;
        word-break: break-word;
    }
    .attendance-page article,
    .attendance-page section,
    .attendance-page aside,
    .attendance-page form {
        min-width: 0;
    }
</style>
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

<div class="attendance-page mx-auto w-full min-w-0 max-w-7xl space-y-6">
    @if(session('success'))<div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800">{{ session('error') }}</div>@endif

    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route($routePrefix.'.absensi.index') }}" class="text-sm font-semibold text-gray-500 hover:text-red-600">← Kembali ke daftar sesi</a>
        <div class="flex min-w-0 flex-wrap gap-2">
            <a href="{{ route($routePrefix.'.absensi.export', $attendanceSession) }}" class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-xs font-bold text-gray-700">Ekspor CSV</a>
            @if($canManage && $attendanceSession->status === 'open')
                <form method="POST" action="{{ route($routePrefix.'.absensi.close', $attendanceSession) }}" onsubmit="return confirm('Tutup sesi absensi ini? QR tidak akan dapat digunakan lagi.');">
                    @csrf
                    <button class="rounded-lg bg-red-600 px-4 py-2.5 text-xs font-bold text-white">Tutup Sesi</button>
                </form>
            @endif
        </div>
    </div>

    <div class="grid min-w-0 grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,340px)]">
        <section class="min-w-0 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm sm:p-7">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div><p class="text-xs font-bold uppercase tracking-[0.18em] text-red-600">Sesi absensi</p><h2 class="mt-2 text-2xl font-black">{{ $attendanceSession->title }}</h2><p class="mt-2 text-sm text-gray-500">{{ $attendanceSession->description ?: 'Tidak ada keterangan tambahan.' }}</p></div>
                <span class="rounded-full px-3 py-1 text-xs font-bold {{ $attendanceSession->status === 'closed' ? 'bg-gray-100 text-gray-600' : ($attendanceSession->ends_at->isPast() ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700') }}">{{ $attendanceSession->status === 'closed' ? 'Ditutup' : ($attendanceSession->ends_at->isPast() ? 'Berakhir' : 'Dibuka') }}</span>
            </div>
            <dl class="mt-6 grid gap-4 border-t border-gray-100 pt-5 sm:grid-cols-2">
                <div><dt class="text-xs font-semibold text-gray-500">Cabang olahraga</dt><dd class="mt-1 text-sm font-bold">{{ $attendanceSession->cabangOlahraga?->nama ?? 'Semua cabang olahraga' }}</dd></div>
                <div><dt class="text-xs font-semibold text-gray-500">Role peserta</dt><dd class="mt-1 text-sm font-bold">{{ collect($attendanceSession->target_roles ?? [])->map(fn($role) => $roleLabels[$role] ?? $role)->join(', ') ?: ($attendanceSession->target_role === 'all' ? 'Atlet dan Pelatih (sesi lama)' : ucfirst($attendanceSession->target_role)) }}</dd></div>
                <div><dt class="text-xs font-semibold text-gray-500">Mulai</dt><dd class="mt-1 text-sm font-bold">{{ $attendanceSession->starts_at->format('d/m/Y H:i') }}</dd></div>
                <div><dt class="text-xs font-semibold text-gray-500">Selesai</dt><dd class="mt-1 text-sm font-bold">{{ $attendanceSession->ends_at->format('d/m/Y H:i') }}</dd></div>
            </dl>
        </section>

        <aside id="attendance-qr-print" class="min-w-0 w-full overflow-hidden rounded-2xl border border-gray-200 bg-white p-4 text-center shadow-sm sm:p-5">
            <h3 class="font-black">QR Code Harian</h3>
            <p class="mt-1 text-xs text-gray-500">QR berubah setiap hari dan hanya berlaku pada tanggal yang tampil. Halaman ini akan memuat ulang QR setelah pergantian hari.</p>
            <div id="attendance-qr-svg" class="attendance-qr-frame">{!! $qrSvg !!}</div>
            <p class="attendance-qr-url rounded-lg bg-gray-50 p-3 text-[11px] text-gray-600">{{ $scanUrl }}</p>
            <div class="no-print mt-3 space-y-2">
                <button type="button" id="copy-attendance-url" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-xs font-bold text-gray-700" data-url="{{ $scanUrl }}">Salin tautan absensi</button>
                @if($attendanceSession->status === 'open' && $attendanceSession->ends_at->isFuture())
                    <div class="grid min-w-0 grid-cols-2 gap-2">
                        <a href="{{ route($routePrefix.'.absensi.qr.download', $attendanceSession) }}" class="rounded-lg bg-gray-900 px-3 py-2.5 text-xs font-bold text-white hover:bg-gray-700">Unduh SVG</a>
                        <button type="button" id="download-qr-png" data-filename="qr-absensi-{{ \Illuminate\Support\Str::slug($attendanceSession->title) }}-{{ now()->format('Ymd') }}.png" class="rounded-lg bg-[#E60012] px-3 py-2.5 text-xs font-bold text-white hover:bg-red-700">Unduh PNG</button>
                    </div>
                    <button type="button" id="print-attendance-qr" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-xs font-bold text-gray-700">Cetak QR</button>
                @endif
                <p id="copy-feedback" class="text-xs text-green-700" aria-live="polite"></p>
            </div>
            <p class="mt-3 text-xs leading-5 text-gray-500">QR sesi juga otomatis tampil pada halaman Absensi Saya milik peserta yang memenuhi role dan cabor. Peserta dapat mengunduh QR dari HP, lalu memilih gambar QR tersebut dari galeri untuk memindainya pada perangkat yang sama. Setelah token valid, sistem membuka kamera selfie.</p>
            @if($attendanceSession->status !== 'open' || $attendanceSession->ends_at->isPast())
                <p class="mt-3 text-xs font-semibold text-red-600">Sesi sudah ditutup atau berakhir. QR tidak dapat digunakan.</p>
            @elseif(now()->lt($attendanceSession->starts_at))
                <p class="mt-3 text-xs font-semibold text-amber-700">QR aktif setelah sesi dimulai pada {{ $attendanceSession->starts_at->format('d/m/Y H:i') }}.</p>
            @endif
        </aside>
    </div>

    <section class="min-w-0 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-5 py-4 sm:px-6">
            <div><h3 class="font-black">Daftar Kehadiran</h3><p class="mt-1 text-xs text-gray-500">Selfie check-in dan check-out tersimpan secara privat.</p></div>
            <span class="rounded-full bg-green-50 px-3 py-1 text-xs font-black text-green-700">{{ number_format($attendanceSession->records()->count(), 0, ',', '.') }} check-in</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500"><tr><th class="px-5 py-3">Peserta</th><th class="px-5 py-3">Peran</th><th class="px-5 py-3">Cabang</th><th class="px-5 py-3">Check-in</th><th class="px-5 py-3">Check-out</th>@if($canViewSelfies)<th class="px-5 py-3">Selfie</th>@endif</tr></thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($records as $record)
                        <tr>
                            <td class="px-5 py-4"><p class="font-bold text-gray-900">{{ $record->participant_name }}</p><p class="mt-1 text-xs text-gray-500">{{ $record->participant_email ?: '-' }}</p></td>
                            <td class="px-5 py-4 text-gray-600">{{ $roleLabels[$record->role_snapshot] ?? $record->role_snapshot }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ $record->cabangOlahraga?->nama ?? 'Non-Cabor' }}</td>
                            <td class="px-5 py-4 text-xs text-gray-600">{{ $record->checked_in_at?->format('d/m/Y H:i:s') ?? '-' }}</td>
                            <td class="px-5 py-4 text-xs text-gray-600">{{ $record->checked_out_at?->format('d/m/Y H:i:s') ?? 'Belum check-out' }}</td>
                            @if($canViewSelfies)
                                <td class="px-5 py-4 text-xs">
                                    <a target="_blank" rel="noopener" href="{{ route($routePrefix.'.absensi.selfie', ['attendanceRecord' => $record, 'kind' => 'check-in']) }}" class="font-bold text-red-600 hover:underline">Check-in</a>
                                    @if($record->check_out_selfie_path)
                                        <span class="px-1 text-gray-300">|</span>
                                        <a target="_blank" rel="noopener" href="{{ route($routePrefix.'.absensi.selfie', ['attendanceRecord' => $record, 'kind' => 'check-out']) }}" class="font-bold text-red-600 hover:underline">Check-out</a>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="{{ $canViewSelfies ? 6 : 5 }}" class="px-5 py-12 text-center text-sm text-gray-500">Belum ada peserta yang melakukan absensi.</td></tr>
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
<script>
    document.getElementById('print-attendance-qr')?.addEventListener('click', () => window.print());

    document.getElementById('download-qr-png')?.addEventListener('click', (event) => {
        const button = event.currentTarget;
        const svgElement = document.querySelector('#attendance-qr-svg svg');
        const feedback = document.getElementById('copy-feedback');

        if (!svgElement) {
            feedback.textContent = 'QR tidak dapat dibaca untuk diunduh.';
            return;
        }

        const svgMarkup = new XMLSerializer().serializeToString(svgElement);
        const svgBlob = new Blob([svgMarkup], { type: 'image/svg+xml;charset=utf-8' });
        const svgUrl = URL.createObjectURL(svgBlob);
        const image = new Image();

        image.onload = () => {
            const canvas = document.createElement('canvas');
            canvas.width = 960;
            canvas.height = 960;
            const context = canvas.getContext('2d');

            if (!context) {
                URL.revokeObjectURL(svgUrl);
                feedback.textContent = 'Browser tidak dapat menyiapkan gambar PNG.';
                return;
            }

            context.fillStyle = '#ffffff';
            context.fillRect(0, 0, canvas.width, canvas.height);
            context.drawImage(image, 0, 0, canvas.width, canvas.height);
            URL.revokeObjectURL(svgUrl);

            canvas.toBlob((pngBlob) => {
                if (!pngBlob) {
                    feedback.textContent = 'Gambar PNG gagal dibuat. Gunakan tombol Unduh SVG.';
                    return;
                }

                const pngUrl = URL.createObjectURL(pngBlob);
                const link = document.createElement('a');
                link.href = pngUrl;
                link.download = button.dataset.filename || 'qr-absensi.png';
                document.body.appendChild(link);
                link.click();
                link.remove();
                window.setTimeout(() => URL.revokeObjectURL(pngUrl), 1000);
                feedback.textContent = 'QR PNG berhasil disiapkan.';
            }, 'image/png');
        };

        image.onerror = () => {
            URL.revokeObjectURL(svgUrl);
            feedback.textContent = 'Gambar PNG gagal dibuat. Gunakan tombol Unduh SVG.';
        };

        image.src = svgUrl;
    });
</script>
<style>
    @media print {
        @page { margin: 12mm; }
        body * { visibility: hidden !important; }
        #attendance-qr-print,
        #attendance-qr-print * { visibility: visible !important; }
        #attendance-qr-print {
            position: absolute !important;
            inset: 0 auto auto 0 !important;
            width: 100% !important;
            max-width: none !important;
            border: 0 !important;
            box-shadow: none !important;
        }
        #attendance-qr-print .no-print { display: none !important; }
    }
</style>
<script>
    (() => {
        const untilNextLocalDay = {{ max(1000, (int) now()->diffInMilliseconds(now()->addDay()->startOfDay())) }};
        window.setTimeout(() => window.location.reload(), untilNextLocalDay);
    })();
</script>
@endsection
