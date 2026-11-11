
@extends('layouts.admin')

@section('title', 'Edit Pengguna')
@section('heading', 'Edit Pengguna')

@section('content')

<div class="mb-7">

    <a href="{{ route('admin.pengguna.index') }}"
       class="text-sm font-semibold text-gray-500 hover:text-[#E60012]">

        ← Kembali ke Manajemen Pengguna

    </a>

    <h2 class="mt-4 text-xl font-black">
        Edit Data Pengguna
    </h2>

    <p class="mt-1 text-sm text-gray-500">
        Perbarui informasi akun {{ $pengguna->name }}.
    </p>

</div>


<form
    action="{{ route('admin.pengguna.update', $pengguna) }}"
    method="POST">

    @csrf
    @method('PUT')

    @include('admin.pengguna._form')

</form>

@endsection
