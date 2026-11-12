<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Portal informasi KONI Kabupaten Klaten: cabang olahraga, atlet, pelatih, dan kepengurusan.">
    <title>@yield('title', 'Portal Informasi') - {{ $profilKoni?->nama_singkat ?? 'KONI Klaten' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#F7F8FA] text-slate-900 antialiased">
    <a href="#konten-utama" class="koni-skip-link">Langsung ke konten utama</a>
    <header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/95 backdrop-blur">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
            <a href="{{ route('public.home') }}" class="flex shrink-0 items-center gap-3" aria-label="Beranda KONI Klaten">
                @if($profilKoni?->logo_path)
                    <img src="{{ asset('storage/'.$profilKoni->logo_path) }}" alt="Logo {{ $profilKoni->nama_singkat }}" class="h-11 w-11 rounded-xl bg-white object-contain">
                @elseif(file_exists(public_path('images/logo-koni.png')))
                    <img src="{{ asset('images/logo-koni.png') }}" alt="Logo KONI" class="h-11 w-11 rounded-xl bg-white object-contain">
                @else
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#D71920] text-lg font-black text-white shadow-sm">K</span>
                @endif
                <span class="leading-tight">
                    <span class="block text-sm font-black tracking-wide text-slate-950">{{ \Illuminate\Support\Str::upper($profilKoni?->nama_singkat ?? 'KONI KLATEN') }}</span>
                    <span class="block text-[10px] font-semibold tracking-[0.16em] text-slate-500">{{ $profilKoni?->nama_organisasi ?? 'KABUPATEN KLATEN' }}</span>
                </span>
            </a>

            <nav class="hidden items-center gap-4 text-xs font-semibold text-slate-600 xl:flex koni-public-nav" aria-label="Navigasi utama">
                <a href="{{ route('public.home') }}" @if(request()->routeIs('public.home')) aria-current="page" @endif class="transition hover:text-[#D71920]">Beranda</a>
                <a href="{{ route('public.home') }}#tentang" class="transition hover:text-[#D71920]">Profil KONI</a>
                <a href="{{ route('public.cabor.index') }}" @if(request()->routeIs('public.cabor.index')) aria-current="page" @endif class="transition hover:text-[#D71920]">Cabang Olahraga</a>
                <a href="{{ route('public.agenda.index') }}" @if(request()->routeIs('public.agenda.index')) aria-current="page" @endif class="transition hover:text-[#D71920]">Agenda & Kejuaraan</a>
                <a href="{{ route('public.berita.index') }}" @if(request()->routeIs('public.berita.index')) aria-current="page" @endif class="transition hover:text-[#D71920]">Berita</a>
                <a href="{{ route('public.dokumentasi.index') }}" @if(request()->routeIs('public.dokumentasi.index')) aria-current="page" @endif class="transition hover:text-[#D71920]">Dokumentasi</a>
                <a href="{{ route('public.statistik.index') }}" @if(request()->routeIs('public.statistik.index')) aria-current="page" @endif class="transition hover:text-[#D71920]">Statistik Prestasi</a>
                <a href="{{ route('public.struktur.index') }}" @if(request()->routeIs('public.struktur.index')) aria-current="page" @endif class="transition hover:text-[#D71920]">Struktur Pengurus</a>
            </nav>

            <div class="flex items-center gap-2">
                @if(Route::has('login'))
                    <a href="{{ route('login') }}" class="rounded-xl bg-[#D71920] px-4 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-[#B9151B] sm:text-sm">
                        Portal Anggota <span aria-hidden="true">→</span>
                    </a>
                @endif
                <details class="relative xl:hidden">
                    <summary class="cursor-pointer list-none rounded-xl border border-slate-200 px-3 py-2.5 text-sm font-bold text-slate-700" aria-label="Buka menu navigasi">☰ Menu</summary>
                    <nav class="koni-public-nav absolute right-0 top-full mt-2 grid max-h-[75vh] w-56 gap-1 overflow-y-auto rounded-2xl border border-slate-200 bg-white p-2 shadow-xl" aria-label="Navigasi utama seluler">
                        <a class="rounded-lg px-3 py-2 text-sm hover:bg-slate-50" href="{{ route('public.home') }}" @if(request()->routeIs('public.home')) aria-current="page" @endif>Beranda</a>
                        <a class="rounded-lg px-3 py-2 text-sm hover:bg-slate-50" href="{{ route('public.home') }}#tentang">Profil KONI</a>
                        <a class="rounded-lg px-3 py-2 text-sm hover:bg-slate-50" href="{{ route('public.cabor.index') }}" @if(request()->routeIs('public.cabor.index')) aria-current="page" @endif>Cabang Olahraga</a>
                        <a class="rounded-lg px-3 py-2 text-sm hover:bg-slate-50" href="{{ route('public.agenda.index') }}" @if(request()->routeIs('public.agenda.index')) aria-current="page" @endif>Agenda & Kejuaraan</a>
                        <a class="rounded-lg px-3 py-2 text-sm hover:bg-slate-50" href="{{ route('public.berita.index') }}" @if(request()->routeIs('public.berita.index')) aria-current="page" @endif>Berita</a>
                        <a class="rounded-lg px-3 py-2 text-sm hover:bg-slate-50" href="{{ route('public.dokumentasi.index') }}" @if(request()->routeIs('public.dokumentasi.index')) aria-current="page" @endif>Dokumentasi</a>
                        <a class="rounded-lg px-3 py-2 text-sm hover:bg-slate-50" href="{{ route('public.statistik.index') }}" @if(request()->routeIs('public.statistik.index')) aria-current="page" @endif>Statistik Prestasi</a>
                        <a class="rounded-lg px-3 py-2 text-sm hover:bg-slate-50" href="{{ route('public.struktur.index') }}" @if(request()->routeIs('public.struktur.index')) aria-current="page" @endif>Struktur Pengurus</a>
                    </nav>
                </details>
            </div>
        </div>
    </header>

    <main id="konten-utama" tabindex="-1">
        @yield('content')
    </main>

    <footer class="mt-20 bg-[#17191F] text-white">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 md:grid-cols-2 lg:px-8">
            <div>
                <div class="flex items-center gap-3">
                    @if($profilKoni?->logo_path)<img src="{{ asset('storage/'.$profilKoni->logo_path) }}" alt="Logo {{ $profilKoni->nama_singkat }}" class="h-11 w-11 rounded-xl bg-white object-contain p-1">@else<span class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#D71920] text-lg font-black">K</span>@endif
                    <div>
                        <p class="font-black tracking-wide">{{ $profilKoni?->nama_organisasi ?? 'KONI KABUPATEN KLATEN' }}</p>
                        <p class="text-xs text-slate-400">{{ $profilKoni?->tagline ?? 'Portal Informasi Olahraga' }}</p>
                    </div>
                </div>
                <p class="mt-5 max-w-md text-sm leading-7 text-slate-400">{{ $profilKoni?->deskripsi ?: 'Pusat informasi cabang olahraga, atlet, pelatih, dan kepengurusan Komite Olahraga Nasional Indonesia Kabupaten Klaten.' }}</p>
                @if($profilKoni?->alamat)<p class="mt-3 text-xs leading-6 text-slate-400">{{ $profilKoni->alamat }}</p>@endif
                @if($profilKoni?->telepon || $profilKoni?->email)<p class="mt-2 text-xs text-slate-400">{{ $profilKoni?->telepon }} @if($profilKoni?->telepon && $profilKoni?->email) · @endif {{ $profilKoni?->email }}</p>@endif
            </div>
            <div class="md:justify-self-end">
                <p class="text-sm font-bold">Jelajahi</p>
                <div class="mt-4 grid gap-3 text-sm text-slate-400">
                    <a class="transition hover:text-white" href="{{ route('public.cabor.index') }}">Daftar Cabang Olahraga</a>
                    <a class="transition hover:text-white" href="{{ route('public.agenda.index') }}">Agenda & Kejuaraan</a>
                    <a class="transition hover:text-white" href="{{ route('public.berita.index') }}">Berita KONI</a>
                    <a class="transition hover:text-white" href="{{ route('public.dokumentasi.index') }}">Dokumentasi</a>
                    <a class="transition hover:text-white" href="{{ route('public.statistik.index') }}">Statistik Prestasi</a>
                    <a class="transition hover:text-white" href="{{ route('public.struktur.index') }}">Struktur Pengurus KONI</a>
                    @if($profilKoni?->website)<a class="transition hover:text-white" href="{{ $profilKoni->website }}" target="_blank" rel="noopener noreferrer">Website resmi</a>@endif
                    @if($profilKoni?->instagram)<a class="transition hover:text-white" href="{{ $profilKoni->instagram }}" target="_blank" rel="noopener noreferrer">Instagram</a>@endif
                    @if($profilKoni?->facebook)<a class="transition hover:text-white" href="{{ $profilKoni->facebook }}" target="_blank" rel="noopener noreferrer">Facebook</a>@endif
                    @if($profilKoni?->youtube)<a class="transition hover:text-white" href="{{ $profilKoni->youtube }}" target="_blank" rel="noopener noreferrer">YouTube</a>@endif
                    @if(Route::has('login'))
                        <a class="transition hover:text-white" href="{{ route('login') }}">Login Portal Anggota</a>
                    @endif
                </div>
            </div>
        </div>
        <div class="border-t border-white/10">
            <div class="mx-auto flex max-w-7xl flex-col gap-2 px-4 py-5 text-xs text-slate-500 sm:px-6 md:flex-row md:items-center md:justify-between lg:px-8">
                <span>© {{ date('Y') }} {{ $profilKoni?->nama_organisasi ?? 'KONI Kabupaten Klaten' }}.</span>
                <span>Portal Informasi dan Manajemen Olahraga</span>
            </div>
        </div>
    </footer>
</body>
</html>
