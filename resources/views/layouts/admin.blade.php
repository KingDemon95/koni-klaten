
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Dashboard') - KONI Kabupaten Klaten</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-[#F4F5F7] text-gray-900">

<div class="min-h-screen lg:flex">

    <!-- SIDEBAR DESKTOP -->
    <aside class="hidden w-72 shrink-0 flex-col bg-[#171717] text-white lg:flex">

        <div class="flex items-center gap-3 border-b border-white/10 px-6 py-7">

            @if(file_exists(public_path('images/logo-koni.png')))
                <img
                    src="{{ asset('images/logo-koni.png') }}"
                    alt="Logo KONI Klaten"
                    class="h-12 w-12 rounded-lg bg-white object-contain p-1"
                >
            @else
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#E60012] text-xl font-black">
                    K
                </div>
            @endif

            <div>
                <h2 class="font-black tracking-wider">KONI KLATEN</h2>
                <p class="text-[10px] tracking-[0.2em] text-gray-400">
                    ADMIN PANEL
                </p>
            </div>

        </div>

        <div class="px-6 pt-7 pb-2">
            <p class="text-[10px] font-bold tracking-[0.2em] text-gray-500">
                MENU NAVIGASI
            </p>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto px-4 py-4">
            @include('admin.partials.nav-links')
        </nav>

        <div class="border-t border-white/10 p-5">
            <p class="text-xs text-gray-400">
                Sistem Informasi dan Manajemen
            </p>
            <p class="mt-1 text-xs font-bold text-[#FFD700]">
                KONI Kabupaten Klaten
            </p>
        </div>

    </aside>


    <!-- MAIN CONTENT -->
    <div class="min-w-0 flex-1">

        <!-- TOPBAR -->
        <header class="sticky top-0 z-30 flex min-h-20 items-center justify-between border-b border-gray-200 bg-white px-4 sm:px-7">

            <div class="flex items-center gap-3">

                <!-- MOBILE MENU -->
                <details class="relative lg:hidden">

                    <summary class="cursor-pointer list-none rounded-lg bg-gray-100 px-3 py-2 text-sm font-bold">
                        ☰ Menu
                    </summary>

                    <div class="absolute left-0 top-full z-50 mt-3 w-72 rounded-xl bg-[#171717] p-4 shadow-2xl">

                        <div class="mb-4 border-b border-white/10 pb-4">
                            <p class="font-black text-white">KONI KLATEN</p>
                            <p class="text-xs text-gray-400">ADMIN PANEL</p>
                        </div>

                        <nav class="space-y-1">
                            @include('admin.partials.nav-links')
                        </nav>

                    </div>

                </details>

                <div>
                    <p class="text-[10px] font-bold tracking-[0.2em] text-[#E60012]">
                        ADMINISTRATION
                    </p>

                    <h1 class="text-lg font-black text-gray-900 sm:text-xl">
                        @yield('heading', 'Dashboard')
                    </h1>
                </div>

            </div>


            <!-- USER PROFILE -->
            <div class="flex items-center gap-3">

                <div class="hidden text-right sm:block">
                    <p class="text-sm font-bold">
                        {{ auth()->user()->name }}
                    </p>

                    <p class="text-xs text-gray-500">
                        Administrator KONI
                    </p>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#171717] font-bold text-[#FFD700]">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
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


        <!-- PAGE CONTENT -->
        <main class="p-4 sm:p-6 lg:p-8">

            @yield('content')

        </main>

    </div>

</div>

</body>
</html>
