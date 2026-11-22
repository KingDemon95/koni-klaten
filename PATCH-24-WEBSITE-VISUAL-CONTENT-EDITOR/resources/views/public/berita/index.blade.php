@extends('layouts.public')
@section('title', 'Berita KONI Klaten')
@section('content')
@php($copy = $websitePageSections ?? [])

@if($copy['hero']['visible'] ?? true)
    @php($hero = $copy['hero'])
    <section class="public-page-hero website-managed-section text-white" style="order: {{ $hero['order'] ?? 10 }}"><div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8 sm:py-20">@include('public.partials.managed-heading', ['sectionKey' => 'hero', 'headingTag' => 'h1'])</div></section>
@endif

@if($copy['filters']['visible'] ?? true)
    @php($filters = $copy['filters'])
    <section class="website-managed-section website-section-surface mx-auto w-full max-w-7xl px-4 pt-10 sm:px-6 lg:px-8" style="order: {{ $filters['order'] ?? 20 }}">
        @include('public.partials.managed-heading', ['sectionKey' => 'filters', 'wrapperClass' => 'mb-4'])
        <form method="GET" class="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 sm:grid-cols-[1fr_240px_auto]">
            <input type="search" name="q" value="{{ $search }}" maxlength="100" placeholder="{{ $filters['search_placeholder'] }}" class="rounded-lg border-slate-300 text-sm focus:border-red-500 focus:ring-red-500">
            <select name="kategori" class="rounded-lg border-slate-300 text-sm focus:border-red-500 focus:ring-red-500"><option value="">Semua kategori</option>@foreach($categories as $item)<option value="{{ $item }}" @selected($kategori === $item)>{{ $item }}</option>@endforeach</select>
            <button class="website-page-button rounded-lg px-5 py-2.5 text-sm font-bold">{{ $filters['button_label'] ?: 'Cari berita' }}</button>
        </form>
    </section>
@endif

@if($copy['listing']['visible'] ?? true)
    @php($listing = $copy['listing'])
    <section class="website-managed-section website-section-surface mx-auto w-full max-w-7xl px-4 py-12 sm:px-6 lg:px-8" style="order: {{ $listing['order'] ?? 30 }}">
        @include('public.partials.managed-heading', ['sectionKey' => 'listing', 'wrapperClass' => 'mb-7'])
        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            @forelse($berita as $item)
                <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                    @if($item->media_path)<a href="{{ route('public.berita.show', $item->slug) }}"><img src="{{ route('public.publikasi.media', $item->slug) }}" alt="{{ $item->judul }}" class="h-52 w-full object-cover"></a>@else<div class="flex h-52 items-center justify-center bg-gradient-to-br from-[#17191F] to-[#6B1B22] text-5xl font-black text-white">K</div>@endif
                    <div class="p-5"><div class="flex flex-wrap items-center gap-2"><span class="rounded-full bg-red-50 px-3 py-1 text-[10px] font-black uppercase tracking-wide text-[#D71920]">{{ $item->kategori ?: 'Berita' }}</span><span class="text-xs text-slate-500">{{ $item->published_at?->translatedFormat('d M Y') }}</span></div><h2 class="mt-3 text-lg font-black leading-snug text-slate-900"><a class="hover:text-[#D71920]" href="{{ route('public.berita.show', $item->slug) }}">{{ $item->judul }}</a></h2><p class="mt-2 line-clamp-3 text-sm leading-6 text-slate-500">{{ $item->ringkasan ?: \Illuminate\Support\Str::limit(strip_tags($item->konten), 150) }}</p><a href="{{ route('public.berita.show', $item->slug) }}" class="website-page-accent mt-4 inline-block text-sm font-bold">{{ $listing['detail_link_label'] }} →</a></div>
                </article>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center"><p class="font-bold text-slate-800">Belum ada berita yang dipublikasikan.</p><p class="mt-2 text-sm text-slate-500">Berita KONI Klaten akan tampil setelah diterbitkan oleh pengelola.</p></div>
            @endforelse
        </div>
        <div class="mt-8">{{ $berita->links() }}</div>
    </section>
@endif
@endsection
