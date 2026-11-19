@php
    $items = [
        ['label' => 'Dashboard', 'route' => 'dashboard.sekretaris-koni', 'active' => 'dashboard.sekretaris-koni'],
        ['label' => 'Cabang Olahraga', 'route' => 'sekretaris.cabor.index', 'active' => 'sekretaris.cabor.*'],
        ['label' => 'Data Atlet', 'route' => 'sekretaris.atlet.index', 'active' => 'sekretaris.atlet.*'],
        ['label' => 'Data Pelatih', 'route' => 'sekretaris.pelatih.index', 'active' => 'sekretaris.pelatih.*'],
        ['label' => 'Struktur Organisasi', 'route' => 'sekretaris.pengurus.index', 'active' => 'sekretaris.pengurus.*'],
        ['label' => 'Persuratan & Persetujuan', 'route' => 'sekretaris.surat.index', 'active' => 'sekretaris.surat.*'],
        ['label' => 'Template Surat', 'route' => 'sekretaris.templates.index', 'active' => 'sekretaris.templates.*'],
        ['label' => 'Laporan & Rekapitulasi', 'route' => 'sekretaris.laporan.index', 'active' => 'sekretaris.laporan.*'],
        ['label' => 'Inventaris', 'route' => 'sekretaris.inventaris.index', 'active' => 'sekretaris.inventaris.*'],
        ['label' => 'Riwayat Aktivitas', 'route' => 'sekretaris.audit.index', 'active' => 'sekretaris.audit.*'],
    ];
@endphp

@foreach ($items as $item)
    <a href="{{ route($item['route']) }}"
       class="group flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition {{ request()->routeIs($item['active']) ? 'bg-[#E60012] text-white shadow-lg shadow-red-950/20' : 'text-gray-400 hover:bg-white/10 hover:text-white' }}">
        <span class="text-xs font-bold opacity-70">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
        <span>{{ $item['label'] }}</span>
    </a>
@endforeach
