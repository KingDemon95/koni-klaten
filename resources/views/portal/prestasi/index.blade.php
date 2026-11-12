@extends($layout)
@section('title', 'Prestasi Saya')
@section('heading', 'Prestasi Saya')
@section('content')
<div class="space-y-6">
    @if(session('success'))<div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">{{ session('success') }}</div>@endif
    <section class="flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-gradient-to-r from-[#A90010] to-[#E60012] p-6 text-white">
        <div><p class="text-xs font-bold tracking-widest text-yellow-300">PORTOFOLIO {{ strtoupper($jenis) }}</p><h2 class="mt-2 text-2xl font-black">Prestasi dan Pencapaian</h2><p class="mt-1 text-sm text-red-100">Setiap prestasi harus diverifikasi Admin Cabor sebelum ditampilkan di website publik.</p></div>
        <a href="{{ route($prefix.'.prestasi.create') }}" class="rounded-lg bg-white px-4 py-3 text-sm font-bold text-red-700">+ Unggah Prestasi</a>
    </section>
    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <h3 class="text-lg font-black">Riwayat Prestasi</h3>
        <div class="mt-4 space-y-3">
            @forelse($prestasis as $item)
                <article class="rounded-xl border border-gray-200 p-4">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0"><h4 class="font-black">{{ $item->nama_kejuaraan }}</h4><p class="mt-1 text-sm text-gray-500">{{ ucfirst($item->tingkat) }} · {{ $item->tanggal_prestasi?->format('d/m/Y') }} · {{ $item->hasil }}@if($item->jenis_medali) · Medali {{ ucfirst($item->jenis_medali) }}@endif</p>@if($item->kategori)<p class="mt-1 text-xs text-gray-500">{{ $item->kategori }}</p>@endif</div>
                        @php($statusClass = ['terverifikasi' => 'bg-green-100 text-green-800', 'menunggu_verifikasi' => 'bg-amber-100 text-amber-800', 'perlu_revisi' => 'bg-blue-100 text-blue-800', 'ditolak' => 'bg-red-100 text-red-800'][$item->status] ?? 'bg-gray-100 text-gray-700')
                        <span class="rounded-full px-3 py-1 text-xs font-bold {{ $statusClass }}">{{ str_replace('_', ' ', ucfirst($item->status)) }}</span>
                    </div>
                    @if($item->catatan_verifikasi)
                        <p class="mt-3 rounded-lg {{ $item->status === 'ditolak' ? 'bg-red-50 text-red-900' : 'bg-amber-50 text-amber-900' }} p-3 text-sm">
                            <strong>{{ $item->status === 'ditolak' ? 'Alasan penolakan dari Admin Cabor:' : 'Catatan Admin Cabor:' }}</strong>
                            {{ $item->catatan_verifikasi }}
                        </p>
                    @endif
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route($prefix.'.prestasi.bukti', $item) }}" class="rounded-lg border border-gray-300 px-3 py-2 text-xs font-bold">Unduh Bukti</a>
                        @if($item->status !== 'terverifikasi')
                            <a href="{{ route($prefix.'.prestasi.edit', $item) }}" class="rounded-lg bg-gray-900 px-3 py-2 text-xs font-bold text-white">Edit / Kirim Ulang</a>
                            <form method="POST" action="{{ route($prefix.'.prestasi.destroy', $item) }}" onsubmit="return confirm('Hapus data prestasi ini?')">@csrf @method('DELETE')<button class="rounded-lg border border-red-200 px-3 py-2 text-xs font-bold text-red-700">Hapus</button></form>
                        @endif
                    </div>
                </article>
            @empty
                <div class="rounded-xl border border-dashed border-gray-300 p-8 text-center text-sm text-gray-500">Belum ada prestasi yang diunggah.</div>
            @endforelse
        </div>
        <div class="mt-5">{{ $prestasis->links() }}</div>
    </section>
</div>
@endsection
