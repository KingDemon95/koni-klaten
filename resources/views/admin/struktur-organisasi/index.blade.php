
@extends('layouts.admin')

@section('title', 'Struktur Organisasi')
@section('heading', 'Struktur Organisasi')

@section('content')

    <!-- HEADER -->
    <div class="mb-7 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">

        <div>
            <h2 class="text-xl font-black">
                Struktur Organisasi KONI Klaten
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Kelola pengurus KONI dan pengurus seluruh cabang olahraga.
            </p>
        </div>

        <a href="{{ route('admin.struktur-organisasi.create') }}"
           class="rounded-xl bg-[#E60012] px-5 py-3 text-center text-sm font-bold text-white shadow-lg shadow-red-100 hover:bg-red-700">

            + Tambah Pengurus

        </a>

    </div>


    <!-- NOTIFICATION -->
    @if(session('success'))
        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif


    <!-- STATISTICS -->
    <div class="mb-7 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">

        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">Total Pengurus</p>
            <h3 class="mt-2 text-3xl font-black">{{ $total }}</h3>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">Pengurus KONI</p>
            <h3 class="mt-2 text-3xl font-black">{{ $pengurusKoni }}</h3>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">Pengurus Aktif</p>
            <h3 class="mt-2 text-3xl font-black text-green-600">{{ $aktif }}</h3>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">Pengurus Nonaktif</p>
            <h3 class="mt-2 text-3xl font-black text-gray-500">{{ $nonaktif }}</h3>
        </div>

    </div>


    <!-- FILTER -->
    <div class="mb-5 rounded-xl border border-gray-200 bg-white p-5">

        <form action="{{ route('admin.struktur-organisasi.index') }}"
              method="GET"
              class="grid gap-3 md:grid-cols-2 lg:grid-cols-4">

            <input
                type="text"
                name="q"
                value="{{ $search }}"
                placeholder="Cari nama, jabatan, bidang..."
                class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500"
            >

            <select
                name="tingkat"
                class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500">

                <option value="">Semua Organisasi</option>

                <option value="koni" @selected($tingkat === 'koni')>
                    Pengurus KONI
                </option>

                <option value="cabor" @selected($tingkat === 'cabor')>
                    Pengurus Cabor
                </option>

            </select>


            <select
                name="cabor_id"
                class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500">

                <option value="">Semua Cabor</option>

                @foreach($cabors as $cabor)

                    <option
                        value="{{ $cabor->id }}"
                        @selected((string) $caborId === (string) $cabor->id)
                    >
                        {{ $cabor->nama }}
                    </option>

                @endforeach

            </select>


            <select
                name="status"
                class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500">

                <option value="">Semua Status</option>

                <option value="aktif" @selected($status === 'aktif')>
                    Aktif
                </option>

                <option value="nonaktif" @selected($status === 'nonaktif')>
                    Nonaktif
                </option>

            </select>


            <div class="flex gap-2 md:col-span-2 lg:col-span-4">

                <button
                    type="submit"
                    class="rounded-lg bg-[#171717] px-6 py-3 text-sm font-bold text-white">

                    Cari Pengurus

                </button>

                <a
                    href="{{ route('admin.struktur-organisasi.index') }}"
                    class="rounded-lg border border-gray-200 px-6 py-3 text-sm font-bold text-gray-600">

                    Reset

                </a>

            </div>

        </form>

    </div>


    <!-- TABLE -->
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">

        <div class="border-b border-gray-100 px-5 py-5">

            <h3 class="font-black">Daftar Pengurus</h3>

            <p class="mt-1 text-xs text-gray-500">
                Data kepengurusan KONI dan cabang olahraga.
            </p>

        </div>


        <div class="overflow-x-auto">

            <table class="w-full min-w-[1000px] text-left">

                <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">

                    <tr>
                        <th class="px-5 py-4">Nama Pengurus</th>
                        <th class="px-5 py-4">Jabatan</th>
                        <th class="px-5 py-4">Organisasi</th>
                        <th class="px-5 py-4">Periode</th>
                        <th class="px-5 py-4">Status</th>
                        <th class="px-5 py-4 text-center">Aksi</th>
                    </tr>

                </thead>


                <tbody class="divide-y divide-gray-100">

                    @forelse($penguruses as $pengurus)

                        <tr class="transition hover:bg-gray-50">

                            <!-- PROFILE -->
                            <td class="px-5 py-4">

                                <div class="flex items-center gap-3">

                                    @if($pengurus->foto_path)

                                        <img
                                            src="{{ asset('storage/'.$pengurus->foto_path) }}"
                                            alt="{{ $pengurus->nama_lengkap }}"
                                            class="h-12 w-12 rounded-full border border-gray-100 object-cover"
                                        >

                                    @else

                                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-50 font-black text-red-600">
                                            {{ strtoupper(substr($pengurus->nama_lengkap, 0, 1)) }}
                                        </div>

                                    @endif


                                    <div>

                                        <p class="text-sm font-bold text-gray-900">
                                            {{ $pengurus->nama_lengkap }}
                                        </p>

                                        <p class="mt-1 text-xs text-gray-500">
                                            {{ $pengurus->email ?: 'Email belum tersedia' }}
                                        </p>

                                    </div>

                                </div>

                            </td>


                            <!-- POSITION -->
                            <td class="px-5 py-4">

                                <p class="text-sm font-semibold text-gray-700">
                                    {{ $pengurus->jabatan }}
                                </p>

                                <p class="mt-1 text-xs text-gray-400">
                                    {{ $pengurus->bidang ?: '-' }}
                                </p>

                            </td>


                            <!-- ORGANIZATION -->
                            <td class="px-5 py-4">

                                @if($pengurus->cabangOlahraga)

                                    <span class="rounded-lg bg-blue-50 px-3 py-2 text-xs font-bold text-blue-700">
                                        {{ $pengurus->cabangOlahraga->nama }}
                                    </span>

                                @else

                                    <span class="rounded-lg bg-red-50 px-3 py-2 text-xs font-bold text-red-700">
                                        KONI Klaten
                                    </span>

                                @endif

                            </td>


                            <!-- PERIOD -->
                            <td class="px-5 py-4 text-sm text-gray-600">

                                {{ $pengurus->periode_mulai ?: '-' }}
                                -
                                {{ $pengurus->periode_selesai ?: '-' }}

                            </td>


                            <!-- STATUS -->
                            <td class="px-5 py-4">

                                @if($pengurus->status === 'aktif')

                                    <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-bold text-green-700">
                                        Aktif
                                    </span>

                                @else

                                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-bold text-gray-600">
                                        Nonaktif
                                    </span>

                                @endif

                            </td>


                            <!-- ACTION -->
                            <td class="px-5 py-4">

                                <div class="flex items-center justify-center gap-2">

                                    <a
                                        href="{{ route('admin.struktur-organisasi.edit', $pengurus) }}"
                                        class="rounded-lg bg-yellow-50 px-3 py-2 text-xs font-bold text-yellow-700">

                                        Edit

                                    </a>


                                    <form
                                        action="{{ route('admin.struktur-organisasi.destroy', $pengurus) }}"
                                        method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus pengurus ini?')">

                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="rounded-lg bg-red-50 px-3 py-2 text-xs font-bold text-red-600">

                                            Hapus

                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="6" class="px-5 py-16 text-center">

                                <div class="text-4xl">🏛️</div>

                                <p class="mt-4 font-bold text-gray-700">
                                    Belum ada data pengurus.
                                </p>

                                <p class="mt-2 text-sm text-gray-400">
                                    Tambahkan pengurus KONI atau cabor pertama.
                                </p>

                                <a href="{{ route('admin.struktur-organisasi.create') }}"
                                   class="mt-5 inline-block rounded-lg bg-[#E60012] px-5 py-3 text-xs font-bold text-white">

                                    Tambah Pengurus

                                </a>

                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        <div class="border-t border-gray-100 p-5">
            {{ $penguruses->links() }}
        </div>

    </div>

@endsection
