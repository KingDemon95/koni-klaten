@extends('layouts.public')
@section('title', 'Statistik Prestasi')
@section('content')
@php
    $copy = $websitePageSections ?? [];
    $maxTrend = max(1, (int) ($trend->max('jumlah') ?? 0));
    $maxRanking = max(1, (int) ($ranking->max('prestasi_terverifikasi_count') ?? 0));
    $tingkatLabels = ['lokal' => 'Lokal', 'kabupaten' => 'Kabupaten', 'provinsi' => 'Provinsi', 'nasional' => 'Nasional', 'internasional' => 'Internasional'];
@endphp

<div class="website-section-grid mx-auto w-full max-w-7xl grid-cols-1 gap-12 px-4 py-12 sm:px-6 lg:grid-cols-2 lg:px-8">
@if($copy['hero']['visible'] ?? true)
    @php($hero = $copy['hero'])
    <section class="public-page-hero website-managed-section text-white lg:col-span-2" style="order: {{ $hero['order'] ?? 10 }}">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
            @include('public.partials.managed-heading', ['sectionKey' => 'hero', 'headingTag' => 'h1'])
            <form method="GET" action="{{ route('public.statistik.index') }}" class="mt-8 flex flex-col gap-3 rounded-2xl border border-white/10 bg-white/5 p-4 sm:flex-row sm:items-end">
                <div class="flex-1"><label for="tahun" class="website-hero-description mb-2 block text-xs font-bold">{{ $hero['filter_label'] }}</label><select id="tahun" name="tahun" class="w-full rounded-xl border-0 bg-white px-4 py-3 text-sm font-semibold text-slate-900 focus:ring-2 focus:ring-red-400"><option value="">Semua tahun</option>@foreach($availableYears as $year)<option value="{{ $year }}" @selected($selectedYear === $year)>{{ $year }}</option>@endforeach</select></div>
                <button class="website-hero-button rounded-xl px-5 py-3 text-sm font-bold transition">{{ $hero['button_label'] ?: 'Terapkan filter tahun' }}</button>
                @if($selectedYear)<a href="{{ route('public.statistik.index') }}" class="rounded-xl border border-white/20 px-5 py-3 text-center text-sm font-bold text-white hover:bg-white/10">{{ $hero['reset_label'] }}</a>@endif
            </form>
            @if($selectedYear)<p class="website-hero-eyebrow mt-3 text-xs font-semibold">{{ $hero['selected_year_prefix'] }} {{ $selectedYear }}.</p>@else<p class="website-hero-description mt-3 text-xs">{{ $hero['all_years_text'] }}</p>@endif
        </div>
    </section>
@endif

    @if($copy['overview']['visible'] ?? true)
        @php($overview = $copy['overview'])
        <section class="website-managed-section website-section-surface lg:col-span-2" style="order: {{ $overview['order'] ?? 20 }}">
            @include('public.partials.managed-heading', ['sectionKey' => 'overview', 'wrapperClass' => 'mb-5'])
            <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
                @foreach([['label' => $overview['cabor_label'], 'value' => $statistics['cabor']], ['label' => $overview['athlete_label'], 'value' => $statistics['atlet']], ['label' => $overview['coach_label'], 'value' => $statistics['pelatih']], ['label' => $overview['achievement_label'], 'value' => $statistics['prestasi']]] as $stat)
                    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="website-section-title text-3xl font-black">{{ number_format($stat['value'], 0, ',', '.') }}</p><p class="website-section-description mt-2 text-xs font-semibold leading-5">{{ $stat['label'] }}</p></article>
                @endforeach
            </div>
        </section>
    @endif

    @if($copy['medals']['visible'] ?? true)
        @php($medalsCopy = $copy['medals'])
        <section class="website-managed-section website-section-surface lg:col-span-2" style="order: {{ $medalsCopy['order'] ?? 30 }}">
            @include('public.partials.managed-heading', ['sectionKey' => 'medals', 'wrapperClass' => 'mb-5'])
            <div class="grid gap-3 sm:grid-cols-3">
                @foreach([['label' => $medalsCopy['gold_label'], 'value' => $statistics['emas'], 'style' => 'border-amber-200 bg-amber-50 text-amber-900'], ['label' => $medalsCopy['silver_label'], 'value' => $statistics['perak'], 'style' => 'border-slate-300 bg-slate-100 text-slate-800'], ['label' => $medalsCopy['bronze_label'], 'value' => $statistics['perunggu'], 'style' => 'border-orange-200 bg-orange-50 text-orange-900']] as $medal)
                    <article class="rounded-2xl border p-5 {{ $medal['style'] }}"><p class="text-3xl font-black">{{ number_format($medal['value'], 0, ',', '.') }}</p><p class="mt-1 text-sm font-bold">Medali {{ $medal['label'] }}</p></article>
                @endforeach
            </div>
        </section>
    @endif

    @if($copy['trends']['visible'] ?? true)
        @php($trendsCopy = $copy['trends'])
        <section class="website-managed-section website-section-surface rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" style="order: {{ $trendsCopy['order'] ?? 40 }}">
            @include('public.partials.managed-heading', ['sectionKey' => 'trends', 'wrapperClass' => 'mb-5'])
            <div class="space-y-4">
                @foreach($trend as $item)
                    <div class="grid grid-cols-[3.5rem_1fr_2.5rem] items-center gap-3"><span class="text-xs font-bold text-slate-500">{{ $item['tahun'] }}</span><div class="h-3 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full" style="width: {{ $item['jumlah'] > 0 ? max(3, ($item['jumlah'] / $maxTrend) * 100) : 0 }}%; background: var(--website-page-accent-color, var(--website-primary, #D71920))"></div></div><span class="text-right text-xs font-black text-slate-900">{{ number_format($item['jumlah'], 0, ',', '.') }}</span></div>
                @endforeach
            </div>
        </section>
    @endif

    @if($copy['levels']['visible'] ?? true)
        @php($levelsCopy = $copy['levels'])
        <section class="website-managed-section website-section-surface rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" style="order: {{ $levelsCopy['order'] ?? 50 }}">
            @include('public.partials.managed-heading', ['sectionKey' => 'levels', 'wrapperClass' => 'mb-5'])
            <div class="space-y-3">
                @forelse($tingkatLabels as $key => $label)
                    <div class="flex items-center justify-between gap-4 border-b border-slate-100 pb-3 last:border-0"><span class="text-sm font-semibold text-slate-600">{{ $label }}</span><span class="rounded-lg bg-slate-100 px-3 py-1 text-sm font-black text-slate-900">{{ number_format((int) ($tingkatCounts[$key] ?? 0), 0, ',', '.') }}</span></div>
                @empty
                    <p class="text-sm text-slate-500">Belum ada data tingkat kejuaraan.</p>
                @endforelse
            </div>
        </section>
    @endif

    @if($copy['ranking']['visible'] ?? true)
        @php($rankingCopy = $copy['ranking'])
        <section class="website-managed-section website-section-surface lg:col-span-2" style="order: {{ $rankingCopy['order'] ?? 60 }}">
            @include('public.partials.managed-heading', ['sectionKey' => 'ranking', 'wrapperClass' => 'mb-5'])
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                @forelse($ranking as $index => $cabor)
                    <div class="grid grid-cols-[2rem_1fr_auto] items-center gap-3 border-b border-slate-100 p-4 last:border-0 sm:grid-cols-[2.5rem_1fr_9rem_8rem] sm:gap-5 sm:px-5">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-xs font-black text-slate-600">{{ $index + 1 }}</span>
                        <div class="min-w-0"><p class="truncate text-sm font-black text-slate-900">{{ $cabor->nama }}</p><div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full" style="width: {{ max(3, ($cabor->prestasi_terverifikasi_count / $maxRanking) * 100) }}%; background: var(--website-page-accent-color, var(--website-primary, #D71920))"></div></div></div>
                        <div class="col-span-2 flex flex-wrap justify-end gap-2 text-[11px] font-bold sm:col-span-1"><span class="rounded-full bg-amber-50 px-2.5 py-1 text-amber-800">E {{ $cabor->medali_emas_count }}</span><span class="rounded-full bg-slate-100 px-2.5 py-1 text-slate-700">P {{ $cabor->medali_perak_count }}</span><span class="rounded-full bg-orange-50 px-2.5 py-1 text-orange-800">B {{ $cabor->medali_perunggu_count }}</span></div>
                        <p class="col-start-2 text-xs font-semibold text-slate-500 sm:col-start-auto sm:text-right"><strong class="text-slate-900">{{ $cabor->prestasi_terverifikasi_count }}</strong> {{ $rankingCopy['achievement_label'] }}</p>
                    </div>
                @empty
                    <p class="p-8 text-center text-sm text-slate-500">Belum ada prestasi terverifikasi untuk periode ini.</p>
                @endforelse
            </div>
        </section>
    @endif

    @if($copy['recent']['visible'] ?? true)
        @php($recentCopy = $copy['recent'])
        <section class="website-managed-section website-section-surface lg:col-span-2" style="order: {{ $recentCopy['order'] ?? 70 }}">
            @include('public.partials.managed-heading', ['sectionKey' => 'recent', 'wrapperClass' => 'mb-5'])
            <div class="grid gap-4 md:grid-cols-2">
                @forelse($recentPrestasis as $prestasi)
                    <article class="rounded-2xl border border-slate-200 bg-white p-5"><div class="flex flex-wrap items-center gap-2"><span class="rounded-full bg-red-50 px-3 py-1 text-[10px] font-black uppercase tracking-wide text-[#D71920]">{{ $prestasi->tingkat }}</span>@if($prestasi->jenis_medali)<span class="rounded-full bg-amber-50 px-3 py-1 text-[10px] font-black text-amber-800">Medali {{ ucfirst($prestasi->jenis_medali) }}</span>@endif</div><h3 class="mt-3 font-black text-slate-950">{{ $prestasi->nama_kejuaraan }}</h3><p class="mt-1 text-sm text-slate-600">{{ $prestasi->atlet?->nama_lengkap ?? $prestasi->pelatih?->nama_lengkap ?? 'Atlet/Pelatih KONI Klaten' }}</p><p class="mt-3 text-xs text-slate-500">{{ $prestasi->cabangOlahraga?->nama }} · {{ $prestasi->tanggal_prestasi?->format('d/m/Y') }}</p><p class="website-page-accent mt-2 text-sm font-bold">{{ $prestasi->hasil }}</p></article>
                @empty
                    <p class="col-span-full rounded-2xl border border-dashed border-slate-300 p-8 text-center text-sm text-slate-500">Belum ada prestasi terverifikasi untuk ditampilkan.</p>
                @endforelse
            </div>
        </section>
    @endif
</div>
@endsection
