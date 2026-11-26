@php
    $isKetua = auth()->user()->hasRole('ketua_koni');
    $dashboardRoute = $isKetua ? 'dashboard.ketua-koni' : 'dashboard.admin-koni';

    $groups = [
        [
            'label' => 'Manajemen Data',
            'items' => [
                ['label' => 'Cabang Olahraga', 'route' => 'admin.cabor.index', 'active' => 'admin.cabor.*'],
                ['label' => 'Data Atlet', 'route' => 'admin.atlet.index', 'active' => 'admin.atlet.*'],
                ['label' => 'Data Pelatih', 'route' => 'admin.pelatih.index', 'active' => 'admin.pelatih.*'],
                ['label' => 'Struktur Organisasi', 'route' => 'admin.struktur-organisasi.index', 'active' => 'admin.struktur-organisasi.*'],
            ],
        ],
        [
            'label' => 'Monitoring',
            'items' => [
                ['label' => 'Monitoring Prestasi', 'route' => 'admin.prestasi.index', 'active' => 'admin.prestasi.*'],
                ['label' => 'Laporan & Rekapitulasi', 'route' => 'admin.laporan.index', 'active' => 'admin.laporan.*'],
                ['label' => 'Inventaris', 'route' => 'admin.inventaris.index', 'active' => 'admin.inventaris.*'],
                ['label' => 'Riwayat Aktivitas', 'route' => 'admin.audit.index', 'active' => 'admin.audit.*'],
            ],
        ],
        [
            'label' => 'Operasional',
            'items' => [
                ['label' => 'Agenda & Kejuaraan', 'route' => 'admin.agenda.index', 'active' => 'admin.agenda.*'],
                ['label' => 'Persuratan', 'route' => 'admin.persuratan.index', 'active' => 'admin.persuratan.*'],
                ['label' => 'Template Surat', 'route' => 'admin.templates.index', 'active' => 'admin.templates.*'],
                ['label' => 'Absensi QR & Selfie', 'route' => 'admin.absensi.index', 'active' => 'admin.absensi.index'],
                ['label' => 'Atur Jadwal Absensi', 'route' => 'admin.absensi.jadwal-kerja', 'active' => 'admin.absensi.jadwal-kerja*'],
            ],
        ],
        [
            'label' => 'Publikasi',
            'items' => [
                ['label' => 'Publikasi & Berita', 'route' => 'admin.publikasi.index', 'active' => 'admin.publikasi.*'],
            ],
        ],
    ];

    if (!$isKetua) {
        array_unshift($groups[0]['items'], [
            'label' => 'Master Data',
            'route' => 'admin.master-data.index',
            'active' => 'admin.master-data.*',
        ]);

        $groups[] = [
            'label' => 'Pengaturan',
            'items' => [
                ['label' => 'Tampilan Dashboard Internal', 'route' => 'admin.dashboard-appearance.edit', 'active' => 'admin.dashboard-appearance.*'],
                ['label' => 'Tampilan Website Publik', 'route' => 'admin.website-appearance.edit', 'active' => 'admin.website-appearance.*'],
                ['label' => 'Manajemen Pengguna', 'route' => 'admin.pengguna.index', 'active' => 'admin.pengguna.*'],
                ['label' => 'Kesehatan Sistem', 'route' => 'admin.system-health', 'active' => 'admin.system-health'],
                ['label' => 'Profil & Pengaturan KONI', 'route' => 'admin.settings', 'active' => 'admin.settings'],
            ],
        ];
    }

    $isAccountActive = request()->routeIs('account.password.*');
@endphp

<a
    href="{{ route($dashboardRoute) }}"
    class="group flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition
    {{ request()->routeIs($dashboardRoute)
        ? 'bg-[#E60012] text-white shadow-lg shadow-red-950/20'
        : 'text-gray-400 hover:bg-white/10 hover:text-white' }}"
>
    <span class="text-xs font-bold opacity-70">01</span>
    <span>Dashboard</span>
</a>

@foreach ($groups as $group)
    @php
        $groupIsActive = collect($group['items'])->contains(
            fn ($item) => request()->routeIs($item['active'])
        );
    @endphp

    <details class="group rounded-xl" {{ $groupIsActive ? 'open' : '' }}>
        <summary class="flex cursor-pointer list-none items-center justify-between gap-3 rounded-xl px-4 py-3 text-sm font-semibold transition
            {{ $groupIsActive ? 'bg-white/10 text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white' }}">
            <span>{{ $group['label'] }}</span>
            <svg class="h-4 w-4 shrink-0 transition-transform group-open:rotate-180" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.09 1.032l-4.25 4.3a.75.75 0 01-1.07 0l-4.25-4.3a.75.75 0 01.02-1.052z" clip-rule="evenodd"/>
            </svg>
        </summary>

        <div class="ml-3 mt-1 space-y-1 border-l border-white/15 py-1 pl-3">
            @foreach ($group['items'] as $item)
                <a
                    href="{{ route($item['route']) }}"
                    class="block rounded-lg px-3 py-2.5 text-sm font-medium transition
                    {{ request()->routeIs($item['active'])
                        ? 'bg-[#E60012] text-white shadow-lg shadow-red-950/20'
                        : 'text-gray-400 hover:bg-white/10 hover:text-white' }}"
                >
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>
    </details>
@endforeach

<a
    href="{{ route('account.password.edit') }}"
    class="group flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition
    {{ $isAccountActive
        ? 'bg-[#E60012] text-white shadow-lg shadow-red-950/20'
        : 'text-gray-400 hover:bg-white/10 hover:text-white' }}"
>
    <span>Keamanan Akun</span>
</a>
