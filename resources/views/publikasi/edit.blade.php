@extends($layout)
@section('title', 'Edit Konten Publikasi')
@section('heading', 'Edit Konten Publikasi')
@section('content')
<div class="mx-auto max-w-4xl space-y-5">
    <a href="{{ route($routePrefix.'.publikasi.show', $publikasi) }}" class="text-sm font-bold text-[#D71920] hover:underline">← Kembali ke Detail</a>
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
        <h2 class="text-xl font-black text-slate-900">Perbarui konten</h2>
        @include('publikasi._form', ['action' => route($routePrefix.'.publikasi.update', $publikasi), 'method' => 'PUT'])
    </section>
</div>
@endsection
