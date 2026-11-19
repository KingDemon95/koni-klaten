@extends('layouts.sekretaris')

@section('title', 'Dashboard Sekretaris KONI')
@section('heading', 'Dashboard Sekretaris KONI')

@section('content')
<div class="space-y-6">
    <section class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#171717] to-[#3b1015] p-6 text-white shadow-lg sm:p-8">
        <div class="relative z-10 max-w-3xl">
            <p class="text-xs font-bold tracking-[0.2em] text-yellow-300">ADMINISTRASI KESEKRETARIATAN</p>
            <h2 class="mt-3 text-2xl font-black sm:text-3xl">Selamat datang, {{ auth()->user()->name }}.</h2>
            <p class="mt-3 text-sm leading-relaxed text-gray-200">Pantau data organisasi, kelola template surat, dan berikan persetujuan atau penolakan atas surat yang telah diperiksa Staff KONI.</p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('sekretaris.surat.index') }}" class="rounded-lg bg-white px-4 py-2.5 text-xs font-black text-gray-900 hover:bg-gray-100">Buka Persetujuan Surat</a>
                <a href="{{ route('sekretaris.templates.index') }}" class="rounded-lg border border-white/40 bg-white/10 px-4 py-2.5 text-xs font-bold text-white hover:bg-white/20">Kelola Template Surat</a>
            </div>
        </div>
        <div class="absolute -right-12 -top-20 h-64 w-64 rounded-full border-[30px] border-white/10"></div>
    </section>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([['Cabang olahraga aktif', $stats['cabor'], 'sekretaris.cabor.index'], ['Atlet aktif', $stats['atlet'], 'sekretaris.atlet.index'], ['Pelatih aktif', $stats['pelatih'], 'sekretaris.pelatih.index'], ['Pengurus aktif', $stats['pengurus'], 'sekretaris.pengurus.index'], ['Surat menunggu persetujuan', $stats['surat_menunggu'], 'sekretaris.surat.index'], ['Surat menunggu disampaikan', $stats['surat_disampaikan'], 'sekretaris.surat.index'], ['Agenda dipublikasikan', $stats['agenda_terbit'], 'public.agenda.index'], ['Template surat aktif', $stats['template_aktif'], 'sekretaris.templates.index']] as [$label, $value, $target])
            <a href="{{ route($target) }}" class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <p class="text-xs font-semibold text-gray-500">{{ $label }}</p>
                <p class="mt-3 text-3xl font-black tabular-nums text-gray-900">{{ $value }}</p>
                <p class="mt-2 text-[11px] font-bold text-red-600">Lihat detail →</p>
            </a>
        @endforeach
    </section>

    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div><h3 class="font-black">Surat untuk ditinjau</h3><p class="mt-1 text-xs text-gray-500">Sekretaris dapat memutuskan surat secara mandiri. Persetujuan Ketua tidak menjadi syarat tambahan.</p></div>
            <a href="{{ route('sekretaris.surat.index') }}" class="text-xs font-bold text-red-600">Buka persuratan</a>
        </div>
        <div class="mt-4 divide-y divide-gray-100">
            @forelse ($recentSurats as $surat)
                <a href="{{ route('sekretaris.surat.show', $surat) }}" class="flex flex-wrap items-center justify-between gap-3 py-4 hover:text-red-700">
                    <div><p class="text-sm font-bold">{{ $surat->perihal }}</p><p class="mt-1 text-xs text-gray-500">{{ $surat->cabangOlahraga?->nama ?? 'KONI Kabupaten' }} · {{ $surat->creator?->name ?? 'Pengirim tidak tersedia' }}</p></div>
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-bold text-gray-700">{{ \App\Models\Surat::statusLabels()[$surat->status] ?? $surat->status }}</span>
                </a>
            @empty
                <p class="py-8 text-center text-sm text-gray-500">Belum ada surat yang memerlukan tindak lanjut.</p>
            @endforelse
        </div>
    </section>
</div>
@endsection
