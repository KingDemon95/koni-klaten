<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Staff KONI') - KONI Kabupaten Klaten</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-[#F4F5F7] text-gray-900">


<a href="#konten-utama" class="koni-skip-link">Langsung ke konten utama</a>
<div class="min-h-screen lg:flex">

    <!-- SIDEBAR DESKTOP -->
    <aside class="hidden w-72 shrink-0 flex-col bg-[#171717] text-white lg:flex">

        <div class="border-b border-white/10 px-6 py-7">

            <div class="flex items-center gap-3">

                @if(file_exists(public_path('images/logo-koni.png')))
                    <img
                        src="{{ asset('images/logo-koni.png') }}"
                        alt="KONI Klaten"
                        class="h-12 w-12 rounded-lg bg-white object-contain p-1"
                    >
                @else
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#E60012] text-xl font-black">
                        K
                    </div>
                @endif

                <div>
                    <h2 class="font-black tracking-wider">KONI KLATEN</h2>
                    <p class="text-[10px] tracking-widest text-gray-400">
                        STAFF PANEL
                    </p>
                </div>

            </div>

        </div>

        <div class="px-6 pt-7 pb-2">
            <p class="text-[10px] font-bold tracking-widest text-gray-500">
                MENU STAFF KONI
            </p>
        </div>

        <nav class="flex-1 space-y-1 px-4 py-4">

            <a href="{{ route('dashboard.staff-koni') }}"
               class="block rounded-xl px-4 py-3 text-sm font-semibold
               {{ request()->routeIs('dashboard.staff-koni')
                    ? 'bg-[#E60012] text-white'
                    : 'text-gray-400 hover:bg-white/10 hover:text-white' }}">
                Dashboard
            </a>

            <a href="{{ route('staff.laporan.index') }}"
               class="block rounded-xl px-4 py-3 text-sm font-semibold
               {{ request()->routeIs('staff.laporan.*') ? 'bg-[#E60012] text-white' : 'text-gray-400 hover:bg-white/10 hover:text-white' }}">
                Laporan & Rekapitulasi
            </a>

            @foreach([
                'cabor' => 'Cabang Olahraga',
                'atlet' => 'Data Atlet',
                'pelatih' => 'Data Pelatih',
                'pengurus' => 'Struktur Organisasi',
            ] as $modul => $label)

                <a href="{{ route('staff.'.$modul.'.index') }}"
                   class="block rounded-xl px-4 py-3 text-sm font-semibold
                   {{ request()->routeIs('staff.'.$modul.'.*')
                        ? 'bg-[#E60012] text-white'
                        : 'text-gray-400 hover:bg-white/10 hover:text-white' }}">

                    {{ $label }}

                </a>

            @endforeach

            <a href="{{ route('staff.agenda.index') }}"
               class="block rounded-xl px-4 py-3 text-sm font-semibold
               {{ request()->routeIs('staff.agenda.*') ? 'bg-[#E60012] text-white' : 'text-gray-400 hover:bg-white/10 hover:text-white' }}">
                Agenda & Kejuaraan
            </a>

            <a href="{{ route('staff.surat.index') }}"
               class="block rounded-xl px-4 py-3 text-sm font-semibold
               {{ request()->routeIs('staff.surat.*') ? 'bg-[#E60012] text-white' : 'text-gray-400 hover:bg-white/10 hover:text-white' }}">
                Persuratan
            </a>

            <a href="{{ route('staff.templates.index') }}"
               class="block rounded-xl px-4 py-3 text-sm font-semibold
               {{ request()->routeIs('staff.templates.*') ? 'bg-[#E60012] text-white' : 'text-gray-400 hover:bg-white/10 hover:text-white' }}">
                Template Surat
            </a>

            <a href="{{ route('staff.publikasi.index') }}"
               class="block rounded-xl px-4 py-3 text-sm font-semibold
               {{ request()->routeIs('staff.publikasi.*') ? 'bg-[#E60012] text-white' : 'text-gray-400 hover:bg-white/10 hover:text-white' }}">
                Dokumentasi & Publikasi
            </a>

        </nav>

        <div class="border-t border-white/10 p-5">

            <p class="text-xs text-gray-400">
                Sistem Informasi dan Manajemen
            </p>

            <p class="mt-1 text-xs font-bold text-[#FFD700]">
                KONI Kabupaten Klaten
            </p>

            <p class="mt-2 text-[10px] text-gray-500">
                PENGELOLAAN DATA & PUBLIKASI AGENDA
            </p>

        </div>

    </aside>


    <!-- MAIN CONTENT -->
    <div class="min-w-0 flex-1">

        <!-- TOPBAR -->
        <header class="sticky top-0 z-30 flex min-h-20 items-center justify-between gap-3 border-b border-gray-200 bg-white px-4 sm:px-7">

            <div class="flex items-center gap-3">

                <!-- MOBILE MENU -->
                <details class="relative lg:hidden">

                    <summary class="cursor-pointer list-none rounded-lg bg-gray-100 px-3 py-2 text-sm font-bold">
                        ☰
                    </summary>

                    <nav class="absolute left-0 top-full z-50 mt-3 flex w-60 flex-col gap-3 rounded-xl bg-[#171717] p-4 text-sm font-medium text-white shadow-xl">

                        <a href="{{ route('dashboard.staff-koni') }}">
                            Dashboard
                        </a>

                        <a href="{{ route('staff.laporan.index') }}">
                            Laporan & Rekapitulasi
                        </a>

                        <a href="{{ route('staff.cabor.index') }}">
                            Cabang Olahraga
                        </a>

                        <a href="{{ route('staff.atlet.index') }}">
                            Data Atlet
                        </a>

                        <a href="{{ route('staff.pelatih.index') }}">
                            Data Pelatih
                        </a>

                        <a href="{{ route('staff.pengurus.index') }}">
                            Struktur Organisasi
                        </a>
                        <a href="{{ route('staff.agenda.index') }}">
                            Agenda & Kejuaraan
                        </a>
                        <a href="{{ route('staff.surat.index') }}">Persuratan</a>
                        <a href="{{ route('staff.templates.index') }}">Template Surat</a>
                        <a href="{{ route('staff.publikasi.index') }}">Dokumentasi & Publikasi</a>

                    </nav>

                </details>

                <div>

                    <p class="text-[10px] font-bold tracking-widest text-[#E60012]">
                        STAFF ADMINISTRATION
                    </p>

                    <h1 class="text-lg font-black sm:text-xl">
                        @yield('heading', 'Dashboard')
                    </h1>

                </div>

            </div>


            <!-- PROFILE -->
            <div class="flex items-center gap-3">

                @include('partials.notification-link')

                <div class="hidden text-right sm:block">

                    <p class="text-sm font-bold">
                        {{ auth()->user()->name }}
                    </p>

                    <p class="text-xs text-gray-500">
                        Staff KONI Kabupaten Klaten
                    </p>

                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#171717] font-black text-[#FFD700]">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button
                        type="submit"
                        class="rounded-lg border border-gray-200 px-3 py-2 text-xs font-bold text-gray-600 transition hover:border-red-500 hover:bg-red-50 hover:text-red-600">

                        Logout

                    </button>
                </form>

            </div>

        </header>

        <main id="konten-utama" tabindex="-1" class="p-4 sm:p-6 lg:p-8">
            @include('partials.demo-feedback')
            @yield('content')
        </main>

    </div>

</div>

</body>
</html>