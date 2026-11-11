@extends('layouts.admin')

@section('title', 'Tambah Pelatih')
@section('heading', 'Tambah Pelatih')

@section('content')

<div class="mx-auto max-w-4xl">

    <div class="mb-6">
        <a href="{{ route('admin.pelatih.index') }}"
           class="text-sm font-semibold text-red-600">
            ← Kembali ke daftar pelatih
        </a>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">

        <h2 class="text-xl font-black">Tambah Pelatih Baru</h2>

        <p class="mb-8 mt-2 text-sm text-gray-500">
            Lengkapi informasi pelatih yang akan didaftarkan.
        </p>

        @include('admin.pelatih._form')

    </div>

</div>

@endsection