
@extends('layouts.admin')

@section('title', 'Dashboard Admin KONI')
@section('heading', 'Dashboard')

@section('content')

    <!-- WELCOME BANNER -->
    <section class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#A90010] to-[#E60012] p-6 text-white shadow-lg sm:p-8">

        <div class="relative z-10 max-w-2xl">

            <p class="text-xs font-bold tracking-[0.25em] text-yellow-300">
                SISTEM INFORMASI DAN MANAJEMEN
            </p>

            <h2 class="mt-3 text-2xl font-black sm:text-3xl">
                Selamat Datang,
                {{ auth()->user()->name }}!
            </h2>

            <p class="mt-3 max-w-xl text-sm leading-relaxed text-red-100">
                Kelola data olahraga, atlet, pelatih, cabang olahraga,
                administrasi, dan seluruh aktivitas KONI Kabupaten Klaten
                melalui satu sistem terintegrasi.
            </p>

            <div class="mt-6 inline-flex rounded-lg bg-black/20 px-4 py-2 text-xs font-bold tracking-wider text-yellow-300">
                ADMIN KONI
            </div>

        </div>

        <div class="absolute -right-12 -top-20 h-64 w-64 rounded-full border-[30px] border-white/10"></div>
        <div class="absolute -bottom-24 right-32 h-48 w-48 rounded-full bg-yellow-400/10 blur-2xl"></div>

    </section>


    <!-- STATISTICS -->
    <section class="mt-8">

        <div class="mb-5 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-black">Ringkasan Data</h3>
                <p class="mt-1 text-xs text-gray-500">
                    Statistik utama organisasi KONI Klaten.
                </p>
            </div>

            <span class="rounded-full bg-yellow-100 px-3 py-1 text-[10px] font-bold text-yellow-800">
                DATA BELUM TERHUBUNG
            </span>
        </div>


        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">

            @foreach([
                ['title' => 'Cabang Olahraga', 'number' => '—', 'desc' => 'Total cabor terdaftar', 'color' => 'border-red-500'],
                ['title' => 'Total Atlet', 'number' => '—', 'desc' => 'Atlet terdaftar', 'color' => 'border-yellow-500'],
                ['title' => 'Total Pelatih', 'number' => '—', 'desc' => 'Pelatih terdaftar', 'color' => 'border-blue-500'],
                ['title' => 'Persuratan', 'number' => '—', 'desc' => 'Surat dalam proses', 'color' => 'border-green-500'],
            ] as $stat)

                <div class="rounded-xl border border-gray-200 border-l-4 {{ $stat['color'] }} bg-white p-5 shadow-sm">

                    <p class="text-sm font-semibold text-gray-500">
                        {{ $stat['title'] }}
                    </p>

                    <h4 class="mt-3 text-3xl font-black text-gray-900">
                        {{ $stat['number'] }}
                    </h4>

                    <p class="mt-2 text-xs text-gray-400">
                        {{ $stat['desc'] }}
                    </p>

                </div>

            @endforeach

        </div>

    </section>


    <!-- QUICK ACCESS -->
    <section class="mt-9">

        <div class="mb-5">
            <h3 class="text-lg font-black">Akses Cepat</h3>

            <p class="mt-1 text-xs text-gray-500">
                Akses modul pengelolaan KONI Klaten.
            </p>
        </div>


        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">

            <a href="{{ route('admin.cabor.index') }}"
                class="group rounded-xl border border-gray-200 bg-white p-5 transition hover:-translate-y-1 hover:border-red-300 hover:shadow-lg">

                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-red-50 text-lg font-black text-red-600">
                    C
                </div>

                <h4 class="mt-4 font-bold">Cabang Olahraga</h4>

                <p class="mt-1 text-xs leading-relaxed text-gray-500">
                    Kelola data dan struktur organisasi cabor.
                </p>

                <p class="mt-4 text-xs font-bold text-red-600">
                    Buka Modul →
                </p>

            </a>


            <a href="{{ route('admin.atlet.index') }}"
                class="group rounded-xl border border-gray-200 bg-white p-5 transition hover:-translate-y-1 hover:border-red-300 hover:shadow-lg">

                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-yellow-50 text-lg font-black text-yellow-700">
                    A
                </div>

                <h4 class="mt-4 font-bold">Data Atlet</h4>

                <p class="mt-1 text-xs leading-relaxed text-gray-500">
                    Kelola identitas dan prestasi atlet.
                </p>

                <p class="mt-4 text-xs font-bold text-red-600">
                    Buka Modul →
                </p>

            </a>


            <a href="{{ route('admin.pelatih.index') }}"
                class="group rounded-xl border border-gray-200 bg-white p-5 transition hover:-translate-y-1 hover:border-red-300 hover:shadow-lg">

                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-lg font-black text-blue-700">
                    P
                </div>

                <h4 class="mt-4 font-bold">Data Pelatih</h4>

                <p class="mt-1 text-xs leading-relaxed text-gray-500">
                    Kelola data dan profil pelatih.
                </p>

                <p class="mt-4 text-xs font-bold text-red-600">
                    Buka Modul →
                </p>

            </a>


            <a href="{{ route('admin.persuratan.index') }}"
                class="group rounded-xl border border-gray-200 bg-white p-5 transition hover:-translate-y-1 hover:border-red-300 hover:shadow-lg">

                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-green-50 text-lg font-black text-green-700">
                    S
                </div>

                <h4 class="mt-4 font-bold">Persuratan</h4>

                <p class="mt-1 text-xs leading-relaxed text-gray-500">
                    Kelola surat masuk dan keluar.
                </p>

                <p class="mt-4 text-xs font-bold text-red-600">
                    Buka Modul →
                </p>

            </a>

        </div>

    </section>


    <!-- BOTTOM INFORMATION -->
    <section class="mt-9 grid gap-5 lg:grid-cols-2">

        <div class="rounded-xl border border-gray-200 bg-white p-6">

            <h3 class="font-black">Aktivitas Terbaru</h3>

            <p class="mt-2 text-sm text-gray-500">
                Belum ada aktivitas yang tercatat.
            </p>

            <div class="mt-5 rounded-lg bg-gray-50 p-4 text-xs text-gray-500">
                Riwayat aktivitas akan muncul setelah modul pengelolaan
                data dan administrasi selesai dibuat.
            </div>

        </div>


        <div class="rounded-xl border border-gray-200 bg-white p-6">

            <h3 class="font-black">Informasi Sistem</h3>

            <div class="mt-5 space-y-4">

                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">Nama organisasi</span>
                    <span class="font-bold">KONI Klaten</span>
                </div>

                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">Hak akses</span>
                    <span class="font-bold text-red-600">Administrator</span>
                </div>

                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">Status akun</span>
                    <span class="font-bold text-green-600">Aktif</span>
                </div>

            </div>

        </div>

    </section>

@endsection
