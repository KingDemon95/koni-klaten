@extends('layouts.cabor')
@section('title', 'Dokumen Izin Latihan')
@section('heading', 'Izin Latihan')
@section('content')
<div class="space-y-5">
    @if(session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            <p class="font-bold">Permohonan belum dapat diproses:</p>
            <ul class="mt-2 list-inside list-disc">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <section class="rounded-2xl bg-gradient-to-r from-[#171717] to-[#3b1015] p-6 text-white">
        <p class="text-xs font-bold tracking-widest text-yellow-300">ADMIN CABANG OLAHRAGA</p>
        <h2 class="mt-2 text-2xl font-black">Dokumen Izin Latihan</h2>
        <p class="mt-1 text-sm text-gray-200">Periksa dokumen Atlet dan Pelatih pada cabang olahraga Anda, lalu berikan keputusan.</p>
    </section>

    <section class="space-y-3">
        @forelse($items as $item)
            <article class="space-y-4 rounded-xl border border-gray-200 bg-white p-4">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h3 class="font-black">{{ $item->nama_pemohon }}</h3>
                        <p class="mt-1 text-xs text-gray-500">{{ ucfirst($item->jenis_pemohon) }} · {{ $item->tujuan }}</p>
                        <p class="mt-1 text-sm text-gray-600">{{ $item->tanggal_mulai?->format('d/m/Y') }} @if($item->tanggal_selesai)– {{ $item->tanggal_selesai->format('d/m/Y') }} @endif</p>
                        <p class="mt-1 text-xs text-gray-500">{{ $item->keperluan }}</p>
                    </div>
                    @php
                        $statusClass = [
                            'diajukan' => 'bg-amber-100 text-amber-800',
                            'disetujui' => 'bg-green-100 text-green-800',
                            'ditolak' => 'bg-red-100 text-red-800',
                        ][$item->status] ?? 'bg-gray-100 text-gray-700';
                    @endphp
                    <span class="rounded-full px-3 py-1 text-xs font-bold {{ $statusClass }}">{{ ucfirst($item->status) }}</span>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('cabor.izin-latihan.download', $item) }}" class="rounded-lg bg-gray-900 px-3 py-2 text-xs font-bold text-white">Unduh Dokumen</a>
                </div>

                @if($item->catatan_admin)
                    <div class="rounded-lg bg-red-50 p-3 text-sm text-red-800">
                        <strong>Catatan keputusan:</strong> {{ $item->catatan_admin }}
                    </div>
                @endif

                @if($item->status === 'diajukan')
                    <form method="POST" action="{{ route('cabor.izin-latihan.keputusan', $item) }}" class="grid gap-3 border-t border-gray-100 pt-4">
                        @csrf
                        <div>
                            <label for="catatan-izin-{{ $item->id }}" class="mb-1 block text-xs font-bold">Catatan (wajib jika ditolak)</label>
                            <textarea id="catatan-izin-{{ $item->id }}" name="catatan_admin" rows="2" maxlength="2000" class="w-full rounded-lg border-gray-300 text-sm" placeholder="Tuliskan alasan jika permohonan ditolak">{{ old('catatan_admin') }}</textarea>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button name="status" value="disetujui" class="rounded-lg bg-green-700 px-4 py-3 text-xs font-bold text-white">Terima Izin</button>
                            <button name="status" value="ditolak" class="rounded-lg border border-red-200 px-4 py-3 text-xs font-bold text-red-700">Tolak Izin</button>
                        </div>
                    </form>
                @elseif($item->diproses_at)
                    <p class="text-xs text-gray-500">Diproses {{ $item->diproses_at->format('d/m/Y H:i') }}@if($item->pemroses) oleh {{ $item->pemroses->name }}@endif</p>
                @endif
            </article>
        @empty
            <div class="rounded-xl border border-dashed border-gray-300 bg-white p-8 text-center text-sm text-gray-500">Belum ada dokumen izin latihan yang dikirim dari cabor ini.</div>
        @endforelse
        <div>{{ $items->links() }}</div>
    </section>
</div>
@endsection
