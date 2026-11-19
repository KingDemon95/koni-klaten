@extends($routePrefix === 'admin' ? 'layouts.admin' : 'layouts.cabor')

@section('title', 'Edit Inventaris')
@section('heading', 'Edit Inventaris')

@section('content')
<div class="mx-auto max-w-4xl space-y-5">
    <div><h2 class="text-xl font-black">Ubah data barang</h2><p class="mt-1 text-sm text-gray-500">{{ $inventoryItem->kode_barang }} · Kepemilikan barang tidak dapat dipindahkan melalui form edit.</p></div>
    @include('inventaris._form', ['action' => route($routePrefix.'.inventaris.update', $inventoryItem), 'method' => 'PUT', 'item' => $inventoryItem])
</div>
@endsection
