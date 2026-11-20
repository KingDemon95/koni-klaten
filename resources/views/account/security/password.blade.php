<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Keamanan Akun - KONI Kabupaten Klaten</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#F4F5F7] text-gray-900">
    <a href="#konten-utama" class="koni-skip-link">Langsung ke konten utama</a>

    <main id="konten-utama" tabindex="-1" class="flex min-h-screen items-center justify-center px-4 py-10">
        <section class="w-full max-w-xl rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
            <div class="mb-6 flex items-center gap-3">
                @if(file_exists(public_path('images/logo-koni.png')))
                    <img src="{{ asset('images/logo-koni.png') }}" alt="Logo KONI Klaten" class="h-12 w-12 rounded-xl bg-white object-contain p-1">
                @else
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#E60012] text-xl font-black text-white">K</div>
                @endif
                <div>
                    <p class="text-xs font-bold tracking-[0.18em] text-[#E60012]">KONI KABUPATEN KLATEN</p>
                    <p class="text-sm font-semibold text-gray-500">Keamanan Akun</p>
                </div>
            </div>

            @if($requiresChange)
                <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm leading-6 text-amber-900">
                    <p class="font-bold">Penggantian password wajib dilakukan.</p>
                    <p class="mt-1">Akun Anda menggunakan password awal. Buat password pribadi sebelum melanjutkan ke dashboard atau fitur lainnya.</p>
                </div>
            @else
                <div class="mb-6">
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#E60012]">Pengaturan keamanan</p>
                    <h1 class="mt-2 text-2xl font-black">Ubah Password</h1>
                    <p class="mt-2 text-sm leading-6 text-gray-600">Perbarui password secara berkala dan jangan gunakan password yang sama dengan akun lain.</p>
                </div>
            @endif

            @if(session('warning'))
                <p role="alert" class="mb-4 rounded-lg bg-amber-50 p-3 text-sm text-amber-800">{{ session('warning') }}</p>
            @endif

            @if(session('success'))
                <p role="status" class="mb-4 rounded-lg bg-green-50 p-3 text-sm text-green-800">{{ session('success') }}</p>
            @endif

            <form method="POST" action="{{ route('account.password.update') }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="current_password" class="mb-2 block text-sm font-semibold text-gray-700">Password saat ini</label>
                    <input id="current_password" name="current_password" type="password" required autocomplete="current-password" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm outline-none focus:border-[#E60012] focus:ring-2 focus:ring-red-100">
                    @error('current_password')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password" class="mb-2 block text-sm font-semibold text-gray-700">Password baru</label>
                    <input id="password" name="password" type="password" required minlength="10" autocomplete="new-password" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm outline-none focus:border-[#E60012] focus:ring-2 focus:ring-red-100">
                    <p class="mt-2 text-xs leading-5 text-gray-500">Minimal 10 karakter, memuat huruf besar, huruf kecil, dan angka.</p>
                    @error('password')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password_confirmation" class="mb-2 block text-sm font-semibold text-gray-700">Konfirmasi password baru</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required minlength="10" autocomplete="new-password" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm outline-none focus:border-[#E60012] focus:ring-2 focus:ring-red-100">
                </div>

                <button type="submit" class="w-full rounded-xl bg-[#E60012] px-4 py-3.5 text-sm font-bold text-white transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300 focus:ring-offset-2">
                    Simpan Password Baru
                </button>
            </form>

            <div class="mt-6 flex items-center justify-between border-t border-gray-100 pt-5">
                @unless($requiresChange)
                    <a href="{{ route('dashboard') }}" class="text-sm font-semibold text-gray-600 hover:text-[#E60012]">Kembali ke dashboard</a>
                @else
                    <span class="text-xs text-gray-500">Selesaikan penggantian password untuk melanjutkan.</span>
                @endunless
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm font-semibold text-gray-500 hover:text-red-600">Logout</button>
                </form>
            </div>
        </section>
    </main>
</body>
</html>
