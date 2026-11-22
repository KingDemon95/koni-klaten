@extends('layouts.public')
@section('title', 'Cabang Olahraga')
@section('content')
@php($copy = $websitePageSections ?? [])

@if($copy['hero']['visible'] ?? true)
    @php($hero = $copy['hero'])
    <section class="public-page-hero website-managed-section text-white" style="order: {{ $hero['order'] ?? 10 }}">
        <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8 lg:py-16">
            @include('public.partials.managed-heading', ['sectionKey' => 'hero', 'headingTag' => 'h1'])
        </div>
    </section>
@endif

@if($copy['results']['visible'] ?? true)
    @php($results = $copy['results'])
    <section class="website-managed-section website-section-surface mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8" style="order: {{ $results['order'] ?? 20 }}">
        <div class="mb-7 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            @include('public.partials.managed-heading', ['sectionKey' => 'results'])
            <p class="website-section-description text-sm">Menampilkan <strong class="website-section-title">{{ $cabors->total() }}</strong> {{ $results['count_label'] }}</p>
        </div>
        <form method="GET" action="{{ route('public.cabor.index') }}" class="mb-8 flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 sm:flex-row">
            <label for="q" class="sr-only">Cari cabang olahraga</label>
            <input id="q" name="q" value="{{ $search }}" type="search" placeholder="{{ $results['search_placeholder'] }}" class="min-w-0 flex-1 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-red-400 focus:ring-2 focus:ring-red-100">
            <button type="submit" class="website-page-button rounded-xl px-6 py-3 text-sm font-bold">{{ $results['button_label'] }}</button>
            @if($search !== '')<a href="{{ route('public.cabor.index') }}" class="rounded-xl border border-slate-200 px-5 py-3 text-center text-sm font-bold text-slate-600 hover:bg-slate-50">{{ $results['reset_label'] }}</a>@endif
        </form>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse($cabors as $cabor)
                <a href="{{ route('public.cabor.show', $cabor) }}" class="group rounded-2xl border border-slate-200 bg-white p-5 transition hover:-translate-y-1 hover:border-red-200 hover:shadow-xl hover:shadow-slate-200/60">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex h-14 w-14 items-center justify-center overflow-hidden rounded-2xl bg-red-50 text-lg font-black text-[#D71920]">@if($cabor->logo_path)<img src="{{ asset('storage/'.$cabor->logo_path) }}" alt="Logo {{ $cabor->nama }}" class="h-full w-full object-contain">@else{{ strtoupper(substr($cabor->singkatan ?: $cabor->nama, 0, 2)) }}@endif</div>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-500">Cabor</span>
                    </div>
                    <h2 class="mt-5 text-lg font-black text-slate-900 group-hover:text-[#D71920]">{{ $cabor->nama }}</h2>
                    @if($cabor->singkatan)<p class="mt-1 text-xs font-semibold text-slate-400">{{ $cabor->singkatan }}</p>@endif
                    <p class="mt-4 min-h-12 text-sm leading-6 text-slate-500">{{ $cabor->deskripsi ?: 'Informasi cabang olahraga dan pembinaan atlet.' }}</p>
                    <div class="mt-5 flex flex-wrap gap-4 border-t border-slate-100 pt-4 text-xs text-slate-500"><span><strong class="text-slate-900">{{ $cabor->atlet_aktif_count }}</strong> Atlet</span><span><strong class="text-slate-900">{{ $cabor->pelatih_aktif_count }}</strong> Pelatih</span><span class="website-page-accent font-bold">{{ $results['detail_link_label'] }} →</span></div>
                </a>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center"><h2 class="font-bold text-slate-800">Cabang olahraga tidak ditemukan</h2><p class="mt-2 text-sm text-slate-500">Coba gunakan kata kunci yang berbeda.</p></div>
            @endforelse
        </div>
        <div class="mt-8">{{ $cabors->links() }}</div>
    </section>
@endif
@endsection
