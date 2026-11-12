@extends($layout)
@section('title', 'Izin Latihan')
@section('heading', 'Izin Latihan')
@section('content')
<div class="space-y-6">
    @if(session('success'))<div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800"><ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <section class="rounded-2xl bg-gradient-to-r from-[#A90010] to-[#E60012] p-6 text-white"><p class="text-xs font-bold tracking-widest text-yellow-300">DOKUMEN ADMINISTRASI</p><h2 class="mt-2 text-2xl font-black">Pengajuan Izin Latihan</h2><p class="mt-1 text-sm text-red-100">Unduh template resmi terlebih dahulu jika diperlukan, isi dokumen, lalu unggah kembali melalui formulir ini.</p></section>
    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
        <h3 class="text-lg font-black">Unggah Dokumen Izin</h3>
        <form method="POST" action="{{ route($prefix.'.izin-latihan.store') }}" enctype="multipart/form-data" class="mt-4 grid gap-4 sm:grid-cols-2">
            @csrf
            <div><label for="tujuan" class="mb-1 block text-sm font-bold">Tujuan / pihak yang dituju</label><input id="tujuan" name="tujuan" required maxlength="180" value="{{ old('tujuan') }}" class="w-full rounded-lg border-gray-300 text-sm" placeholder="Contoh: Pengurus KONI Kabupaten Klaten"></div>
            <div><label for="tanggal_mulai" class="mb-1 block text-sm font-bold">Tanggal mulai izin</label><input id="tanggal_mulai" type="date" name="tanggal_mulai" min="{{ now()->toDateString() }}" required value="{{ old('tanggal_mulai') }}" class="w-full rounded-lg border-gray-300 text-sm"></div>
            <div><label for="tanggal_selesai" class="mb-1 block text-sm font-bold">Tanggal selesai (opsional)</label><input id="tanggal_selesai" type="date" name="tanggal_selesai" min="{{ old('tanggal_mulai', now()->toDateString()) }}" value="{{ old('tanggal_selesai') }}" class="w-full rounded-lg border-gray-300 text-sm"></div>
            <div><label for="file" class="mb-1 block text-sm font-bold">Dokumen izin latihan</label><input id="file" type="file" name="file" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" class="w-full rounded-lg border border-gray-300 p-2 text-sm"><p class="mt-1 text-xs text-gray-500">PDF, JPG, PNG, DOC, DOCX · Maksimal 10 MB.</p></div>
            <div class="sm:col-span-2"><label for="keperluan" class="mb-1 block text-sm font-bold">Keperluan / keterangan</label><textarea id="keperluan" name="keperluan" rows="3" required maxlength="2000" class="w-full rounded-lg border-gray-300 text-sm" placeholder="Jelaskan kegiatan atau alasan izin latihan">{{ old('keperluan') }}</textarea></div>
            <div class="sm:col-span-2"><button class="rounded-lg bg-[#E60012] px-5 py-3 text-sm font-bold text-white hover:bg-red-700">Unggah Dokumen</button></div>
        </form>
    </section>
    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-wrap items-center justify-between gap-3"><div><h3 class="text-lg font-black">Riwayat Dokumen Izin</h3><p class="mt-1 text-sm text-gray-500">Dokumen yang sudah Anda unggah.</p></div><a href="{{ route($prefix.'.templates.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-xs font-bold">Lihat Template Surat</a></div>
        <div class="mt-4 space-y-3">
            @forelse($items as $item)
                <article class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-gray-200 p-4">
                    <div><h4 class="font-bold">{{ $item->tujuan }}</h4><p class="mt-1 text-sm text-gray-500">{{ $item->tanggal_mulai?->format('d/m/Y') }} @if($item->tanggal_selesai)– {{ $item->tanggal_selesai->format('d/m/Y') }} @endif · {{ $item->file_original_name }}</p>@php($statusClass = ['diajukan' => 'bg-amber-100 text-amber-800', 'disetujui' => 'bg-green-100 text-green-800', 'ditolak' => 'bg-red-100 text-red-800'][$item->status] ?? 'bg-gray-100 text-gray-700')
                        <p class="mt-2"><span class="rounded-full px-3 py-1 text-xs font-bold {{ $statusClass }}">Status: {{ ucfirst($item->status) }}</span><span class="ml-2 text-xs text-gray-500">Diunggah {{ $item->created_at?->format('d/m/Y H:i') }}</span></p>
                        @if($item->catatan_admin)<p class="mt-3 rounded-lg bg-red-50 p-3 text-sm text-red-800"><strong>Catatan Admin Cabor:</strong> {{ $item->catatan_admin }}</p>@endif</div>
                    <div class="flex gap-2"><a href="{{ route($prefix.'.izin-latihan.download', $item) }}" class="rounded-lg bg-gray-900 px-3 py-2 text-xs font-bold text-white">Unduh</a>@if($item->status === 'diajukan')<form method="POST" action="{{ route($prefix.'.izin-latihan.destroy', $item) }}" onsubmit="return confirm('Hapus dokumen ini?')">@csrf @method('DELETE')<button class="rounded-lg border border-red-200 px-3 py-2 text-xs font-bold text-red-700">Hapus</button></form>@endif</div>
                </article>
            @empty
                <div class="rounded-xl border border-dashed border-gray-300 p-8 text-center text-sm text-gray-500">Belum ada dokumen izin latihan yang diunggah.</div>
            @endforelse
        </div>
        <div class="mt-5">{{ $items->links() }}</div>
    </section>
</div>
@endsection
