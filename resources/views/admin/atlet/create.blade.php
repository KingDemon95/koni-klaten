@extends('layouts.admin')

@section('title', 'Tambah Atlet')
@section('heading', 'Tambah Atlet')

@section('content')

<div class="mx-auto max-w-4xl">

    <div class="mb-6">
        <a href="{{ route('admin.atlet.index') }}"
           class="text-sm font-semibold text-red-600">
            ← Kembali ke daftar atlet
        </a>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">

        <h2 class="text-xl font-black">Tambah Atlet Baru</h2>

        <p class="mb-8 mt-2 text-sm text-gray-500">
            Lengkapi informasi atlet yang akan didaftarkan.
        </p>

        @include('admin.atlet._form')

    </div>

</div>

@endsection