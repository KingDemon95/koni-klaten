
@php
$items = [
    [
        'label' => 'Dashboard',
        'route' => 'dashboard.admin-koni',
        'active' => 'dashboard.admin-koni',
        'icon' => '01',
    ],
    [
        'label' => 'Cabang Olahraga',
        'route' => 'admin.cabor.index',
        'active' => 'admin.cabor.*',
        'icon' => '02',
    ],
    [
        'label' => 'Data Atlet',
        'route' => 'admin.atlet.index',
        'active' => 'admin.atlet.*',
        'icon' => '03',
    ],
    [
        'label' => 'Data Pelatih',
        'route' => 'admin.pelatih.index',
        'active' => 'admin.pelatih.*',
        'icon' => '04',
    ],
    [
        'label' => 'Struktur Organisasi',
        'route' => 'admin.struktur-organisasi.index',
        'active' => 'admin.struktur-organisasi.*',
        'icon' => '05',
    ],
    [
        'label' => 'Monitoring Prestasi',
        'route' => 'admin.prestasi.index',
        'active' => 'admin.prestasi.*',
        'icon' => '06',
    ],
    [
        'label' => 'Laporan & Rekapitulasi',
        'route' => 'admin.laporan.index',
        'active' => 'admin.laporan.*',
        'icon' => '07',
    ],
    [
        'label' => 'Agenda & Kejuaraan',
        'route' => 'admin.agenda.index',
        'active' => 'admin.agenda.*',
        'icon' => '08',
    ],
    [
        'label' => 'Persuratan',
        'route' => 'admin.persuratan.index',
        'active' => 'admin.persuratan.*',
        'icon' => '09',
    ],
    [
        'label' => 'Template Surat',
        'route' => 'admin.templates.index',
        'active' => 'admin.templates.*',
        'icon' => '10',
    ],
    [
        'label' => 'Publikasi & Berita',
        'route' => 'admin.publikasi.index',
        'active' => 'admin.publikasi.*',
        'icon' => '11',
    ],
    [
        'label' => 'Manajemen Pengguna',
        'route' => 'admin.pengguna.index',
        'active' => 'admin.pengguna.*',
        'icon' => '12',
    ],
    [
        'label' => 'Profil & Pengaturan KONI',
        'route' => 'admin.settings',
        'active' => 'admin.settings',
        'icon' => '13',
    ],
];
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
            {{ $item['icon'] }}
        </span>

        <span>{{ $item['label'] }}</span>
    </a>
@endforeach
