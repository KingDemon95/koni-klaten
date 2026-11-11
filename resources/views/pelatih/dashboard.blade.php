
@extends('layouts.pelatih')

@section('title', 'Dashboard Pelatih')
@section('heading', 'Dashboard Pelatih')

@section('content')

<!-- SAMBUTAN -->
<section class="overflow-hidden rounded-2xl bg-gradient-to-r from-[#A90010] to-[#E60012] p-7 text-white shadow-lg">

    <p class="text-xs font-bold tracking-widest text-yellow-300">
        PORTAL PELATIH KONI KABUPATEN KLATEN
    </p>

    <h2 class="mt-3 text-2xl font-black sm:text-3xl">
        Selamat Datang, {{ $user->name }}!
    </h2>

    <p class="mt-3 max-w-2xl text-sm leading-relaxed text-red-100">
        Pantau informasi kepelatihan, cabang olahraga,
        dan profil pribadi melalui Portal Pelatih KONI Klaten.
    </p>

    <span class="mt-5 inline-block rounded-lg bg-black/20 px-4 py-2 text-xs font-bold text-yellow-300">
        AKUN PELATIH
    </span>

</section>


@if($pelatih)

    <!-- PROFIL SINGKAT -->
    <section class="mt-8">

        <h3 class="mb-5 text-lg font-black">
            Ringkasan Profil Pelatih
        </h3>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">

            <div class="flex flex-col gap-5 sm:flex-row sm:items-center">

                @if($pelatih->foto_path)

                    <img
                        src="{{ asset('storage/'.$pelatih->foto_path) }}"
                        alt="Foto {{ $pelatih->nama_lengkap }}"
                        width="96"
                        height="96"
                        style="width:96px;height:96px;max-width:96px;max-height:96px;object-fit:cover;flex-shrink:0;border-radius:16px;"
                        class="shrink-0 border border-gray-200"
                    >

                @else

                    <div class="flex h-24 w-24 shrink-0 items-center justify-center rounded-2xl bg-red-50 text-3xl font-black text-red-600">
                        {{ strtoupper(mb_substr($pelatih->nama_lengkap, 0, 1)) }}
                    </div>

                @endif

                <div class="min-w-0 flex-1">

                    <h4 class="text-xl font-black">
                        {{ $pelatih->nama_lengkap }}
                    </h4>

                    <p class="mt-2 text-sm text-gray-500">
                        {{ $pelatih->cabangOlahraga?->nama ?? 'Cabor belum tersedia' }}
                    </p>

                    <p class="mt-1 text-sm text-gray-500">
                        {{ $pelatih->spesialisasi ?: 'Spesialisasi belum diisi' }}
                    </p>

                    <span class="mt-3 inline-block rounded-full px-3 py-1 text-xs font-bold
                        {{ $pelatih->status === 'aktif'
                            ? 'bg-green-100 text-green-700'
                            : 'bg-gray-100 text-gray-600' }}">

                        {{ $pelatih->status === 'aktif' ? 'Pelatih Aktif' : 'Pelatih Nonaktif' }}

                    </span>

                </div>

                <a
                    href="{{ route('pelatih.profil') }}"
                    class="inline-flex justify-center rounded-xl bg-[#E60012] px-5 py-3 text-sm font-bold text-white hover:bg-red-700"
                >
                    Lihat Profil Saya
                </a>

            </div>

        </div>

    </section>


    <!-- STATISTIK PROFIL -->
    <section class="mt-7 grid gap-4 md:grid-cols-3">

        <div class="rounded-xl border border-gray-200 bg-white p-5">

            <p class="text-sm text-gray-500">
                Cabang Olahraga
            </p>

            <h4 class="mt-3 text-lg font-black">
                {{ $pelatih->cabangOlahraga?->nama ?? '-' }}
            </h4>

        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5">

            <p class="text-sm text-gray-500">
                Pengalaman Kepelatihan
            </p>

            <h4 class="mt-3 text-lg font-black">
                {{ $pelatih->pengalaman_tahun !== null
                    ? $pelatih->pengalaman_tahun.' tahun'
                    : '-' }}
            </h4>

        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5">

            <p class="text-sm text-gray-500">
                Status Sertifikasi
            </p>

            <h4 class="mt-3 text-lg font-black">
                {{ filled($pelatih->sertifikasi)
                    ? 'Data tersedia'
                    : 'Belum diisi' }}
            </h4>

        </div>

    </section>

@else

    <!-- PROFIL BELUM TERHUBUNG -->
    <section class="mt-8 rounded-2xl border border-yellow-200 bg-yellow-50 p-6">

        <h3 class="text-lg font-black text-yellow-900">
            Profil Pelatih Belum Terhubung
        </h3>

        <p class="mt-3 text-sm leading-relaxed text-yellow-800">
            Akun Anda sudah memiliki role Pelatih,
            tetapi belum terhubung dengan data Pelatih
            dalam database KONI Kabupaten Klaten.
        </p>

        <p class="mt-3 text-sm text-yellow-800">
            Silakan hubungi Administrator KONI untuk
            menghubungkan akun dengan profil kepelatihan Anda.
        </p>

    </section>

@endif


<!-- INFORMASI AKSES -->
<section class="mt-8 rounded-xl border border-gray-200 bg-white p-6">

    <h3 class="font-black">
        Informasi Akses Pelatih
    </h3>

    <p class="mt-3 text-sm leading-relaxed text-gray-600">
        Anda hanya dapat melihat informasi yang terhubung
        dengan akun sendiri. Perubahan data kepelatihan
        dikelola oleh Administrator KONI atau Admin Cabor
        yang memiliki kewenangan.
    </p>

</section>

@endsection
