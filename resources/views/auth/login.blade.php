
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - KONI Kabupaten Klaten</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-[#111111]">

<div class="grid min-h-screen lg:grid-cols-2">

    <!-- BRANDING -->
    <section class="relative flex min-h-[350px] items-center overflow-hidden bg-[#171717] p-8 text-white lg:p-16">

        <div class="absolute -left-32 -top-32 h-96 w-96 rounded-full bg-red-600/20 blur-3xl"></div>
        <div class="absolute -bottom-32 -right-20 h-96 w-96 rounded-full bg-yellow-500/10 blur-3xl"></div>

        <div class="relative z-10 max-w-xl">

            <div class="mb-10 inline-flex items-center gap-3">
                <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-[#E60012] text-xl font-black text-white">
                    K
                </div>

                <div>
                    <h2 class="text-xl font-black tracking-wider">
                        KONI KLATEN
                    </h2>
                    <p class="text-xs tracking-widest text-gray-400">
                        KABUPATEN KLATEN
                    </p>
                </div>
            </div>

            <p class="mb-4 text-sm font-bold uppercase tracking-[0.3em] text-[#FFD700]">
                Komite Olahraga Nasional Indonesia
            </p>

            <h1 class="text-4xl font-black leading-tight sm:text-5xl">
                Satukan Semangat,
                <span class="block text-[#FFD700]">
                    Raih Prestasi.
                </span>
            </h1>

            <p class="mt-6 max-w-md leading-relaxed text-gray-400">
                Sistem Informasi dan Manajemen Olahraga
                KONI Kabupaten Klaten.
            </p>

            <div class="mt-12 h-1 w-24 rounded-full bg-[#E60012]"></div>

            <p class="mt-6 text-xs text-gray-500">
                © {{ date('Y') }} KONI Kabupaten Klaten.
                Seluruh hak cipta dilindungi.
            </p>

        </div>
    </section>


    <!-- LOGIN FORM -->
    <section class="flex items-center justify-center bg-[#f5f5f5] px-6 py-12">

        <div class="w-full max-w-md rounded-3xl bg-white p-8 shadow-xl sm:p-10">

            <div class="mb-8">
                <span class="text-sm font-bold uppercase tracking-widest text-[#E60012]">
                    Welcome Back
                </span>

                <h2 class="mt-3 text-3xl font-black text-gray-900">
                    Selamat Datang
                </h2>

                <p class="mt-2 text-sm text-gray-500">
                    Silakan login untuk mengakses sistem KONI Klaten.
                </p>
            </div>


            @if ($errors->any())
                <div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                    Email atau password yang kamu masukkan tidak sesuai.
                </div>
            @endif

            @if (session('status'))
                <div class="mb-5 rounded-xl bg-green-50 p-4 text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif


            <form action="{{ route('login') }}" method="POST">
                @csrf

                <div class="mb-5">
                    <label for="email"
                        class="mb-2 block text-sm font-semibold text-gray-700">
                        Email
                    </label>

                    <input
                        type="email"
                        name="email"
                        id="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="nama@email.com"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none transition focus:border-[#E60012] focus:ring-2 focus:ring-red-100"
                    >

                    @error('email')
                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>


                <div class="mb-5">
                    <label for="password"
                        class="mb-2 block text-sm font-semibold text-gray-700">
                        Password
                    </label>

                    <input
                        type="password"
                        name="password"
                        id="password"
                        required
                        autocomplete="current-password"
                        placeholder="Masukkan password"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none transition focus:border-[#E60012] focus:ring-2 focus:ring-red-100"
                    >

                    @error('password')
                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>


                <div class="mb-7 flex items-center gap-2">
                    <input
                        type="checkbox"
                        name="remember"
                        id="remember"
                        value="1"
                        class="h-4 w-4 accent-[#E60012]"
                    >

                    <label for="remember" class="text-sm text-gray-600">
                        Ingat saya
                    </label>
                </div>


                <button
                    type="submit"
                    class="w-full rounded-xl bg-[#E60012] py-3.5 text-sm font-bold text-white shadow-lg shadow-red-200 transition hover:bg-red-700"
                >
                    Masuk ke Dashboard
                </button>

            </form>


            <div class="mt-8 border-t border-gray-100 pt-6 text-center">
                <p class="text-xs leading-relaxed text-gray-400">
                    Belum memiliki akun?
                    <br>
                    Hubungi administrator KONI Kabupaten Klaten.
                </p>
            </div>

        </div>

    </section>

</div>

</body>
</html>
