
@extends('layouts.admin')

@section('title', 'Manajemen Cabang Olahraga')
@section('heading', 'Cabang Olahraga')

@section('content')

    <!-- HEADER -->
    <div class="mb-7 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">

        <div>
            <h2 class="text-xl font-black">Manajemen Cabang Olahraga</h2>

            <p class="mt-1 text-sm text-gray-500">
                Kelola seluruh cabang olahraga KONI Kabupaten Klaten.
            </p>
        </div>

        <a href="{{ route('admin.cabor.create') }}"
           class="inline-flex items-center justify-center rounded-xl bg-[#E60012] px-5 py-3 text-sm font-bold text-white shadow-lg shadow-red-100 transition hover:bg-red-700">

            + Tambah Cabor

        </a>

    </div>


    <!-- NOTIFICATION -->
    @if(session('success'))
        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4 text-sm font-medium text-green-700">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif


    <!-- STATISTICS -->
    <div class="mb-7 grid gap-4 sm:grid-cols-3">

        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">Total Cabor</p>
            <h3 class="mt-2 text-3xl font-black">{{ $total }}</h3>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">Cabor Aktif</p>
            <h3 class="mt-2 text-3xl font-black text-green-600">{{ $aktif }}</h3>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">Cabor Nonaktif</p>
            <h3 class="mt-2 text-3xl font-black text-gray-500">{{ $nonaktif }}</h3>
        </div>

    </div>


    <!-- SEARCH AND FILTER -->
    <div class="mb-5 rounded-xl border border-gray-200 bg-white p-5">

        <form action="{{ route('admin.cabor.index') }}"
              method="GET"
              class="grid gap-3 md:grid-cols-[1fr_200px_auto]">

            <input
                type="text"
                name="q"
                value="{{ $search }}"
                placeholder="Cari nama cabor, singkatan, atau ketua..."
                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500 focus:ring-2 focus:ring-red-100"
            >

            <select
                name="status"
                class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500">

                <option value="">Semua Status</option>
                <option value="aktif" @selected($status === 'aktif')>Aktif</option>
                <option value="nonaktif" @selected($status === 'nonaktif')>Nonaktif</option>

            </select>

            <button
                type="submit"
                class="rounded-lg bg-[#171717] px-6 py-3 text-sm font-bold text-white hover:bg-gray-800">

                Cari Data

            </button>

        </form>

    </div>


    <!-- DATA TABLE -->
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">

        <div class="border-b border-gray-100 px-5 py-5">
            <h3 class="font-black">Daftar Cabang Olahraga</h3>

            <p class="mt-1 text-xs text-gray-500">
                Data cabang olahraga yang terdaftar dalam sistem.
            </p>
        </div>


        <div class="overflow-x-auto">

            <table class="w-full min-w-[850px] text-left">

                <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">

                    <tr>
                        <th class="px-5 py-4">Cabor</th>
                        <th class="px-5 py-4">Ketua</th>
                        <th class="px-5 py-4">Kontak</th>
                        <th class="px-5 py-4">Status</th>
                        <th class="px-5 py-4 text-center">Aksi</th>
                    </tr>

                </thead>


                <tbody class="divide-y divide-gray-100">

                    @forelse($cabors as $cabor)

                        <tr class="transition hover:bg-gray-50">

                            <!-- CABOR -->
                            <td class="px-5 py-4">

                                <div class="flex items-center gap-3">

                                    @if($cabor->logo_path)

                                        <img
                                            src="{{ asset('storage/'.$cabor->logo_path) }}"
                                            alt="{{ $cabor->nama }}"
                                            class="h-12 w-12 rounded-lg border border-gray-100 bg-white object-contain p-1"
                                        >

                                    @else

                                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-red-50 text-sm font-black text-red-600">
                                            {{ strtoupper(substr($cabor->nama, 0, 2)) }}
                                        </div>

                                    @endif


                                    <div>
                                        <p class="text-sm font-bold text-gray-900">
                                            {{ $cabor->nama }}
                                        </p>

                                        <p class="mt-1 text-xs text-gray-500">
                                            {{ $cabor->singkatan ?: 'Tanpa singkatan' }}
                                        </p>
                                    </div>

                                </div>

                            </td>


                            <!-- KETUA -->
                            <td class="px-5 py-4 text-sm text-gray-600">
                                {{ $cabor->nama_ketua ?: '-' }}
                            </td>


                            <!-- KONTAK -->
                            <td class="px-5 py-4">

                                <p class="text-sm text-gray-600">
                                    {{ $cabor->telepon ?: '-' }}
                                </p>

                                <p class="mt-1 text-xs text-gray-400">
                                    {{ $cabor->email ?: '-' }}
                                </p>

                            </td>


                            <!-- STATUS -->
                            <td class="px-5 py-4">

                                @if($cabor->status === 'aktif')

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
                                        href="{{ route('admin.cabor.edit', $cabor) }}"
                                        class="rounded-lg bg-yellow-50 px-3 py-2 text-xs font-bold text-yellow-700 hover:bg-yellow-100">

                                        Edit

                                    </a>


                                    <form
                                        action="{{ route('admin.cabor.destroy', $cabor) }}"
                                        method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus cabor ini?')">

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
                            <td colspan="5" class="px-5 py-16 text-center">

                                <div class="text-4xl">🏆</div>

                                <p class="mt-4 font-bold text-gray-700">
                                    Belum ada data cabang olahraga.
                                </p>

                                <p class="mt-2 text-sm text-gray-400">
                                    Silakan tambahkan cabang olahraga pertama.
                                </p>

                                <a href="{{ route('admin.cabor.create') }}"
                                   class="mt-5 inline-block rounded-lg bg-[#E60012] px-5 py-3 text-xs font-bold text-white">

                                    Tambah Cabor

                                </a>

                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        <!-- PAGINATION -->
        <div class="border-t border-gray-100 p-5">
            {{ $cabors->links() }}
        </div>

    </div>

@endsection
