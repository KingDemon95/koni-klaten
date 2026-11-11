
@extends('layouts.cabor')

@php
    $editing = $pengurus->exists;

    $formAction = $editing
        ? route('cabor.pengurus.update', $pengurus->id)
        : route('cabor.pengurus.store');
@endphp

@section('title', $editing ? 'Edit Pengurus' : 'Tambah Pengurus')
@section('heading', $editing ? 'Edit Pengurus' : 'Tambah Pengurus')

@section('content')

<div class="mb-7">

    <a href="{{ route('cabor.pengurus.index') }}"
       class="text-sm font-semibold text-gray-500 hover:text-red-600">
        ← Kembali ke Struktur Organisasi
    </a>

    <h2 class="mt-4 text-xl font-black">
        {{ $editing ? 'Edit Pengurus Cabor' : 'Tambah Pengurus Cabor' }}
    </h2>

    <p class="mt-1 text-sm text-gray-500">
        Cabang Olahraga:
        <strong>{{ $cabor->nama }}</strong>
    </p>

</div>


@if($errors->any())
    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4">
        <p class="font-bold text-red-700">
            Periksa kembali formulir berikut:
        </p>

        <ul class="mt-2 list-inside list-disc text-sm text-red-600">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif


<form action="{{ $formAction }}"
      method="POST"
      enctype="multipart/form-data"
      class="space-y-6">

    @csrf

    @if($editing)
        @method('PUT')
    @endif


    <!-- BIODATA -->
    <section class="rounded-xl border border-gray-200 bg-white p-6">

        <h3 class="mb-5 border-b border-gray-100 pb-4 font-black">
            Biodata Pengurus
        </h3>

        <div class="grid gap-5 md:grid-cols-2">

            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-bold">
                    Nama Lengkap *
                </label>

                <input
                    type="text"
                    name="nama_lengkap"
                    required
                    maxlength="150"
                    value="{{ old('nama_lengkap', $pengurus->nama_lengkap) }}"
                    placeholder="Nama lengkap pengurus"
                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500"
                >
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold">
                    Jabatan *
                </label>

                <input
                    type="text"
                    name="jabatan"
                    required
                    maxlength="120"
                    value="{{ old('jabatan', $pengurus->jabatan) }}"
                    placeholder="Contoh: Ketua"
                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500"
                >
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold">
                    Bidang
                </label>

                <input
                    type="text"
                    name="bidang"
                    maxlength="120"
                    value="{{ old('bidang', $pengurus->bidang) }}"
                    placeholder="Contoh: Pembinaan Prestasi"
                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500"
                >
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold">
                    Jenis Kelamin
                </label>

                <select
                    name="jenis_kelamin"
                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm">

                    <option value="">Pilih Jenis Kelamin</option>

                    <option value="L"
                        @selected(old('jenis_kelamin', $pengurus->jenis_kelamin) === 'L')>
                        Laki-laki
                    </option>

                    <option value="P"
                        @selected(old('jenis_kelamin', $pengurus->jenis_kelamin) === 'P')>
                        Perempuan
                    </option>

                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold">
                    Nomor Telepon
                </label>

                <input
                    type="text"
                    name="no_telepon"
                    maxlength="30"
                    value="{{ old('no_telepon', $pengurus->no_telepon) }}"
                    placeholder="08xxxxxxxxxx"
                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500"
                >
            </div>

            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-bold">
                    Email
                </label>

                <input
                    type="email"
                    name="email"
                    maxlength="255"
                    value="{{ old('email', $pengurus->email) }}"
                    placeholder="email@contoh.com"
                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500"
                >
            </div>

        </div>

    </section>


    <!-- PERIODE -->
    <section class="rounded-xl border border-gray-200 bg-white p-6">

        <h3 class="mb-5 border-b border-gray-100 pb-4 font-black">
            Periode Kepengurusan
        </h3>

        <div class="grid gap-5 md:grid-cols-2">

            <div>
                <label class="mb-2 block text-sm font-bold">
                    Tahun Mulai
                </label>

                <input
                    type="number"
                    name="periode_mulai"
                    min="1900"
                    max="2100"
                    value="{{ old('periode_mulai', $pengurus->periode_mulai) }}"
                    placeholder="2025"
                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm"
                >
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold">
                    Tahun Selesai
                </label>

                <input
                    type="number"
                    name="periode_selesai"
                    min="1900"
                    max="2100"
                    value="{{ old('periode_selesai', $pengurus->periode_selesai) }}"
                    placeholder="2029"
                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm"
                >
            </div>

        </div>

        <p class="mt-3 text-xs text-gray-500">
            Tahun selesai tidak boleh lebih kecil dari tahun mulai.
        </p>

    </section>


    <!-- FOTO -->
    <section class="rounded-xl border border-gray-200 bg-white p-6">

        <h3 class="mb-5 border-b border-gray-100 pb-4 font-black">
            Foto Pengurus
        </h3>

        @if($pengurus->foto_path)
            <img
                src="{{ asset('storage/'.$pengurus->foto_path) }}"
                alt="{{ $pengurus->nama_lengkap }}"
                class="mb-4 h-28 w-28 rounded-xl border border-gray-200 object-cover"
            >
        @endif

        <input
            type="file"
            name="foto"
            accept=".jpg,.jpeg,.png,.webp"
            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm"
        >

        <p class="mt-2 text-xs text-gray-500">
            Format JPG, JPEG, PNG, WEBP. Maksimal 2 MB.
            Jika sedang mengedit, kosongkan untuk mempertahankan foto lama.
        </p>

    </section>


    <!-- STATUS DAN URUTAN -->
    <section class="rounded-xl border border-gray-200 bg-white p-6">

        <h3 class="mb-5 border-b border-gray-100 pb-4 font-black">
            Pengaturan Tampilan
        </h3>

        <div class="grid gap-5 md:grid-cols-2">

            <div>
                <label class="mb-2 block text-sm font-bold">
                    Urutan Tampil *
                </label>

                <input
                    type="number"
                    name="urutan"
                    required
                    min="0"
                    max="999"
                    value="{{ old('urutan', $pengurus->urutan ?? 0) }}"
                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm"
                >

                <p class="mt-1 text-xs text-gray-500">
                    Angka kecil tampil lebih dahulu.
                </p>
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold">
                    Status *
                </label>

                <select
                    name="status"
                    required
                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm">

                    <option value="aktif"
                        @selected(old('status', $pengurus->status ?? 'aktif') === 'aktif')>
                        Aktif
                    </option>

                    <option value="nonaktif"
                        @selected(old('status', $pengurus->status ?? 'aktif') === 'nonaktif')>
                        Nonaktif
                    </option>

                </select>
            </div>

        </div>

    </section>


    <!-- INFORMASI KEPEMILIKAN -->
    <div class="rounded-xl border border-blue-100 bg-blue-50 p-5">

        <p class="text-sm font-bold text-blue-800">
            Cabang Olahraga: {{ $cabor->nama }}
        </p>

        <p class="mt-2 text-xs text-blue-700">
            Pengurus otomatis menjadi bagian dari cabang olahraga
            yang terhubung dengan akun Anda.
            Cabang olahraga tidak dapat dipindahkan melalui formulir ini.
        </p>

    </div>


    <!-- ACTION -->
    <div class="flex flex-col-reverse justify-end gap-3 sm:flex-row">

        <a href="{{ route('cabor.pengurus.index') }}"
           class="rounded-xl border border-gray-200 bg-white px-6 py-3 text-center text-sm font-bold text-gray-600">
            Batal
        </a>

        <button
            type="submit"
            class="rounded-xl bg-[#E60012] px-7 py-3 text-sm font-bold text-white shadow-lg shadow-red-100 transition hover:bg-red-700">

            {{ $editing ? 'Simpan Perubahan' : 'Simpan Pengurus' }}

        </button>

    </div>

</form>

@endsection
