@extends('layouts.public')
@section('title', $agenda->judul)
@section('content')
<article class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
    <a href="{{ route('public.agenda.index') }}" class="text-sm font-bold text-[#D71920] hover:underline">← Kembali ke Agenda</a>
    <div class="mt-6 flex flex-wrap items-center gap-3"><span class="rounded-full bg-red-50 px-3 py-1 text-xs font-black uppercase tracking-wide text-[#D71920]">{{ $agenda->jenis }}</span><span class="text-sm text-slate-500">{{ $agenda->tanggal_mulai?->translatedFormat('d F Y') }}@if($agenda->tanggal_selesai) – {{ $agenda->tanggal_selesai->translatedFormat('d F Y') }}@endif</span></div>
    <h1 class="mt-4 text-3xl font-black leading-tight text-slate-950 sm:text-5xl">{{ $agenda->judul }}</h1>
    <div class="mt-5 flex flex-wrap gap-x-6 gap-y-2 text-sm text-slate-500"><span>📍 {{ $agenda->lokasi ?: 'Lokasi belum dicantumkan' }}</span><span>🏅 {{ $agenda->cabangOlahraga?->nama ?? 'Kegiatan KONI Klaten' }}</span>@if($agenda->waktu_mulai)<span>🕒 {{ substr($agenda->waktu_mulai, 0, 5) }} WIB</span>@endif</div>
    @if($agenda->poster_path)<img src="{{ asset('storage/'.$agenda->poster_path) }}" alt="Poster {{ $agenda->judul }}" class="mt-8 max-h-[520px] w-full rounded-2xl border border-slate-200 object-cover">@endif
    <div class="prose prose-slate mt-8 max-w-none whitespace-pre-line leading-8">{{ $agenda->deskripsi ?: 'Informasi kegiatan KONI Kabupaten Klaten.' }}</div>
</article>
@endsection
