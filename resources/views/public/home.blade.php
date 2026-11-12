@extends('layouts.public')

@section('title', 'Beranda')

@section('content')
<section class="relative isolate overflow-hidden bg-[#17191F]">
    <div class="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_78%_30%,rgba(215,25,32,0.42),transparent_34%),linear-gradient(115deg,#17191F_0%,#24252B_55%,#4A171B_100%)]"></div>
    <div class="absolute -right-24 -top-28 -z-10 h-96 w-96 rounded-full border-[38px] border-white/5"></div>
    <div class="absolute -bottom-36 right-[18%] -z-10 h-80 w-80 rounded-full border-[26px] border-[#D71920]/20"></div>

    <div class="mx-auto grid max-w-7xl items-center gap-12 px-4 py-20 sm:px-6 sm:py-24 lg:grid-cols-[1.15fr_.85fr] lg:px-8 lg:py-28">
        <div>
            <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-4 py-2 text-[11px] font-bold tracking-[0.2em] text-red-200">
                <span class="h-2 w-2 rounded-full bg-[#F0444B]"></span>
                KOMITE OLAHRAGA NASIONAL INDONESIA
            </span>
            <h1 class="mt-7 max-w-3xl text-4xl font-black leading-[1.08] tracking-tight text-white sm:text-5xl lg:text-6xl">
                Bersama membangun <span class="text-[#FF656B]">prestasi olahraga</span> Kabupaten Klaten.
            </h1>
            <p class="mt-6 max-w-2xl text-base leading-8 text-slate-300 sm:text-lg">
                {{ $profilKoni?->tagline ?: 'Satu portal informasi untuk mengenal cabang olahraga, atlet, pelatih, dan kepengurusan KONI Kabupaten Klaten.' }}
            </p>
            <div class="mt-9 flex flex-wrap gap-3">
                <a href="{{ route('public.cabor.index') }}" class="rounded-xl bg-[#D71920] px-6 py-3.5 text-sm font-bold text-white shadow-lg shadow-red-950/20 transition hover:-translate-y-0.5 hover:bg-[#B9151B]">Jelajahi Cabang Olahraga <span aria-hidden="true">→</span></a>
                <a href="#tentang" class="rounded-xl border border-white/20 bg-white/5 px-6 py-3.5 text-sm font-bold text-white transition hover:bg-white/10">Tentang KONI</a>
            </div>
        </div>

        <div class="relative mx-auto w-full max-w-md">
            <div class="rounded-[2rem] border border-white/15 bg-white/[0.07] p-5 shadow-2xl shadow-black/20 backdrop-blur-sm sm:p-7">
                <div class="flex items-center justify-between border-b border-white/10 pb-5">
                    <div>
                        <p class="text-xs font-bold tracking-[0.18em] text-red-200">PORTAL OLAHRAGA</p>
                        <p class="mt-1 text-xl font-black text-white">KONI Klaten</p>
                    </div>
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-[#D71920] text-2xl font-black text-white">K</div>
                </div>
                <div class="grid grid-cols-2 gap-3 pt-5">
                    @foreach ([['label' => 'Cabang olahraga', 'value' => $statistics['cabor']], ['label' => 'Atlet aktif', 'value' => $statistics['atlet']], ['label' => 'Pelatih aktif', 'value' => $statistics['pelatih']], ['label' => 'Pengurus KONI', 'value' => $statistics['pengurus']]] as $stat)
                        <div class="rounded-2xl border border-white/10 bg-black/10 p-4">
                            <p class="text-3xl font-black text-white">{{ number_format($stat['value'], 0, ',', '.') }}</p>
                            <p class="mt-1 text-xs leading-5 text-slate-300">{{ $stat['label'] }}</p>
                        </div>
                    @endforeach
                </div>
                <div class="mt-5 rounded-2xl bg-[#D71920] p-4 text-white">
                    <p class="text-xs font-bold tracking-wider text-red-100">SATU WADAH INFORMASI</p>
                    <p class="mt-1 text-sm font-semibold">Kenali potensi dan ekosistem olahraga Klaten.</p>
                </div>
            </div>
            <div class="absolute -bottom-5 -left-5 -z-10 h-28 w-28 rounded-full bg-[#D71920]/50 blur-2xl"></div>
        </div>
    </div>
</section>

<section class="border-b border-slate-200 bg-white">
    <div class="mx-auto grid max-w-7xl grid-cols-2 gap-5 px-4 py-7 sm:px-6 md:grid-cols-3 lg:grid-cols-6 lg:px-8">
        @foreach ([['value' => $statistics['cabor'], 'label' => 'Cabang olahraga'], ['value' => $statistics['atlet'], 'label' => 'Atlet aktif'], ['value' => $statistics['pelatih'], 'label' => 'Pelatih aktif'], ['value' => $statistics['pengurus'], 'label' => 'Pengurus KONI'], ['value' => $statistics['prestasi'], 'label' => 'Prestasi terverifikasi'], ['value' => $statistics['medali'], 'label' => 'Medali terdata']] as $stat)
            <div class="text-center md:text-left">
                <p class="text-2xl font-black text-[#D71920]">{{ number_format($stat['value'], 0, ',', '.') }}</p>
                <p class="mt-1 text-xs font-semibold text-slate-500">{{ $stat['label'] }}</p>
            </div>
        @endforeach
    </div>
</section>

<section id="tentang" class="scroll-mt-24 mx-auto grid max-w-7xl gap-10 px-4 py-20 sm:px-6 lg:grid-cols-[.8fr_1.2fr] lg:px-8">
    <div>
        <p class="text-xs font-black tracking-[0.2em] text-[#D71920]">TENTANG ORGANISASI</p>
        <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">{{ 'Mengenal ' . ($profilKoni?->nama_organisasi ?? 'KONI Kabupaten Klaten') }}</h2>
    </div>
    <div class="space-y-5 text-sm leading-8 text-slate-600 sm:text-base">
        <p>{{ $profilKoni?->deskripsi ?: 'Komite Olahraga Nasional Indonesia (KONI) Kabupaten Klaten merupakan wadah koordinasi pembinaan dan pengembangan olahraga prestasi di tingkat kabupaten.' }}</p>
        @if($profilKoni?->sejarah)<div><h3 class="font-black text-slate-900">Sejarah</h3><p class="mt-1 whitespace-pre-line">{{ $profilKoni->sejarah }}</p></div>@endif
        @if($profilKoni?->visi)<div><h3 class="font-black text-slate-900">Visi</h3><p class="mt-1 whitespace-pre-line">{{ $profilKoni->visi }}</p></div>@endif
        @if($profilKoni?->misi)<div><h3 class="font-black text-slate-900">Misi</h3><p class="mt-1 whitespace-pre-line">{{ $profilKoni->misi }}</p></div>@endif
        <p>Melalui portal ini, masyarakat dapat mengenal cabang olahraga, melihat informasi atlet dan pelatih yang terdaftar, serta mengetahui susunan pengurus organisasi.</p>
        <a href="{{ route('public.struktur.index') }}" class="inline-flex items-center gap-2 font-bold text-[#D71920] hover:underline">Lihat struktur pengurus <span aria-hidden="true">→</span></a>
    </div>
</section>

<section class="bg-white py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <p class="text-xs font-black tracking-[0.2em] text-[#D71920]">EKOSISTEM OLAHRAGA</p>
                <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-950">Cabang olahraga</h2>
                <p class="mt-2 max-w-xl text-sm leading-7 text-slate-500">Jelajahi cabang olahraga yang terdaftar di KONI Kabupaten Klaten.</p>
            </div>
            <a href="{{ route('public.cabor.index') }}" class="text-sm font-bold text-[#D71920] hover:underline">Lihat semua cabor →</a>
        </div>

        <div class="mt-9 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($cabors as $cabor)
                <a href="{{ route('public.cabor.show', $cabor) }}" class="group rounded-2xl border border-slate-200 bg-[#FAFAFB] p-5 transition hover:-translate-y-1 hover:border-red-200 hover:bg-white hover:shadow-xl hover:shadow-slate-200/60">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex h-14 w-14 items-center justify-center overflow-hidden rounded-2xl bg-red-50 text-lg font-black text-[#D71920]">
                            @if($cabor->logo_path)
                                <img src="{{ asset('storage/'.$cabor->logo_path) }}" alt="Logo {{ $cabor->nama }}" class="h-full w-full object-contain">
                            @else
                                {{ strtoupper(substr($cabor->singkatan ?: $cabor->nama, 0, 2)) }}
                            @endif
                        </div>
                        <span class="rounded-full bg-white px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-500">Cabor</span>
                    </div>
                    <h3 class="mt-5 text-lg font-black text-slate-900 group-hover:text-[#D71920]">{{ $cabor->nama }}</h3>
                    @if($cabor->singkatan)
                        <p class="mt-1 text-xs font-semibold text-slate-400">{{ $cabor->singkatan }}</p>
                    @endif
                    <p class="mt-3 line-clamp-2 min-h-10 text-sm leading-6 text-slate-500">{{ $cabor->deskripsi ?: 'Informasi cabang olahraga dan pembinaan atlet.' }}</p>
                    <div class="mt-5 flex gap-5 border-t border-slate-200 pt-4 text-xs text-slate-500">
                        <span><strong class="text-slate-900">{{ $cabor->atlet_aktif_count }}</strong> Atlet</span>
                        <span><strong class="text-slate-900">{{ $cabor->pelatih_aktif_count }}</strong> Pelatih</span>
                    </div>
                </a>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center">
                    <p class="font-bold text-slate-700">Data cabang olahraga belum tersedia.</p>
                    <p class="mt-2 text-sm text-slate-500">Informasi akan muncul setelah data cabor ditambahkan oleh pengelola.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-xs font-black tracking-[0.2em] text-[#D71920]">KEPENGURUSAN</p>
            <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-950">Pengurus KONI Klaten</h2>
            <p class="mt-2 text-sm leading-7 text-slate-500">Susunan pengurus tingkat kabupaten yang tercatat dalam sistem.</p>
        </div>
        <a href="{{ route('public.struktur.index') }}" class="text-sm font-bold text-[#D71920] hover:underline">Lihat struktur lengkap →</a>
    </div>

    <div class="mt-9 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($pengurusKoni as $pengurus)
            <article class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-full bg-slate-100 text-lg font-black text-slate-500">
                    @if($pengurus->foto_path)
                        <img src="{{ asset('storage/'.$pengurus->foto_path) }}" alt="Foto {{ $pengurus->nama_lengkap }}" class="h-full w-full object-cover">
                    @else
                        {{ strtoupper(substr($pengurus->nama_lengkap, 0, 1)) }}
                    @endif
                </div>
                <div class="min-w-0">
                    <h3 class="truncate font-bold text-slate-900">{{ $pengurus->nama_lengkap }}</h3>
                    <p class="mt-1 text-sm font-semibold text-[#D71920]">{{ $pengurus->jabatan }}</p>
                    @if($pengurus->bidang)
                        <p class="mt-1 text-xs text-slate-500">{{ $pengurus->bidang }}</p>
                    @endif
                </div>
            </article>
        @empty
            <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-10 text-center text-sm text-slate-500">Data pengurus KONI belum tersedia.</div>
        @endforelse
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-xs font-black tracking-[0.2em] text-[#D71920]">CAPAIAN OLAHRAGA</p>
            <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-950">Prestasi terverifikasi</h2>
            <p class="mt-2 max-w-xl text-sm leading-7 text-slate-500">Capaian atlet dan pelatih yang telah diverifikasi oleh Admin Cabor.</p>
        </div>
        <a href="{{ route('public.statistik.index') }}" class="text-sm font-bold text-[#D71920] hover:underline">Lihat statistik lengkap →</a>
    </div>
    <div class="mt-8 grid gap-4 md:grid-cols-3">
        @forelse($featuredPrestasis as $prestasi)
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span class="rounded-full bg-red-50 px-3 py-1 text-[10px] font-black uppercase tracking-wide text-[#D71920]">{{ ucfirst($prestasi->tingkat) }}</span>
                    @if($prestasi->jenis_medali)
                        <span class="rounded-full bg-amber-50 px-3 py-1 text-[10px] font-black uppercase tracking-wide text-amber-800">Medali {{ ucfirst($prestasi->jenis_medali) }}</span>
                    @endif
                </div>
                <h3 class="mt-4 text-lg font-black text-slate-900">{{ $prestasi->nama_kejuaraan }}</h3>
                <p class="mt-1 text-sm text-slate-600">{{ $prestasi->atlet?->nama_lengkap ?? $prestasi->pelatih?->nama_lengkap ?? 'Atlet/Pelatih KONI Klaten' }}</p>
                <p class="mt-3 text-xs text-slate-500">{{ $prestasi->cabangOlahraga?->nama }} · {{ $prestasi->tanggal_prestasi?->format('d/m/Y') }}</p>
                <p class="mt-2 text-sm font-bold text-[#D71920]">{{ $prestasi->hasil }}</p>
            </article>
        @empty
            <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-10 text-center text-sm text-slate-500">Belum ada prestasi terverifikasi yang dipublikasikan.</div>
        @endforelse
    </div>
</section>

<section class="bg-white py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <p class="text-xs font-black tracking-[0.2em] text-[#D71920]">KALENDER OLAHRAGA</p>
                <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-950">Agenda & kejuaraan mendatang</h2>
                <p class="mt-2 max-w-xl text-sm leading-7 text-slate-500">Informasi kegiatan dan kejuaraan yang telah dipublikasikan oleh pengelola KONI Klaten.</p>
            </div>
            <a href="{{ route('public.agenda.index') }}" class="text-sm font-bold text-[#D71920] hover:underline">Lihat semua agenda →</a>
        </div>
        <div class="mt-8 grid gap-4 md:grid-cols-3">
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

<section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div><p class="text-xs font-black tracking-[0.2em] text-[#D71920]">KABAR TERBARU</p><h2 class="mt-3 text-3xl font-black tracking-tight text-slate-950">Berita KONI Klaten</h2><p class="mt-2 text-sm leading-7 text-slate-500">Informasi dan perkembangan olahraga Kabupaten Klaten.</p></div>
        <a href="{{ route('public.berita.index') }}" class="text-sm font-bold text-[#D71920] hover:underline">Semua berita →</a>
    </div>
    <div class="mt-8 grid gap-5 md:grid-cols-3">
        @forelse($latestBerita as $item)
            <a href="{{ route('public.berita.show', $item->slug) }}" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                @if($item->media_path)<img src="{{ route('public.publikasi.media', $item->slug) }}" alt="{{ $item->judul }}" class="h-44 w-full object-cover">@else<div class="flex h-44 items-center justify-center bg-gradient-to-br from-[#17191F] to-[#6B1B22] text-4xl font-black text-white">K</div>@endif
                <div class="p-5"><p class="text-xs font-bold text-[#D71920]">{{ $item->kategori ?: 'Berita' }} · {{ $item->published_at?->translatedFormat('d M Y') }}</p><h3 class="mt-2 text-lg font-black text-slate-900">{{ $item->judul }}</h3><p class="mt-2 line-clamp-2 text-sm leading-6 text-slate-500">{{ $item->ringkasan ?: \Illuminate\Support\Str::limit(strip_tags($item->konten), 110) }}</p></div>
            </a>
        @empty
            <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-9 text-center text-sm text-slate-500">Belum ada berita yang dipublikasikan.</div>
        @endforelse
    </div>
</section>

<section class="bg-white py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end"><div><p class="text-xs font-black tracking-[0.2em] text-[#D71920]">GALERI KEGIATAN</p><h2 class="mt-3 text-3xl font-black tracking-tight text-slate-950">Dokumentasi olahraga</h2><p class="mt-2 text-sm leading-7 text-slate-500">Foto dan video kegiatan KONI Kabupaten Klaten.</p></div><a href="{{ route('public.dokumentasi.index') }}" class="text-sm font-bold text-[#D71920] hover:underline">Lihat semua dokumentasi →</a></div>
        <div class="mt-8 grid gap-5 md:grid-cols-3">
            @forelse($latestDokumentasi as $item)
                <a href="{{ route('public.dokumentasi.show', $item->slug) }}" class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"><div class="relative h-48 bg-slate-900">@if($item->jenis === 'foto')<img src="{{ route('public.publikasi.media', $item->slug) }}" alt="{{ $item->judul }}" class="h-full w-full object-cover">@elseif($item->thumbnail_path)<img src="{{ route('public.publikasi.media', [$item->slug, 'thumbnail']) }}" alt="{{ $item->judul }}" class="h-full w-full object-cover">@else<div class="flex h-full items-center justify-center text-4xl text-white">▶</div>@endif</div><div class="p-5"><p class="text-xs font-bold uppercase text-[#D71920]">{{ $item->jenis }} · {{ $item->published_at?->translatedFormat('d M Y') }}</p><h3 class="mt-2 font-black text-slate-900 group-hover:text-[#D71920]">{{ $item->judul }}</h3></div></a>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-9 text-center text-sm text-slate-500">Belum ada dokumentasi yang dipublikasikan.</div>
            @endforelse
        </div>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 pb-20 sm:px-6 lg:px-8">
    <div class="flex flex-col items-start justify-between gap-6 rounded-[2rem] bg-[#D71920] px-7 py-9 text-white sm:flex-row sm:items-center sm:px-10 sm:py-11">
        <div>
            <p class="text-xs font-bold tracking-[0.2em] text-red-100">PORTAL ANGGOTA</p>
            <h2 class="mt-2 text-2xl font-black">Atlet, pelatih, dan pengelola cabor</h2>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-red-100">Masuk ke portal anggota untuk mengakses layanan sesuai hak akses akun.</p>
        </div>
        @if(Route::has('login'))
            <a href="{{ route('login') }}" class="shrink-0 rounded-xl bg-white px-6 py-3.5 text-sm font-black text-[#D71920] transition hover:bg-red-50">Masuk ke Portal <span aria-hidden="true">→</span></a>
        @endif
    </div>
</section>
@endsection
