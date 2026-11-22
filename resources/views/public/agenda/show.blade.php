@extends('layouts.public')
@section('title', $agenda->judul)
@section('content')
@php($copy = $websitePageSections ?? [])
<article class="website-section-stack">
    @if($copy['hero']['visible'] ?? true)
        @php($hero = $copy['hero'])
        <header class="public-page-hero website-managed-section text-white" style="order: {{ $hero['order'] ?? 10 }}">
            <div class="mx-auto max-w-4xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8">
                @if(($hero['button_label'] ?? '') !== '')<a href="{{ \App\Models\WebsiteAppearanceSetting::buttonUrl($hero['button_target'] ?? 'agenda') }}" class="website-hero-eyebrow text-sm font-bold hover:underline">← {{ $hero['button_label'] }}</a>@endif
                <div class="mt-6 flex flex-wrap items-center gap-3"><span class="rounded-full bg-white/10 px-3 py-1 text-xs font-black uppercase tracking-wide text-red-200">{{ $agenda->jenis }}</span><span class="website-hero-description text-sm">{{ $agenda->tanggal_mulai?->translatedFormat('d F Y') }}@if($agenda->tanggal_selesai) sampai {{ $agenda->tanggal_selesai->translatedFormat('d F Y') }}@endif</span></div>
                @include('public.partials.managed-heading', ['sectionKey' => 'hero', 'headingTag' => 'h1', 'dynamicTitle' => $agenda->judul])
                <div class="website-hero-description mt-5 flex flex-wrap gap-x-6 gap-y-2 text-sm"><span>Lokasi: {{ $agenda->lokasi ?: 'Lokasi belum dicantumkan' }}</span><span>Cabang: {{ $agenda->cabangOlahraga?->nama ?? 'Kegiatan KONI Klaten' }}</span>@if($agenda->waktu_mulai)<span>Waktu: {{ substr($agenda->waktu_mulai, 0, 5) }} WIB</span>@endif</div>
            </div>
        </header>
    @endif

    @if($copy['details']['visible'] ?? true)
        @php($details = $copy['details'])
        <section class="website-managed-section website-section-surface mx-auto w-full max-w-4xl px-4 py-10 sm:px-6 lg:px-8" style="order: {{ $details['order'] ?? 20 }}">
            @include('public.partials.managed-heading', ['sectionKey' => 'details', 'wrapperClass' => 'mb-6'])
            @if($agenda->poster_path)<img src="{{ asset('storage/'.$agenda->poster_path) }}" alt="Poster {{ $agenda->judul }}" class="max-h-[520px] w-full rounded-2xl border border-slate-200 object-cover">@endif
            <div class="prose prose-slate mt-8 max-w-none whitespace-pre-line leading-8">{{ $agenda->deskripsi ?: 'Informasi kegiatan KONI Kabupaten Klaten.' }}</div>
        </section>
    @endif
</article>
@endsection
