
@extends('layouts.staff')

@section('title', 'Dashboard Staff KONI')
@section('heading', 'Dashboard Staff KONI')

@section('content')

<!-- WELCOME -->
<section class="overflow-hidden rounded-2xl bg-gradient-to-r from-[#A90010] to-[#E60012] p-7 text-white shadow-lg">

    <p class="text-xs font-bold tracking-widest text-yellow-300">
        SISTEM INFORMASI KONI KABUPATEN KLATEN
    </p>

    <h2 class="mt-3 text-2xl font-black sm:text-3xl">
        Selamat Datang, {{ auth()->user()->name }}!
    </h2>

    <p class="mt-3 max-w-2xl text-sm leading-relaxed text-red-100">
        Pantau informasi cabang olahraga, atlet, pelatih,
        dan kepengurusan dalam satu dashboard.
    </p>

    <span class="mt-5 inline-block rounded-lg bg-black/20 px-4 py-2 text-xs font-bold text-yellow-300">
        STAFF KONI
    </span>

</section>


<!-- STATISTIK -->
<section class="mt-8">

    <div class="mb-5">
        <h3 class="text-lg font-black">Ringkasan Data KONI Klaten</h3>

        <p class="mt-1 text-sm text-gray-500">
            Rekap seluruh data yang terdaftar dalam sistem.
        </p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">

        @foreach($stats as $stat)

            <a href="{{ route($stat['route']) }}"
               class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:border-red-200 hover:shadow-lg">

                <p class="text-sm font-semibold text-gray-500">
                    {{ $stat['label'] }}
                </p>

                <h4 class="mt-3 text-3xl font-black text-gray-900">
                    {{ $stat['total'] }}
                </h4>

                <p class="mt-3 text-xs font-semibold text-green-600">
                    {{ $stat['aktif'] }} {{ $stat['aktif_label'] ?? 'aktif' }}
                </p>

                <p class="mt-4 text-xs font-bold text-[#E60012]">
                    Lihat Data →
                </p>

            </a>

        @endforeach

    </div>

</section>


<!-- INFORMASI HAK AKSES -->
<section class="mt-8 rounded-xl border border-gray-200 bg-white p-6">

    <h3 class="font-black">
        Informasi Hak Akses Staff KONI
    </h3>

    <p class="mt-3 text-sm leading-relaxed text-gray-600">
        Anda dapat melihat rekap cabang olahraga, data atlet,
        data pelatih, serta struktur organisasi. Staff KONI juga dapat
        mengelola seluruh agenda dan kejuaraan serta langsung mempublikasikannya.
    </p>

    <div class="mt-5 rounded-lg border border-yellow-200 bg-yellow-50 p-4">

        <p class="text-sm font-bold text-yellow-800">
            Hak Pengelolaan Agenda
        </p>

        <p class="mt-2 text-xs leading-relaxed text-yellow-700">
            Staff KONI dapat membuat, mengubah, menghapus, meninjau pengajuan,
            meminta revisi, dan langsung mempublikasikan agenda serta kejuaraan.
            Pengelolaan akun dan data master tetap mengikuti kewenangan masing-masing role.
        </p>

    </div>

</section>

@endsection
