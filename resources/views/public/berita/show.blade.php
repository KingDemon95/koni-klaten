@extends('layouts.public')
@section('title', $publikasi->judul)
@section('content')
<article class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
    <a href="{{ route('public.berita.index') }}" class="text-sm font-bold text-[#D71920] hover:underline">← Kembali ke Berita</a>
    <div class="mt-6 flex flex-wrap items-center gap-3"><span class="rounded-full bg-red-50 px-3 py-1 text-xs font-black uppercase tracking-wide text-[#D71920]">{{ $publikasi->kategori ?: 'Berita KONI' }}</span><span class="text-sm text-slate-500">{{ $publikasi->published_at?->translatedFormat('d F Y') }}</span></div>
    <h1 class="mt-4 text-3xl font-black leading-tight text-slate-950 sm:text-5xl">{{ $publikasi->judul }}</h1>
    @if($publikasi->ringkasan)<p class="mt-5 text-lg leading-8 text-slate-600">{{ $publikasi->ringkasan }}</p>@endif
    @if($publikasi->media_path)<img src="{{ route('public.publikasi.media', $publikasi->slug) }}" alt="{{ $publikasi->judul }}" class="mt-8 max-h-[560px] w-full rounded-2xl border border-slate-200 object-cover">@endif
    <div class="prose prose-slate mt-8 max-w-none whitespace-pre-line leading-8">{{ $publikasi->konten }}</div>
</article>
@endsection
