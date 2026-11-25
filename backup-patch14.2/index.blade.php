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
                <p class="mt-2 max-w-2xl text-sm text-red-100">Buat sesi kegiatan. QR akan otomatis tampil pada halaman Absensi Saya milik peserta yang sesuai dengan role dan cabang olahraganya.</p>
            </div>
            <a href="{{ route($routePrefix.'.absensi.create') }}" class="shrink-0 rounded-lg bg-white px-5 py-3 text-sm font-black text-red-700 hover:bg-red-50">+ Buat Sesi Absensi</a>
        </section>
    @else
        <section class="rounded-2xl bg-gradient-to-r from-[#8F0010] to-[#E60012] p-6 text-white shadow-lg">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-yellow-200">Kehadiran pribadi</p>
            <h2 class="mt-2 text-2xl font-black">Absensi QR dan Selfie</h2>
            <p class="mt-2 max-w-2xl text-sm text-red-100">Unduh QR sesi dari halaman ini, lalu pilih gambar QR tersebut dari galeri untuk memulai absensi. Setelah QR valid, kamera selfie akan dibuka.</p>
        </section>
    @endif

    <section class="rounded-2xl border border-red-100 bg-white p-5 shadow-sm sm:p-6">
        <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-red-600">Akses pribadi</p>
                <h3 class="mt-1 text-lg font-black">Absensi Saya</h3>
                <p class="mt-1 max-w-2xl text-xs leading-5 text-gray-500">QR ditampilkan sesuai role dan cabang olahraga akun Anda. Unduh QR, lalu pindai gambar dari galeri HP. Sistem baru membuka formulir selfie setelah token QR tervalidasi.</p>
            </div>
            <span class="rounded-full bg-red-50 px-3 py-1 text-xs font-bold text-red-700">{{ $mySessions->count() }} sesi tersedia</span>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse($mySessions as $mySession)
                @php($myRecord = $mySession->records->first())
                @php($isUpcoming = $mySession->starts_at->isFuture() && !$myRecord)
                @php($isCompleted = $myRecord?->checked_out_at !== null)
                <article class="flex flex-col rounded-xl border border-gray-200 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <h4 class="font-black text-gray-900">{{ $mySession->title }}</h4>
                        @if($isCompleted)
                            <span class="shrink-0 rounded-full bg-green-100 px-2.5 py-1 text-[10px] font-bold text-green-700">Selesai</span>
                        @elseif($myRecord)
                            <span class="shrink-0 rounded-full bg-amber-100 px-2.5 py-1 text-[10px] font-bold text-amber-700">Menunggu check-out</span>
                        @elseif($isUpcoming)
                            <span class="shrink-0 rounded-full bg-blue-100 px-2.5 py-1 text-[10px] font-bold text-blue-700">Akan datang</span>
                        @else
                            <span class="shrink-0 rounded-full bg-green-100 px-2.5 py-1 text-[10px] font-bold text-green-700">Dibuka</span>
                        @endif
                    </div>
                    <p class="mt-2 text-xs text-gray-500">{{ $mySession->cabangOlahraga?->nama ?? 'Semua cabang olahraga' }}</p>
                    <p class="mt-1 text-xs text-gray-500">{{ $mySession->starts_at->format('d/m/Y H:i') }} s.d. {{ $mySession->ends_at->format('d/m/Y H:i') }}</p>

                    @if($myRecord)
                        <div class="mt-3 rounded-lg bg-gray-50 p-3">
                            <p class="text-xs text-gray-600">Check-in: {{ $myRecord->checked_in_at?->format('d/m/Y H:i') ?? '-' }}</p>
                            <p class="mt-1 text-xs text-gray-600">Check-out: {{ $myRecord->checked_out_at?->format('d/m/Y H:i') ?? 'Belum dilakukan' }}</p>
                        </div>
                    @endif

                    @if(!$isCompleted)
                        <div class="mt-4 rounded-xl border border-gray-100 bg-gray-50 p-3">
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-xs font-black text-gray-800">QR Absensi Hari Ini</p>
                                <span class="text-[10px] font-semibold text-gray-500">Token harian</span>
                            </div>
                            <div id="attendance-qr-{{ $mySession->id }}" class="mx-auto my-3 flex max-w-[180px] items-center justify-center rounded-lg bg-white p-3 [&_svg]:h-full [&_svg]:w-full">
                                {!! $mySession->qr_svg !!}
                            </div>
                            <button type="button" class="download-attendance-qr w-full rounded-lg bg-gray-900 px-3 py-2.5 text-xs font-black text-white hover:bg-gray-700" data-qr-target="attendance-qr-{{ $mySession->id }}" data-filename="qr-absensi-{{ \Illuminate\Support\Str::slug($mySession->title) }}-{{ now()->format('Ymd') }}.png">
                                Unduh QR ke HP
                            </button>
                            <input type="file" accept="image/*" class="attendance-qr-file hidden" aria-label="Pilih gambar QR absensi dari galeri">
                            <button type="button" class="choose-attendance-qr mt-2 w-full rounded-lg border border-red-200 bg-white px-3 py-2.5 text-xs font-black text-red-700 hover:bg-red-50">
                                Pindai QR dari Galeri
                            </button>
                            <p class="attendance-qr-feedback mt-2 text-center text-[11px] leading-5 text-gray-500" role="status" aria-live="polite">Unduh QR ini, lalu pilih file gambarnya melalui tombol pindai.</p>
                        </div>
                    @else
                        <div class="mt-4 rounded-xl border border-green-100 bg-green-50 p-4 text-center">
                            <p class="text-sm font-black text-green-800">Absensi sesi ini sudah lengkap.</p>
                            <p class="mt-1 text-xs text-green-700">Check-in dan check-out sudah tercatat.</p>
                        </div>
                    @endif
                </article>
            @empty
                <div class="rounded-xl border border-dashed border-gray-300 px-4 py-8 text-center md:col-span-2 xl:col-span-3">
                    <p class="text-sm font-bold text-gray-700">Belum ada sesi absensi untuk akun Anda.</p>
                    <p class="mt-1 text-xs text-gray-500">QR akan muncul setelah pengelola membuat sesi yang mencakup role dan cabang olahraga Anda.</p>
                </div>
            @endforelse
        </div>
    </section>
    <div id="qr-file-reader" class="hidden" aria-hidden="true"></div>

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

    @if($canViewRecords)
    <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4 sm:px-6">
            <h3 class="font-black">{{ $canManage || $canViewRecords ? 'Daftar Sesi Absensi' : 'Kegiatan Absensi yang Tersedia' }}</h3>
            <p class="mt-1 text-xs text-gray-500">
                @if($canManage)
                    Sesi yang dibuat sesuai kewenangan akun Anda.
                @elseif($canViewRecords)
                    Sesi kegiatan dan rekap yang dapat Anda lihat.
                @else
                    Unduh QR dari Absensi Saya, lalu pindai gambar QR dari galeri HP.
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
    @endif

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
<script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
    (() => {
        const scanRouteTemplate = @json(route('attendance.check-in.show', 'QR_TOKEN_PLACEHOLDER', false));
        const readerElementId = 'qr-file-reader';

        const showFeedback = (button, message, isError = false) => {
            const card = button.closest('article');
            const feedback = card?.querySelector('.attendance-qr-feedback');
            if (feedback) {
                feedback.textContent = message;
                feedback.classList.toggle('text-red-600', isError);
                feedback.classList.toggle('text-gray-500', !isError);
            }
        };

        const downloadQrPng = (button) => {
            const target = document.getElementById(button.dataset.qrTarget || '');
            const svgElement = target?.querySelector('svg');
            if (!svgElement) {
                showFeedback(button, 'QR tidak dapat dibaca untuk diunduh.', true);
                return;
            }

            button.disabled = true;
            const originalLabel = button.textContent;
            button.textContent = 'Menyiapkan gambar...';
            const svgMarkup = new XMLSerializer().serializeToString(svgElement);
            const svgUrl = URL.createObjectURL(new Blob([svgMarkup], { type: 'image/svg+xml;charset=utf-8' }));
            const image = new Image();

            image.onload = () => {
                const canvas = document.createElement('canvas');
                canvas.width = 960;
                canvas.height = 960;
                const context = canvas.getContext('2d');
                if (!context) {
                    URL.revokeObjectURL(svgUrl);
                    button.disabled = false;
                    button.textContent = originalLabel;
                    showFeedback(button, 'Browser tidak dapat membuat gambar QR.', true);
                    return;
                }

                context.fillStyle = '#ffffff';
                context.fillRect(0, 0, canvas.width, canvas.height);
                context.imageSmoothingEnabled = false;
                context.drawImage(image, 0, 0, canvas.width, canvas.height);
                URL.revokeObjectURL(svgUrl);

                canvas.toBlob((blob) => {
                    button.disabled = false;
                    button.textContent = originalLabel;
                    if (!blob) {
                        showFeedback(button, 'Gambar QR gagal dibuat. Muat ulang halaman dan coba lagi.', true);
                        return;
                    }

                    const pngUrl = URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = pngUrl;
                    link.download = button.dataset.filename || 'qr-absensi.png';
                    document.body.appendChild(link);
                    link.click();
                    link.remove();
                    window.setTimeout(() => URL.revokeObjectURL(pngUrl), 1500);
                    showFeedback(button, 'QR berhasil diunduh. Pilih gambar tersebut melalui tombol Pindai QR dari Galeri.');
                }, 'image/png');
            };

            image.onerror = () => {
                URL.revokeObjectURL(svgUrl);
                button.disabled = false;
                button.textContent = originalLabel;
                showFeedback(button, 'Gambar QR gagal dibuat. Coba muat ulang halaman.', true);
            };

            image.src = svgUrl;
        };

        const decodeQrImage = async (file) => {
            if ('BarcodeDetector' in window && 'createImageBitmap' in window) {
                try {
                    const detector = new BarcodeDetector({ formats: ['qr_code'] });
                    const bitmap = await createImageBitmap(file);
                    try {
                        const results = await detector.detect(bitmap);
                        if (results.length && results[0].rawValue) return results[0].rawValue;
                    } finally {
                        bitmap.close?.();
                    }
                } catch (error) {
                    // Use the bundled scanner fallback for browsers with partial API support.
                }
            }

            if (typeof window.Html5Qrcode === 'function') {
                const scanner = new Html5Qrcode(readerElementId);
                try {
                    return await scanner.scanFile(file, false);
                } finally {
                    try { scanner.clear(); } catch (error) {}
                }
            }

            throw new Error('Pemindai QR tidak tersedia. Periksa koneksi internet atau gunakan browser Chrome terbaru.');
        };

        const openAttendanceFromQr = (decodedText) => {
            let decodedUrl;
            try {
                decodedUrl = new URL(decodedText, window.location.origin);
            } catch (error) {
                throw new Error('Gambar tidak berisi tautan QR yang valid.');
            }

            const match = decodedUrl.pathname.match(/\/absensi\/scan\/([A-Za-z0-9_.-]+)\/?$/);
            if (!match) {
                throw new Error('QR yang dipilih bukan QR absensi KONI Klaten.');
            }

            // Keep navigation on the currently opened application origin. The server
            // verifies the signed token, daily date, participant role, and cabor.
            const localScanPath = scanRouteTemplate.replace('QR_TOKEN_PLACEHOLDER', encodeURIComponent(match[1]));
            window.location.assign(new URL(localScanPath, window.location.origin).toString());
        };

        document.querySelectorAll('.download-attendance-qr').forEach((button) => {
            button.addEventListener('click', () => downloadQrPng(button));
        });

        document.querySelectorAll('.choose-attendance-qr').forEach((button) => {
            const card = button.closest('article');
            const input = card?.querySelector('.attendance-qr-file');
            if (!input) return;

            button.addEventListener('click', () => input.click());
            input.addEventListener('change', async () => {
                const file = input.files?.[0];
                if (!file) return;

                if (file.type && !file.type.startsWith('image/')) {
                    showFeedback(button, 'Pilih file gambar QR, misalnya PNG atau JPG.', true);
                    input.value = '';
                    return;
                }

                if (file.size > 12 * 1024 * 1024) {
                    showFeedback(button, 'Ukuran gambar QR maksimal 12 MB.', true);
                    input.value = '';
                    return;
                }

                button.disabled = true;
                button.textContent = 'Membaca QR...';
                showFeedback(button, 'Sedang membaca gambar QR dari galeri.');

                try {
                    const decodedText = await decodeQrImage(file);
                    openAttendanceFromQr(decodedText);
                } catch (error) {
                    showFeedback(button, error?.message || 'QR tidak terbaca. Pastikan gambar jelas dan tidak terpotong.', true);
                    button.disabled = false;
                    button.textContent = 'Pindai QR dari Galeri';
                } finally {
                    input.value = '';
                }
            });
        });

        const untilNextLocalDay = {{ max(1000, (int) now()->diffInMilliseconds(now()->addDay()->startOfDay())) }};
        window.setTimeout(() => window.location.reload(), untilNextLocalDay);
    })();
</script>

@endsection
