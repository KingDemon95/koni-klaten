@extends('layouts.public')
@section('title', $publikasi->judul)
@section('content')
@php($copy = $websitePageSections ?? [])
<article class="website-section-stack">
    @if($copy['hero']['visible'] ?? true)
        @php($hero = $copy['hero'])
        <header class="public-page-hero website-managed-section text-white" style="order: {{ $hero['order'] ?? 10 }}">
            <div class="mx-auto max-w-4xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8">
                @if(($hero['button_label'] ?? '') !== '')<a href="{{ \App\Models\WebsiteAppearanceSetting::buttonUrl($hero['button_target'] ?? 'berita') }}" class="website-hero-eyebrow text-sm font-bold hover:underline">← {{ $hero['button_label'] }}</a>@endif
                <div class="mt-6 flex flex-wrap items-center gap-3"><span class="rounded-full bg-white/10 px-3 py-1 text-xs font-black uppercase tracking-wide text-red-200">{{ $publikasi->kategori ?: 'Berita KONI' }}</span><span class="website-hero-description text-sm">{{ $publikasi->published_at?->translatedFormat('d F Y') }}</span></div>
                @include('public.partials.managed-heading', ['sectionKey' => 'hero', 'headingTag' => 'h1', 'dynamicTitle' => $publikasi->judul, 'dynamicDescription' => $publikasi->ringkasan ?? ''])
            </div>
        </header>
    @endif

    @if($copy['article']['visible'] ?? true)
        @php($article = $copy['article'])
        <section class="website-managed-section website-section-surface mx-auto w-full max-w-4xl px-4 py-10 sm:px-6 lg:px-8" style="order: {{ $article['order'] ?? 20 }}">
            @include('public.partials.managed-heading', ['sectionKey' => 'article', 'wrapperClass' => 'mb-6'])
            @if($publikasi->media_path)<img src="{{ route('public.publikasi.media', $publikasi->slug) }}" alt="{{ $publikasi->judul }}" class="max-h-[560px] w-full rounded-2xl border border-slate-200 object-cover">@endif
            <div class="prose prose-slate mt-8 max-w-none whitespace-pre-line leading-8">{{ $publikasi->konten }}</div>
        </section>
    @endif
</article>
@endsection
