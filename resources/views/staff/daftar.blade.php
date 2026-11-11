
@extends('layouts.staff')

@section('title', 'Data '.$judul)
@section('heading', 'Data '.$judul)

@section('content')

@php
    $routeIndex = 'staff.'.$modul.'.index';
@endphp

<!-- HEADER -->
<div class="mb-7">

    <h2 class="text-xl font-black">
        Data {{ $judul }}
    </h2>

    <p class="mt-1 text-sm text-gray-500">
        Daftar {{ strtolower($judul) }}
        dalam sistem KONI Kabupaten Klaten.
    </p>

    <span class="mt-3 inline-block rounded-full bg-yellow-100 px-3 py-1 text-xs font-bold text-yellow-800">
        Akses Baca Saja
    </span>

</div>


<!-- STATISTIK GLOBAL -->
<div class="mb-7 grid gap-4 sm:grid-cols-3">

    <div class="rounded-xl border border-gray-200 bg-white p-5">

        <p class="text-sm text-gray-500">
            Total {{ $judul }}
        </p>

        <h3 class="mt-2 text-3xl font-black">
            {{ $total }}
        </h3>

    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-5">

        <p class="text-sm text-gray-500">
            Aktif
        </p>

        <h3 class="mt-2 text-3xl font-black text-green-600">
            {{ $aktif }}
        </h3>

    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-5">

        <p class="text-sm text-gray-500">
            Nonaktif
        </p>

        <h3 class="mt-2 text-3xl font-black text-gray-500">
            {{ $nonaktif }}
        </h3>

    </div>

</div>

<p class="mb-4 text-xs text-gray-500">
    Statistik di atas menghitung seluruh data, tidak mengikuti filter tabel.
</p>


<!-- SEARCH AND FILTER -->
<div class="mb-5 rounded-xl border border-gray-200 bg-white p-5">

    <form
        action="{{ route($routeIndex) }}"
        method="GET"
        class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">

        <input
            type="text"
            name="q"
            maxlength="100"
            value="{{ $search }}"
            placeholder="Cari {{ strtolower($judul) }}..."
            class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500"
        >

        @if($modul !== 'cabor')

            <select
                name="cabor_id"
                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm">

                <option value="">Semua Cabor</option>

                @foreach($cabors as $cabor)

                    <option
                        value="{{ $cabor->id }}"
                        @selected((string) $caborId === (string) $cabor->id)>

                        {{ $cabor->nama }}

                    </option>

                @endforeach

            </select>

        @endif

        <select
            name="status"
            class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm">

            <option value="">Semua Status</option>

            <option
                value="aktif"
                @selected($status === 'aktif')>
                Aktif
            </option>

            <option
                value="nonaktif"
                @selected($status === 'nonaktif')>
                Nonaktif
            </option>

        </select>

        <div class="flex gap-2">

            <button
                type="submit"
                class="flex-1 rounded-lg bg-[#171717] px-5 py-3 text-sm font-bold text-white hover:bg-gray-800">
                Cari Data
            </button>

            <a
                href="{{ route($routeIndex) }}"
                class="rounded-lg border border-gray-200 px-4 py-3 text-center text-sm font-bold text-gray-600 hover:bg-gray-50">
                Reset
            </a>

        </div>

    </form>

</div>


<!-- DATA TABLE -->
<div class="overflow-hidden rounded-xl border border-gray-200 bg-white">

    <div class="border-b border-gray-100 px-5 py-5">

        <h3 class="font-black">
            Daftar {{ $judul }}
        </h3>

        <p class="mt-1 text-xs text-gray-500">
            Data ditampilkan untuk keperluan monitoring Staff KONI.
        </p>

    </div>

    <div class="overflow-x-auto">

        <table class="w-full min-w-[700px] text-left">

            <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">

                <tr>
                    <th class="px-5 py-4">Nama</th>
                    <th class="px-5 py-4">
                        {{ $modul === 'cabor' ? 'Singkatan' : 'Cabang Olahraga' }}
                    </th>
                    <th class="px-5 py-4">Keterangan</th>
                    <th class="px-5 py-4">Status</th>
                </tr>

            </thead>

            <tbody class="divide-y divide-gray-100">

                @forelse($items as $item)

                    <tr class="transition hover:bg-gray-50">

                        <!-- NAMA -->
                        <td class="px-5 py-4">

                            <p class="text-sm font-bold text-gray-900">

                                {{ $modul === 'cabor'
                                    ? $item->nama
                                    : $item->nama_lengkap }}

                            </p>

                        </td>


                        <!-- CABOR / SINGKATAN -->
                        <td class="px-5 py-4 text-sm text-gray-600">

                            @if($modul === 'cabor')

                                {{ $item->singkatan ?: '-' }}

                            @elseif(
                                $modul === 'pengurus' &&
                                $item->cabang_olahraga_id === null
                            )

                                KONI Kabupaten

                            @else

                                {{ $item->cabangOlahraga?->nama ?? '-' }}

                            @endif

                        </td>


                        <!-- KETERANGAN -->
                        <td class="px-5 py-4 text-sm text-gray-600">

                            @if($modul === 'cabor')

                                {{ $item->nama_ketua ?: '-' }}

                            @elseif($modul === 'pengurus')

                                {{ $item->jabatan ?: '-' }}

                            @elseif($modul === 'pelatih')

                                {{ $item->spesialisasi ?: '-' }}

                            @else

                                -

                            @endif

                        </td>


                        <!-- STATUS -->
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

                    </tr>

                @empty

                    <tr>

                        <td
                            colspan="4"
                            class="px-5 py-12 text-center">

                            <p class="font-bold text-gray-600">
                                Data tidak ditemukan.
                            </p>

                            <p class="mt-1 text-sm text-gray-400">
                                Coba ubah kata kunci atau filter pencarian.
                            </p>

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>


    <!-- PAGINATION -->
    <div class="border-t border-gray-100 px-5 py-5">
        {{ $items->links() }}
    </div>

</div>

@endsection
