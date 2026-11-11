
@extends('layouts.atlet')

@section('title', 'Dashboard Atlet')
@section('heading', 'Dashboard Atlet')

@section('content')

<!-- SELAMAT DATANG -->
<section class="overflow-hidden rounded-2xl bg-gradient-to-r from-[#A90010] to-[#E60012] p-7 text-white shadow-lg">

    <p class="text-xs font-bold tracking-widest text-yellow-300">
        PORTAL ATLET KONI KABUPATEN KLATEN
    </p>

    <h2 class="mt-3 text-2xl font-black sm:text-3xl">
        Selamat Datang, {{ $user->name }}!
    </h2>

    <p class="mt-3 max-w-2xl text-sm leading-relaxed text-red-100">
        Lihat data keatletan dan informasi profil Anda
        melalui portal pribadi KONI Kabupaten Klaten.
    </p>

    <span class="mt-5 inline-block rounded-lg bg-black/20 px-4 py-2 text-xs font-bold text-yellow-300">
        AKUN ATLET
    </span>

</section>


@if($atlet)

    <!-- RINGKASAN PROFIL -->
    <section class="mt-8">

        <h3 class="mb-5 text-lg font-black">
            Ringkasan Profil Atlet
        </h3>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">

            <div class="flex flex-col gap-5 sm:flex-row sm:items-center">

                @if($atlet->foto_path)
                    
                    <img
                        src="{{ asset('storage/'.$atlet->foto_path) }}"
                        alt="Foto {{ $atlet->nama_lengkap }}"
                        width="96"
                        height="96"
                        style="width:96px;height:96px;max-width:96px;max-height:96px;object-fit:cover;flex-shrink:0;border-radius:16px;"
                        class="shrink-0 border border-gray-200"
                    >

                @else

                    <div class="flex h-28 w-28 items-center justify-center rounded-2xl bg-red-50 text-4xl font-black text-red-600">
                        {{ strtoupper(mb_substr($atlet->nama_lengkap, 0, 1)) }}
                    </div>

                @endif

                <div class="min-w-0 flex-1">

                    <h4 class="text-xl font-black">
                        {{ $atlet->nama_lengkap }}
                    </h4>

                    <p class="mt-2 text-sm text-gray-500">
                        {{ $atlet->cabangOlahraga?->nama ?? 'Cabor belum tersedia' }}
                    </p>

                    <span class="mt-3 inline-block rounded-full px-3 py-1 text-xs font-bold
                        {{ $atlet->status === 'aktif'
                            ? 'bg-green-100 text-green-700'
                            : 'bg-gray-100 text-gray-600' }}">

                        {{ $atlet->status === 'aktif' ? 'Atlet Aktif' : 'Atlet Nonaktif' }}

                    </span>

                </div>

                <a
                    href="{{ route('atlet.profil') }}"
                    class="inline-flex justify-center rounded-xl bg-[#E60012] px-5 py-3 text-sm font-bold text-white transition hover:bg-red-700"
                >
                    Lihat Profil Saya
                </a>

            </div>

        </div>

    </section>


    <!-- INFORMASI RINGKAS -->
    <section class="mt-7 grid gap-4 md:grid-cols-3">

        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">
                Cabang Olahraga
            </p>

            <p class="mt-3 text-lg font-black">
                {{ $atlet->cabangOlahraga?->nama ?? '-' }}
            </p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">
                Status Atlet
            </p>

            <p class="mt-3 text-lg font-black
                {{ $atlet->status === 'aktif' ? 'text-green-600' : 'text-gray-500' }}">
                {{ ucfirst($atlet->status ?? '-') }}
            </p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">
                Profil Terhubung
            </p>

            <p class="mt-3 text-lg font-black text-green-600">
                Ya
            </p>
        </div>

    </section>

@else

    <!-- BELUM TERHUBUNG -->
    <section class="mt-8 rounded-2xl border border-yellow-200 bg-yellow-50 p-6">

        <h3 class="text-lg font-black text-yellow-900">
            Profil Atlet Belum Terhubung
        </h3>

        <p class="mt-3 text-sm leading-relaxed text-yellow-800">
            Akun Anda memiliki role Atlet, tetapi belum
            dihubungkan dengan data atlet dalam sistem.
            Silakan hubungi Administrator KONI Kabupaten Klaten
            untuk menghubungkan profil Anda.
        </p>

        <p class="mt-4 text-xs text-yellow-700">
            Anda tetap dapat login, tetapi data keatletan
            belum dapat ditampilkan.
        </p>

    </section>

@endif


<!-- INFO KEAMANAN -->
<section class="mt-8 rounded-xl border border-gray-200 bg-white p-6">

    <h3 class="font-black">
        Akses Pribadi Atlet
    </h3>

    <p class="mt-3 text-sm leading-relaxed text-gray-600">
        Halaman ini hanya menampilkan data yang terhubung
        dengan akun Anda. Perubahan data atlet saat ini
        dikelola oleh Administrator KONI atau Admin Cabor
        yang memiliki kewenangan.
    </p>

</section>

@endsection
