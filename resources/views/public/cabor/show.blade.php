@extends('layouts.public')

@section('title', $cabangOlahraga->nama)

@section('content')
<section class="bg-[#17191F] text-white">
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
        <a href="{{ route('public.cabor.index') }}" class="text-xs font-bold text-red-200 hover:text-white">← Semua Cabang Olahraga</a>
        <div class="mt-6 flex flex-col gap-6 sm:flex-row sm:items-center">
            <div class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-3xl bg-white text-2xl font-black text-[#D71920]">
                @if($cabangOlahraga->logo_path)
                    <img src="{{ asset('storage/'.$cabangOlahraga->logo_path) }}" alt="Logo {{ $cabangOlahraga->nama }}" class="h-full w-full object-contain p-2">
                @else
                    {{ strtoupper(substr($cabangOlahraga->singkatan ?: $cabangOlahraga->nama, 0, 2)) }}
                @endif
            </div>
            <div>
                <p class="text-xs font-black tracking-[0.2em] text-red-300">PROFIL CABANG OLAHRAGA</p>
                <h1 class="mt-2 text-3xl font-black sm:text-4xl">{{ $cabangOlahraga->nama }}</h1>
                @if($cabangOlahraga->singkatan)<p class="mt-2 text-sm font-semibold text-slate-300">{{ $cabangOlahraga->singkatan }}</p>@endif
                @if($cabangOlahraga->deskripsi)<p class="mt-3 max-w-3xl text-sm leading-7 text-slate-300">{{ $cabangOlahraga->deskripsi }}</p>@endif
            </div>
        </div>
    </div>
</section>

<section class="mx-auto max-w-7xl space-y-14 px-4 py-12 sm:px-6 lg:px-8">
    <section>
        <div class="mb-6 flex items-end justify-between gap-4">
            <div>
                <p class="text-xs font-black tracking-[0.2em] text-[#D71920]">SUMBER DAYA ATLET</p>
                <h2 class="mt-2 text-2xl font-black text-slate-950">Atlet aktif</h2>
            </div>
            <span class="text-xs font-semibold text-slate-500">{{ $atlets->total() }} atlet</span>
        </div>
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @forelse($atlets as $atlet)
                <article class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-full bg-slate-100 font-black text-slate-500">
                        @if($atlet->foto_path)
                            <img src="{{ asset('storage/'.$atlet->foto_path) }}" alt="Foto {{ $atlet->nama_lengkap }}" class="h-full w-full object-cover">
                        @else
                            {{ strtoupper(substr($atlet->nama_lengkap, 0, 1)) }}
                        @endif
                    </div>
                    <div class="min-w-0"><h3 class="truncate text-sm font-bold text-slate-900">{{ $atlet->nama_lengkap }}</h3><p class="mt-1 text-xs text-slate-500">Atlet {{ $cabangOlahraga->nama }}</p></div>
                </article>
            @empty
                <p class="col-span-full rounded-xl bg-white p-5 text-sm text-slate-500">Belum ada data atlet aktif yang dipublikasikan.</p>
            @endforelse
        </div>
        @if($atlets->hasPages())<div class="mt-5">{{ $atlets->links() }}</div>@endif
    </section>

    <section>
        <div class="mb-6 flex items-end justify-between gap-4">
            <div><p class="text-xs font-black tracking-[0.2em] text-[#D71920]">CAPAIAN OLAHRAGA</p><h2 class="mt-2 text-2xl font-black text-slate-950">Prestasi atlet dan pelatih terverifikasi</h2></div>
            <span class="text-xs font-semibold text-slate-500">{{ $prestasis->total() }} prestasi</span>
        </div>
        <div class="grid gap-3 md:grid-cols-2">
            @forelse($prestasis as $prestasi)
                <article class="rounded-2xl border border-slate-200 bg-white p-5">
                    <p class="text-xs font-black uppercase tracking-wider text-[#D71920]">{{ ucfirst($prestasi->tingkat) }}</p>
                    <h3 class="mt-2 font-black text-slate-950">{{ $prestasi->nama_kejuaraan }}</h3>
                    <p class="mt-1 text-sm text-slate-600">{{ $prestasi->atlet?->nama_lengkap ?? $prestasi->pelatih?->nama_lengkap ?? 'Atlet/Pelatih KONI Klaten' }}</p>
                    <div class="mt-3 flex flex-wrap gap-2 text-xs text-slate-500">
                        <span>{{ $prestasi->hasil }}</span>
                        @if($prestasi->jenis_medali)<span>· Medali {{ ucfirst($prestasi->jenis_medali) }}</span>@endif
                        @if($prestasi->kategori)<span>· {{ $prestasi->kategori }}</span>@endif
                        <span>· {{ $prestasi->tanggal_prestasi?->format('d/m/Y') }}</span>
                    </div>
                </article>
            @empty
                <p class="col-span-full rounded-xl bg-white p-5 text-sm text-slate-500">Belum ada prestasi yang diverifikasi untuk dipublikasikan.</p>
            @endforelse
        </div>
        @if($prestasis->hasPages())<div class="mt-5">{{ $prestasis->links() }}</div>@endif
    </section>

    <section>
        <div class="mb-6 flex items-end justify-between gap-4">
            <div><p class="text-xs font-black tracking-[0.2em] text-[#D71920]">TENAGA PEMBINAAN</p><h2 class="mt-2 text-2xl font-black text-slate-950">Pelatih aktif</h2></div>
            <span class="text-xs font-semibold text-slate-500">{{ $pelatihs->total() }} pelatih</span>
        </div>
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @forelse($pelatihs as $pelatih)
                <article class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-full bg-slate-100 font-black text-slate-500">
                        @if($pelatih->foto_path)<img src="{{ asset('storage/'.$pelatih->foto_path) }}" alt="Foto {{ $pelatih->nama_lengkap }}" class="h-full w-full object-cover">@else{{ strtoupper(substr($pelatih->nama_lengkap, 0, 1)) }}@endif
                    </div>
                    <div class="min-w-0"><h3 class="truncate text-sm font-bold text-slate-900">{{ $pelatih->nama_lengkap }}</h3><p class="mt-1 text-xs text-slate-500">{{ $pelatih->spesialisasi ?: 'Pelatih' }}</p></div>
                </article>
            @empty
                <p class="col-span-full rounded-xl bg-white p-5 text-sm text-slate-500">Belum ada data pelatih aktif yang dipublikasikan.</p>
            @endforelse
        </div>
        @if($pelatihs->hasPages())<div class="mt-5">{{ $pelatihs->links() }}</div>@endif
    </section>

    <section>
        <div class="mb-6"><p class="text-xs font-black tracking-[0.2em] text-[#D71920]">ORGANISASI CABANG</p><h2 class="mt-2 text-2xl font-black text-slate-950">Struktur pengurus cabor</h2></div>
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @forelse($penguruses as $pengurus)
                <article class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-full bg-red-50 font-black text-[#D71920]">
                        @if($pengurus->foto_path)<img src="{{ asset('storage/'.$pengurus->foto_path) }}" alt="Foto {{ $pengurus->nama_lengkap }}" class="h-full w-full object-cover">@else{{ strtoupper(substr($pengurus->nama_lengkap, 0, 1)) }}@endif
                    </div>
                    <div class="min-w-0"><h3 class="truncate text-sm font-bold text-slate-900">{{ $pengurus->nama_lengkap }}</h3><p class="mt-1 text-xs font-semibold text-[#D71920]">{{ $pengurus->jabatan }}</p>@if($pengurus->bidang)<p class="mt-1 text-xs text-slate-500">{{ $pengurus->bidang }}</p>@endif</div>
                </article>
            @empty
                <p class="col-span-full rounded-xl bg-white p-5 text-sm text-slate-500">Data struktur pengurus cabang olahraga belum tersedia.</p>
            @endforelse
        </div>
    </section>
</section>
@endsection
