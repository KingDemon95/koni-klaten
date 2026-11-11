
@extends('layouts.cabor')

@section('title', $jenis === 'atlet' ? 'Data Atlet' : 'Data Pelatih')
@section('heading', $jenis === 'atlet' ? 'Data Atlet' : 'Data Pelatih')

@section('content')

@php
    $judul = $jenis === 'atlet' ? 'Atlet' : 'Pelatih';

    $routeIndex = $jenis === 'atlet'
        ? 'cabor.atlet.index'
        : 'cabor.pelatih.index';
@endphp

@if(session('success'))
    <div class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4 text-sm font-medium text-green-700">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-medium text-red-700">
        {{ session('error') }}
    </div>
@endif

<div class="mb-7 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">

    <div>
        <h2 class="text-xl font-black">
            Manajemen Data {{ $judul }}
        </h2>

        <p class="mt-1 text-sm text-gray-500">
            Data {{ strtolower($judul) }}
            Cabang Olahraga {{ $cabor->nama }}.
        </p>
    </div>

    <a href="{{ route('cabor.'.$jenis.'.create') }}"
       class="inline-flex items-center justify-center rounded-xl bg-[#E60012] px-5 py-3 text-sm font-bold text-white transition hover:bg-red-700">

        + Tambah {{ $judul }}

    </a>

</div>

<!-- STATISTIK -->
<div class="mb-7 grid gap-4 sm:grid-cols-3">

    <div class="rounded-xl border border-gray-200 bg-white p-5">
        <p class="text-sm text-gray-500">
            Total {{ $judul }}
        </p>
        <h3 class="mt-2 text-3xl font-black">{{ $total }}</h3>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-5">
        <p class="text-sm text-gray-500">
            {{ $judul }} Aktif
        </p>
        <h3 class="mt-2 text-3xl font-black text-green-600">
            {{ $aktif }}
        </h3>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-5">
        <p class="text-sm text-gray-500">
            {{ $judul }} Nonaktif
        </p>
        <h3 class="mt-2 text-3xl font-black text-gray-500">
            {{ $nonaktif }}
        </h3>
    </div>

</div>


<!-- FILTER -->
<div class="mb-5 rounded-xl border border-gray-200 bg-white p-5">

    <form action="{{ route($routeIndex) }}"
          method="GET"
          class="flex flex-col gap-3 md:flex-row">

        <input
            type="text"
            name="q"
            value="{{ $search }}"
            placeholder="Cari nama {{ strtolower($judul) }}..."
            class="min-w-0 flex-1 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500"
        >

        <select
            name="status"
            class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm">

            <option value="">Semua Status</option>

            <option value="aktif" @selected($status === 'aktif')>
                Aktif
            </option>

            <option value="nonaktif" @selected($status === 'nonaktif')>
                Nonaktif
            </option>

        </select>

        <button
            type="submit"
            class="rounded-lg bg-[#171717] px-6 py-3 text-sm font-bold text-white">

            Cari Data

        </button>

        <a href="{{ route($routeIndex) }}"
           class="rounded-lg border border-gray-200 px-5 py-3 text-center text-sm font-bold">
            Reset
        </a>

    </form>

</div>


<!-- TABLE -->
<div class="overflow-hidden rounded-xl border border-gray-200 bg-white">

    <div class="border-b border-gray-100 px-5 py-5">
        <h3 class="font-black">Daftar {{ $judul }}</h3>

        <p class="mt-1 text-xs text-gray-500">
            Hanya menampilkan data cabang olahraga Anda.
        </p>
    </div>

    <div class="overflow-x-auto">

        <table class="w-full min-w-[600px] text-left">

            <thead class="bg-gray-50 text-xs uppercase text-gray-500">

                <tr>
                    <th class="px-5 py-4">Nama {{ $judul }}</th>
                    <th class="px-5 py-4">Cabang Olahraga</th>
                    <th class="px-5 py-4">Status</th>
                    <th class="px-5 py-4 text-center">Aksi</th>
                </tr>

            </thead>

            <tbody class="divide-y divide-gray-100">

                @forelse($items as $item)

                    <tr class="hover:bg-gray-50">

                        <td class="px-5 py-4">

                            <p class="text-sm font-bold">
                                {{ $item->nama_lengkap }}
                            </p>

                        </td>

                        <td class="px-5 py-4 text-sm text-gray-600">
                            {{ $cabor->nama }}
                        </td>

                        <td class="px-5 py-4">

                            @if($item->status === 'aktif')

                                <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-bold text-green-700">
                                    Aktif
                                </span>

                            @else

                                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-bold text-gray-600">
                                    Nonaktif
                                </span>

                            @endif

                        </td>
                
                        <td class="px-5 py-4">

                            <div class="flex items-center justify-center gap-2">

                                <a
                                    href="{{ route('cabor.'.$jenis.'.edit', $item->id) }}"
                                    class="rounded-lg bg-yellow-50 px-3 py-2 text-xs font-bold text-yellow-700 hover:bg-yellow-100">

                                    Edit

                                </a>

                                <form
                                    method="POST"
                                    action="{{ route('cabor.'.$jenis.'.destroy', $item->id) }}"
                                    onsubmit="return confirm('Yakin ingin menghapus data ini?')">

                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="rounded-lg bg-red-50 px-3 py-2 text-xs font-bold text-red-600 hover:bg-red-100">

                                        Hapus

                                    </button>

                                </form>

                            </div>

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="4" class="px-5 py-12 text-center text-sm text-gray-500">
                            Belum ada data {{ strtolower($judul) }} yang sesuai.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

    <div class="border-t border-gray-100 p-5">
        {{ $items->links() }}
    </div>

</div>

@endsection
