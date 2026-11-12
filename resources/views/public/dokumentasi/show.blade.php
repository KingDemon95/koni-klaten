@extends('layouts.public')
@section('title', $publikasi->judul)
@section('content')
<article class="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
    <a href="{{ route('public.dokumentasi.index') }}" class="text-sm font-bold text-[#D71920] hover:underline">← Kembali ke Dokumentasi</a>
    <div class="mt-6 flex flex-wrap items-center gap-3"><span class="rounded-full bg-red-50 px-3 py-1 text-xs font-black uppercase tracking-wide text-[#D71920]">{{ $publikasi->jenis }}</span><span class="text-sm text-slate-500">{{ $publikasi->published_at?->translatedFormat('d F Y') }}</span></div>
    <h1 class="mt-4 text-3xl font-black leading-tight text-slate-950 sm:text-5xl">{{ $publikasi->judul }}</h1>
    @if($publikasi->jenis === 'foto')<img src="{{ route('public.publikasi.media', $publikasi->slug) }}" alt="{{ $publikasi->judul }}" class="mt-8 max-h-[680px] w-full rounded-2xl border border-slate-200 object-contain bg-slate-100">@else<video controls preload="metadata" poster="{{ $publikasi->thumbnail_path ? route('public.publikasi.media', [$publikasi->slug, 'thumbnail']) : '' }}" class="mt-8 max-h-[680px] w-full rounded-2xl bg-black"><source src="{{ route('public.publikasi.media', $publikasi->slug) }}"></video>@endif
    @if($publikasi->ringkasan)<p class="mt-6 text-lg leading-8 text-slate-600">{{ $publikasi->ringkasan }}</p>@endif
    @if($publikasi->konten)<div class="prose prose-slate mt-5 max-w-none whitespace-pre-line leading-8">{{ $publikasi->konten }}</div>@endif
</article>
@endsection
