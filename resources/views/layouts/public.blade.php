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
            font-family: var(--website-font-family, Figtree, ui-sans-serif, system-ui, sans-serif);
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
        /* Flex children with auto margins must retain the full available width. */
        .public-site > .website-managed-section,
        .public-site .website-section-stack > .website-managed-section {
            box-sizing: border-box;
            width: 100%;
        }
        .public-site .public-page-hero {
            background-color: var(--website-page-hero-background, #17191F);
            background-image:
                linear-gradient(115deg, rgba(23, 25, 31, var(--website-page-overlay-alpha, .62)), rgba(23, 25, 31, var(--website-page-overlay-alpha, .62))),
                var(--website-page-backdrop, none);
            background-position: center, var(--website-page-position, center);
            background-size: cover, cover;
            background-repeat: no-repeat;
        }
        @supports (background-image: linear-gradient(90deg, color-mix(in srgb, red 50%, transparent), transparent)) {
            .public-site .public-page-hero {
                background-image:
                    linear-gradient(115deg, color-mix(in srgb, var(--website-page-hero-background, #17191F) var(--website-page-overlay, 62%), transparent), color-mix(in srgb, var(--website-page-hero-background, #17191F) var(--website-page-overlay, 62%), transparent)),
                    var(--website-page-backdrop, none);
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

        /* Header publik tetap berada di dalam viewport pada layar kecil. */
        .public-header-inner {
            box-sizing: border-box;
            display: flex;
            width: 100%;
            max-width: 80rem;
            min-width: 0;
            align-items: center;
            justify-content: space-between;
            gap: .5rem;
            margin-inline: auto;
            padding: .75rem .75rem;
        }
        .public-brand { display: flex; min-width: 0; flex: 1 1 auto; align-items: center; gap: .5rem; }
        .public-brand-copy { display: block; min-width: 0; max-width: 100%; line-height: 1.25; }
        .public-brand-name, .public-brand-org { display: block; max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .public-header-actions { display: flex; min-width: 0; flex: 0 0 auto; align-items: center; gap: .5rem; margin-left: auto; }
        .public-portal-desktop { display: none; }
        .public-portal-mobile { display: block; }
        .public-mobile-menu { position: relative; flex: 0 0 auto; }
        .public-mobile-menu-trigger { display: inline-flex; min-height: 2.5rem; align-items: center; justify-content: center; cursor: pointer; list-style: none; white-space: nowrap; }
        .public-mobile-menu-trigger::-webkit-details-marker { display: none; }
        .public-mobile-menu-panel {
            position: absolute;
            top: calc(100% + .5rem);
            right: 0;
            z-index: 50;
            box-sizing: border-box;
            width: min(16rem, calc(100vw - 1.5rem));
            max-height: 75vh;
            overflow-x: hidden;
            overflow-y: auto;
        }
        .public-mobile-menu-panel a { min-width: 0; overflow-wrap: anywhere; }
        @media (min-width: 640px) {
            .public-header-inner { gap: 1rem; padding-inline: 1.5rem; }
            .public-portal-desktop { display: inline-flex; }
            .public-portal-mobile { display: none; }
        }
        @media (min-width: 1024px) {
            .public-header-inner { padding-inline: 2rem; }
        }
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
        <div class="public-header-inner">
            <a href="{{ route('public.home') }}" class="public-brand" aria-label="Beranda KONI Klaten">
                @if($profilKoni?->logo_path)
                    <img src="{{ asset('storage/'.$profilKoni->logo_path) }}" alt="Logo {{ $profilKoni->nama_singkat }}" class="h-11 w-11 rounded-xl bg-white object-contain">
                @elseif(file_exists(public_path('images/logo-koni.png')))
                    <img src="{{ asset('images/logo-koni.png') }}" alt="Logo KONI" class="h-11 w-11 rounded-xl bg-white object-contain">
                @else
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#D71920] text-lg font-black text-white shadow-sm">K</span>
                @endif
                <span class="public-brand-copy">
                    <span class="public-brand-name text-sm font-black tracking-wide text-slate-950">{{ \Illuminate\Support\Str::upper($profilKoni?->nama_singkat ?? 'KONI KLATEN') }}</span>
                    <span class="public-brand-org text-[10px] font-semibold tracking-[0.16em] text-slate-500">{{ $profilKoni?->nama_organisasi ?? 'KABUPATEN KLATEN' }}</span>
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

            <div class="public-header-actions">
                @if(Route::has('login'))
                    <a href="{{ route('login') }}" class="public-portal-desktop items-center rounded-xl bg-[#D71920] px-4 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-[#B9151B] sm:text-sm">
                        {{ $globalContent['portal_button'] ?? 'Portal Anggota' }} <span aria-hidden="true">→</span>
                    </a>
                @endif
                <details class="public-mobile-menu xl:hidden">
                    <summary class="public-mobile-menu-trigger rounded-xl border border-slate-200 px-3 py-2.5 text-sm font-bold text-slate-700" aria-label="Buka menu navigasi">☰</summary>
                    <nav class="koni-public-nav public-mobile-menu-panel grid gap-1 rounded-2xl border border-slate-200 bg-white p-2 shadow-xl" aria-label="Navigasi utama seluler">
                        @if(Route::has('login'))
                            <a class="public-portal-mobile rounded-lg bg-[#D71920] px-3 py-2.5 text-sm font-bold text-white" href="{{ route('login') }}">{{ $globalContent['portal_button'] ?? 'Portal Anggota' }} <span aria-hidden="true">→</span></a>
                        @endif
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
