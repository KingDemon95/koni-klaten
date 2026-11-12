@extends('layouts.public')

@section('title', 'Struktur Pengurus KONI')

@section('content')
<section class="bg-[#17191F] text-white">
    <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8 lg:py-16">
        <p class="text-xs font-black tracking-[0.2em] text-red-300">ORGANISASI</p>
        <h1 class="mt-3 text-3xl font-black sm:text-4xl">Struktur Pengurus KONI Klaten</h1>
        <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-300">Informasi susunan pengurus tingkat Kabupaten Klaten yang tercatat dan berstatus aktif.</p>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="mb-7 flex items-center justify-between gap-4">
        <p class="text-sm text-slate-500">{{ $penguruses->count() }} pengurus aktif terdaftar</p>
        <a href="{{ route('public.home') }}" class="text-sm font-bold text-[#D71920] hover:underline">← Kembali ke beranda</a>
    </div>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse($penguruses as $pengurus)
            <article class="rounded-2xl border border-slate-200 bg-white p-5">
                <div class="flex items-center gap-4">
                    <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-full bg-slate-100 text-xl font-black text-slate-500">
                        @if($pengurus->foto_path)<img src="{{ asset('storage/'.$pengurus->foto_path) }}" alt="Foto {{ $pengurus->nama_lengkap }}" class="h-full w-full object-cover">@else{{ strtoupper(substr($pengurus->nama_lengkap, 0, 1)) }}@endif
                    </div>
                    <div class="min-w-0"><h2 class="font-black text-slate-900">{{ $pengurus->nama_lengkap }}</h2><p class="mt-1 text-sm font-bold text-[#D71920]">{{ $pengurus->jabatan }}</p></div>
                </div>
                @if($pengurus->bidang)<p class="mt-4 text-sm text-slate-600">{{ $pengurus->bidang }}</p>@endif
                @if($pengurus->periode_mulai || $pengurus->periode_selesai)
                    <p class="mt-3 border-t border-slate-100 pt-3 text-xs text-slate-500">Periode {{ $pengurus->periode_mulai ?: '?' }}–{{ $pengurus->periode_selesai ?: '?' }}</p>
                @endif
            </article>
        @empty
            <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center">
                <h2 class="font-bold text-slate-800">Data pengurus belum tersedia</h2>
                <p class="mt-2 text-sm text-slate-500">Susunan pengurus akan ditampilkan setelah data ditambahkan oleh Admin KONI.</p>
            </div>
        @endforelse
    </div>
</section>
@endsection
