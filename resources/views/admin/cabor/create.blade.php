@extends('layouts.admin')

@section('title', 'Tambah Cabor')
@section('heading', 'Tambah Cabang Olahraga')

@section('content')

<div class="mx-auto max-w-4xl">

    <div class="mb-6">
        <a href="{{ route('admin.cabor.index') }}"
           class="text-sm font-semibold text-red-600">
            ← Kembali ke daftar cabor
        </a>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">

        <h2 class="text-xl font-black">
            Tambah Cabang Olahraga
        </h2>

        <p class="mb-8 mt-2 text-sm text-gray-500">
            Lengkapi informasi cabang olahraga yang akan didaftarkan.
        </p>

        @include('admin.cabor._form')

    </div>

</div>

@endsection