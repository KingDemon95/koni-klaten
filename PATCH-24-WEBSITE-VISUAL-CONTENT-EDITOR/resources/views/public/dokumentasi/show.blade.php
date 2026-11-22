@extends('layouts.public')
@section('title', $publikasi->judul)
@section('content')
@php($copy = $websitePageSections ?? [])
<article class="website-section-stack">
    @if($copy['hero']['visible'] ?? true)
        @php($hero = $copy['hero'])
        <header class="public-page-hero website-managed-section text-white" style="order: {{ $hero['order'] ?? 10 }}">
            <div class="mx-auto max-w-5xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8">
                @if(($hero['button_label'] ?? '') !== '')<a href="{{ \App\Models\WebsiteAppearanceSetting::buttonUrl($hero['button_target'] ?? 'dokumentasi') }}" class="website-hero-eyebrow text-sm font-bold hover:underline">← {{ $hero['button_label'] }}</a>@endif
                <div class="mt-6 flex flex-wrap items-center gap-3"><span class="rounded-full bg-white/10 px-3 py-1 text-xs font-black uppercase tracking-wide text-red-200">{{ $publikasi->jenis }}</span><span class="website-hero-description text-sm">{{ $publikasi->published_at?->translatedFormat('d F Y') }}</span></div>
                @include('public.partials.managed-heading', ['sectionKey' => 'hero', 'headingTag' => 'h1', 'dynamicTitle' => $publikasi->judul])
            </div>
        </header>
    @endif

    @if($copy['media']['visible'] ?? true)
        @php($media = $copy['media'])
        <section class="website-managed-section website-section-surface mx-auto w-full max-w-5xl px-4 py-10 sm:px-6 lg:px-8" style="order: {{ $media['order'] ?? 20 }}">
            @include('public.partials.managed-heading', ['sectionKey' => 'media', 'wrapperClass' => 'mb-6'])
            @if($publikasi->jenis === 'foto')
                <img src="{{ route('public.publikasi.media', $publikasi->slug) }}" alt="{{ $publikasi->judul }}" class="max-h-[680px] w-full rounded-2xl border border-slate-200 object-contain bg-slate-100">
            @else
                <video controls preload="metadata" poster="{{ $publikasi->thumbnail_path ? route('public.publikasi.media', [$publikasi->slug, 'thumbnail']) : '' }}" class="max-h-[680px] w-full rounded-2xl bg-black"><source src="{{ route('public.publikasi.media', $publikasi->slug) }}"></video>
            @endif
            @if($publikasi->ringkasan)<p class="website-section-description mt-6 text-lg leading-8">{{ $publikasi->ringkasan }}</p>@endif
            @if($publikasi->konten)<div class="prose prose-slate mt-5 max-w-none whitespace-pre-line leading-8">{{ $publikasi->konten }}</div>@endif
        </section>
    @endif
</article>
@endsection
