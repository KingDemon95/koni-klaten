
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard KONI Klaten</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-100">

    <nav class="flex items-center justify-between bg-[#171717] px-6 py-5 text-white">
        <div>
            <h1 class="font-black tracking-wider">
                KONI KLATEN
            </h1>
            <p class="text-xs text-gray-400">
                Sistem Informasi dan Manajemen
            </p>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="rounded-lg bg-[#E60012] px-4 py-2 text-sm font-bold">
                Logout
            </button>
        </form>
    </nav>

    <main class="mx-auto max-w-7xl px-6 py-12">

        <div class="rounded-2xl bg-white p-8 shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-widest text-red-600">
                Dashboard Pengguna
            </p>

            <h2 class="mt-3 text-3xl font-black text-gray-900">
                Selamat Datang,
                {{ auth()->user()->name }}!
            </h2>

            <p class="mt-3 text-gray-500">
                Kamu berhasil masuk ke sistem KONI Kabupaten Klaten.
            </p>

            <div class="mt-6 inline-block rounded-lg bg-yellow-100 px-4 py-2 text-sm font-bold text-yellow-800">
                Role:
                {{ auth()->user()->getRoleNames()->implode(', ') }}
            </div>
        </div>

    </main>

</body>
</html>
