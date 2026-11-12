@extends('layouts.public')
@section('title', 'Dokumentasi KONI Klaten')
@section('content')
<section class="bg-[#17191F] py-14 text-white sm:py-20"><div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"><p class="text-xs font-black tracking-[0.2em] text-red-300">GALERI KEGIATAN</p><h1 class="mt-3 text-3xl font-black sm:text-5xl">Dokumentasi</h1><p class="mt-4 max-w-2xl text-sm leading-7 text-slate-300">Foto dan video kegiatan olahraga, kejuaraan, serta aktivitas KONI Kabupaten Klaten.</p></div></section>
<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <form method="GET" class="mb-8 grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 sm:grid-cols-[1fr_170px_220px_auto]"><input type="search" name="q" value="{{ $search }}" maxlength="100" placeholder="Cari dokumentasi..." class="rounded-lg border-slate-300 text-sm focus:border-red-500 focus:ring-red-500"><select name="jenis" class="rounded-lg border-slate-300 text-sm focus:border-red-500 focus:ring-red-500"><option value="">Foto & Video</option><option value="foto" @selected($jenisInput === 'foto')>Foto</option><option value="video" @selected($jenisInput === 'video')>Video</option></select><select name="kategori" class="rounded-lg border-slate-300 text-sm focus:border-red-500 focus:ring-red-500"><option value="">Semua kategori</option>@foreach($categories as $item)<option value="{{ $item }}" @selected($kategori === $item)>{{ $item }}</option>@endforeach</select><button class="rounded-lg bg-[#D71920] px-5 py-2.5 text-sm font-bold text-white">Cari</button></form>
    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
        @forelse($dokumentasi as $item)
            <a href="{{ route('public.dokumentasi.show', $item->slug) }}" class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                <div class="relative h-56 overflow-hidden bg-slate-900">
                    @if($item->jenis === 'foto')<img src="{{ route('public.publikasi.media', $item->slug) }}" alt="{{ $item->judul }}" class="h-full w-full object-cover transition group-hover:scale-105">@elseif($item->thumbnail_path)<img src="{{ route('public.publikasi.media', [$item->slug, 'thumbnail']) }}" alt="{{ $item->judul }}" class="h-full w-full object-cover">@else<div class="flex h-full items-center justify-center text-5xl text-white">▶</div>@endif
                    @if($item->jenis === 'video')<span class="absolute bottom-3 left-3 rounded-full bg-black/75 px-3 py-1 text-xs font-bold text-white">▶ Video</span>@endif
                </div>
                <div class="p-5"><div class="flex items-center justify-between gap-2"><span class="rounded-full bg-red-50 px-3 py-1 text-[10px] font-black uppercase tracking-wide text-[#D71920]">{{ $item->kategori ?: ucfirst($item->jenis) }}</span><span class="text-xs text-slate-500">{{ $item->published_at?->translatedFormat('d M Y') }}</span></div><h2 class="mt-3 text-lg font-black text-slate-900 group-hover:text-[#D71920]">{{ $item->judul }}</h2><p class="mt-2 line-clamp-2 text-sm leading-6 text-slate-500">{{ $item->ringkasan ?: $item->konten }}</p></div>
            </a>
        @empty
            <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center"><p class="font-bold text-slate-800">Belum ada dokumentasi yang dipublikasikan.</p><p class="mt-2 text-sm text-slate-500">Galeri foto dan video akan tampil setelah diterbitkan.</p></div>
        @endforelse
    </div>
    <div class="mt-8">{{ $dokumentasi->links() }}</div>
</section>
@endsection
