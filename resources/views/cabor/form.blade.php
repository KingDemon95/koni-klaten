
@extends('layouts.cabor')

@php
    $editing = $item->exists;
    $judul = $jenis === 'atlet' ? 'Atlet' : 'Pelatih';

    $routeBase = 'cabor.'.$jenis;

    $formAction = $editing
        ? route($routeBase.'.update', $item->id)
        : route($routeBase.'.store');
@endphp

@section('title', ($editing ? 'Edit ' : 'Tambah ').$judul)
@section('heading', ($editing ? 'Edit ' : 'Tambah ').$judul)

@section('content')

<div class="mb-7">

    <a href="{{ route($routeBase.'.index') }}"
       class="text-sm font-semibold text-gray-500 hover:text-red-600">
        ← Kembali ke Daftar {{ $judul }}
    </a>

    <h2 class="mt-4 text-xl font-black">
        {{ $editing ? 'Edit Data' : 'Tambah Data' }} {{ $judul }}
    </h2>

    <p class="mt-1 text-sm text-gray-500">
        Cabang Olahraga:
        <strong>{{ $cabor->nama }}</strong>
    </p>

</div>

@if($errors->any())
    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4">
        <p class="font-bold text-red-700">
            Periksa kembali formulir:
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
      enctype="multipart/form-data">

    @csrf

    @if($editing)
        @method('PUT')
    @endif

    <div class="grid gap-6 lg:grid-cols-2">

        <!-- IDENTITAS -->
        <section class="rounded-xl border border-gray-200 bg-white p-6">

            <h3 class="mb-6 border-b border-gray-100 pb-4 font-black">
                Identitas {{ $judul }}
            </h3>

            <div class="space-y-5">

                <div>
                    <label class="mb-2 block text-sm font-bold">
                        Nama Lengkap *
                    </label>
                    <input
                        name="nama_lengkap"
                        type="text"
                        required
                        maxlength="150"
                        value="{{ old('nama_lengkap', $item->nama_lengkap) }}"
                        class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-red-500"
                    >
                </div>

                <div>
                    <label class="mb-2 block text-sm font-bold">
                        Jenis Kelamin *
                    </label>
                    <select name="jenis_kelamin"
                            required
                            class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm">

                        <option value="">Pilih Jenis Kelamin</option>

                        <option value="L"
                            @selected(old('jenis_kelamin', $item->jenis_kelamin) === 'L')>
                            Laki-laki
                        </option>

                        <option value="P"
                            @selected(old('jenis_kelamin', $item->jenis_kelamin) === 'P')>
                            Perempuan
                        </option>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-bold">
                        Tempat Lahir
                    </label>
                    <input
                        name="tempat_lahir"
                        type="text"
                        maxlength="100"
                        value="{{ old('tempat_lahir', $item->tempat_lahir) }}"
                        class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm"
                    >
                </div>

                <div>
                    <label class="mb-2 block text-sm font-bold">
                        Tanggal Lahir
                    </label>
                    <input
                        name="tanggal_lahir"
                        type="date"
                        max="{{ now()->toDateString() }}"
                        value="{{ old('tanggal_lahir', $item->tanggal_lahir ? substr((string) $item->tanggal_lahir, 0, 10) : '') }}"
                        class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm"
                    >
                </div>

                <div>
                    <label class="mb-2 block text-sm font-bold">
                        Alamat
                    </label>
                    <textarea
                        name="alamat"
                        rows="3"
                        maxlength="2000"
                        class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm"
                    >{{ old('alamat', $item->alamat) }}</textarea>
                </div>

            </div>

        </section>


        <!-- KONTAK DAN STATUS -->
        <section class="rounded-xl border border-gray-200 bg-white p-6">

            <h3 class="mb-6 border-b border-gray-100 pb-4 font-black">
                Kontak dan Status
            </h3>

            <div class="space-y-5">

                <div>
                    <label class="mb-2 block text-sm font-bold">
                        Nomor Telepon
                    </label>
                    <input
                        name="no_telepon"
                        type="text"
                        maxlength="30"
                        value="{{ old('no_telepon', $item->no_telepon) }}"
                        class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm"
                    >
                </div>

                <div>
                    <label class="mb-2 block text-sm font-bold">
                        Email
                    </label>
                    <input
                        name="email"
                        type="email"
                        maxlength="255"
                        value="{{ old('email', $item->email) }}"
                        class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm"
                    >
                </div>

                <div>
                    <label class="mb-2 block text-sm font-bold">
                        Status *
                    </label>
                    <select
                        name="status"
                        required
                        class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm">

                        <option value="aktif"
                            @selected(old('status', $item->status ?? 'aktif') === 'aktif')>
                            Aktif
                        </option>

                        <option value="nonaktif"
                            @selected(old('status', $item->status ?? 'aktif') === 'nonaktif')>
                            Nonaktif
                        </option>

                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-bold">
                        Foto {{ $judul }} (Maksimal 2 MB)
                    </label>

                    @if($item->foto_path)
                        <img
                            src="{{ asset('storage/'.$item->foto_path) }}"
                            alt="Foto {{ $judul }}"
                            class="mb-3 h-28 w-28 rounded-xl border border-gray-200 object-cover"
                        >
                    @endif

                    <input
                        type="file"
                        name="foto"
                        accept=".jpg,.jpeg,.png,.webp"
                        class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm"
                    >

                    <p class="mt-2 text-xs text-gray-500">
                        Kosongkan untuk mempertahankan foto sebelumnya.
                    </p>
                </div>

            </div>

        </section>

    </div>


    <!-- KHUSUS PELATIH -->
    @if($jenis === 'pelatih')

        <section class="mt-6 rounded-xl border border-gray-200 bg-white p-6">

            <h3 class="mb-6 border-b border-gray-100 pb-4 font-black">
                Informasi Kepelatihan
            </h3>

            <div class="grid gap-5 md:grid-cols-2">

                <div>
                    <label class="mb-2 block text-sm font-bold">
                        Pendidikan Terakhir
                    </label>
                    <input
                        type="text"
                        name="pendidikan_terakhir"
                        maxlength="150"
                        value="{{ old('pendidikan_terakhir', $item->pendidikan_terakhir) }}"
                        class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm"
                    >
                </div>

                <div>
                    <label class="mb-2 block text-sm font-bold">
                        Spesialisasi
                    </label>
                    <input
                        type="text"
                        name="spesialisasi"
                        maxlength="150"
                        value="{{ old('spesialisasi', $item->spesialisasi) }}"
                        class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm"
                    >
                </div>

                <div>
                    <label class="mb-2 block text-sm font-bold">
                        Pengalaman (Tahun)
                    </label>
                    <input
                        type="number"
                        name="pengalaman_tahun"
                        min="0"
                        max="60"
                        value="{{ old('pengalaman_tahun', $item->pengalaman_tahun) }}"
                        class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm"
                    >
                </div>

                <div>
                    <label class="mb-2 block text-sm font-bold">
                        Sertifikasi
                    </label>
                    <textarea
                        name="sertifikasi"
                        rows="3"
                        maxlength="5000"
                        class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm"
                    >{{ old('sertifikasi', $item->sertifikasi) }}</textarea>
                </div>

            </div>

        </section>

    @endif


    <!-- INFORMASI CABOR -->
    <div class="mt-6 rounded-xl border border-blue-100 bg-blue-50 p-5">

        <p class="text-sm font-bold text-blue-800">
            Cabang Olahraga: {{ $cabor->nama }}
        </p>

        <p class="mt-2 text-xs text-blue-700">
            Data akan otomatis terhubung dengan cabang olahraga
            akun Admin Cabor Anda. Cabang olahraga tidak dapat
            dipindahkan melalui formulir ini.
        </p>

    </div>


    <div class="mt-7 flex flex-col-reverse justify-end gap-3 sm:flex-row">

        <a
            href="{{ route($routeBase.'.index') }}"
            class="rounded-xl border border-gray-200 bg-white px-6 py-3 text-center text-sm font-bold text-gray-600">
            Batal
        </a>

        <button
            type="submit"
            class="rounded-xl bg-[#E60012] px-7 py-3 text-sm font-bold text-white transition hover:bg-red-700">
            {{ $editing ? 'Simpan Perubahan' : 'Simpan '.$judul }}
        </button>

    </div>

</form>

@endsection
