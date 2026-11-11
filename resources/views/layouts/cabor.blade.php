
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Admin Cabor') - KONI Klaten</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-[#F4F5F7] text-gray-900">

<div class="min-h-screen lg:flex">

    <!-- SIDEBAR -->
    <aside class="hidden w-72 shrink-0 flex-col bg-[#171717] text-white lg:flex">

        <div class="border-b border-white/10 px-6 py-7">

            <div class="flex items-center gap-3">

                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#E60012] font-black">
                    K
                </div>

                <div>
                    <h2 class="font-black">KONI KLATEN</h2>
                    <p class="text-xs text-gray-400">ADMIN CABOR</p>
                </div>

            </div>

            <p class="mt-5 rounded-lg bg-white/10 px-3 py-2 text-sm font-semibold text-[#FFD700]">
                {{ $cabor->nama }}
            </p>

        </div>

        <nav class="flex-1 space-y-2 px-4 py-7">

            <a href="{{ route('dashboard.admin-cabor') }}"
               class="block rounded-xl px-4 py-3 text-sm font-semibold
               {{ request()->routeIs('dashboard.admin-cabor')
                    ? 'bg-[#E60012] text-white'
                    : 'text-gray-400 hover:bg-white/10 hover:text-white' }}">
                Dashboard
            </a>

            <a href="{{ route('cabor.atlet.index') }}"
               class="block rounded-xl px-4 py-3 text-sm font-semibold
               {{ request()->routeIs('cabor.atlet.*')
                    ? 'bg-[#E60012] text-white'
                    : 'text-gray-400 hover:bg-white/10 hover:text-white' }}">
                Data Atlet
            </a>

            <a href="{{ route('cabor.pelatih.index') }}"
               class="block rounded-xl px-4 py-3 text-sm font-semibold
               {{ request()->routeIs('cabor.pelatih.*')
                    ? 'bg-[#E60012] text-white'
                    : 'text-gray-400 hover:bg-white/10 hover:text-white' }}">
                Data Pelatih
            </a>
           
            <a href="{{ route('cabor.pengurus.index') }}"
            class="block rounded-xl px-4 py-3 text-sm font-semibold
            {{ request()->routeIs('cabor.pengurus.*')
                    ? 'bg-[#E60012] text-white'
                    : 'text-gray-400 hover:bg-white/10 hover:text-white' }}">

                Struktur Organisasi

            </a>

        </nav>

        <div class="border-t border-white/10 p-5 text-xs text-gray-400">
            KONI Kabupaten Klaten
        </div>

    </aside>


    <!-- MAIN -->
    <div class="min-w-0 flex-1">

        <header class="sticky top-0 z-30 flex min-h-20 items-center justify-between gap-3 border-b border-gray-200 bg-white px-4 sm:px-7">

            <div class="flex items-center gap-3">

                <details class="relative lg:hidden">
                    <summary class="cursor-pointer list-none rounded-lg bg-gray-100 px-3 py-2 text-sm font-bold">
                        ☰
                    </summary>

                    <nav class="absolute left-0 top-full z-50 mt-3 flex w-56 flex-col gap-3 rounded-xl bg-[#171717] p-4 text-sm text-white shadow-xl">
                        <a href="{{ route('dashboard.admin-cabor') }}">Dashboard</a>
                        <a href="{{ route('cabor.atlet.index') }}">Data Atlet</a>
                        <a href="{{ route('cabor.pelatih.index') }}">Data Pelatih</a>
                        <a href="{{ route('cabor.pengurus.index') }}">Struktur Organisasi</a>
                    </nav>
                </details>

                <div>
                    <p class="text-[10px] font-bold tracking-widest text-[#E60012]">
                        ADMIN CABANG OLAHRAGA
                    </p>

                    <h1 class="text-lg font-black">
                        @yield('heading', 'Dashboard')
                    </h1>
                </div>

            </div>


            <div class="flex items-center gap-3">

                <div class="hidden text-right sm:block">
                    <p class="text-sm font-bold">
                        {{ auth()->user()->name }}
                    </p>

                    <p class="text-xs text-gray-500">
                        {{ $cabor->nama }}
                    </p>
                </div>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf

                    <button type="submit"
                            class="rounded-lg border border-gray-200 px-4 py-2 text-xs font-bold hover:bg-red-50 hover:text-red-600">
                        Logout
                    </button>
                </form>

            </div>

        </header>

        <main class="p-4 sm:p-6 lg:p-8">
            @yield('content')
        </main>

    </div>

</div>

</body>
</html>
