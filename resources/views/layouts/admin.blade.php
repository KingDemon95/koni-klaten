
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Dashboard') - KONI Kabupaten Klaten</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .admin-topbar {
            box-sizing: border-box;
            display: flex;
            min-width: 0;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: .5rem;
            padding: .75rem .75rem;
        }
        .admin-topbar-left { display: flex; min-width: 0; flex: 1 1 auto; align-items: center; gap: .5rem; }
        .admin-topbar-heading { min-width: 0; flex: 1 1 auto; }
        .admin-topbar-label { display: none; }
        .admin-topbar-title { max-width: 34vw; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 1rem; line-height: 1.5; }
        .admin-topbar-right { display: flex; min-width: 0; flex: 0 0 auto; align-items: center; gap: .375rem; }
        .admin-topbar-avatar { display: none !important; }
        .admin-topbar-logout { padding: .5rem .625rem; white-space: nowrap; }
        .admin-mobile-menu { position: relative; flex: 0 0 auto; }
        .admin-mobile-menu-trigger { display: inline-flex; min-height: 2.5rem; align-items: center; justify-content: center; cursor: pointer; list-style: none; white-space: nowrap; }
        .admin-mobile-menu-trigger::-webkit-details-marker { display: none; }
        .admin-mobile-menu-label { display: none; }
        .admin-mobile-menu-panel { position: absolute; top: 100%; left: 0; z-index: 50; box-sizing: border-box; width: min(18rem, calc(100vw - 1.5rem)); margin-top: .75rem; }
        @media (min-width: 640px) {
            .admin-topbar { flex-wrap: nowrap; gap: 1rem; padding-inline: 1.75rem; }
            .admin-topbar-left { gap: .75rem; }
            .admin-topbar-label { display: block; }
            .admin-topbar-title { max-width: none; font-size: 1.25rem; }
            .admin-topbar-right { gap: .75rem; }
            .admin-topbar-avatar { display: flex !important; }
            .admin-topbar-logout { padding-inline: .75rem; }
            .admin-mobile-menu-label { display: inline; }
        }
    </style>
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
                    {{ auth()->user()->hasRole('ketua_koni') ? 'KETUA PANEL' : 'ADMIN PANEL' }}
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
        <header class="admin-topbar sticky top-0 z-30 min-h-20 border-b border-gray-200 bg-white">

            <div class="admin-topbar-left">

                <!-- MOBILE MENU -->
                <details class="admin-mobile-menu lg:hidden">

                    <summary class="admin-mobile-menu-trigger rounded-lg bg-gray-100 px-2 py-2 text-sm font-bold" aria-label="Buka menu admin">
                        ☰ <span class="admin-mobile-menu-label">Menu</span>
                    </summary>

                    <div class="admin-mobile-menu-panel rounded-xl bg-[#171717] p-4 shadow-2xl">

                        <div class="mb-4 border-b border-white/10 pb-4">
                            <p class="font-black text-white">KONI KLATEN</p>
                            <p class="text-xs text-gray-400">{{ auth()->user()->hasRole('ketua_koni') ? 'KETUA PANEL' : 'ADMIN PANEL' }}</p>
                        </div>

                        <nav class="space-y-1">
                            @include('admin.partials.nav-links')
                        </nav>

                    </div>

                </details>

                <div class="admin-topbar-heading">
                    <p class="admin-topbar-label text-[10px] font-bold tracking-[0.2em] text-[#E60012]">
                        {{ auth()->user()->hasRole('ketua_koni') ? 'KEPEMIMPINAN KONI' : 'ADMINISTRATION' }}
                    </p>

                    <h1 class="admin-topbar-title font-black text-gray-900">
                        @yield('heading', 'Dashboard')
                    </h1>
                </div>

            </div>


            <!-- USER PROFILE -->
            <div class="admin-topbar-right">

                @include('partials.notification-link')

                <div class="hidden text-right sm:block">
                    <p class="text-sm font-bold">
                        {{ auth()->user()->name }}
                    </p>

                    <p class="text-xs text-gray-500">
                        {{ auth()->user()->hasRole('ketua_koni') ? 'Ketua KONI' : 'Administrator KONI' }}
                    </p>
                </div>

                <div class="admin-topbar-avatar flex h-10 w-10 items-center justify-center rounded-full bg-[#171717] font-bold text-[#FFD700]">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button
                        type="submit"
                        class="admin-topbar-logout rounded-lg border border-gray-200 text-xs font-bold text-gray-600 transition hover:border-red-500 hover:bg-red-50 hover:text-red-600"
                    >
                        Logout
                    </button>
                </form>

            </div>

        </header>


        <!-- PAGE CONTENT -->
        <main id="konten-utama" tabindex="-1" class="min-w-0 w-full max-w-full p-4 sm:p-6 lg:p-8">
            @include('partials.demo-feedback')

            @yield('content')

        </main>

    </div>

</div>

</body>
</html>
