@extends($layout)
@section('title', 'Tambah Konten Publikasi')
@section('heading', 'Tambah Konten Publikasi')
@section('content')
<div class="mx-auto max-w-4xl space-y-5">
    <a href="{{ route($routePrefix.'.publikasi.index') }}" class="text-sm font-bold text-[#D71920] hover:underline">← Kembali ke Publikasi</a>
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
        <h2 class="text-xl font-black text-slate-900">Buat berita atau dokumentasi</h2>
        <p class="mt-1 text-sm text-slate-500">Konten baru disimpan sebagai draft. Tekan tombol Publikasikan setelah konten siap ditampilkan.</p>
        @include('publikasi._form', ['action' => route($routePrefix.'.publikasi.store'), 'method' => 'POST'])
    </section>
</div>
@endsection
