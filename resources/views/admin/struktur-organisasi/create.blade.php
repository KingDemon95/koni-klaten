@extends('layouts.admin')

@section('title', 'Tambah Pengurus')
@section('heading', 'Tambah Pengurus')

@section('content')

<div class="mx-auto max-w-4xl">

    <div class="mb-6">
        <a href="{{ route('admin.struktur-organisasi.index') }}"
           class="text-sm font-semibold text-red-600">
            ← Kembali ke struktur organisasi
        </a>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">

        <h2 class="text-xl font-black">
            Tambah Pengurus Baru
        </h2>

        <p class="mb-8 mt-2 text-sm text-gray-500">
            Lengkapi informasi pengurus KONI atau cabang olahraga.
        </p>

        @include('admin.struktur-organisasi._form')

    </div>

</div>

@endsection