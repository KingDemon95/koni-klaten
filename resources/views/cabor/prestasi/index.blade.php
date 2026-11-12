@extends('layouts.cabor')
@section('title', 'Verifikasi Prestasi Atlet')
@section('heading', 'Verifikasi Prestasi')
@section('content')
<div class="space-y-6">
    @if(session('success'))<div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">{{ session('success') }}</div>@endif
    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            <p class="font-bold">Prestasi belum dapat diproses:</p>
            <ul class="mt-2 list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif
    <section class="rounded-2xl bg-gradient-to-r from-[#171717] to-[#3b1015] p-6 text-white"><p class="text-xs font-bold tracking-widest text-yellow-300">ADMIN CABANG OLAHRAGA</p><h2 class="mt-2 text-2xl font-black">Verifikasi Prestasi Atlet</h2><p class="mt-1 text-sm text-gray-200">Hanya prestasi yang diverifikasi yang akan tampil di website publik.</p></section>
    <div class="flex flex-wrap gap-2">@foreach(['' => 'Semua', 'menunggu_verifikasi' => 'Menunggu', 'terverifikasi' => 'Terverifikasi', 'perlu_revisi' => 'Perlu Revisi', 'ditolak' => 'Ditolak'] as $value => $label)<a href="{{ route('cabor.prestasi.index', $value ? ['status' => $value] : []) }}" class="rounded-full border px-3 py-2 text-xs font-bold {{ ($status ?? '') === $value ? 'border-red-600 bg-red-600 text-white' : 'border-gray-300 bg-white text-gray-700' }}">{{ $label }}</a>@endforeach</div>
    <section class="space-y-4">
        @forelse($prestasis as $item)
            <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div><h3 class="text-lg font-black">{{ $item->nama_kejuaraan }}</h3><p class="mt-1 text-sm text-gray-600">{{ $item->atlet ? 'Atlet' : 'Pelatih' }}: <strong>{{ $item->atlet?->nama_lengkap ?? $item->pelatih?->nama_lengkap ?? 'Profil tidak tersedia' }}</strong></p><p class="mt-1 text-xs text-gray-500">{{ ucfirst($item->tingkat) }} · {{ $item->tanggal_prestasi?->format('d/m/Y') }} · {{ $item->hasil }}@if($item->jenis_medali) · Medali {{ ucfirst($item->jenis_medali) }}@endif</p></div>
                    <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800">{{ str_replace('_', ' ', ucfirst($item->status)) }}</span>
                </div>
                @if($item->keterangan)<p class="mt-3 text-sm text-gray-600">{{ $item->keterangan }}</p>@endif
                <div class="mt-4"><a href="{{ route('cabor.prestasi.bukti', $item) }}" class="rounded-lg border border-gray-300 px-3 py-2 text-xs font-bold">Unduh Bukti Prestasi</a></div>
                @if($item->status === 'menunggu_verifikasi')
                    <form method="POST" action="{{ route('cabor.prestasi.review', $item) }}" class="mt-5 grid gap-3 border-t border-gray-100 pt-4 sm:grid-cols-2">
                        @csrf
                        <div class="sm:col-span-2"><label for="catatan-{{ $item->id }}" class="mb-1 block text-xs font-bold">Catatan (wajib untuk revisi atau penolakan)</label><textarea id="catatan-{{ $item->id }}" name="catatan_verifikasi" rows="2" maxlength="2000" class="w-full rounded-lg border-gray-300 text-sm" placeholder="Tuliskan alasan atau dokumen yang perlu diperbaiki"></textarea></div>
                        <button name="status" value="terverifikasi" class="rounded-lg bg-green-700 px-4 py-3 text-xs font-bold text-white">Verifikasi & Publikasikan</button>
                        <div class="flex flex-wrap gap-2"><button name="status" value="perlu_revisi" class="rounded-lg bg-amber-500 px-4 py-3 text-xs font-bold text-white">Minta Revisi</button><button name="status" value="ditolak" class="rounded-lg border border-red-200 px-4 py-3 text-xs font-bold text-red-700">Tolak</button></div>
                    </form>
                @elseif($item->catatan_verifikasi)
                    <p class="mt-4 rounded-lg {{ $item->status === 'ditolak' ? 'bg-red-50 text-red-800' : 'bg-gray-50 text-gray-700' }} p-3 text-sm"><strong>{{ $item->status === 'ditolak' ? 'Alasan penolakan:' : 'Catatan:' }}</strong> {{ $item->catatan_verifikasi }}</p>
                @endif
            </article>
        @empty
            <div class="rounded-xl border border-dashed border-gray-300 bg-white p-8 text-center text-sm text-gray-500">Belum ada pengajuan prestasi untuk cabang olahraga ini.</div>
        @endforelse
        <div>{{ $prestasis->links() }}</div>
    </section>
</div>
@endsection
