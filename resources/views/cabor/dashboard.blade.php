
@extends('layouts.cabor')

@section('title', 'Dashboard Admin Cabor')
@section('heading', 'Dashboard')

@section('content')

<section class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#A90010] to-[#E60012] p-7 text-white shadow-lg">

    <p class="text-xs font-bold tracking-widest text-yellow-300">
        SISTEM MANAJEMEN CABANG OLAHRAGA
    </p>

    <h2 class="mt-3 text-2xl font-black sm:text-3xl">
        Selamat Datang, {{ auth()->user()->name }}!
    </h2>

    <p class="mt-3 text-sm text-red-100">
        Dashboard Cabang Olahraga {{ $cabor->nama }}
    </p>

    <div class="mt-6 inline-flex rounded-lg bg-black/20 px-4 py-2 text-xs font-bold text-yellow-300">
        ADMIN CABOR
    </div>

</section>


<section class="mt-8">

    <h3 class="mb-5 text-lg font-black">
        Statistik Cabang Olahraga
    </h3>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">

        @foreach([
            ['label' => 'Total Atlet', 'value' => $totalAtlet],
            ['label' => 'Atlet Aktif', 'value' => $atletAktif],
            ['label' => 'Total Pelatih', 'value' => $totalPelatih],
            ['label' => 'Pelatih Aktif', 'value' => $pelatihAktif],
        ] as $stat)

            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">

                <p class="text-sm font-semibold text-gray-500">
                    {{ $stat['label'] }}
                </p>

                <h4 class="mt-3 text-3xl font-black">
                    {{ $stat['value'] }}
                </h4>

            </div>

        @endforeach

    </div>

</section>


<section class="mt-9">

    <h3 class="mb-5 text-lg font-black">
        Manajemen Cabang Olahraga
    </h3>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">

        <a href="{{ route('cabor.atlet.index') }}"
           class="rounded-xl border border-gray-200 bg-white p-6 transition hover:-translate-y-1 hover:border-red-300 hover:shadow-lg">

            <div class="mb-4 text-3xl">🏅</div>

            <h4 class="font-bold">Data Atlet</h4>

            <p class="mt-2 text-sm text-gray-500">
                Lihat daftar atlet cabang olahraga Anda.
            </p>

            <p class="mt-5 text-xs font-bold text-red-600">
                Lihat Atlet →
            </p>

        </a>


        <a href="{{ route('cabor.pelatih.index') }}"
           class="rounded-xl border border-gray-200 bg-white p-6 transition hover:-translate-y-1 hover:border-red-300 hover:shadow-lg">

            <div class="mb-4 text-3xl">👤</div>

            <h4 class="font-bold">Data Pelatih</h4>

            <p class="mt-2 text-sm text-gray-500">
                Lihat daftar pelatih cabang olahraga Anda.
            </p>

            <p class="mt-5 text-xs font-bold text-red-600">
                Lihat Pelatih →
            </p>

        </a>


        <a href="{{ route('cabor.agenda.index') }}"
           class="rounded-xl border border-gray-200 bg-white p-6 transition hover:-translate-y-1 hover:border-red-300 hover:shadow-lg">
            <div class="mb-4 text-3xl">📅</div>
            <h4 class="font-bold">Agenda & Kejuaraan</h4>
            <p class="mt-2 text-sm text-gray-500">Kelola agenda cabang olahraga dan ajukan publikasi kepada Staff KONI.</p>
            <p class="mt-3 text-xs font-semibold text-gray-500">{{ $totalAgenda }} agenda · {{ $agendaDraft }} perlu diajukan / direvisi</p>
            <p class="mt-4 text-xs font-bold text-red-600">Kelola Agenda →</p>
        </a>

        <a href="{{ route('cabor.pengurus.index') }}" 
            class="rounded-xl border border-gray-200 bg-white p-6 transition hover:-translate-y-1 hover:border-red-300 hover:shadow-lg">

            <div class="mb-4 text-3xl">🏛️</div>

            <h4 class="font-bold">Pengurus Cabor</h4>

            <p class="mt-2 text-sm text-gray-500">
                Total pengurus terdaftar.
            </p>

            <p class="mt-5 text-2xl font-black text-red-600">
                {{ $totalPengurus }}
            </p>

            <p class="mt-3 inline-block text-xs font-bold text-red-600 hover:underline">
                Lihat Struktur Organisasi →
            </p>

        </a>

    </div>

</section>

@endsection
