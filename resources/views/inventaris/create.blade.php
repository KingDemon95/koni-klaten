@extends($routePrefix === 'admin' ? 'layouts.admin' : 'layouts.cabor')

@section('title', 'Tambah Inventaris')
@section('heading', 'Tambah Inventaris')

@section('content')
<div class="mx-auto max-w-4xl space-y-5">
    <div><h2 class="text-xl font-black">Tambah barang inventaris</h2><p class="mt-1 text-sm text-gray-500">Kode barang dibuat otomatis setelah data tersimpan.</p></div>
    @include('inventaris._form', ['action' => route($routePrefix.'.inventaris.store'), 'method' => 'POST', 'item' => null])
</div>
@endsection
