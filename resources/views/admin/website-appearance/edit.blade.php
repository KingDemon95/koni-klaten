@extends('layouts.admin')

@section('title', 'Tampilan Website Publik')
@section('heading', 'Tampilan Website Publik')

@section('content')
<div class="mx-auto max-w-6xl space-y-6">
    @if(session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <p class="font-bold">Ada pengaturan yang perlu diperiksa:</p>
            <ul class="mt-2 list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <section class="rounded-2xl border border-blue-200 bg-blue-50 p-5 sm:p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.18em] text-blue-700">Pusat pengaturan website</p>
                <h2 class="mt-2 text-xl font-black text-blue-950">Atur seluruh halaman publik KONI Klaten</h2>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-blue-900">Pengaturan ini hanya memengaruhi website publik. Dashboard Admin KONI, Staff, Ketua, Sekretaris, Admin Cabor, Atlet, dan Pelatih tidak ikut berubah.</p>
            </div>
            <div class="rounded-xl border border-blue-200 bg-white px-4 py-3 text-xs text-blue-900">
                <p class="font-bold">Status publikasi</p>
                <p class="mt-1">{{ $setting->published_at ? 'Terakhir dipublikasikan '.$setting->published_at->format('d/m/Y H:i') : 'Belum pernah dipublikasikan' }}</p>
            </div>
        </div>
    </section>

    <form method="POST" action="{{ route('admin.website-appearance.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-7">
            <div class="mb-5">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-red-600">Identitas visual global</p>
                <h3 class="mt-2 text-lg font-black">Tema seluruh website publik</h3>
                <p class="mt-1 text-sm text-gray-500">Warna, header, footer, dan tipografi berlaku konsisten pada seluruh halaman publik.</p>
            </div>
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <label for="primary_color" class="mb-2 block text-sm font-bold">Warna utama</label>
                    <input id="primary_color" type="color" name="primary_color" value="{{ old('primary_color', $draftConfig['primary_color'] ?? '#D71920') }}" class="h-12 w-full rounded-lg border border-gray-300 bg-white p-1">
                    <p class="mt-1 text-[11px] text-gray-500">Pilih warna yang cukup gelap agar teks tombol tetap terbaca.</p>
                    @error('primary_color')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="secondary_color" class="mb-2 block text-sm font-bold">Warna sekunder</label>
                    <input id="secondary_color" type="color" name="secondary_color" value="{{ old('secondary_color', $draftConfig['secondary_color'] ?? '#17191F') }}" class="h-12 w-full rounded-lg border border-gray-300 bg-white p-1">
                    @error('secondary_color')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="font_family" class="mb-2 block text-sm font-bold">Jenis huruf</label>
                    <select id="font_family" name="font_family" class="w-full rounded-lg border-gray-300 text-sm">
                        <option value="figtree" @selected(old('font_family', $draftConfig['font_family'] ?? 'figtree') === 'figtree')>Figtree</option>
                        <option value="inter" @selected(old('font_family', $draftConfig['font_family'] ?? 'figtree') === 'inter')>Inter</option>
                        <option value="system" @selected(old('font_family', $draftConfig['font_family'] ?? 'figtree') === 'system')>System default</option>
                    </select>
                    @error('font_family')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="header_background" class="mb-2 block text-sm font-bold">Warna header</label>
                    <select id="header_background" name="header_background" class="w-full rounded-lg border-gray-300 text-sm">
                        <option value="#FFFFFF" @selected(old('header_background', $draftConfig['header_background'] ?? '#FFFFFF') === '#FFFFFF')>Putih</option>
                        <option value="#F7F8FA" @selected(old('header_background', $draftConfig['header_background'] ?? '#FFFFFF') === '#F7F8FA')>Abu-abu sangat muda</option>
                        <option value="#FFF5F5" @selected(old('header_background', $draftConfig['header_background'] ?? '#FFFFFF') === '#FFF5F5')>Merah sangat muda</option>
                    </select>
                    @error('header_background')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="footer_background" class="mb-2 block text-sm font-bold">Warna footer</label>
                    <select id="footer_background" name="footer_background" class="w-full rounded-lg border-gray-300 text-sm">
                        <option value="#17191F" @selected(old('footer_background', $draftConfig['footer_background'] ?? '#17191F') === '#17191F')>Hitam arang</option>
                        <option value="#20242B" @selected(old('footer_background', $draftConfig['footer_background'] ?? '#17191F') === '#20242B')>Abu-abu gelap</option>
                        <option value="#3B1015" @selected(old('footer_background', $draftConfig['footer_background'] ?? '#17191F') === '#3B1015')>Merah gelap</option>
                    </select>
                    @error('footer_background')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        <section class="space-y-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-red-600">Backdrop per halaman</p>
                <h3 class="mt-2 text-lg font-black">Gambar banner halaman publik</h3>
                <p class="mt-1 text-sm text-gray-500">Setiap halaman dapat menggunakan gambar sendiri. Jika gambar kosong, halaman memakai latar bawaan website. Simpan Draft terlebih dahulu sebelum membuka preview agar perubahan terbaru terlihat.</p>
            </div>

            @foreach($pages as $pageKey => $pageLabel)
                @php($pageConfig = $draftConfig['pages'][$pageKey] ?? ['backdrop_path' => null, 'overlay_opacity' => 62, 'background_position' => 'center'])
                <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h4 class="font-black">{{ $pageLabel }}</h4>
                            <p class="mt-1 text-xs text-gray-500">Kunci halaman: {{ $pageKey }}</p>
                        </div>
                        <a href="{{ route('admin.website-appearance.preview', $pageKey) }}" target="_blank" rel="noopener" class="rounded-lg border border-gray-300 px-4 py-2 text-xs font-bold text-gray-700 hover:bg-gray-50">Preview halaman</a>
                    </div>

                    @if($pageConfig['backdrop_path'] ?? null)
                        <div class="mt-4 overflow-hidden rounded-xl border border-gray-200 bg-gray-100">
                            <img src="{{ asset('storage/'.$pageConfig['backdrop_path']) }}" alt="Draft backdrop {{ $pageLabel }}" class="h-40 w-full object-cover" style="object-position: {{ $pageConfig['background_position'] ?? 'center' }}">
                            <p class="px-3 py-2 text-xs text-gray-500">Gambar draft saat ini</p>
                        </div>
                    @else
                        <div class="mt-4 flex h-28 items-center justify-center rounded-xl border border-dashed border-gray-300 bg-gray-50 text-xs font-semibold text-gray-500">Belum ada backdrop khusus</div>
                    @endif

                    <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div class="lg:col-span-1">
                            <label for="backdrop-{{ $pageKey }}" class="mb-2 block text-xs font-bold">Unggah backdrop baru</label>
                            <input id="backdrop-{{ $pageKey }}" type="file" name="backdrops[{{ $pageKey }}]" accept=".jpg,.jpeg,.png,.webp" class="w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-xs">
                            <p class="mt-1 text-[11px] text-gray-500">JPG, PNG, WEBP. Maksimal 4 MB.</p>
                            @error('backdrops.'.$pageKey)<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="opacity-{{ $pageKey }}" class="mb-2 block text-xs font-bold">Lapisan gelap <span data-opacity-label="{{ $pageKey }}">{{ $pageConfig['overlay_opacity'] ?? 62 }}%</span></label>
                            <input id="opacity-{{ $pageKey }}" type="range" name="overlay_opacity[{{ $pageKey }}]" min="10" max="90" value="{{ old('overlay_opacity.'.$pageKey, $pageConfig['overlay_opacity'] ?? 62) }}" class="w-full accent-red-600" data-opacity-slider="{{ $pageKey }}">
                        </div>
                        <div>
                            <label for="position-{{ $pageKey }}" class="mb-2 block text-xs font-bold">Posisi gambar</label>
                            <select id="position-{{ $pageKey }}" name="background_position[{{ $pageKey }}]" class="w-full rounded-lg border-gray-300 text-sm">
                                <option value="center" @selected(old('background_position.'.$pageKey, $pageConfig['background_position'] ?? 'center') === 'center')>Tengah</option>
                                <option value="top" @selected(old('background_position.'.$pageKey, $pageConfig['background_position'] ?? 'center') === 'top')>Atas</option>
                                <option value="bottom" @selected(old('background_position.'.$pageKey, $pageConfig['background_position'] ?? 'center') === 'bottom')>Bawah</option>
                            </select>
                        </div>
                    </div>

                    @if($pageConfig['backdrop_path'] ?? null)
                        <label class="mt-4 flex items-center gap-2 rounded-lg border border-red-100 bg-red-50 p-3 text-xs font-semibold text-red-800">
                            <input type="checkbox" name="remove_backdrop[{{ $pageKey }}]" value="1" class="rounded border-red-300 text-red-600">
                            Hapus backdrop draft halaman ini
                        </label>
                    @endif
                </article>
            @endforeach
        </section>

        <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="max-w-2xl text-xs leading-5 text-gray-500">Simpan Draft hanya menyimpan perubahan di panel Admin. Website publik tetap menggunakan tampilan yang sudah dipublikasikan sampai Anda menekan tombol Publikasikan.</p>
            <button class="rounded-lg bg-[#E60012] px-5 py-3 text-sm font-bold text-white hover:bg-red-700">Simpan Draft</button>
        </div>
    </form>

    <section class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-green-200 bg-green-50 p-5">
        <div>
            <h3 class="font-black text-green-950">Publikasikan tampilan website</h3>
            <p class="mt-1 text-xs leading-5 text-green-900">Publikasi menerapkan draft ke seluruh halaman publik terkait. Pastikan Anda sudah memeriksa preview setiap halaman.</p>
        </div>
        <form method="POST" action="{{ route('admin.website-appearance.publish') }}" onsubmit="return confirm('Publikasikan seluruh perubahan draft ke website publik?');">
            @csrf
            <button class="rounded-lg bg-green-700 px-5 py-3 text-sm font-bold text-white hover:bg-green-800">Publikasikan Website</button>
        </form>
    </section>
</div>

<script>
    document.querySelectorAll('[data-opacity-slider]').forEach((slider) => {
        const key = slider.dataset.opacitySlider;
        const label = document.querySelector(`[data-opacity-label="${key}"]`);
        slider.addEventListener('input', () => {
            label.textContent = `${slider.value}%`;
        });
    });
</script>
@endsection
