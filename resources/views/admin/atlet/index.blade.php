
@extends('layouts.admin')

@section('title', 'Manajemen Atlet')
@section('heading', 'Data Atlet')

@section('content')

    <!-- HEADER -->
    <div class="mb-7 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">

        <div>
            <h2 class="text-xl font-black">Manajemen Data Atlet</h2>

            <p class="mt-1 text-sm text-gray-500">
                Kelola seluruh data atlet KONI Kabupaten Klaten.
            </p>
        </div>

        <a href="{{ route('admin.atlet.create') }}"
           class="rounded-xl bg-[#E60012] px-5 py-3 text-center text-sm font-bold text-white shadow-lg shadow-red-100 hover:bg-red-700">

            + Tambah Atlet

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
    <div class="mb-7 grid gap-4 sm:grid-cols-3">

        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">Total Atlet</p>
            <h3 class="mt-2 text-3xl font-black">{{ $total }}</h3>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">Atlet Aktif</p>
            <h3 class="mt-2 text-3xl font-black text-green-600">{{ $aktif }}</h3>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">Atlet Nonaktif</p>
            <h3 class="mt-2 text-3xl font-black text-gray-500">{{ $nonaktif }}</h3>
        </div>

    </div>


    <!-- SEARCH & FILTER -->
    <div class="mb-5 rounded-xl border border-gray-200 bg-white p-5">

        <form action="{{ route('admin.atlet.index') }}"
              method="GET"
              class="grid gap-3 md:grid-cols-3">

            <input
                type="text"
                name="q"
                value="{{ $search }}"
                placeholder="Cari nama, email, atau nomor telepon..."
                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500"
            >


            <select
                name="cabor_id"
                class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500">

                <option value="">Semua Cabang Olahraga</option>

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


            <div class="flex gap-2 md:col-span-3">

                <button
                    type="submit"
                    class="rounded-lg bg-[#171717] px-6 py-3 text-sm font-bold text-white hover:bg-gray-800">

                    Cari Atlet

                </button>

                <a
                    href="{{ route('admin.atlet.index') }}"
                    class="rounded-lg border border-gray-200 px-6 py-3 text-sm font-bold text-gray-600 hover:bg-gray-50">

                    Reset

                </a>

            </div>

        </form>

    </div>


    <!-- DATA TABLE -->
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">

        <div class="border-b border-gray-100 px-5 py-5">

            <h3 class="font-black">Daftar Atlet</h3>

            <p class="mt-1 text-xs text-gray-500">
                Seluruh atlet yang telah terdaftar.
            </p>

        </div>


        <div class="overflow-x-auto">

            <table class="w-full min-w-[950px] text-left">

                <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">

                    <tr>
                        <th class="px-5 py-4">Atlet</th>
                        <th class="px-5 py-4">Jenis Kelamin</th>
                        <th class="px-5 py-4">Cabang Olahraga</th>
                        <th class="px-5 py-4">Tanggal Lahir</th>
                        <th class="px-5 py-4">Status</th>
                        <th class="px-5 py-4 text-center">Aksi</th>
                    </tr>

                </thead>


                <tbody class="divide-y divide-gray-100">

                    @forelse($atlets as $atlet)

                        <tr class="transition hover:bg-gray-50">

                            <!-- PROFILE -->
                            <td class="px-5 py-4">

                                <div class="flex items-center gap-3">

                                    @if($atlet->foto_path)

                                        <img
                                            src="{{ asset('storage/'.$atlet->foto_path) }}"
                                            alt="{{ $atlet->nama_lengkap }}"
                                            class="h-12 w-12 rounded-full border border-gray-100 object-cover"
                                        >

                                    @else

                                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-50 font-black text-red-600">
                                            {{ strtoupper(substr($atlet->nama_lengkap, 0, 1)) }}
                                        </div>

                                    @endif


                                    <div>

                                        <p class="text-sm font-bold text-gray-900">
                                            {{ $atlet->nama_lengkap }}
                                        </p>

                                        <p class="mt-1 text-xs text-gray-500">
                                            {{ $atlet->email ?: 'Email belum tersedia' }}
                                        </p>

                                    </div>

                                </div>

                            </td>


                            <!-- GENDER -->
                            <td class="px-5 py-4 text-sm text-gray-600">

                                {{ $atlet->jenis_kelamin === 'L'
                                    ? 'Laki-laki'
                                    : 'Perempuan' }}

                            </td>


                            <!-- CABOR -->
                            <td class="px-5 py-4">

                                <p class="text-sm font-semibold text-gray-700">
                                    {{ $atlet->cabangOlahraga->nama }}
                                </p>

                                <p class="mt-1 text-xs text-gray-400">
                                    {{ $atlet->cabangOlahraga->singkatan ?: '-' }}
                                </p>

                            </td>


                            <!-- BIRTH -->
                            <td class="px-5 py-4 text-sm text-gray-600">

                                @if($atlet->tanggal_lahir)

                                    {{ $atlet->tanggal_lahir->format('d/m/Y') }}

                                @else

                                    -

                                @endif

                            </td>


                            <!-- STATUS -->
                            <td class="px-5 py-4">

                                @if($atlet->status === 'aktif')

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
                                        href="{{ route('admin.atlet.edit', $atlet) }}"
                                        class="rounded-lg bg-yellow-50 px-3 py-2 text-xs font-bold text-yellow-700 hover:bg-yellow-100">

                                        Edit

                                    </a>


                                    <form
                                        action="{{ route('admin.atlet.destroy', $atlet) }}"
                                        method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus data atlet ini?')">

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

                            <td colspan="6" class="px-5 py-16 text-center">

                                <div class="text-4xl">🏅</div>

                                <p class="mt-4 font-bold text-gray-700">
                                    Belum ada data atlet.
                                </p>

                                <p class="mt-2 text-sm text-gray-400">
                                    Tambahkan atlet pertama untuk memulai.
                                </p>

                                <a href="{{ route('admin.atlet.create') }}"
                                   class="mt-5 inline-block rounded-lg bg-[#E60012] px-5 py-3 text-xs font-bold text-white">

                                    Tambah Atlet

                                </a>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        <div class="border-t border-gray-100 p-5">
            {{ $atlets->links() }}
        </div>

    </div>

@endsection
