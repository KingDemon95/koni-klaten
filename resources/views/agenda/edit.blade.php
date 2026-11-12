@extends($layout)
@section('title', 'Edit Agenda')
@section('heading', 'Edit Agenda')
@section('content')
<div class="mx-auto max-w-4xl rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-8">
    <div class="mb-6"><h2 class="text-xl font-black">Perbarui agenda</h2><p class="mt-1 text-sm text-slate-500">Perubahan akan disimpan sesuai status dan kewenangan akun.</p></div>
    @include('agenda._form')
</div>
@endsection
