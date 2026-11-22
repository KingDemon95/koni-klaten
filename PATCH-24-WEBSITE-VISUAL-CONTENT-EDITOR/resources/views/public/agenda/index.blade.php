@extends('layouts.public')
@section('title', 'Agenda & Kejuaraan')
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
        <form method="GET" action="{{ route('public.agenda.index') }}" class="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 sm:grid-cols-[1fr_220px_auto]">
            <input type="search" name="q" value="{{ $search }}" placeholder="{{ $filters['search_placeholder'] }}" class="rounded-lg border-slate-300 text-sm focus:border-red-500 focus:ring-red-500">
            <select name="jenis" class="rounded-lg border-slate-300 text-sm focus:border-red-500 focus:ring-red-500"><option value="">Semua jenis</option><option value="agenda" @selected($jenis === 'agenda')>Agenda</option><option value="kegiatan" @selected($jenis === 'kegiatan')>Kegiatan</option><option value="kejuaraan" @selected($jenis === 'kejuaraan')>Kejuaraan</option></select>
            <button class="website-page-button rounded-lg px-5 py-2.5 text-sm font-bold">{{ $filters['button_label'] ?: 'Cari agenda' }}</button>
        </form>
    </section>
@endif

@if($copy['listing']['visible'] ?? true)
    @php($listing = $copy['listing'])
    <section class="website-managed-section website-section-surface mx-auto w-full max-w-7xl px-4 py-12 sm:px-6 lg:px-8" style="order: {{ $listing['order'] ?? 30 }}">
        @include('public.partials.managed-heading', ['sectionKey' => 'listing', 'wrapperClass' => 'mb-7'])
        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            @forelse($agendas as $agenda)
                <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                    @if($agenda->poster_path)<a href="{{ route('public.agenda.show', $agenda->slug) }}"><img src="{{ asset('storage/'.$agenda->poster_path) }}" alt="Poster {{ $agenda->judul }}" class="h-48 w-full object-cover"></a>@else<div class="flex h-48 items-center justify-center bg-gradient-to-br from-[#17191F] to-[#6B1B22] text-5xl font-black text-white">K</div>@endif
                    <div class="p-5"><div class="flex flex-wrap items-center gap-2"><span class="rounded-full bg-red-50 px-3 py-1 text-[10px] font-black uppercase tracking-wide text-[#D71920]">{{ $agenda->jenis }}</span><span class="text-xs text-slate-500">{{ $agenda->tanggal_mulai?->translatedFormat('d M Y') }}</span></div><h2 class="mt-3 text-lg font-black leading-snug text-slate-900"><a class="hover:text-[#D71920]" href="{{ route('public.agenda.show', $agenda->slug) }}">{{ $agenda->judul }}</a></h2><p class="mt-2 line-clamp-3 text-sm leading-6 text-slate-500">{{ $agenda->deskripsi ?: 'Informasi kegiatan KONI Kabupaten Klaten.' }}</p><div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-4 text-xs text-slate-500"><span>{{ $agenda->cabangOlahraga?->nama ?? 'Kegiatan KONI' }}</span><a href="{{ route('public.agenda.show', $agenda->slug) }}" class="website-page-accent font-bold">Selengkapnya →</a></div></div>
                </article>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center"><p class="font-bold text-slate-800">Belum ada agenda yang dipublikasikan.</p><p class="mt-2 text-sm text-slate-500">Informasi agenda dan kejuaraan akan muncul di halaman ini setelah dipublikasikan.</p></div>
            @endforelse
        </div>
        <div class="mt-8">{{ $agendas->links() }}</div>
    </section>
@endif
@endsection
