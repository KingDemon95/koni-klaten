<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Portal informasi KONI Kabupaten Klaten: cabang olahraga, atlet, pelatih, dan kepengurusan.">
    <title>@yield('title', 'Portal Informasi') - {{ $profilKoni?->nama_singkat ?? 'KONI Klaten' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .public-site {
            display: flex;
            flex-direction: column;
            align-items: stretch;
            width: 100%;
            min-width: 0;
            font-family: var(--website-font-family, Figtree, ui-sans-serif, system-ui, sans-serif);
        }
        /*
         * Public sections are flex items. Without an explicit width, sections
         * that also use mx-auto can shrink to their max-content width instead
         * of filling the page. Keep each section full-width while preserving
         * any max-w-* constraint declared by its own markup.
         */
        .public-site > .website-managed-section {
            width: 100%;
            min-width: 0;
            box-sizing: border-box;
        }
        .public-site > .website-section-grid {
            width: 100%;
            min-width: 0;
            box-sizing: border-box;
        }
        .public-site .website-section-stack {
            display: flex;
            flex-direction: column;
        }
        .public-site .website-section-grid {
            display: grid;
        }
        .public-site .website-page-button {
            background-color: var(--website-page-button-background, var(--website-primary, #D71920)) !important;
            color: var(--website-page-button-text-color, #FFFFFF) !important;
        }
        .public-site .website-managed-section {
            order: var(--website-section-order, 0);
        }
        .public-site .public-page-hero {
            position: relative;
            width: 100%;
            min-width: 0;
            background-color: var(--website-page-hero-background, #17191F);
            background-image:
                linear-gradient(115deg, rgba(23, 25, 31, var(--website-page-overlay-alpha, .62)), rgba(23, 25, 31, var(--website-page-overlay-alpha, .62))),
                var(--website-page-backdrop, none);
            background-position: center, var(--website-page-position, center);
            background-size: cover, cover;
            background-repeat: no-repeat, no-repeat;
        }
        @supports (background-image: linear-gradient(90deg, color-mix(in srgb, red 50%, transparent), transparent)) {
            .public-site .public-page-hero {
                background-image:
                    linear-gradient(
                        115deg,
                        color-mix(in srgb, var(--website-page-hero-background, #17191F) var(--website-page-overlay, 62%), transparent),
                        color-mix(in srgb, var(--website-page-hero-background, #17191F) var(--website-page-overlay, 62%), transparent)
                    ),
                    var(--website-page-backdrop, none);
                background-size: cover, cover;
            }
        }
        .public-site .website-hero-title { color: var(--website-page-title-color, #FFFFFF) !important; }
        .public-site .website-hero-description { color: var(--website-page-description-color, #CBD5E1) !important; }
        .public-site .website-hero-eyebrow { color: var(--website-page-eyebrow-color, #FCA5A5) !important; }
        .public-site .website-hero-accent { color: var(--website-page-accent-color, #FF656B) !important; }
        .public-site .website-hero-button {
            background-color: var(--website-page-button-background, #D71920) !important;
            color: var(--website-page-button-text-color, #FFFFFF) !important;
        }
        .public-site .website-section-title { color: var(--website-section-heading-color, #0F172A) !important; }
        .public-site .website-section-description { color: var(--website-section-text-color, #64748B) !important; }
        .public-site .website-section-eyebrow,
        .public-site .website-page-accent { color: var(--website-page-accent-color, var(--website-primary, #D71920)) !important; }
        .public-site .website-section-surface { background-color: var(--website-section-background-color, #FFFFFF) !important; }
        .public-theme .bg-\[\#D71920\] { background-color: var(--website-primary, #D71920) !important; }
        .public-theme .text-\[\#D71920\] { color: var(--website-primary, #D71920) !important; }
        .public-theme .bg-\[\#E60012\] { background-color: var(--website-primary, #D71920) !important; }
        .public-theme .text-\[\#E60012\] { color: var(--website-primary, #D71920) !important; }
        .public-theme .bg-\[\#17191F\] { background-color: var(--website-secondary, #17191F) !important; }
        .public-theme .bg-red-600, .public-theme .bg-red-700 { background-color: var(--website-primary, #D71920) !important; }
        .public-theme .text-red-600, .public-theme .text-red-700 { color: var(--website-primary, #D71920) !important; }
        .public-theme .hover\:bg-red-700:hover { background-color: var(--website-primary, #D71920) !important; filter: brightness(.9); }
        .public-theme .public-site .public-page-hero { background-color: var(--website-page-hero-background, #17191F) !important; }
        .public-theme footer { background-color: var(--website-footer-background, #17191F) !important; }
        .public-theme .public-site .text-\[\#D71920\] { color: var(--website-page-accent-color, var(--website-primary, #D71920)) !important; }
        .public-theme .public-site .bg-\[\#D71920\] { background-color: var(--website-page-accent-color, var(--website-primary, #D71920)) !important; }
        .public-theme .public-site .hover\:text-\[\#D71920\]:hover { color: var(--website-page-accent-color, var(--website-primary, #D71920)) !important; }
    </style>
</head>
<body class="public-theme min-h-screen bg-[#F7F8FA] text-slate-900 antialiased" style="font-family: {{ $websiteFontFamily ?? 'Figtree, ui-sans-serif, system-ui, sans-serif' }}; --website-primary: {{ $websiteAppearance['primary_color'] ?? '#D71920' }}; --website-secondary: {{ $websiteAppearance['secondary_color'] ?? '#17191F' }}; --website-footer-background: {{ $websiteAppearance['footer_background'] ?? '#17191F' }}">
    @if($websiteAppearancePreview ?? false)
        <div class="flex flex-wrap items-center justify-between gap-3 bg-amber-100 px-4 py-3 text-xs font-bold text-amber-950">
            <span>MODE PREVIEW DRAFT. Tampilan ini belum dipublikasikan ke pengunjung.</span>
            <a href="{{ route('admin.website-appearance.edit', ['page' => $websitePageKey ?? 'home']) }}" class="underline">Kembali ke pengaturan tampilan</a>
        </div>
    @endif
    @php($globalContent = $websiteAppearance['global_content'] ?? [])
    <a href="#konten-utama" class="koni-skip-link">Langsung ke konten utama</a>
    <header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/95 backdrop-blur" style="background-color: {{ $websiteAppearance['header_background'] ?? '#FFFFFF' }}">
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
                <a href="{{ route('public.home') }}" @if(request()->routeIs('public.home')) aria-current="page" @endif class="transition hover:text-[#D71920]">{{ $globalContent['nav_home'] ?? 'Beranda' }}</a>
                <a href="{{ route('public.home') }}#tentang" class="transition hover:text-[#D71920]">{{ $globalContent['nav_profile'] ?? 'Profil KONI' }}</a>
                <a href="{{ route('public.cabor.index') }}" @if(request()->routeIs('public.cabor.index')) aria-current="page" @endif class="transition hover:text-[#D71920]">{{ $globalContent['nav_cabor'] ?? 'Cabang Olahraga' }}</a>
                <a href="{{ route('public.agenda.index') }}" @if(request()->routeIs('public.agenda.index')) aria-current="page" @endif class="transition hover:text-[#D71920]">{{ $globalContent['nav_agenda'] ?? 'Agenda & Kejuaraan' }}</a>
                <a href="{{ route('public.berita.index') }}" @if(request()->routeIs('public.berita.index')) aria-current="page" @endif class="transition hover:text-[#D71920]">{{ $globalContent['nav_news'] ?? 'Berita' }}</a>
                <a href="{{ route('public.dokumentasi.index') }}" @if(request()->routeIs('public.dokumentasi.index')) aria-current="page" @endif class="transition hover:text-[#D71920]">{{ $globalContent['nav_documentation'] ?? 'Dokumentasi' }}</a>
                <a href="{{ route('public.statistik.index') }}" @if(request()->routeIs('public.statistik.index')) aria-current="page" @endif class="transition hover:text-[#D71920]">{{ $globalContent['nav_statistics'] ?? 'Statistik Prestasi' }}</a>
                <a href="{{ route('public.struktur.index') }}" @if(request()->routeIs('public.struktur.index')) aria-current="page" @endif class="transition hover:text-[#D71920]">{{ $globalContent['nav_structure'] ?? 'Struktur Pengurus' }}</a>
            </nav>

            <div class="flex items-center gap-2">
                @if(Route::has('login'))
                    <a href="{{ route('login') }}" class="rounded-xl bg-[#D71920] px-4 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-[#B9151B] sm:text-sm">
                        {{ $globalContent['portal_button'] ?? 'Portal Anggota' }} <span aria-hidden="true">→</span>
                    </a>
                @endif
                <details class="relative xl:hidden">
                    <summary class="cursor-pointer list-none rounded-xl border border-slate-200 px-3 py-2.5 text-sm font-bold text-slate-700" aria-label="Buka menu navigasi">☰ Menu</summary>
                    <nav class="koni-public-nav absolute right-0 top-full mt-2 grid max-h-[75vh] w-56 gap-1 overflow-y-auto rounded-2xl border border-slate-200 bg-white p-2 shadow-xl" aria-label="Navigasi utama seluler">
                        <a class="rounded-lg px-3 py-2 text-sm hover:bg-slate-50" href="{{ route('public.home') }}" @if(request()->routeIs('public.home')) aria-current="page" @endif>{{ $globalContent['nav_home'] ?? 'Beranda' }}</a>
                        <a class="rounded-lg px-3 py-2 text-sm hover:bg-slate-50" href="{{ route('public.home') }}#tentang">{{ $globalContent['nav_profile'] ?? 'Profil KONI' }}</a>
                        <a class="rounded-lg px-3 py-2 text-sm hover:bg-slate-50" href="{{ route('public.cabor.index') }}" @if(request()->routeIs('public.cabor.index')) aria-current="page" @endif>{{ $globalContent['nav_cabor'] ?? 'Cabang Olahraga' }}</a>
                        <a class="rounded-lg px-3 py-2 text-sm hover:bg-slate-50" href="{{ route('public.agenda.index') }}" @if(request()->routeIs('public.agenda.index')) aria-current="page" @endif>{{ $globalContent['nav_agenda'] ?? 'Agenda & Kejuaraan' }}</a>
                        <a class="rounded-lg px-3 py-2 text-sm hover:bg-slate-50" href="{{ route('public.berita.index') }}" @if(request()->routeIs('public.berita.index')) aria-current="page" @endif>{{ $globalContent['nav_news'] ?? 'Berita' }}</a>
                        <a class="rounded-lg px-3 py-2 text-sm hover:bg-slate-50" href="{{ route('public.dokumentasi.index') }}" @if(request()->routeIs('public.dokumentasi.index')) aria-current="page" @endif>{{ $globalContent['nav_documentation'] ?? 'Dokumentasi' }}</a>
                        <a class="rounded-lg px-3 py-2 text-sm hover:bg-slate-50" href="{{ route('public.statistik.index') }}" @if(request()->routeIs('public.statistik.index')) aria-current="page" @endif>{{ $globalContent['nav_statistics'] ?? 'Statistik Prestasi' }}</a>
                        <a class="rounded-lg px-3 py-2 text-sm hover:bg-slate-50" href="{{ route('public.struktur.index') }}" @if(request()->routeIs('public.struktur.index')) aria-current="page" @endif>{{ $globalContent['nav_structure'] ?? 'Struktur Pengurus' }}</a>
                    </nav>
                </details>
            </div>
        </div>
    </header>

    <main
        id="konten-utama"
        tabindex="-1"
        class="public-site"
        data-website-page="{{ $websitePageKey ?? 'home' }}"
        data-website-mode="{{ ($websiteAppearancePreview ?? false) ? 'draft-preview' : 'published' }}"
        data-website-backdrop="{{ $websitePageBackdropUrl ?? '' }}"
        style="
            --website-primary: {{ $websiteAppearance['primary_color'] ?? '#D71920' }};
            --website-secondary: {{ $websiteAppearance['secondary_color'] ?? '#17191F' }};
            --website-font-family: {{ $websiteFontFamily ?? 'Figtree, ui-sans-serif, system-ui, sans-serif' }};
            --website-page-backdrop: @if($websitePageBackdropUrl ?? null) url('{{ $websitePageBackdropUrl }}') @else none @endif;
            --website-page-overlay: {{ (int) ($websitePageAppearance['overlay_opacity'] ?? 62) }}%;
            --website-page-overlay-alpha: {{ ((int) ($websitePageAppearance['overlay_opacity'] ?? 62)) / 100 }};
            --website-page-position: {{ $websitePageAppearance['background_position'] ?? 'center' }};
            --website-page-hero-background: {{ $websitePageAppearance['hero_background_color'] ?? '#17191F' }};
            --website-page-title-color: {{ $websitePageAppearance['title_color'] ?? '#FFFFFF' }};
            --website-page-description-color: {{ $websitePageAppearance['description_color'] ?? '#CBD5E1' }};
            --website-page-eyebrow-color: {{ $websitePageAppearance['eyebrow_color'] ?? '#FCA5A5' }};
            --website-page-accent-color: {{ $websitePageAppearance['accent_color'] ?? ($websiteAppearance['primary_color'] ?? '#D71920') }};
            --website-page-button-background: {{ $websitePageAppearance['button_background'] ?? '#D71920' }};
            --website-page-button-text-color: {{ $websitePageAppearance['button_text_color'] ?? '#FFFFFF' }};
            --website-section-background-color: {{ $websitePageAppearance['section_background_color'] ?? '#FFFFFF' }};
            --website-section-heading-color: {{ $websitePageAppearance['section_heading_color'] ?? '#0F172A' }};
            --website-section-text-color: {{ $websitePageAppearance['section_text_color'] ?? '#64748B' }};
        "
    >
        @yield('content')
    </main>

    <footer class="mt-20 bg-[#17191F] text-white" style="background-color: {{ $websiteAppearance['footer_background'] ?? '#17191F' }}">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 md:grid-cols-2 lg:px-8">
            <div>
                <div class="flex items-center gap-3">
                    @if($profilKoni?->logo_path)<img src="{{ asset('storage/'.$profilKoni->logo_path) }}" alt="Logo {{ $profilKoni->nama_singkat }}" class="h-11 w-11 rounded-xl bg-white object-contain p-1">@else<span class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#D71920] text-lg font-black">K</span>@endif
                    <div>
                        <p class="font-black tracking-wide">{{ $profilKoni?->nama_organisasi ?? 'KONI KABUPATEN KLATEN' }}</p>
                        <p class="text-xs text-slate-400">{{ $profilKoni?->tagline ?? 'Portal Informasi Olahraga' }}</p>
                    </div>
                </div>
                <p class="mt-5 max-w-md text-sm leading-7 text-slate-400">{{ ($globalContent['footer_description'] ?? '') !== '' ? $globalContent['footer_description'] : ($profilKoni?->deskripsi ?: 'Pusat informasi cabang olahraga, atlet, pelatih, dan kepengurusan Komite Olahraga Nasional Indonesia Kabupaten Klaten.') }}</p>
                @if($profilKoni?->alamat)<p class="mt-3 text-xs leading-6 text-slate-400">{{ $profilKoni->alamat }}</p>@endif
                @if($profilKoni?->telepon || $profilKoni?->email)<p class="mt-2 text-xs text-slate-400">{{ $profilKoni?->telepon }} @if($profilKoni?->telepon && $profilKoni?->email) · @endif {{ $profilKoni?->email }}</p>@endif
            </div>
            <div class="md:justify-self-end">
                <p class="text-sm font-bold">{{ $globalContent['footer_heading'] ?? 'Jelajahi' }}</p>
                <div class="mt-4 grid gap-3 text-sm text-slate-400">
                    <a class="transition hover:text-white" href="{{ route('public.cabor.index') }}">{{ $globalContent['nav_cabor'] ?? 'Cabang Olahraga' }}</a>
                    <a class="transition hover:text-white" href="{{ route('public.agenda.index') }}">{{ $globalContent['nav_agenda'] ?? 'Agenda & Kejuaraan' }}</a>
                    <a class="transition hover:text-white" href="{{ route('public.berita.index') }}">{{ $globalContent['nav_news'] ?? 'Berita' }}</a>
                    <a class="transition hover:text-white" href="{{ route('public.dokumentasi.index') }}">{{ $globalContent['nav_documentation'] ?? 'Dokumentasi' }}</a>
                    <a class="transition hover:text-white" href="{{ route('public.statistik.index') }}">{{ $globalContent['nav_statistics'] ?? 'Statistik Prestasi' }}</a>
                    <a class="transition hover:text-white" href="{{ route('public.struktur.index') }}">{{ $globalContent['nav_structure'] ?? 'Struktur Pengurus' }}</a>
                    @if($profilKoni?->website)<a class="transition hover:text-white" href="{{ $profilKoni->website }}" target="_blank" rel="noopener noreferrer">Website resmi</a>@endif
                    @if($profilKoni?->instagram)<a class="transition hover:text-white" href="{{ $profilKoni->instagram }}" target="_blank" rel="noopener noreferrer">Instagram</a>@endif
                    @if($profilKoni?->facebook)<a class="transition hover:text-white" href="{{ $profilKoni->facebook }}" target="_blank" rel="noopener noreferrer">Facebook</a>@endif
                    @if($profilKoni?->youtube)<a class="transition hover:text-white" href="{{ $profilKoni->youtube }}" target="_blank" rel="noopener noreferrer">YouTube</a>@endif
                    @if(Route::has('login'))
                        <a class="transition hover:text-white" href="{{ route('login') }}">{{ $globalContent['footer_portal'] ?? 'Login Portal Anggota' }}</a>
                    @endif
                </div>
            </div>
        </div>
        <div class="border-t border-white/10">
            <div class="mx-auto flex max-w-7xl flex-col gap-2 px-4 py-5 text-xs text-slate-500 sm:px-6 md:flex-row md:items-center md:justify-between lg:px-8">
                <span>© {{ date('Y') }} {{ $profilKoni?->nama_organisasi ?? 'KONI Kabupaten Klaten' }}.</span>
                <span>{{ $globalContent['footer_bottom'] ?? 'Portal Informasi dan Manajemen Olahraga' }}</span>
            </div>
        </div>
    </footer>
</body>
</html>
