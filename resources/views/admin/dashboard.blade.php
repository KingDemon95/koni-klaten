@extends('layouts.admin')

@section('title', 'Dashboard Admin KONI')
@section('heading', 'Dashboard')

@section('content')
<div class="space-y-8">
    <section class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#A90010] to-[#E60012] p-6 text-white shadow-lg sm:p-8">
        <div class="relative z-10 max-w-3xl">
            <p class="text-xs font-bold tracking-[0.25em] text-yellow-300">PUSAT KENDALI ORGANISASI</p>
            <h2 class="mt-3 text-2xl font-black sm:text-3xl">Selamat datang, {{ auth()->user()->name }}.</h2>
            <p class="mt-3 text-sm leading-relaxed text-red-100">Pantau data organisasi, akun, agenda, prestasi, persuratan, dan publikasi KONI Kabupaten Klaten dari satu tempat.</p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('admin.pengguna.create') }}" class="rounded-lg bg-white px-4 py-2.5 text-xs font-black text-[#B40012] hover:bg-red-50">+ Buat Akun Pengguna</a>
                <a href="{{ route('admin.settings') }}" class="rounded-lg border border-white/40 bg-white/10 px-4 py-2.5 text-xs font-bold text-white hover:bg-white/20">Kelola Profil KONI</a>
            </div>
        </div>
        <div class="absolute -right-12 -top-20 h-64 w-64 rounded-full border-[30px] border-white/10"></div>
        <div class="absolute -bottom-24 right-32 h-48 w-48 rounded-full bg-yellow-400/10 blur-2xl"></div>
    </section>

    <section>
        <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
            <div><h3 class="text-lg font-black">Ringkasan organisasi</h3><p class="mt-1 text-xs text-gray-500">Jumlah data terkini dari database aplikasi.</p></div>
            <span class="rounded-full bg-green-50 px-3 py-1 text-[10px] font-bold uppercase tracking-wide text-green-700">Data langsung</span>
        </div>
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([
                ['label' => 'Cabang olahraga aktif', 'value' => $stats['cabor_aktif'], 'note' => 'Cabang terdaftar', 'color' => 'border-red-500', 'route' => 'admin.cabor.index'],
                ['label' => 'Atlet aktif', 'value' => $stats['atlet_aktif'], 'note' => 'Atlet terdaftar', 'color' => 'border-amber-500', 'route' => 'admin.atlet.index'],
                ['label' => 'Pelatih aktif', 'value' => $stats['pelatih_aktif'], 'note' => 'Pelatih terdaftar', 'color' => 'border-blue-500', 'route' => 'admin.pelatih.index'],
                ['label' => 'Akun pengguna', 'value' => $stats['pengguna'], 'note' => $stats['pengguna_aktif'].' akun aktif', 'color' => 'border-emerald-500', 'route' => 'admin.pengguna.index'],
                ['label' => 'Prestasi terverifikasi', 'value' => $stats['prestasi_terverifikasi'], 'note' => 'Sudah disahkan Admin Cabor', 'color' => 'border-yellow-500', 'route' => 'admin.prestasi.index'],
                ['label' => 'Agenda dipublikasikan', 'value' => $stats['agenda_terbit'], 'note' => 'Tampil di portal publik', 'color' => 'border-indigo-500', 'route' => 'admin.agenda.index'],
                ['label' => 'Surat menunggu keputusan', 'value' => $stats['surat_menunggu_admin'], 'note' => 'Sudah diteruskan Staff', 'color' => 'border-orange-500', 'route' => 'admin.persuratan.index'],
                ['label' => 'Konten dipublikasikan', 'value' => $stats['publikasi_terbit'], 'note' => 'Berita dan dokumentasi', 'color' => 'border-purple-500', 'route' => 'admin.publikasi.index'],
            ] as $stat)
                <a href="{{ route($stat['route']) }}" class="rounded-xl border border-gray-200 border-l-4 {{ $stat['color'] }} bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <p class="text-xs font-semibold text-gray-500">{{ $stat['label'] }}</p>
                    <p class="mt-3 text-3xl font-black tabular-nums text-gray-900">{{ number_format($stat['value'], 0, ',', '.') }}</p>
                    <p class="mt-2 text-[11px] text-gray-400">{{ $stat['note'] }}</p>
                </a>
            @endforeach
        </div>
    </section>

    <section class="grid gap-5 xl:grid-cols-[1.2fr_.8fr]">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="flex items-start justify-between gap-3"><div><h3 class="font-black">Hal yang perlu dipantau</h3><p class="mt-1 text-xs text-gray-500">Ringkasan pekerjaan lintas modul.</p></div><span class="rounded-full bg-gray-100 px-3 py-1 text-[10px] font-bold text-gray-600">Monitoring</span></div>
            <div class="mt-5 divide-y divide-gray-100">
                @foreach ([
                    ['label' => 'Agenda diajukan Admin Cabor', 'value' => $queues['agenda_diajukan'], 'route' => 'admin.agenda.index'],
                    ['label' => 'Prestasi menunggu verifikasi Admin Cabor', 'value' => $queues['prestasi_menunggu'], 'route' => 'admin.prestasi.index'],
                    ['label' => 'Surat menunggu keputusan Admin KONI', 'value' => $queues['surat_menunggu_admin'], 'route' => 'admin.persuratan.index'],
                    ['label' => 'Keputusan surat menunggu disampaikan Staff', 'value' => $queues['surat_perlu_disampaikan'], 'route' => 'admin.persuratan.index'],
                    ['label' => 'Konten masih berupa draft', 'value' => $queues['publikasi_draft'], 'route' => 'admin.publikasi.index'],
                ] as $queue)
                    <a href="{{ route($queue['route']) }}" class="flex items-center justify-between gap-4 py-4 transition hover:text-[#D71920]">
                        <span class="text-sm font-medium text-gray-700">{{ $queue['label'] }}</span>
                        <span class="min-w-9 rounded-full bg-gray-100 px-3 py-1 text-center text-sm font-black tabular-nums text-gray-800">{{ $queue['value'] }}</span>
                    </a>
                @endforeach
            </div>
            <p class="mt-3 rounded-lg bg-blue-50 p-3 text-xs leading-5 text-blue-800">Verifikasi prestasi tetap menjadi kewenangan Admin Cabor sesuai cabang masing-masing. Panel ini hanya menampilkan jumlah pengajuan untuk pemantauan Admin KONI.</p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
            <h3 class="font-black">Komposisi akun berdasarkan role</h3>
            <p class="mt-1 text-xs text-gray-500">Seluruh akun dibuat dan dikelola melalui Manajemen Pengguna.</p>
            <div class="mt-5 space-y-4">
                @foreach ($roleCounts as $label => $count)
                    <div>
                        <div class="mb-1.5 flex items-center justify-between text-xs"><span class="font-semibold text-gray-600">{{ $label }}</span><span class="font-black tabular-nums text-gray-900">{{ number_format($count, 0, ',', '.') }}</span></div>
                        <div class="h-2 overflow-hidden rounded-full bg-gray-100"><div class="h-full rounded-full bg-[#D71920]" style="width: {{ $roleCounts ? ($count / max(array_sum($roleCounts), 1)) * 100 : 0 }}%"></div></div>
                    </div>
                @endforeach
            </div>
            <a href="{{ route('admin.pengguna.index') }}" class="mt-6 inline-flex text-xs font-black text-[#D71920] hover:underline">Buka manajemen pengguna →</a>
        </div>
    </section>

    <section class="grid gap-5 lg:grid-cols-2">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="flex items-center justify-between gap-3"><div><h3 class="font-black">Aktivitas persuratan terbaru</h3><p class="mt-1 text-xs text-gray-500">Riwayat alur surat melalui Staff KONI.</p></div><a href="{{ route('admin.persuratan.index') }}" class="text-xs font-bold text-[#D71920]">Lihat semua</a></div>
            <div class="mt-4 space-y-3">
                @forelse ($recentSuratActivities as $activity)
                    <div class="rounded-xl bg-gray-50 p-3">
                        <p class="text-sm font-bold text-gray-800">{{ $activity->surat?->perihal ?? 'Surat dihapus' }}</p>
                        <p class="mt-1 text-xs text-gray-500">{{ $activity->action }} · {{ $activity->actor?->name ?? 'Pengguna tidak tersedia' }}</p>
                        <p class="mt-1 text-[11px] text-gray-400">{{ $activity->created_at?->format('d/m/Y H:i') }}</p>
                    </div>
                @empty
                    <p class="rounded-xl border border-dashed border-gray-200 p-5 text-center text-xs text-gray-500">Belum ada aktivitas persuratan.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="flex items-center justify-between gap-3"><div><h3 class="font-black">Konten terakhir diperbarui</h3><p class="mt-1 text-xs text-gray-500">Berita dan dokumentasi yang dikelola.</p></div><a href="{{ route('admin.publikasi.index') }}" class="text-xs font-bold text-[#D71920]">Lihat semua</a></div>
            <div class="mt-4 space-y-3">
                @forelse ($recentPublikasis as $item)
                    <div class="flex items-start justify-between gap-3 rounded-xl bg-gray-50 p-3">
                        <div class="min-w-0"><p class="truncate text-sm font-bold text-gray-800">{{ $item->judul }}</p><p class="mt-1 text-xs text-gray-500">{{ \App\Models\Publikasi::jenisLabels()[$item->jenis] ?? ucfirst($item->jenis) }} · {{ $item->creator?->name ?? 'Pengguna tidak tersedia' }}</p></div>
                        <span class="shrink-0 rounded-full px-2.5 py-1 text-[10px] font-bold {{ $item->status === 'published' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">{{ $item->status === 'published' ? 'Terbit' : 'Draft' }}</span>
                    </div>
                @empty
                    <p class="rounded-xl border border-dashed border-gray-200 p-5 text-center text-xs text-gray-500">Belum ada konten yang dibuat.</p>
                @endforelse
            </div>
        </div>
    </section>
</div>
@endsection
