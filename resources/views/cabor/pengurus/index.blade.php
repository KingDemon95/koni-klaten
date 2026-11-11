
@extends('layouts.cabor')

@section('title', 'Struktur Organisasi Cabor')
@section('heading', 'Struktur Organisasi')

@section('content')

<div class="mb-7 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">

    <div>
        <h2 class="text-xl font-black">
            Struktur Organisasi Cabang Olahraga
        </h2>

        <p class="mt-1 text-sm text-gray-500">
            Kelola pengurus {{ $cabor->nama }}.
        </p>
    </div>

    <a href="{{ route('cabor.pengurus.create') }}"
       class="inline-flex items-center justify-center rounded-xl bg-[#E60012] px-5 py-3 text-sm font-bold text-white shadow-lg shadow-red-100 hover:bg-red-700">

        + Tambah Pengurus

    </a>

</div>


<!-- NOTIFIKASI -->
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


<!-- STATISTIK -->
<div class="mb-7 grid gap-4 sm:grid-cols-3">

    <div class="rounded-xl border border-gray-200 bg-white p-5">
        <p class="text-sm text-gray-500">Total Pengurus</p>
        <h3 class="mt-2 text-3xl font-black">
            {{ $total }}
        </h3>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-5">
        <p class="text-sm text-gray-500">Pengurus Aktif</p>
        <h3 class="mt-2 text-3xl font-black text-green-600">
            {{ $aktif }}
        </h3>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-5">
        <p class="text-sm text-gray-500">Pengurus Nonaktif</p>
        <h3 class="mt-2 text-3xl font-black text-gray-500">
            {{ $nonaktif }}
        </h3>
    </div>

</div>


<!-- FILTER -->
<div class="mb-5 rounded-xl border border-gray-200 bg-white p-5">

    <form
        action="{{ route('cabor.pengurus.index') }}"
        method="GET"
        class="flex flex-col gap-3 sm:flex-row">

        <select
            name="status"
            class="min-w-0 flex-1 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm">

            <option value="">Semua Status</option>

            <option value="aktif"
                @selected($status === 'aktif')>
                Aktif
            </option>

            <option value="nonaktif"
                @selected($status === 'nonaktif')>
                Nonaktif
            </option>

        </select>

        <button
            type="submit"
            class="rounded-lg bg-[#171717] px-6 py-3 text-sm font-bold text-white">
            Terapkan Filter
        </button>

        <a href="{{ route('cabor.pengurus.index') }}"
           class="rounded-lg border border-gray-200 px-5 py-3 text-center text-sm font-bold">
            Reset
        </a>

    </form>

</div>


<!-- DATA PENGURUS -->
<div class="overflow-hidden rounded-xl border border-gray-200 bg-white">

    <div class="border-b border-gray-100 p-5">

        <h3 class="font-black">
            Daftar Pengurus {{ $cabor->nama }}
        </h3>

        <p class="mt-1 text-xs text-gray-500">
            Urutan pengurus mengikuti pengaturan urutan tampil.
        </p>

    </div>

    <div class="overflow-x-auto">

        <table class="w-full min-w-[950px] text-left">

            <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                <tr>
                    <th class="px-5 py-4">Pengurus</th>
                    <th class="px-5 py-4">Jabatan / Bidang</th>
                    <th class="px-5 py-4">Periode</th>
                    <th class="px-5 py-4">Urutan</th>
                    <th class="px-5 py-4">Status</th>
                    <th class="px-5 py-4 text-center">Aksi</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">

                @forelse($pengurus as $item)

                    <tr class="transition hover:bg-gray-50">

                        <!-- PENGURUS -->
                        <td class="px-5 py-4">

                            <div class="flex items-center gap-3">

                                @if($item->foto_path)

                                    <img
                                        src="{{ asset('storage/'.$item->foto_path) }}"
                                        alt="{{ $item->nama_lengkap }}"
                                        class="h-12 w-12 shrink-0 rounded-lg border border-gray-100 object-cover"
                                    >

                                @else

                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-red-50 text-lg font-black text-red-600">
                                        {{ strtoupper(substr($item->nama_lengkap, 0, 1)) }}
                                    </div>

                                @endif

                                <div>

                                    <p class="text-sm font-bold text-gray-900">
                                        {{ $item->nama_lengkap }}
                                    </p>

                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ $item->no_telepon ?: '-' }}
                                    </p>

                                </div>

                            </div>

                        </td>


                        <!-- JABATAN -->
                        <td class="px-5 py-4">

                            <p class="text-sm font-semibold text-gray-700">
                                {{ $item->jabatan }}
                            </p>

                            <p class="mt-1 text-xs text-gray-500">
                                {{ $item->bidang ?: '-' }}
                            </p>

                        </td>


                        <!-- PERIODE -->
                        <td class="px-5 py-4 text-sm text-gray-600">

                            @if($item->periode_mulai || $item->periode_selesai)

                                {{ $item->periode_mulai ?: '?' }}
                                -
                                {{ $item->periode_selesai ?: '?' }}

                            @else

                                -

                            @endif

                        </td>


                        <!-- URUTAN -->
                        <td class="px-5 py-4 text-sm font-semibold text-gray-600">
                            {{ $item->urutan }}
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


                        <!-- AKSI -->
                        <td class="px-5 py-4">

                            <div class="flex items-center justify-center gap-2">

                                <a
                                    href="{{ route('cabor.pengurus.edit', $item->id) }}"
                                    class="rounded-lg bg-yellow-50 px-3 py-2 text-xs font-bold text-yellow-700 hover:bg-yellow-100">
                                    Edit
                                </a>

                                <form
                                    action="{{ route('cabor.pengurus.destroy', $item->id) }}"
                                    method="POST"
                                    onsubmit="return confirm('Yakin ingin menghapus pengurus ini?')">

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
                        <td colspan="6" class="px-5 py-12 text-center">

                            <p class="font-bold text-gray-600">
                                Belum ada data pengurus.
                            </p>

                            <p class="mt-1 text-sm text-gray-400">
                                Tambahkan pengurus cabang olahraga untuk memulai.
                            </p>

                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

    <div class="border-t border-gray-100 p-5">
        {{ $pengurus->links() }}
    </div>

</div>

@endsection
