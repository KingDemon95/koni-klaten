@extends($layout)

@section('title', 'Detail Surat')
@section('heading', 'Detail Surat')

@section('content')
@php
    $routeBase = $prefix === 'admin' ? 'admin.persuratan' : $prefix.'.surat';
    $statusLabels = \App\Models\Surat::statusLabels();
    $canCaborEdit = $prefix === 'cabor' && in_array($surat->status, [\App\Models\Surat::STATUS_DRAFT, \App\Models\Surat::STATUS_REVISI], true);
    $canStaffReview = $prefix === 'staff' && in_array($surat->status, [\App\Models\Surat::STATUS_DIAJUKAN, \App\Models\Surat::STATUS_PERLU_REVISI_STAFF], true);
    $canStaffForward = $prefix === 'staff' && $surat->status === \App\Models\Surat::STATUS_DIAJUKAN;
    $canStaffDeliver = $prefix === 'staff' && $surat->status === \App\Models\Surat::STATUS_MENUNGGU_PENYAMPAIAN;
    $canAdminDecide = $prefix === 'admin' && $surat->status === \App\Models\Surat::STATUS_DITERUSKAN;
    $statusColor = match ($surat->status) {
        'diterima' => 'bg-green-100 text-green-800',
        'ditolak' => 'bg-red-100 text-red-800',
        'revisi', 'perlu_revisi_staff' => 'bg-amber-100 text-amber-800',
        'diteruskan_admin', 'menunggu_penyampaian' => 'bg-blue-100 text-blue-800',
        default => 'bg-gray-100 text-gray-700',
    };
@endphp

<div class="space-y-6">
    @if (session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <p class="font-bold">Periksa kembali data yang dimasukkan:</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route($routeBase.'.index') }}" class="text-sm font-bold text-gray-600 hover:text-red-600">← Kembali ke daftar surat</a>
        <span class="inline-flex rounded-full px-3 py-1.5 text-xs font-black {{ $statusColor }}">{{ $statusLabels[$surat->status] ?? $surat->status }}</span>
    </div>

    <section class="grid gap-6 xl:grid-cols-[minmax(0,1.5fr)_minmax(300px,1fr)]">
        <div class="space-y-6">
            <article class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-red-600">{{ ucfirst($surat->jenis_surat) }}</p>
                        <h2 class="mt-2 text-2xl font-black text-gray-900">{{ $surat->perihal }}</h2>
                        <p class="mt-2 text-sm text-gray-500">{{ $surat->cabangOlahraga?->nama ?? 'KONI Kabupaten Klaten' }}</p>
                    </div>
                    <div class="rounded-xl bg-gray-50 px-4 py-3 text-right">
                        <p class="text-xs text-gray-500">Tanggal surat</p>
                        <p class="mt-1 font-bold text-gray-900">{{ $surat->tanggal_surat?->format('d M Y') }}</p>
                    </div>
                </div>

                <dl class="mt-7 grid gap-4 border-t border-gray-100 pt-6 sm:grid-cols-2">
                    <div><dt class="text-xs font-semibold text-gray-500">Nomor surat</dt><dd class="mt-1 text-sm font-bold text-gray-900">{{ $surat->nomor_surat ?: 'Belum dicantumkan' }}</dd></div>
                    <div><dt class="text-xs font-semibold text-gray-500">Diajukan oleh</dt><dd class="mt-1 text-sm font-bold text-gray-900">{{ $surat->creator?->name ?? 'Pengguna tidak tersedia' }}</dd></div>
                    <div><dt class="text-xs font-semibold text-gray-500">Diperiksa Staff</dt><dd class="mt-1 text-sm font-bold text-gray-900">{{ $surat->staff?->name ?? 'Belum diperiksa' }}</dd></div>
                    <div><dt class="text-xs font-semibold text-gray-500">Keputusan Admin KONI</dt><dd class="mt-1 text-sm font-bold text-gray-900">{{ $prefix === 'cabor' && $surat->status === 'menunggu_penyampaian' ? 'Belum disampaikan' : (in_array($surat->status, ['perlu_revisi_staff', 'menunggu_penyampaian', 'revisi', 'diterima', 'ditolak'], true) && $surat->keputusan_admin ? ucfirst($surat->keputusan_admin) : 'Belum ada keputusan') }}</dd></div>
                </dl>

                @if ($surat->ringkasan)
                    <div class="mt-7 border-t border-gray-100 pt-6">
                        <h3 class="text-sm font-black text-gray-900">Ringkasan / Keterangan</h3>
                        <p class="mt-2 whitespace-pre-line text-sm leading-7 text-gray-600">{{ $surat->ringkasan }}</p>
                    </div>
                @endif

                <div class="mt-7 border-t border-gray-100 pt-6">
                    <h3 class="text-sm font-black text-gray-900">Dokumen</h3>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <a href="{{ route($routeBase.'.file', [$surat, 'asli']) }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm font-bold text-gray-700 hover:border-red-300 hover:text-red-700">Unduh surat asli · {{ $surat->file_original_name }}</a>
                        @if ($surat->staff_attachment_path && ($prefix !== 'cabor' || in_array($surat->status, ['revisi', 'diterima', 'ditolak'], true)))
                            <a href="{{ route($routeBase.'.file', [$surat, 'staff']) }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm font-bold text-gray-700 hover:border-red-300 hover:text-red-700">Lampiran Staff · {{ $surat->staff_attachment_original_name }}</a>
                        @endif
                        @if ($surat->admin_response_path && ($prefix !== 'cabor' || in_array($surat->status, ['revisi', 'diterima', 'ditolak'], true)))
                            <a href="{{ route($routeBase.'.file', [$surat, 'balasan']) }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm font-bold text-gray-700 hover:border-red-300 hover:text-red-700">Dokumen balasan Admin · {{ $surat->admin_response_original_name }}</a>
                        @endif
                    </div>
                </div>
            </article>

            <article class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-black text-gray-900">Riwayat Proses Surat</h3>
                <p class="mt-1 text-sm text-gray-500">Setiap perubahan status dicatat untuk menjaga jejak administrasi.</p>
                <ol class="mt-6 space-y-5">
                    @forelse ($activities as $activity)
                        <li class="relative border-l-2 border-gray-200 pb-1 pl-5 last:border-transparent">
                            <span class="absolute -left-[7px] top-1 h-3 w-3 rounded-full bg-red-600 ring-4 ring-white"></span>
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <p class="text-sm font-black text-gray-900">{{ str($activity->action)->replace('_', ' ')->title() }}</p>
                                <time class="text-xs text-gray-500">{{ $activity->created_at?->format('d M Y, H:i') }}</time>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">{{ $activity->actor?->name ?? 'Sistem' }}@if($activity->status_to) · {{ $statusLabels[$activity->status_to] ?? $activity->status_to }}@endif</p>
                            @if ($activity->catatan)
                                <p class="mt-2 whitespace-pre-line rounded-lg bg-gray-50 p-3 text-sm leading-6 text-gray-600">{{ $activity->catatan }}</p>
                            @endif
                        </li>
                    @empty
                        <li class="text-sm text-gray-500">Belum ada riwayat aktivitas.</li>
                    @endforelse
                </ol>
            </article>
        </div>

        <aside class="space-y-6">
            @if ($prefix === 'cabor' && $surat->status === \App\Models\Surat::STATUS_MENUNGGU_PENYAMPAIAN)
                <div class="rounded-xl border border-blue-200 bg-blue-50 p-5 text-sm leading-6 text-blue-900">Keputusan Admin KONI sudah diproses. Staff KONI sedang menyampaikan hasil resmi kepada Admin Cabor.</div>
            @endif

            @if ($prefix === 'staff' && $surat->status === \App\Models\Surat::STATUS_PERLU_REVISI_STAFF)
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-5">
                    <h3 class="font-black text-amber-900">Keputusan Admin meminta revisi</h3>
                    <p class="mt-2 whitespace-pre-line text-sm leading-6 text-amber-800">{{ $surat->catatan_admin }}</p>
                    <p class="mt-2 text-xs text-amber-700">Staff perlu meneruskan catatan ini kepada Admin Cabor.</p>
                </div>
            @endif

            @if ($prefix === 'staff' && $surat->status === \App\Models\Surat::STATUS_MENUNGGU_PENYAMPAIAN)
                <div class="rounded-xl border border-blue-200 bg-blue-50 p-5">
                    <h3 class="font-black text-blue-900">Keputusan Admin KONI</h3>
                    <p class="mt-2 text-sm font-bold text-blue-900">{{ ucfirst($surat->keputusan_admin ?? '-') }}</p>
                    @if ($surat->catatan_admin)<p class="mt-2 whitespace-pre-line text-sm text-blue-800">{{ $surat->catatan_admin }}</p>@endif
                    <p class="mt-3 text-xs text-blue-700">Sampaikan hasil ini kepada Admin Cabor untuk menutup proses surat.</p>
                </div>
            @endif

            @if ($canCaborEdit)
                <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                    <h3 class="font-black text-gray-900">Tindakan Admin Cabor</h3>
                    @if ($surat->status === \App\Models\Surat::STATUS_REVISI && $surat->catatan_staff)
                        <div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">
                            <p class="font-bold">Catatan revisi Staff</p>
                            <p class="mt-1 whitespace-pre-line">{{ $surat->catatan_staff }}</p>
                        </div>
                    @endif
                    <a href="{{ route('cabor.surat.edit', $surat) }}" class="mt-4 inline-flex w-full justify-center rounded-lg bg-gray-900 px-4 py-3 text-sm font-bold text-white">Edit Surat</a>
                    <form method="POST" action="{{ route('cabor.surat.submit', $surat) }}" class="mt-3">@csrf<button class="w-full rounded-lg bg-[#E60012] px-4 py-3 text-sm font-bold text-white hover:bg-red-700">Ajukan ke Staff KONI</button></form>
                    @if ($surat->status === \App\Models\Surat::STATUS_DRAFT)
                        <form method="POST" action="{{ route('cabor.surat.destroy', $surat) }}" class="mt-3" onsubmit="return confirm('Hapus draft surat ini?')">@csrf @method('DELETE')<button class="w-full rounded-lg border border-red-200 px-4 py-3 text-sm font-bold text-red-700">Hapus Draft</button></form>
                    @endif
                </section>
            @endif

            @if ($canStaffReview)
                <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                    <h3 class="font-black text-gray-900">Tindakan Staff KONI</h3>
                    @if ($canStaffForward)
                        <form method="POST" action="{{ route('staff.surat.teruskan', $surat) }}" enctype="multipart/form-data" class="mt-4 space-y-3">
                            @csrf
                            <label class="block text-sm font-bold text-gray-700" for="catatan_staff_forward">Catatan untuk Admin KONI</label>
                            <textarea id="catatan_staff_forward" name="catatan_staff" rows="3" maxlength="3000" class="w-full rounded-lg border-gray-300 text-sm" placeholder="Catatan pemeriksaan atau pengantar (opsional)"></textarea>
                            <label class="block text-sm font-bold text-gray-700" for="lampiran_staff">Lampiran Staff (opsional)</label>
                            <input id="lampiran_staff" type="file" name="lampiran_staff" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="w-full rounded-lg border border-gray-300 p-2 text-xs">
                            <button class="w-full rounded-lg bg-[#E60012] px-4 py-3 text-sm font-bold text-white hover:bg-red-700">Teruskan ke Admin KONI</button>
                        </form>
                    @endif
                    <form method="POST" action="{{ route('staff.surat.revisi', $surat) }}" class="mt-5 space-y-3 border-t border-gray-100 pt-5">
                        @csrf
                        <label for="catatan_staff_revisi" class="block text-sm font-bold text-gray-700">{{ $surat->status === \App\Models\Surat::STATUS_PERLU_REVISI_STAFF ? 'Catatan revisi dari keputusan Admin' : 'Catatan revisi untuk Admin Cabor' }}</label>
                        <textarea id="catatan_staff_revisi" name="catatan_staff" rows="3" maxlength="3000" required class="w-full rounded-lg border-gray-300 text-sm" placeholder="Jelaskan bagian yang perlu diperbaiki">{{ old('catatan_staff', $surat->status === \App\Models\Surat::STATUS_PERLU_REVISI_STAFF ? $surat->catatan_admin : '') }}</textarea>
                        <button class="w-full rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm font-bold text-amber-900">{{ $surat->status === \App\Models\Surat::STATUS_PERLU_REVISI_STAFF ? 'Kirim Catatan Revisi ke Admin Cabor' : 'Minta Revisi Admin Cabor' }}</button>
                    </form>
                </section>
            @endif

            @if ($canAdminDecide)
                <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                    <h3 class="font-black text-gray-900">Keputusan Admin KONI</h3>
                    <p class="mt-1 text-xs leading-5 text-gray-500">Keputusan akan dikirim kembali kepada Staff KONI. Staff yang menyampaikan hasil kepada Admin Cabor.</p>
                    <form method="POST" action="{{ route('admin.persuratan.keputusan', $surat) }}" enctype="multipart/form-data" class="mt-4 space-y-3">
                        @csrf
                        <label for="keputusan_admin" class="block text-sm font-bold text-gray-700">Keputusan</label>
                        <select id="keputusan_admin" name="keputusan_admin" required class="w-full rounded-lg border-gray-300 text-sm">
                            <option value="">Pilih keputusan</option>
                            <option value="diterima">Disetujui / Diterima</option>
                            <option value="ditolak">Ditolak</option>
                            <option value="revisi">Perlu Revisi</option>
                        </select>
                        <label for="catatan_admin" class="block text-sm font-bold text-gray-700">Catatan keputusan <span class="font-normal text-gray-500">(wajib untuk ditolak/revisi)</span></label>
                        <textarea id="catatan_admin" name="catatan_admin" rows="4" maxlength="3000" class="w-full rounded-lg border-gray-300 text-sm" placeholder="Alasan atau arahan keputusan"></textarea>
                        <label for="file_balasan" class="block text-sm font-bold text-gray-700">Dokumen balasan (opsional)</label>
                        <input id="file_balasan" type="file" name="file_balasan" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="w-full rounded-lg border border-gray-300 p-2 text-xs">
                        <button class="w-full rounded-lg bg-[#E60012] px-4 py-3 text-sm font-bold text-white hover:bg-red-700">Simpan Keputusan</button>
                    </form>
                </section>
            @endif

            @if ($canStaffDeliver)
                <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                    <h3 class="font-black text-gray-900">Sampaikan Hasil ke Admin Cabor</h3>
                    <p class="mt-2 text-sm text-gray-600">Pastikan keputusan Admin KONI dan dokumen balasan sudah diperiksa sebelum disampaikan.</p>
                    <form method="POST" action="{{ route('staff.surat.sampaikan', $surat) }}" class="mt-4" onsubmit="return confirm('Sampaikan hasil keputusan ini kepada Admin Cabor?')">@csrf<button class="w-full rounded-lg bg-[#E60012] px-4 py-3 text-sm font-bold text-white hover:bg-red-700">Tandai Hasil Sudah Disampaikan</button></form>
                </section>
            @endif

            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <h3 class="font-black text-gray-900">Informasi Alur</h3>
                <ol class="mt-3 space-y-2 text-xs leading-5 text-gray-600">
                    <li>1. Admin Cabor mengajukan surat kepada Staff KONI.</li>
                    <li>2. Staff memeriksa dan meneruskan surat kepada Admin KONI, atau meminta revisi.</li>
                    <li>3. Admin KONI memberikan keputusan.</li>
                    <li>4. Staff menyampaikan keputusan kepada Admin Cabor dan menutup proses.</li>
                </ol>
            </section>
        </aside>
    </section>
</div>
@endsection
