@extends('layouts.public')
@section('title', 'Berita KONI Klaten')
@section('content')
<section class="bg-[#17191F] py-14 text-white sm:py-20"><div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"><p class="text-xs font-black tracking-[0.2em] text-red-300">KABAR OLAHRAGA KLATEN</p><h1 class="mt-3 text-3xl font-black sm:text-5xl">Berita KONI Klaten</h1><p class="mt-4 max-w-2xl text-sm leading-7 text-slate-300">Informasi, kabar kegiatan, dan perkembangan olahraga Kabupaten Klaten.</p></div></section>
<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <form method="GET" class="mb-8 grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 sm:grid-cols-[1fr_240px_auto]"><input type="search" name="q" value="{{ $search }}" maxlength="100" placeholder="Cari berita..." class="rounded-lg border-slate-300 text-sm focus:border-red-500 focus:ring-red-500"><select name="kategori" class="rounded-lg border-slate-300 text-sm focus:border-red-500 focus:ring-red-500"><option value="">Semua kategori</option>@foreach($categories as $item)<option value="{{ $item }}" @selected($kategori === $item)>{{ $item }}</option>@endforeach</select><button class="rounded-lg bg-[#D71920] px-5 py-2.5 text-sm font-bold text-white">Cari</button></form>
    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
        @forelse($berita as $item)
            <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                @if($item->media_path)<a href="{{ route('public.berita.show', $item->slug) }}"><img src="{{ route('public.publikasi.media', $item->slug) }}" alt="{{ $item->judul }}" class="h-52 w-full object-cover"></a>@else<div class="flex h-52 items-center justify-center bg-gradient-to-br from-[#17191F] to-[#6B1B22] text-5xl font-black text-white">K</div>@endif
                <div class="p-5"><div class="flex flex-wrap items-center gap-2"><span class="rounded-full bg-red-50 px-3 py-1 text-[10px] font-black uppercase tracking-wide text-[#D71920]">{{ $item->kategori ?: 'Berita' }}</span><span class="text-xs text-slate-500">{{ $item->published_at?->translatedFormat('d M Y') }}</span></div><h2 class="mt-3 text-lg font-black leading-snug text-slate-900"><a class="hover:text-[#D71920]" href="{{ route('public.berita.show', $item->slug) }}">{{ $item->judul }}</a></h2><p class="mt-2 line-clamp-3 text-sm leading-6 text-slate-500">{{ $item->ringkasan ?: \Illuminate\Support\Str::limit(strip_tags($item->konten), 150) }}</p><a href="{{ route('public.berita.show', $item->slug) }}" class="mt-4 inline-block text-sm font-bold text-[#D71920]">Baca selengkapnya →</a></div>
            </article>
        @empty
            <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center"><p class="font-bold text-slate-800">Belum ada berita yang dipublikasikan.</p><p class="mt-2 text-sm text-slate-500">Berita KONI Klaten akan tampil setelah diterbitkan oleh pengelola.</p></div>
        @endforelse
    </div>
    <div class="mt-8">{{ $berita->links() }}</div>
</section>
@endsection
