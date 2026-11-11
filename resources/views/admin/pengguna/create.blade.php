
@extends('layouts.admin')

@section('title', 'Tambah Pengguna')
@section('heading', 'Tambah Pengguna')

@section('content')

<div class="mb-7">

    <a href="{{ route('admin.pengguna.index') }}"
       class="text-sm font-semibold text-gray-500 hover:text-[#E60012]">

        ← Kembali ke Manajemen Pengguna

    </a>

    <h2 class="mt-4 text-xl font-black">
        Tambah Pengguna Baru
    </h2>

    <p class="mt-1 text-sm text-gray-500">
        Buat akun baru dan tentukan hak akses pengguna.
    </p>

</div>


<form
    action="{{ route('admin.pengguna.store') }}"
    method="POST">

    @csrf

    @include('admin.pengguna._form')

</form>

@endsection
