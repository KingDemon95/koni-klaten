
@php
$isKetua = auth()->user()->hasRole('ketua_koni');
$items = [
    ['label' => 'Dashboard', 'route' => $isKetua ? 'dashboard.ketua-koni' : 'dashboard.admin-koni', 'active' => $isKetua ? 'dashboard.ketua-koni' : 'dashboard.admin-koni'],
    ['label' => 'Cabang Olahraga', 'route' => 'admin.cabor.index', 'active' => 'admin.cabor.*'],
    ['label' => 'Data Atlet', 'route' => 'admin.atlet.index', 'active' => 'admin.atlet.*'],
    ['label' => 'Data Pelatih', 'route' => 'admin.pelatih.index', 'active' => 'admin.pelatih.*'],
    ['label' => 'Struktur Organisasi', 'route' => 'admin.struktur-organisasi.index', 'active' => 'admin.struktur-organisasi.*'],
    ['label' => 'Monitoring Prestasi', 'route' => 'admin.prestasi.index', 'active' => 'admin.prestasi.*'],
    ['label' => 'Laporan & Rekapitulasi', 'route' => 'admin.laporan.index', 'active' => 'admin.laporan.*'],
    ['label' => 'Inventaris', 'route' => 'admin.inventaris.index', 'active' => 'admin.inventaris.*'],
    ['label' => 'Riwayat Aktivitas', 'route' => 'admin.audit.index', 'active' => 'admin.audit.*'],
    ['label' => 'Agenda & Kejuaraan', 'route' => 'admin.agenda.index', 'active' => 'admin.agenda.*'],
    ['label' => 'Persuratan', 'route' => 'admin.persuratan.index', 'active' => 'admin.persuratan.*'],
    ['label' => 'Template Surat', 'route' => 'admin.templates.index', 'active' => 'admin.templates.*'],
    ['label' => 'Publikasi & Berita', 'route' => 'admin.publikasi.index', 'active' => 'admin.publikasi.*'],
];

if (!$isKetua) {
    array_splice($items, 4, 0, [[
        'label' => 'Master Data',
        'route' => 'admin.master-data.index',
        'active' => 'admin.master-data.*',
    ]]);
    $items[] = ['label' => 'Tampilan Dashboard Internal', 'route' => 'admin.dashboard-appearance.edit', 'active' => 'admin.dashboard-appearance.*'];
    $items[] = ['label' => 'Tampilan Website Publik', 'route' => 'admin.website-appearance.edit', 'active' => 'admin.website-appearance.*'];
    $items[] = ['label' => 'Manajemen Pengguna', 'route' => 'admin.pengguna.index', 'active' => 'admin.pengguna.*'];
    $items[] = ['label' => 'Kesehatan Sistem', 'route' => 'admin.system-health', 'active' => 'admin.system-health'];
    $items[] = ['label' => 'Profil & Pengaturan KONI', 'route' => 'admin.settings', 'active' => 'admin.settings'];
}

$items[] = ['label' => 'Absensi QR & Selfie', 'route' => 'admin.absensi.index', 'active' => 'admin.absensi.*'];
$items[] = ['label' => 'Keamanan Akun', 'route' => 'account.password.edit', 'active' => 'account.password.*'];
@endphp

@foreach ($items as $item)
    <a
        href="{{ route($item['route']) }}"
        class="group flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition
        {{ request()->routeIs($item['active'])
            ? 'bg-[#E60012] text-white shadow-lg shadow-red-950/20'
            : 'text-gray-400 hover:bg-white/10 hover:text-white' }}"
    >
        <span class="text-xs font-bold opacity-70">
            {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
        </span>

        <span>{{ $item['label'] }}</span>
    </a>
@endforeach
