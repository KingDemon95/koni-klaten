@extends($layout)
@section('title', 'Tambah Agenda')
@section('heading', 'Tambah Agenda')
@section('content')
<div class="mx-auto max-w-4xl rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-8">
    <div class="mb-6"><h2 class="text-xl font-black">Buat agenda baru</h2><p class="mt-1 text-sm text-slate-500">Agenda baru disimpan sebagai draft. Staff KONI dan Admin KONI dapat mempublikasikannya.</p></div>
    @include('agenda._form')
</div>
@endsection
