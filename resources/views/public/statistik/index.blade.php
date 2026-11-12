@extends('layouts.public')

@section('title', 'Statistik Prestasi')

@section('content')
@php
    $maxTrend = max(1, (int) ($trend->max('jumlah') ?? 0));
    $maxRanking = max(1, (int) ($ranking->max('prestasi_terverifikasi_count') ?? 0));
    $tingkatLabels = [
        'lokal' => 'Lokal',
        'kabupaten' => 'Kabupaten',
        'provinsi' => 'Provinsi',
        'nasional' => 'Nasional',
        'internasional' => 'Internasional',
    ];
@endphp

<section class="bg-[#17191F] text-white">
    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
        <p class="text-xs font-black tracking-[0.22em] text-red-300">TRANSPARANSI CAPAIAN OLAHRAGA</p>
        <h1 class="mt-3 max-w-3xl text-3xl font-black tracking-tight sm:text-5xl">Statistik Prestasi KONI Kabupaten Klaten</h1>
        <p class="mt-5 max-w-2xl text-sm leading-7 text-slate-300 sm:text-base">Rekap prestasi yang telah diverifikasi Admin Cabor, termasuk capaian atlet dan pelatih dari cabang olahraga aktif.</p>
        <form method="GET" action="{{ route('public.statistik.index') }}" class="mt-8 flex flex-col gap-3 rounded-2xl border border-white/10 bg-white/5 p-4 sm:flex-row sm:items-end">
            <div class="flex-1">
                <label for="tahun" class="mb-2 block text-xs font-bold text-slate-200">Filter tahun kejuaraan</label>
                <select id="tahun" name="tahun" class="w-full rounded-xl border-0 bg-white px-4 py-3 text-sm font-semibold text-slate-900 focus:ring-2 focus:ring-red-400">
                    <option value="">Semua tahun</option>
                    @foreach($availableYears as $year)
                        <option value="{{ $year }}" @selected($selectedYear === $year)>{{ $year }}</option>
                    @endforeach
                </select>
            </div>
            <button class="rounded-xl bg-[#D71920] px-5 py-3 text-sm font-bold text-white transition hover:bg-red-700">Terapkan filter</button>
            @if($selectedYear)
                <a href="{{ route('public.statistik.index') }}" class="rounded-xl border border-white/20 px-5 py-3 text-center text-sm font-bold text-white hover:bg-white/10">Reset</a>
            @endif
        </form>
        @if($selectedYear)
            <p class="mt-3 text-xs font-semibold text-red-200">Menampilkan data tahun {{ $selectedYear }}.</p>
        @else
            <p class="mt-3 text-xs text-slate-400">Menampilkan seluruh tahun yang tercatat.</p>
        @endif
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
        @foreach([
            ['label' => 'Cabang olahraga aktif', 'value' => $statistics['cabor']],
            ['label' => 'Atlet aktif', 'value' => $statistics['atlet']],
            ['label' => 'Pelatih aktif', 'value' => $statistics['pelatih']],
            ['label' => 'Prestasi terverifikasi', 'value' => $statistics['prestasi']],
        ] as $stat)
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-3xl font-black text-slate-950">{{ number_format($stat['value'], 0, ',', '.') }}</p>
                <p class="mt-2 text-xs font-semibold leading-5 text-slate-500">{{ $stat['label'] }}</p>
            </article>
        @endforeach
    </div>

    <section class="mt-12">
        <div class="mb-5">
            <p class="text-xs font-black tracking-[0.2em] text-[#D71920]">PEROLEHAN MEDALI</p>
            <h2 class="mt-2 text-2xl font-black text-slate-950">Medali yang tercatat</h2>
            <p class="mt-2 text-sm leading-6 text-slate-500">Medali dihitung berdasarkan jenis medali yang dipilih saat prestasi diunggah. Prestasi lama tanpa klasifikasi medali tetap masuk total prestasi, tetapi tidak ditambahkan ke hitungan medali.</p>
        </div>
        <div class="grid gap-3 sm:grid-cols-3">
            @foreach([
                ['label' => 'Emas', 'value' => $statistics['emas'], 'style' => 'border-amber-200 bg-amber-50 text-amber-900'],
                ['label' => 'Perak', 'value' => $statistics['perak'], 'style' => 'border-slate-300 bg-slate-100 text-slate-800'],
                ['label' => 'Perunggu', 'value' => $statistics['perunggu'], 'style' => 'border-orange-200 bg-orange-50 text-orange-900'],
            ] as $medal)
                <article class="rounded-2xl border p-5 {{ $medal['style'] }}">
                    <p class="text-3xl font-black">{{ number_format($medal['value'], 0, ',', '.') }}</p>
                    <p class="mt-1 text-sm font-bold">Medali {{ $medal['label'] }}</p>
                </article>
            @endforeach
        </div>
    </section>

    <div class="mt-12 grid gap-8 lg:grid-cols-[1.15fr_.85fr]">
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div>
                <p class="text-xs font-black tracking-[0.2em] text-[#D71920]">TREN PRESTASI</p>
                <h2 class="mt-2 text-xl font-black text-slate-950">Perkembangan lima tahun</h2>
                <p class="mt-1 text-xs text-slate-500">Jumlah prestasi terverifikasi per tahun, dari cabang olahraga aktif. Grafik ini tetap menampilkan lima tahun terakhir meskipun filter tahun dipilih.</p>
            </div>
            <div class="mt-7 space-y-4">
                @foreach($trend as $item)
                    <div class="grid grid-cols-[3.5rem_1fr_2.5rem] items-center gap-3">
                        <span class="text-xs font-bold text-slate-500">{{ $item['tahun'] }}</span>
                        <div class="h-3 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full bg-[#D71920]" style="width: {{ $item['jumlah'] > 0 ? max(3, ($item['jumlah'] / $maxTrend) * 100) : 0 }}%"></div>
                        </div>
                        <span class="text-right text-xs font-black text-slate-900">{{ number_format($item['jumlah'], 0, ',', '.') }}</span>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div>
                <p class="text-xs font-black tracking-[0.2em] text-[#D71920]">TINGKAT KEJUARAAN</p>
                <h2 class="mt-2 text-xl font-black text-slate-950">Sebaran prestasi</h2>
            </div>
            <div class="mt-5 space-y-3">
                @forelse($tingkatLabels as $key => $label)
                    <div class="flex items-center justify-between gap-4 border-b border-slate-100 pb-3 last:border-0">
                        <span class="text-sm font-semibold text-slate-600">{{ $label }}</span>
                        <span class="rounded-lg bg-slate-100 px-3 py-1 text-sm font-black text-slate-900">{{ number_format((int) ($tingkatCounts[$key] ?? 0), 0, ',', '.') }}</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Belum ada data tingkat kejuaraan.</p>
                @endforelse
            </div>
        </section>
    </div>

    <section class="mt-12">
        <div class="mb-5">
            <p class="text-xs font-black tracking-[0.2em] text-[#D71920]">PERINGKAT CABANG OLAHRAGA</p>
            <h2 class="mt-2 text-2xl font-black text-slate-950">Capaian berdasarkan cabor</h2>
            <p class="mt-2 text-sm text-slate-500">Diurutkan berdasarkan jumlah prestasi terverifikasi, kemudian perolehan medali.</p>
        </div>
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            @forelse($ranking as $index => $cabor)
                <div class="grid grid-cols-[2rem_1fr_auto] items-center gap-3 border-b border-slate-100 p-4 last:border-0 sm:grid-cols-[2.5rem_1fr_9rem_8rem] sm:gap-5 sm:px-5">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-xs font-black text-slate-600">{{ $index + 1 }}</span>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-black text-slate-900">{{ $cabor->nama }}</p>
                        <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full bg-[#D71920]" style="width: {{ max(3, ($cabor->prestasi_terverifikasi_count / $maxRanking) * 100) }}%"></div>
                        </div>
                    </div>
                    <div class="col-span-2 flex flex-wrap justify-end gap-2 text-[11px] font-bold sm:col-span-1">
                        <span class="rounded-full bg-amber-50 px-2.5 py-1 text-amber-800">E {{ $cabor->medali_emas_count }}</span>
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-slate-700">P {{ $cabor->medali_perak_count }}</span>
                        <span class="rounded-full bg-orange-50 px-2.5 py-1 text-orange-800">B {{ $cabor->medali_perunggu_count }}</span>
                    </div>
                    <p class="col-start-2 text-xs font-semibold text-slate-500 sm:col-start-auto sm:text-right"><strong class="text-slate-900">{{ $cabor->prestasi_terverifikasi_count }}</strong> prestasi</p>
                </div>
            @empty
                <p class="p-8 text-center text-sm text-slate-500">Belum ada prestasi terverifikasi untuk periode ini.</p>
            @endforelse
        </div>
    </section>

    <section class="mt-12">
        <div class="mb-5">
            <p class="text-xs font-black tracking-[0.2em] text-[#D71920]">CATATAN PRESTASI</p>
            <h2 class="mt-2 text-2xl font-black text-slate-950">Prestasi terbaru</h2>
        </div>
        <div class="grid gap-4 md:grid-cols-2">
            @forelse($recentPrestasis as $prestasi)
                <article class="rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full bg-red-50 px-3 py-1 text-[10px] font-black uppercase tracking-wide text-[#D71920]">{{ $prestasi->tingkat }}</span>
                        @if($prestasi->jenis_medali)
                            <span class="rounded-full bg-amber-50 px-3 py-1 text-[10px] font-black text-amber-800">Medali {{ ucfirst($prestasi->jenis_medali) }}</span>
                        @endif
                    </div>
                    <h3 class="mt-3 font-black text-slate-950">{{ $prestasi->nama_kejuaraan }}</h3>
                    <p class="mt-1 text-sm text-slate-600">{{ $prestasi->atlet?->nama_lengkap ?? $prestasi->pelatih?->nama_lengkap ?? 'Atlet/Pelatih KONI Klaten' }}</p>
                    <p class="mt-3 text-xs text-slate-500">{{ $prestasi->cabangOlahraga?->nama }} · {{ $prestasi->tanggal_prestasi?->format('d/m/Y') }}</p>
                    <p class="mt-2 text-sm font-bold text-[#D71920]">{{ $prestasi->hasil }}</p>
                </article>
            @empty
                <p class="col-span-full rounded-2xl border border-dashed border-slate-300 p-8 text-center text-sm text-slate-500">Belum ada prestasi terverifikasi untuk ditampilkan.</p>
            @endforelse
        </div>
    </section>
</section>
@endsection
