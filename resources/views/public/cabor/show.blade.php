@extends('layouts.public')
@section('title', $cabangOlahraga->nama)
@section('content')
@php($copy = $websitePageSections ?? [])
<article class="website-section-stack space-y-14">
    @if($copy['hero']['visible'] ?? true)
        @php($hero = $copy['hero'])
        <header class="public-page-hero website-managed-section text-white" style="order: {{ $hero['order'] ?? 10 }}">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8">
                @if(($hero['button_label'] ?? '') !== '')<a href="{{ \App\Models\WebsiteAppearanceSetting::buttonUrl($hero['button_target'] ?? 'cabor') }}" class="website-hero-eyebrow text-sm font-bold hover:underline">← {{ $hero['button_label'] }}</a>@endif
                <div class="mt-6">@include('public.partials.managed-heading', ['sectionKey' => 'hero', 'headingTag' => 'h1', 'dynamicTitle' => $cabangOlahraga->nama, 'dynamicDescription' => $cabangOlahraga->deskripsi ?? ''])</div>
                @if($cabangOlahraga->singkatan)<p class="website-hero-description mt-2 text-sm font-semibold">{{ $cabangOlahraga->singkatan }}</p>@endif
            </div>
        </header>
    @endif

        @if($copy['athletes']['visible'] ?? true)
            @php($athletesCopy = $copy['athletes'])
            <section class="website-managed-section website-section-surface" style="order: {{ $athletesCopy['order'] ?? 20 }}">
                @include('public.partials.managed-heading', ['sectionKey' => 'athletes', 'wrapperClass' => 'mb-6'])
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @forelse($atlets as $atlet)
                        <article class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-4"><div class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-full bg-slate-100 text-lg font-black text-slate-500">@if($atlet->foto_path)<img src="{{ asset('storage/'.$atlet->foto_path) }}" alt="Foto {{ $atlet->nama_lengkap }}" class="h-full w-full object-cover">@else{{ strtoupper(substr($atlet->nama_lengkap, 0, 1)) }}@endif</div><div class="min-w-0"><h3 class="truncate text-sm font-bold text-slate-900">{{ $atlet->nama_lengkap }}</h3><p class="mt-1 text-xs text-slate-500">Atlet {{ $cabangOlahraga->nama }}</p></div></article>
                    @empty
                        <p class="col-span-full rounded-xl bg-white p-5 text-sm text-slate-500">Belum ada data atlet aktif yang dipublikasikan.</p>
                    @endforelse
                </div>
            </section>
        @endif

        @if($copy['achievements']['visible'] ?? true)
            @php($achievementsCopy = $copy['achievements'])
            <section class="website-managed-section website-section-surface" style="order: {{ $achievementsCopy['order'] ?? 30 }}">
                @include('public.partials.managed-heading', ['sectionKey' => 'achievements', 'wrapperClass' => 'mb-6'])
                <div class="grid gap-4 md:grid-cols-2">
                    @forelse($prestasis as $prestasi)
                        <article class="rounded-2xl border border-slate-200 bg-white p-5"><p class="website-page-accent text-xs font-black uppercase tracking-wider">{{ ucfirst($prestasi->tingkat) }}</p><h3 class="mt-2 font-black text-slate-950">{{ $prestasi->nama_kejuaraan }}</h3><p class="mt-1 text-sm text-slate-600">{{ $prestasi->atlet?->nama_lengkap ?? $prestasi->pelatih?->nama_lengkap ?? 'Atlet/Pelatih KONI Klaten' }}</p><p class="mt-3 text-xs text-slate-500">{{ $prestasi->tanggal_prestasi?->format('d/m/Y') }}</p><p class="mt-2 text-sm font-bold text-[#D71920]">{{ $prestasi->hasil }}</p></article>
                    @empty
                        <p class="col-span-full rounded-xl bg-white p-5 text-sm text-slate-500">Belum ada prestasi yang diverifikasi untuk dipublikasikan.</p>
                    @endforelse
                </div>
            </section>
        @endif

        @if($copy['coaches']['visible'] ?? true)
            @php($coachesCopy = $copy['coaches'])
            <section class="website-managed-section website-section-surface" style="order: {{ $coachesCopy['order'] ?? 40 }}">
                @include('public.partials.managed-heading', ['sectionKey' => 'coaches', 'wrapperClass' => 'mb-6'])
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @forelse($pelatihs as $pelatih)
                        <article class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-4"><div class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-full bg-slate-100 text-lg font-black text-slate-500">@if($pelatih->foto_path)<img src="{{ asset('storage/'.$pelatih->foto_path) }}" alt="Foto {{ $pelatih->nama_lengkap }}" class="h-full w-full object-cover">@else{{ strtoupper(substr($pelatih->nama_lengkap, 0, 1)) }}@endif</div><div class="min-w-0"><h3 class="truncate text-sm font-bold text-slate-900">{{ $pelatih->nama_lengkap }}</h3><p class="mt-1 text-xs text-slate-500">{{ $pelatih->spesialisasi ?: 'Pelatih' }}</p></div></article>
                    @empty
                        <p class="col-span-full rounded-xl bg-white p-5 text-sm text-slate-500">Belum ada data pelatih aktif yang dipublikasikan.</p>
                    @endforelse
                </div>
            </section>
        @endif

        @if($copy['management']['visible'] ?? true)
            @php($managementCopy = $copy['management'])
            <section class="website-managed-section website-section-surface" style="order: {{ $managementCopy['order'] ?? 50 }}">
                @include('public.partials.managed-heading', ['sectionKey' => 'management', 'wrapperClass' => 'mb-6'])
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @forelse($penguruses as $pengurus)
                        <article class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-4"><div class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-full bg-slate-100 text-lg font-black text-slate-500">@if($pengurus->foto_path)<img src="{{ asset('storage/'.$pengurus->foto_path) }}" alt="Foto {{ $pengurus->nama_lengkap }}" class="h-full w-full object-cover">@else{{ strtoupper(substr($pengurus->nama_lengkap, 0, 1)) }}@endif</div><div class="min-w-0"><h3 class="truncate text-sm font-bold text-slate-900">{{ $pengurus->nama_lengkap }}</h3><p class="website-page-accent mt-1 text-xs font-semibold">{{ $pengurus->jabatan }}</p>@if($pengurus->bidang)<p class="mt-1 text-xs text-slate-500">{{ $pengurus->bidang }}</p>@endif</div></article>
                    @empty
                        <p class="col-span-full rounded-xl bg-white p-5 text-sm text-slate-500">Data struktur pengurus cabang olahraga belum tersedia.</p>
                    @endforelse
                </div>
            </section>
        @endif
</article>
@endsection
