
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Panel Pelatih') - KONI Klaten</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-[#F4F5F7] text-gray-900">


<a href="#konten-utama" class="koni-skip-link">Langsung ke konten utama</a>
<div class="min-h-screen lg:flex">

    <!-- SIDEBAR DESKTOP -->
    <aside class="hidden w-72 shrink-0 flex-col bg-[#171717] text-white lg:flex">

        <div class="flex items-center gap-3 border-b border-white/10 px-6 py-7">

            @if(file_exists(public_path('images/logo-koni.png')))

                <img
                    src="{{ asset('images/logo-koni.png') }}"
                    alt="KONI Kabupaten Klaten"
                    class="h-12 w-12 rounded-lg bg-white object-contain p-1"
                >

            @else

                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#E60012] text-xl font-black">
                    K
                </div>

            @endif

            <div>
                <h2 class="font-black tracking-wider">
                    KONI KLATEN
                </h2>

                <p class="text-[10px] tracking-widest text-gray-400">
                    PORTAL PELATIH
                </p>
            </div>

        </div>

        <div class="px-6 pt-7 pb-2">
            <p class="text-[10px] font-bold tracking-widest text-gray-500">
                MENU PELATIH
            </p>
        </div>

        <nav class="flex-1 space-y-1 px-4 py-4">

            <a
                href="{{ route('dashboard.pelatih') }}"
                class="block rounded-xl px-4 py-3 text-sm font-semibold transition
                {{ request()->routeIs('dashboard.pelatih')
                    ? 'bg-[#E60012] text-white'
                    : 'text-gray-400 hover:bg-white/10 hover:text-white' }}"
            >
                Dashboard
            </a>

            <a
                href="{{ route('pelatih.profil') }}"
                class="block rounded-xl px-4 py-3 text-sm font-semibold transition
                {{ request()->routeIs('pelatih.profil')
                    ? 'bg-[#E60012] text-white'
                    : 'text-gray-400 hover:bg-white/10 hover:text-white' }}"
            >
                Profil Saya
            </a>

            <a href="{{ route('pelatih.prestasi.index') }}" class="block rounded-xl px-4 py-3 text-sm font-semibold {{ request()->routeIs('pelatih.prestasi.*') ? 'bg-[#E60012] text-white' : 'text-gray-400 hover:bg-white/10 hover:text-white' }}">Prestasi Saya</a>
            <a href="{{ route('pelatih.izin-latihan.index') }}" class="block rounded-xl px-4 py-3 text-sm font-semibold {{ request()->routeIs('pelatih.izin-latihan.*') ? 'bg-[#E60012] text-white' : 'text-gray-400 hover:bg-white/10 hover:text-white' }}">Izin Latihan</a>
            <a href="{{ route('pelatih.templates.index') }}" class="block rounded-xl px-4 py-3 text-sm font-semibold {{ request()->routeIs('pelatih.templates.*') ? 'bg-[#E60012] text-white' : 'text-gray-400 hover:bg-white/10 hover:text-white' }}">Template Surat Izin</a>
        </nav>

        <div class="border-t border-white/10 p-5">
            <p class="text-xs text-gray-400">
                Portal Pribadi Pelatih
            </p>

            <p class="mt-1 text-xs font-bold text-[#FFD700]">
                KONI Kabupaten Klaten
            </p>
        </div>

    </aside>


    <!-- KONTEN UTAMA -->
    <div class="min-w-0 flex-1">

        <header class="sticky top-0 z-30 flex min-h-20 items-center justify-between gap-3 border-b border-gray-200 bg-white px-4 sm:px-7">

            <div class="flex items-center gap-3">

                <!-- MENU MOBILE -->
                <details class="relative lg:hidden">

                    <summary class="cursor-pointer list-none rounded-lg bg-gray-100 px-3 py-2 text-sm font-bold">
                        ☰
                    </summary>

                    <nav class="absolute left-0 top-full z-50 mt-3 flex w-52 flex-col gap-3 rounded-xl bg-[#171717] p-4 text-sm font-semibold text-white shadow-xl">

                        <a href="{{ route('dashboard.pelatih') }}">
                            Dashboard
                        </a>

                        <a href="{{ route('pelatih.profil') }}">
                            Profil Saya
                        </a>
                        <a href="{{ route('pelatih.prestasi.index') }}">Prestasi Saya</a>
                        <a href="{{ route('pelatih.izin-latihan.index') }}">Izin Latihan</a>
                        <a href="{{ route('pelatih.templates.index') }}">Template Surat Izin</a>

                    </nav>

                </details>

                <div>
                    <p class="text-[10px] font-bold tracking-widest text-[#E60012]">
                        COACH PORTAL
                    </p>

                    <h1 class="text-lg font-black sm:text-xl">
                        @yield('heading', 'Dashboard Pelatih')
                    </h1>
                </div>

            </div>


            <!-- AKUN -->
            <div class="flex items-center gap-3">

                @include('partials.notification-link')

                <div class="hidden text-right sm:block">

                    <p class="text-sm font-bold">
                        {{ auth()->user()->name }}
                    </p>

                    <p class="text-xs text-gray-500">
                        Pelatih KONI Klaten
                    </p>

                </div>

                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#171717] font-black text-[#FFD700]">
                    {{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button
                        type="submit"
                        class="rounded-lg border border-gray-200 px-3 py-2 text-xs font-bold text-gray-600 transition hover:border-red-500 hover:bg-red-50 hover:text-red-600"
                    >
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
