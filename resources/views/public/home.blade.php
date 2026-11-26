@extends('layouts.public')

@section('title', 'Beranda')

@section('content')
@php($copy = $websitePageSections ?? [])

@if($copy['hero']['visible'] ?? true)
    @php($hero = $copy['hero'])
    <section class="public-page-hero website-managed-section relative isolate overflow-hidden" style="order: {{ $hero['order'] ?? 10 }}">
        @if($websitePageBackdropUrl ?? null)
            <img src="{{ $websitePageBackdropUrl }}" alt="" aria-hidden="true" class="pointer-events-none absolute inset-0 z-0 h-full w-full object-cover" style="object-position: {{ $websitePageAppearance['background_position'] ?? 'center' }}">
            <div class="pointer-events-none absolute inset-0 z-0" style="background: {{ $websitePageAppearance['hero_background_color'] ?? '#17191F' }}; opacity: {{ ((int) ($websitePageAppearance['overlay_opacity'] ?? 62)) / 100 }}"></div>
        @endif
        <div class="absolute -right-24 -top-28 -z-10 h-96 w-96 rounded-full border-[38px] border-white/5"></div>
        <div class="absolute -bottom-36 right-[18%] -z-10 h-80 w-80 rounded-full border-[26px] border-white/10"></div>
        <div class="relative z-10 mx-auto grid max-w-7xl items-center gap-12 px-4 py-20 sm:px-6 sm:py-24 lg:grid-cols-[1.15fr_.85fr] lg:px-8 lg:py-28">
            <div>
                @if($hero['eyebrow'] !== '')
                    <span class="website-hero-eyebrow inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-4 py-2 text-[11px] font-bold tracking-[0.2em]">
                        <span class="h-2 w-2 rounded-full bg-current"></span>{{ $hero['eyebrow'] }}
                    </span>
                @endif
                @if($hero['title'] !== '')
                    <h1 class="website-hero-title mt-7 max-w-3xl text-4xl font-black leading-[1.08] tracking-tight sm:text-5xl lg:text-6xl">{{ $hero['title'] }}</h1>
                @endif
                @if($hero['description'] !== '')
                    <p class="website-hero-description mt-6 max-w-2xl text-base leading-8 sm:text-lg">{{ $hero['description'] }}</p>
                @endif
                <div class="mt-9 flex flex-wrap gap-3">
                    @if(($hero['button_label'] ?? '') !== '')
                        <a href="{{ \App\Models\WebsiteAppearanceSetting::buttonUrl($hero['button_target'] ?? 'cabor') }}" class="website-hero-button rounded-xl px-6 py-3.5 text-sm font-bold shadow-lg transition hover:-translate-y-0.5">{{ $hero['button_label'] }} <span aria-hidden="true">→</span></a>
                    @endif
                    @if(($hero['secondary_button_label'] ?? '') !== '')
                        <a href="{{ \App\Models\WebsiteAppearanceSetting::buttonUrl($hero['secondary_button_target'] ?? 'about') }}" class="rounded-xl border border-white/20 bg-white/5 px-6 py-3.5 text-sm font-bold text-white transition hover:bg-white/10">{{ $hero['secondary_button_label'] }}</a>
                    @endif
                </div>
            </div>

            <div class="relative mx-auto w-full max-w-md">
                <div class="rounded-[2rem] border border-white/15 bg-white/[0.07] p-5 shadow-2xl shadow-black/20 backdrop-blur-sm sm:p-7">
                    <div class="flex items-center justify-between border-b border-white/10 pb-5">
                        <div>
                            @if(($hero['card_eyebrow'] ?? '') !== '')<p class="website-hero-eyebrow text-xs font-bold tracking-[0.18em]">{{ $hero['card_eyebrow'] }}</p>@endif
                            @if(($hero['card_title'] ?? '') !== '')<p class="website-hero-title mt-1 text-xl font-black">{{ $hero['card_title'] }}</p>@endif
                        </div>
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/15 text-2xl font-black text-white">K</div>
                    </div>
                    <div class="grid grid-cols-2 gap-3 pt-5">
                        @foreach ([['label' => $hero['card_cabor_label'] ?? 'Cabang olahraga', 'value' => $statistics['cabor']], ['label' => $hero['card_athlete_label'] ?? 'Atlet aktif', 'value' => $statistics['atlet']], ['label' => $hero['card_coach_label'] ?? 'Pelatih aktif', 'value' => $statistics['pelatih']], ['label' => $hero['card_management_label'] ?? 'Pengurus KONI', 'value' => $statistics['pengurus']]] as $stat)
                            <div class="rounded-2xl border border-white/10 bg-black/10 p-4">
                                <p class="website-hero-title text-3xl font-black">{{ number_format($stat['value'], 0, ',', '.') }}</p>
                                <p class="website-hero-description mt-1 text-xs leading-5">{{ $stat['label'] }}</p>
                            </div>
                        @endforeach
                    </div>
                    @if(($hero['card_footer_text'] ?? '') !== '')
                        <div class="mt-5 rounded-2xl bg-white/10 p-4 text-white">
                            @if(($hero['card_footer_eyebrow'] ?? '') !== '')<p class="website-hero-eyebrow text-xs font-bold tracking-wider">{{ $hero['card_footer_eyebrow'] }}</p>@endif
                            <p class="website-hero-title mt-1 text-sm font-semibold">{{ $hero['card_footer_text'] }}</p>
                        </div>
                    @endif
                </div>
                <div class="absolute -bottom-5 -left-5 -z-10 h-28 w-28 rounded-full bg-white/20 blur-2xl"></div>
            </div>
        </div>
    </section>
@endif

@if($copy['stats']['visible'] ?? true)
    @php($statsCopy = $copy['stats'])
    <section class="website-managed-section website-section-surface border-b border-slate-200" style="order: {{ $statsCopy['order'] ?? 20 }}">
        <div class="mx-auto max-w-7xl px-4 pt-6 sm:px-6 lg:px-8">@include('public.partials.managed-heading', ['sectionKey' => 'stats', 'wrapperClass' => 'mb-4'])</div>
        <div class="mx-auto grid max-w-7xl grid-cols-2 gap-5 px-4 py-7 sm:px-6 md:grid-cols-3 lg:grid-cols-6 lg:px-8">
            @foreach ([['value' => $statistics['cabor'], 'label' => $statsCopy['cabor_label']], ['value' => $statistics['atlet'], 'label' => $statsCopy['athlete_label']], ['value' => $statistics['pelatih'], 'label' => $statsCopy['coach_label']], ['value' => $statistics['pengurus'], 'label' => $statsCopy['management_label']], ['value' => $statistics['prestasi'], 'label' => $statsCopy['achievement_label']], ['value' => $statistics['medali'], 'label' => $statsCopy['medal_label']]] as $stat)
                <div class="text-center md:text-left">
                    <p class="website-page-accent text-2xl font-black">{{ number_format($stat['value'], 0, ',', '.') }}</p>
                    <p class="website-section-description mt-1 text-xs font-semibold">{{ $stat['label'] }}</p>
                </div>
            @endforeach
        </div>
    </section>
@endif

@if($copy['about']['visible'] ?? true)
    @php($about = $copy['about'])
    <section id="tentang" class="website-managed-section website-section-surface scroll-mt-24 mx-auto grid max-w-7xl gap-10 px-4 py-20 sm:px-6 lg:grid-cols-[.8fr_1.2fr] lg:px-8" style="order: {{ $about['order'] ?? 30 }}">
        <div>
            @include('public.partials.managed-heading', ['sectionKey' => 'about', 'headingTag' => 'h2', 'showDescription' => false])
        </div>
        <div class="website-section-description space-y-5 text-sm leading-8 sm:text-base">
            <p>{{ $about['description'] !== '' ? $about['description'] : ($profilKoni?->deskripsi ?: 'Komite Olahraga Nasional Indonesia (KONI) Kabupaten Klaten merupakan wadah koordinasi pembinaan dan pengembangan olahraga prestasi di tingkat kabupaten.') }}</p>
            @if($profilKoni?->sejarah)<div><h3 class="website-section-title font-black">{{ $about['history_label'] }}</h3><p class="mt-1 whitespace-pre-line">{{ $profilKoni->sejarah }}</p></div>@endif
            @if($profilKoni?->visi)<div><h3 class="website-section-title font-black">{{ $about['vision_label'] }}</h3><p class="mt-1 whitespace-pre-line">{{ $profilKoni->visi }}</p></div>@endif
            @if($profilKoni?->misi)<div><h3 class="website-section-title font-black">{{ $about['mission_label'] }}</h3><p class="mt-1 whitespace-pre-line">{{ $profilKoni->misi }}</p></div>@endif
            @if($about['closing_text'] !== '')<p>{{ $about['closing_text'] }}</p>@endif
            @if(($about['button_label'] ?? '') !== '')
                <a href="{{ \App\Models\WebsiteAppearanceSetting::buttonUrl($about['button_target'] ?? 'struktur') }}" class="website-page-accent inline-flex items-center gap-2 font-bold hover:underline">{{ $about['button_label'] }} <span aria-hidden="true">→</span></a>
            @endif
        </div>
    </section>
@endif

@if($copy['cabor_preview']['visible'] ?? true)
    @php($caborCopy = $copy['cabor_preview'])
    <section class="website-managed-section website-section-surface bg-white py-20" style="order: {{ $caborCopy['order'] ?? 40 }}">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                @include('public.partials.managed-heading', ['sectionKey' => 'cabor_preview'])
                @if(($caborCopy['button_label'] ?? '') !== '')<a href="{{ \App\Models\WebsiteAppearanceSetting::buttonUrl($caborCopy['button_target'] ?? 'cabor') }}" class="website-page-accent text-sm font-bold hover:underline">{{ $caborCopy['button_label'] }} →</a>@endif
            </div>
            <div class="mt-9 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($cabors as $cabor)
                    <a href="{{ route('public.cabor.show', $cabor) }}" class="group rounded-2xl border border-slate-200 bg-[#FAFAFB] p-5 transition hover:-translate-y-1 hover:border-red-200 hover:bg-white hover:shadow-xl hover:shadow-slate-200/60">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex h-14 w-14 items-center justify-center overflow-hidden rounded-2xl bg-red-50 text-lg font-black text-[#D71920]">
                                @if($cabor->logo_path)<img src="{{ asset('storage/'.$cabor->logo_path) }}" alt="Logo {{ $cabor->nama }}" class="h-full w-full object-contain">@else{{ strtoupper(substr($cabor->singkatan ?: $cabor->nama, 0, 2)) }}@endif
                            </div>
                            <span class="rounded-full bg-white px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-500">Cabor</span>
                        </div>
                        <h3 class="mt-5 text-lg font-black text-slate-900 group-hover:text-[#D71920]">{{ $cabor->nama }}</h3>
                        @if($cabor->singkatan)<p class="mt-1 text-xs font-semibold text-slate-400">{{ $cabor->singkatan }}</p>@endif
                        <p class="mt-3 line-clamp-2 min-h-10 text-sm leading-6 text-slate-500">{{ $cabor->deskripsi ?: 'Informasi cabang olahraga dan pembinaan atlet.' }}</p>
                        <div class="mt-5 flex gap-5 border-t border-slate-200 pt-4 text-xs text-slate-500"><span><strong class="text-slate-900">{{ $cabor->atlet_aktif_count }}</strong> Atlet</span><span><strong class="text-slate-900">{{ $cabor->pelatih_aktif_count }}</strong> Pelatih</span></div>
                    </a>
                @empty
                    <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center"><p class="font-bold text-slate-700">Data cabang olahraga belum tersedia.</p><p class="mt-2 text-sm text-slate-500">Informasi akan muncul setelah data cabor ditambahkan oleh pengelola.</p></div>
                @endforelse
            </div>
        </div>
    </section>
@endif

@if($copy['management_preview']['visible'] ?? true)
    @php($managementCopy = $copy['management_preview'])
    <section class="website-managed-section website-section-surface mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8" style="order: {{ $managementCopy['order'] ?? 50 }}">
        <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            @include('public.partials.managed-heading', ['sectionKey' => 'management_preview'])
            @if(($managementCopy['button_label'] ?? '') !== '')<a href="{{ \App\Models\WebsiteAppearanceSetting::buttonUrl($managementCopy['button_target'] ?? 'struktur') }}" class="website-page-accent text-sm font-bold hover:underline">{{ $managementCopy['button_label'] }} →</a>@endif
        </div>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @forelse($pengurusKoni as $pengurus)
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-red-50 text-lg font-black text-[#D71920]">{{ strtoupper(substr($pengurus->nama_lengkap, 0, 1)) }}</div>
                    <h3 class="mt-4 truncate font-bold text-slate-900">{{ $pengurus->nama_lengkap }}</h3>
                    <p class="website-page-accent mt-1 text-sm font-semibold">{{ $pengurus->jabatan }}</p>
                    @if($pengurus->bidang)<p class="mt-1 text-xs text-slate-500">{{ $pengurus->bidang }}</p>@endif
                </article>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-10 text-center text-sm text-slate-500">Data pengurus KONI belum tersedia.</div>
            @endforelse
        </div>
    </section>
@endif

@if($copy['achievements_preview']['visible'] ?? true)
    @php($achievementCopy = $copy['achievements_preview'])
    <section class="website-managed-section website-section-surface mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8" style="order: {{ $achievementCopy['order'] ?? 60 }}">
        <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            @include('public.partials.managed-heading', ['sectionKey' => 'achievements_preview'])
            @if(($achievementCopy['button_label'] ?? '') !== '')<a href="{{ \App\Models\WebsiteAppearanceSetting::buttonUrl($achievementCopy['button_target'] ?? 'statistik') }}" class="website-page-accent text-sm font-bold hover:underline">{{ $achievementCopy['button_label'] }} →</a>@endif
        </div>
        <div class="grid gap-4 md:grid-cols-3">
            @forelse($featuredPrestasis as $prestasi)
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-2"><span class="rounded-full bg-red-50 px-3 py-1 text-[10px] font-black uppercase tracking-wide text-[#D71920]">{{ ucfirst($prestasi->tingkat) }}</span>@if($prestasi->jenis_medali)<span class="rounded-full bg-amber-50 px-3 py-1 text-[10px] font-black uppercase tracking-wide text-amber-800">Medali {{ ucfirst($prestasi->jenis_medali) }}</span>@endif</div>
                    <h3 class="mt-4 text-lg font-black text-slate-900">{{ $prestasi->nama_kejuaraan }}</h3>
                    <p class="mt-1 text-sm text-slate-600">{{ $prestasi->atlet?->nama_lengkap ?? $prestasi->pelatih?->nama_lengkap ?? 'Atlet/Pelatih KONI Klaten' }}</p>
                    <p class="mt-3 text-xs text-slate-500">{{ $prestasi->cabangOlahraga?->nama }} · {{ $prestasi->tanggal_prestasi?->format('d/m/Y') }}</p>
                    <p class="website-page-accent mt-2 text-sm font-bold">{{ $prestasi->hasil }}</p>
                </article>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-10 text-center text-sm text-slate-500">Belum ada prestasi terverifikasi yang dipublikasikan.</div>
            @endforelse
        </div>
    </section>
@endif

@if($copy['agenda_preview']['visible'] ?? true)
    @php($agendaCopy = $copy['agenda_preview'])
    <section class="website-managed-section website-section-surface bg-white py-20" style="order: {{ $agendaCopy['order'] ?? 70 }}">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                @include('public.partials.managed-heading', ['sectionKey' => 'agenda_preview'])
                @if(($agendaCopy['button_label'] ?? '') !== '')<a href="{{ \App\Models\WebsiteAppearanceSetting::buttonUrl($agendaCopy['button_target'] ?? 'agenda') }}" class="website-page-accent text-sm font-bold hover:underline">{{ $agendaCopy['button_label'] }} →</a>@endif
            </div>
            <div class="grid gap-4 md:grid-cols-3">
                @forelse($upcomingAgendas as $agenda)
                    <a href="{{ route('public.agenda.show', $agenda->slug) }}" class="rounded-2xl border border-slate-200 bg-[#FAFAFB] p-5 transition hover:-translate-y-1 hover:border-red-200 hover:bg-white hover:shadow-lg">
                        <div class="flex items-center justify-between gap-3"><span class="rounded-full bg-red-50 px-3 py-1 text-[10px] font-black uppercase tracking-wide text-[#D71920]">{{ $agenda->jenis }}</span><span class="text-xs font-bold text-slate-500">{{ $agenda->tanggal_mulai?->translatedFormat('d M Y') }}</span></div>
                        <h3 class="mt-4 text-lg font-black text-slate-900">{{ $agenda->judul }}</h3>
                        <p class="mt-2 line-clamp-2 text-sm leading-6 text-slate-500">{{ $agenda->deskripsi ?: 'Informasi kegiatan KONI Kabupaten Klaten.' }}</p>
                        <p class="mt-4 border-t border-slate-200 pt-3 text-xs font-semibold text-slate-500">{{ $agenda->cabangOlahraga?->nama ?? 'Kegiatan KONI' }} @if($agenda->lokasi) · {{ $agenda->lokasi }} @endif</p>
                    </a>
                @empty
                    <div class="col-span-full rounded-2xl border border-dashed border-slate-300 px-6 py-10 text-center text-sm text-slate-500">Belum ada agenda mendatang yang dipublikasikan.</div>
                @endforelse
            </div>
        </div>
    </section>
@endif

@if($copy['news_preview']['visible'] ?? true)
    @php($newsCopy = $copy['news_preview'])
    <section class="website-managed-section website-section-surface mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8" style="order: {{ $newsCopy['order'] ?? 80 }}">
        <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            @include('public.partials.managed-heading', ['sectionKey' => 'news_preview'])
            @if(($newsCopy['button_label'] ?? '') !== '')<a href="{{ \App\Models\WebsiteAppearanceSetting::buttonUrl($newsCopy['button_target'] ?? 'berita') }}" class="website-page-accent text-sm font-bold hover:underline">{{ $newsCopy['button_label'] }} →</a>@endif
        </div>
        <div class="grid gap-5 md:grid-cols-3">
            @forelse($latestBerita as $item)
                <a href="{{ route('public.berita.show', $item->slug) }}" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                    @if($item->media_path)<img src="{{ route('public.publikasi.media', $item->slug) }}" alt="{{ $item->judul }}" class="h-44 w-full object-cover">@else<div class="flex h-44 items-center justify-center bg-gradient-to-br from-[#17191F] to-[#6B1B22] text-4xl font-black text-white">K</div>@endif
                    <div class="p-5"><p class="website-page-accent text-xs font-bold">{{ $item->kategori ?: 'Berita' }} · {{ $item->published_at?->translatedFormat('d M Y') }}</p><h3 class="mt-2 text-lg font-black text-slate-900">{{ $item->judul }}</h3><p class="mt-2 line-clamp-2 text-sm leading-6 text-slate-500">{{ $item->ringkasan ?: \Illuminate\Support\Str::limit(strip_tags($item->konten), 110) }}</p></div>
                </a>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-9 text-center text-sm text-slate-500">Belum ada berita yang dipublikasikan.</div>
            @endforelse
        </div>
    </section>
@endif

@if($copy['documentation_preview']['visible'] ?? true)
    @php($documentationCopy = $copy['documentation_preview'])
    <section class="website-managed-section website-section-surface bg-white py-16" style="order: {{ $documentationCopy['order'] ?? 90 }}">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                @include('public.partials.managed-heading', ['sectionKey' => 'documentation_preview'])
                @if(($documentationCopy['button_label'] ?? '') !== '')<a href="{{ \App\Models\WebsiteAppearanceSetting::buttonUrl($documentationCopy['button_target'] ?? 'dokumentasi') }}" class="website-page-accent text-sm font-bold hover:underline">{{ $documentationCopy['button_label'] }} →</a>@endif
            </div>
            <div class="grid gap-5 md:grid-cols-3">
                @forelse($latestDokumentasi as $item)
                    <a href="{{ route('public.dokumentasi.show', $item->slug) }}" class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"><div class="relative h-48 bg-slate-900">@if($item->jenis === 'foto')<img src="{{ route('public.publikasi.media', $item->slug) }}" alt="{{ $item->judul }}" class="h-full w-full object-cover">@elseif($item->thumbnail_path)<img src="{{ route('public.publikasi.media', [$item->slug, 'thumbnail']) }}" alt="{{ $item->judul }}" class="h-full w-full object-cover">@else<div class="flex h-full items-center justify-center text-4xl text-white">▶</div>@endif</div><div class="p-5"><p class="website-page-accent text-xs font-bold uppercase">{{ $item->jenis }} · {{ $item->published_at?->translatedFormat('d M Y') }}</p><h3 class="mt-2 font-black text-slate-900 group-hover:text-[#D71920]">{{ $item->judul }}</h3></div></a>
                @empty
                    <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-9 text-center text-sm text-slate-500">Belum ada dokumentasi yang dipublikasikan.</div>
                @endforelse
            </div>
        </div>
    </section>
@endif

@if($copy['member_cta']['visible'] ?? true)
    @php($memberCopy = $copy['member_cta'])
    <section class="website-managed-section mx-auto max-w-7xl px-4 pb-20 sm:px-6 lg:px-8" style="order: {{ $memberCopy['order'] ?? 100 }}">
        <div class="flex flex-col items-start justify-between gap-6 rounded-[2rem] px-7 py-9 text-white sm:flex-row sm:items-center sm:px-10 sm:py-11" style="background: var(--website-page-accent-color, var(--website-primary, #D71920))">
            <div>
                @if($memberCopy['eyebrow'] !== '')<p class="website-hero-eyebrow text-xs font-bold tracking-[0.2em]">{{ $memberCopy['eyebrow'] }}</p>@endif
                @if($memberCopy['title'] !== '')<h2 class="website-hero-title mt-2 text-2xl font-black">{{ $memberCopy['title'] }}</h2>@endif
                @if($memberCopy['description'] !== '')<p class="website-hero-description mt-2 max-w-2xl text-sm leading-6">{{ $memberCopy['description'] }}</p>@endif
            </div>
            @if(($memberCopy['button_label'] ?? '') !== '' && Route::has('login'))
                <a href="{{ \App\Models\WebsiteAppearanceSetting::buttonUrl($memberCopy['button_target'] ?? 'login') }}" class="shrink-0 rounded-xl bg-white px-6 py-3.5 text-sm font-black text-slate-900 transition hover:bg-red-50">{{ $memberCopy['button_label'] }} <span aria-hidden="true">→</span></a>
            @endif
        </div>
    </section>
@endif
@endsection
