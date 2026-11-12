@extends('layouts.admin')
@section('title', 'Monitoring Prestasi')
@section('heading', 'Monitoring Prestasi')
@section('content')
<div class="space-y-6">
    <section class="rounded-2xl bg-gradient-to-r from-[#171717] to-[#4A171B] p-6 text-white">
        <p class="text-xs font-bold tracking-[0.2em] text-yellow-300">PEMANTAUAN PRESTASI ORGANISASI</p>
        <h2 class="mt-2 text-2xl font-black">Seluruh prestasi atlet dan pelatih</h2>
        <p class="mt-2 max-w-3xl text-sm leading-6 text-gray-200">Admin KONI dapat memantau pengajuan dari seluruh cabang olahraga. Verifikasi dan keputusan publikasi tetap dilakukan oleh Admin Cabor sesuai kewenangannya.</p>
    </section>

    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
        @foreach ([['Semua prestasi', $stats['total']], ['Menunggu verifikasi', $stats['menunggu']], ['Terverifikasi', $stats['terverifikasi']], ['Perlu revisi', $stats['perlu_revisi']], ['Ditolak', $stats['ditolak']]] as [$label, $value])
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm"><p class="text-xs font-semibold text-gray-500">{{ $label }}</p><p class="mt-2 text-2xl font-black tabular-nums">{{ number_format($value, 0, ',', '.') }}</p></div>
        @endforeach
    </div>

    <form method="GET" action="{{ route('admin.prestasi.index') }}" class="grid gap-3 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm md:grid-cols-[1fr_190px_220px_auto]">
        <input type="search" name="q" value="{{ $search }}" maxlength="100" placeholder="Cari kejuaraan atau nama atlet/pelatih..." class="rounded-xl border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
        <select name="status" class="rounded-xl border-gray-300 text-sm focus:border-red-500 focus:ring-red-500"><option value="">Semua status</option>@foreach(['menunggu_verifikasi' => 'Menunggu verifikasi', 'terverifikasi' => 'Terverifikasi', 'perlu_revisi' => 'Perlu revisi', 'ditolak' => 'Ditolak'] as $value => $label)<option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>@endforeach</select>
        <select name="cabor" class="rounded-xl border-gray-300 text-sm focus:border-red-500 focus:ring-red-500"><option value="">Semua cabang olahraga</option>@foreach($cabors as $cabor)<option value="{{ $cabor->id }}" @selected($caborId === $cabor->id)>{{ $cabor->nama }}</option>@endforeach</select>
        <button class="rounded-xl bg-[#E60012] px-5 py-2.5 text-sm font-bold text-white hover:bg-red-700">Filter</button>
    </form>

    <section class="space-y-3">
        @forelse($prestasis as $item)
            <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0"><h3 class="text-lg font-black text-gray-900">{{ $item->nama_kejuaraan }}</h3><p class="mt-1 text-sm text-gray-600">{{ $item->atlet?->nama_lengkap ?? $item->pelatih?->nama_lengkap ?? 'Profil tidak tersedia' }}</p><p class="mt-1 text-xs text-gray-500">{{ $item->cabangOlahraga?->nama ?? 'Cabor tidak tersedia' }} · {{ ucfirst($item->tingkat) }} · {{ $item->tanggal_prestasi?->format('d/m/Y') }}</p></div>
                    <span class="rounded-full px-3 py-1 text-xs font-bold {{ $item->status === 'terverifikasi' ? 'bg-green-100 text-green-800' : ($item->status === 'menunggu_verifikasi' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-700') }}">{{ str_replace('_', ' ', ucfirst($item->status)) }}</span>
                </div>
                <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 pt-3"><p class="text-sm font-semibold text-gray-700">{{ $item->hasil }} @if($item->jenis_medali) · Medali {{ ucfirst($item->jenis_medali) }} @endif</p><div class="flex flex-wrap items-center gap-3"><span class="text-xs text-gray-500">{{ $item->verifier?->name ? 'Diverifikasi oleh '.$item->verifier->name : 'Belum diverifikasi' }}</span><a href="{{ route('admin.prestasi.bukti', $item) }}" class="rounded-lg border border-gray-300 px-3 py-2 text-xs font-bold text-gray-700 hover:bg-gray-50">Unduh bukti</a></div></div>
                @if($item->catatan_verifikasi)<p class="mt-3 rounded-lg bg-gray-50 p-3 text-xs leading-5 text-gray-600"><strong>Catatan verifikasi:</strong> {{ $item->catatan_verifikasi }}</p>@endif
            </article>
        @empty
            <div class="rounded-xl border border-dashed border-gray-300 bg-white p-10 text-center text-sm text-gray-500">Belum ada data prestasi yang sesuai dengan filter.</div>
        @endforelse
        <div>{{ $prestasis->links() }}</div>
    </section>
</div>
@endsection
