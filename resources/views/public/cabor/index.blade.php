@extends('layouts.public')

@section('title', 'Cabang Olahraga')

@section('content')
<section class="bg-[#17191F] text-white">
    <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8 lg:py-16">
        <p class="text-xs font-black tracking-[0.2em] text-red-300">DIREKTORI OLAHRAGA</p>
        <h1 class="mt-3 text-3xl font-black sm:text-4xl">Cabang Olahraga</h1>
        <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-300">Temukan cabang olahraga, informasi atlet, pelatih, dan struktur pengurus pada masing-masing cabang.</p>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <form method="GET" action="{{ route('public.cabor.index') }}" class="mb-8 flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 sm:flex-row">
        <label for="q" class="sr-only">Cari cabang olahraga</label>
        <input id="q" name="q" value="{{ $search }}" type="search" placeholder="Cari nama atau singkatan cabang olahraga..." class="min-w-0 flex-1 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-red-400 focus:ring-2 focus:ring-red-100">
        <button type="submit" class="rounded-xl bg-[#D71920] px-6 py-3 text-sm font-bold text-white transition hover:bg-[#B9151B]">Cari Cabor</button>
        @if($search !== '')
            <a href="{{ route('public.cabor.index') }}" class="rounded-xl border border-slate-200 px-5 py-3 text-center text-sm font-bold text-slate-600 hover:bg-slate-50">Reset</a>
        @endif
    </form>

    <div class="mb-5 flex items-center justify-between gap-4">
        <p class="text-sm text-slate-500">Menampilkan <strong class="text-slate-900">{{ $cabors->total() }}</strong> cabang olahraga</p>
        <a href="{{ route('public.home') }}" class="text-sm font-bold text-[#D71920] hover:underline">← Kembali ke beranda</a>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse($cabors as $cabor)
            <a href="{{ route('public.cabor.show', $cabor) }}" class="group rounded-2xl border border-slate-200 bg-white p-5 transition hover:-translate-y-1 hover:border-red-200 hover:shadow-xl hover:shadow-slate-200/60">
                <div class="flex items-center gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-red-50 text-lg font-black text-[#D71920]">
                        @if($cabor->logo_path)
                            <img src="{{ asset('storage/'.$cabor->logo_path) }}" alt="Logo {{ $cabor->nama }}" class="h-full w-full object-contain">
                        @else
                            {{ strtoupper(substr($cabor->singkatan ?: $cabor->nama, 0, 2)) }}
                        @endif
                    </div>
                    <div class="min-w-0">
                        <h2 class="font-black text-slate-900 group-hover:text-[#D71920]">{{ $cabor->nama }}</h2>
                        @if($cabor->singkatan)<p class="mt-1 text-xs font-semibold text-slate-400">{{ $cabor->singkatan }}</p>@endif
                    </div>
                </div>
                <p class="mt-4 min-h-12 text-sm leading-6 text-slate-500">{{ $cabor->deskripsi ?: 'Informasi cabang olahraga dan pembinaan atlet.' }}</p>
                <div class="mt-5 flex gap-5 border-t border-slate-100 pt-4 text-xs text-slate-500">
                    <span><strong class="text-slate-900">{{ $cabor->atlet_aktif_count }}</strong> Atlet aktif</span>
                    <span><strong class="text-slate-900">{{ $cabor->pelatih_aktif_count }}</strong> Pelatih aktif</span>
                </div>
                <p class="mt-4 text-xs font-bold text-[#D71920]">Lihat detail cabang olahraga →</p>
            </a>
        @empty
            <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center">
                <h2 class="font-bold text-slate-800">Cabang olahraga tidak ditemukan</h2>
                <p class="mt-2 text-sm text-slate-500">Coba gunakan kata kunci yang berbeda.</p>
            </div>
        @endforelse
    </div>

    @if($cabors->hasPages())
        <div class="mt-8">{{ $cabors->links() }}</div>
    @endif
</section>
@endsection
