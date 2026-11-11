
@extends('layouts.admin')

@section('title', 'Manajemen Pengguna')
@section('heading', 'Manajemen Pengguna')

@section('content')

@php
    $roleLabels = [
        'admin_koni' => 'Admin KONI',
        'staff_koni' => 'Staff KONI',
        'admin_cabor' => 'Admin Cabor',
        'atlet' => 'Atlet',
        'pelatih' => 'Pelatih',
        'atlet_pelatih' => 'Atlet / Pelatih',
    ];
@endphp

<!-- HEADER -->
<div class="mb-7 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">

    <div>
        <h2 class="text-xl font-black">Manajemen Pengguna</h2>

        <p class="mt-1 text-sm text-gray-500">
            Kelola akun dan hak akses seluruh pengguna Website KONI Klaten.
        </p>
    </div>

    <a href="{{ route('admin.pengguna.create') }}"
       class="inline-flex items-center justify-center rounded-xl bg-[#E60012] px-5 py-3 text-sm font-bold text-white shadow-lg shadow-red-100 transition hover:bg-red-700">

        + Tambah Pengguna

    </a>

</div>


<!-- NOTIFICATION -->
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


<!-- STATISTICS -->
<div class="mb-7 grid gap-4 sm:grid-cols-3">

    <div class="rounded-xl border border-gray-200 bg-white p-5">
        <p class="text-sm text-gray-500">Total Pengguna</p>
        <h3 class="mt-2 text-3xl font-black">{{ $total }}</h3>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-5">
        <p class="text-sm text-gray-500">Akun Aktif</p>
        <h3 class="mt-2 text-3xl font-black text-green-600">
            {{ $aktif }}
        </h3>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-5">
        <p class="text-sm text-gray-500">Akun Nonaktif</p>
        <h3 class="mt-2 text-3xl font-black text-gray-500">
            {{ $nonaktif }}
        </h3>
    </div>

</div>


<!-- SEARCH AND FILTER -->
<div class="mb-5 rounded-xl border border-gray-200 bg-white p-5">

    <form action="{{ route('admin.pengguna.index') }}"
          method="GET"
          class="grid gap-3 md:grid-cols-[1fr_200px_180px_auto]">

        <input
            type="text"
            name="q"
            value="{{ $search }}"
            placeholder="Cari nama atau email pengguna..."
            class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500 focus:ring-2 focus:ring-red-100"
        >

        <select
            name="role"
            class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500">

            <option value="">Semua Role</option>

            @foreach($roleLabels as $value => $label)
                <option value="{{ $value }}" @selected($role === $value)>
                    {{ $label }}
                </option>
            @endforeach

        </select>

        <select
            name="status"
            class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500">

            <option value="">Semua Status</option>
            <option value="aktif" @selected($status === 'aktif')>Aktif</option>
            <option value="nonaktif" @selected($status === 'nonaktif')>Nonaktif</option>

        </select>

        <button
            type="submit"
            class="rounded-lg bg-[#171717] px-6 py-3 text-sm font-bold text-white transition hover:bg-gray-800">

            Cari Data

        </button>

    </form>

</div>


<!-- DATA TABLE -->
<div class="overflow-hidden rounded-xl border border-gray-200 bg-white">

    <div class="border-b border-gray-100 px-5 py-5">

        <h3 class="font-black">Daftar Pengguna</h3>

        <p class="mt-1 text-xs text-gray-500">
            Seluruh akun yang terdaftar dalam sistem KONI Kabupaten Klaten.
        </p>

    </div>


    <div class="overflow-x-auto">

        <table class="w-full min-w-[900px] text-left">

            <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">

                <tr>
                    <th class="px-5 py-4">Pengguna</th>
                    <th class="px-5 py-4">Role</th>
                    <th class="px-5 py-4">Cabang / Profil</th>
                    <th class="px-5 py-4">Status</th>
                    <th class="px-5 py-4 text-center">Aksi</th>
                </tr>

            </thead>


            <tbody class="divide-y divide-gray-100">

                @forelse($penggunas as $pengguna)

                    @php

                        $roleName = $pengguna->roles->first()?->name ?? '-';

                        $roleLabel = $roleLabels[$roleName]
                            ?? ucfirst(str_replace('_', ' ', $roleName));

                        $roleClass = match($roleName) {
                            'admin_koni' => 'bg-red-100 text-red-700',
                            'staff_koni' => 'bg-blue-100 text-blue-700',
                            'admin_cabor' => 'bg-purple-100 text-purple-700',
                            'atlet' => 'bg-green-100 text-green-700',
                            'pelatih' => 'bg-cyan-100 text-cyan-700',
                            default => 'bg-gray-100 text-gray-600',
                        };

                        $namaCabor =
                            $pengguna->cabangOlahraga?->nama
                            ?? $pengguna->atlet?->cabangOlahraga?->nama
                            ?? $pengguna->pelatih?->cabangOlahraga?->nama;

                        $namaProfil =
                            $pengguna->atlet?->nama_lengkap
                            ?? $pengguna->pelatih?->nama_lengkap;

                    @endphp


                    <tr class="transition hover:bg-gray-50">

                        <!-- USER -->
                        <td class="px-5 py-4">

                            <div class="flex items-center gap-3">

                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-[#171717] text-sm font-black text-[#FFD700]">

                                    {{ strtoupper(substr($pengguna->name, 0, 1)) }}

                                </div>

                                <div>

                                    <p class="text-sm font-bold text-gray-900">
                                        {{ $pengguna->name }}
                                    </p>

                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ $pengguna->email }}
                                    </p>

                                </div>

                            </div>

                        </td>


                        <!-- ROLE -->
                        <td class="px-5 py-4">

                            <span class="rounded-full px-3 py-1 text-xs font-bold {{ $roleClass }}">
                                {{ $roleLabel }}
                            </span>

                        </td>


                        <!-- CABOR / PROFIL -->
                        <td class="px-5 py-4">

                            <p class="text-sm font-medium text-gray-700">
                                {{ $namaProfil ?: '-' }}
                            </p>

                            <p class="mt-1 text-xs text-gray-400">
                                {{ $namaCabor ?: 'Tidak terhubung dengan cabor' }}
                            </p>

                        </td>


                        <!-- STATUS -->
                        <td class="px-5 py-4">

                            @if($pengguna->is_active)

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
                                    href="{{ route('admin.pengguna.edit', $pengguna) }}"
                                    class="rounded-lg bg-yellow-50 px-3 py-2 text-xs font-bold text-yellow-700 transition hover:bg-yellow-100">

                                    Edit

                                </a>


                                @if($pengguna->id !== auth()->id())

                                    <form
                                        action="{{ route('admin.pengguna.destroy', $pengguna) }}"
                                        method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus akun pengguna ini?')">

                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="rounded-lg bg-red-50 px-3 py-2 text-xs font-bold text-red-600 transition hover:bg-red-100">

                                            Hapus

                                        </button>

                                    </form>

                                @else

                                    <span class="text-xs font-medium text-gray-400">
                                        Akun Anda
                                    </span>

                                @endif

                            </div>

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="5" class="px-5 py-12 text-center">

                            <p class="font-bold text-gray-600">
                                Belum ada data pengguna.
                            </p>

                            <p class="mt-1 text-sm text-gray-400">
                                Tambahkan akun pengguna baru untuk memulai.
                            </p>

                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>


    <!-- PAGINATION -->
    <div class="border-t border-gray-100 px-5 py-5">

        {{ $penggunas->links() }}

    </div>

</div>

@endsection
