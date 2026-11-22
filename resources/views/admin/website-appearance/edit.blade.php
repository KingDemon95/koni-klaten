@extends('layouts.admin')

@section('title', 'Editor Website Publik')
@section('heading', 'Editor Website Publik')

@section('content')
@php
    $fieldLabels = [
        'eyebrow' => 'Label kecil / kategori',
        'title' => 'Judul',
        'description' => 'Deskripsi',
        'button_label' => 'Teks tombol',
        'button_target' => 'Tujuan tombol',
        'secondary_button_label' => 'Teks tombol kedua',
        'secondary_button_target' => 'Tujuan tombol kedua',
        'card_eyebrow' => 'Label kartu statistik',
        'card_title' => 'Judul kartu statistik',
        'card_footer_eyebrow' => 'Label bagian bawah kartu',
        'card_footer_text' => 'Tulisan bagian bawah kartu',
        'card_cabor_label' => 'Label jumlah cabor pada kartu',
        'card_athlete_label' => 'Label jumlah atlet pada kartu',
        'card_coach_label' => 'Label jumlah pelatih pada kartu',
        'card_management_label' => 'Label jumlah pengurus pada kartu',
        'cabor_label' => 'Label statistik cabor',
        'athlete_label' => 'Label statistik atlet',
        'coach_label' => 'Label statistik pelatih',
        'management_label' => 'Label statistik pengurus',
        'medal_label' => 'Label statistik medali',
        'history_label' => 'Judul sejarah',
        'vision_label' => 'Judul visi',
        'mission_label' => 'Judul misi',
        'closing_text' => 'Paragraf penutup',
        'gold_label' => 'Label medali emas',
        'silver_label' => 'Label medali perak',
        'bronze_label' => 'Label medali perunggu',
        'achievement_label' => 'Label prestasi',
        'search_placeholder' => 'Placeholder pencarian',
        'reset_label' => 'Teks tombol reset',
        'count_label' => 'Label jumlah data',
        'detail_link_label' => 'Teks tautan detail',
        'filter_label' => 'Label filter',
        'selected_year_prefix' => 'Teks sebelum tahun terpilih',
        'all_years_text' => 'Teks saat semua tahun ditampilkan',
    ];
    $globalContentLabels = [
        'nav_home' => 'Menu Beranda',
        'nav_profile' => 'Menu Profil KONI',
        'nav_cabor' => 'Menu Cabang Olahraga',
        'nav_agenda' => 'Menu Agenda dan Kejuaraan',
        'nav_news' => 'Menu Berita',
        'nav_documentation' => 'Menu Dokumentasi',
        'nav_statistics' => 'Menu Statistik Prestasi',
        'nav_structure' => 'Menu Struktur Pengurus',
        'portal_button' => 'Tombol Portal Anggota',
        'footer_heading' => 'Judul navigasi footer',
        'footer_description' => 'Deskripsi footer (kosong = gunakan profil KONI)',
        'footer_portal' => 'Teks tautan login di footer',
        'footer_bottom' => 'Teks bagian paling bawah footer',
    ];
    $colorLabels = [
        'hero_background_color' => 'Warna latar banner',
        'title_color' => 'Warna judul banner',
        'description_color' => 'Warna deskripsi banner',
        'eyebrow_color' => 'Warna label kecil',
        'section_background_color' => 'Warna latar bagian konten',
        'section_heading_color' => 'Warna judul bagian konten',
        'section_text_color' => 'Warna deskripsi bagian konten',
        'accent_color' => 'Warna aksen dan tautan',
        'button_background' => 'Warna tombol',
        'button_text_color' => 'Warna teks tombol',
    ];
@endphp

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
                <p class="text-xs font-black uppercase tracking-[0.18em] text-blue-700">Editor website publik</p>
                <h2 class="mt-2 text-xl font-black text-blue-950">Atur tampilan dan isi setiap halaman</h2>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-blue-900">Pilih satu halaman, ubah banner, warna, tulisan, dan bagian yang ingin ditampilkan. Simpan sebagai draft, periksa preview, lalu publikasikan jika sudah sesuai. Dashboard internal tidak ikut berubah.</p>
            </div>
            <div class="rounded-xl border border-blue-200 bg-white px-4 py-3 text-xs text-blue-900">
                <p class="font-bold">Publikasi terakhir</p>
                <p class="mt-1">{{ $setting->published_at ? $setting->published_at->format('d/m/Y H:i') : 'Belum pernah dipublikasikan' }}</p>
            </div>
        </div>
    </section>

    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
        <label for="page-selector" class="mb-2 block text-sm font-black text-gray-900">Halaman yang sedang diedit</label>
        <form method="GET" action="{{ route('admin.website-appearance.edit') }}" class="flex flex-col gap-3 sm:flex-row">
            <select id="page-selector" name="page" class="min-w-0 flex-1 rounded-xl border-gray-300 text-sm" onchange="this.form.submit()">
                @foreach($pages as $pageKey => $label)
                    <option value="{{ $pageKey }}" @selected($selectedPage === $pageKey)>{{ $label }}</option>
                @endforeach
            </select>
            <a href="{{ route('admin.website-appearance.preview', $selectedPage) }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center rounded-xl border border-gray-300 px-5 py-3 text-sm font-bold text-gray-700 hover:bg-gray-50">Preview draft halaman</a>
        </form>
        <p class="mt-2 text-xs leading-5 text-gray-500">Pengaturan yang disimpan di bawah hanya mengubah halaman {{ strtolower($pageLabel) }}. Halaman lain tetap memakai pengaturannya sendiri.</p>
    </section>

    <form id="websiteAppearanceForm" method="POST" action="{{ route('admin.website-appearance.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')
        <input type="hidden" name="page_key" value="{{ $selectedPage }}">

        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="mb-5">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-red-600">Tema umum</p>
                <h3 class="mt-1 text-lg font-black text-gray-900">Identitas seluruh website</h3>
                <p class="mt-1 text-sm text-gray-500">Pengaturan ini berlaku untuk semua halaman publik.</p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach(['primary_color' => 'Warna utama website', 'secondary_color' => 'Warna sekunder website', 'header_background' => 'Warna header', 'footer_background' => 'Warna footer'] as $field => $label)
                    <div>
                        <label for="{{ $field }}" class="mb-2 block text-xs font-bold text-gray-700">{{ $label }}</label>
                        <input id="{{ $field }}" type="color" name="{{ $field }}" value="{{ old($field, $draftConfig[$field] ?? '#FFFFFF') }}" class="h-11 w-full rounded-lg border border-gray-300 bg-white p-1">
                        @error($field)<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                @endforeach
                <div>
                    <label for="font_family" class="mb-2 block text-xs font-bold text-gray-700">Jenis huruf</label>
                    <select id="font_family" name="font_family" class="w-full rounded-lg border-gray-300 text-sm">
                        <option value="figtree" @selected(old('font_family', $draftConfig['font_family'] ?? 'figtree') === 'figtree')>Figtree</option>
                        <option value="inter" @selected(old('font_family', $draftConfig['font_family'] ?? 'figtree') === 'inter')>Inter</option>
                        <option value="system" @selected(old('font_family', $draftConfig['font_family'] ?? 'figtree') === 'system')>System default</option>
                    </select>
                    @error('font_family')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2 lg:col-span-3 border-t border-gray-100 pt-4">
                    <h4 class="text-sm font-black text-gray-900">Tulisan navigasi dan footer</h4>
                    <p class="mt-1 text-xs text-gray-500">Bagian ini berlaku sama pada seluruh halaman publik.</p>
                </div>
                @foreach($globalContentLabels as $field => $label)
                    <div class="{{ $field === 'footer_description' ? 'sm:col-span-2 lg:col-span-3' : '' }}">
                        <label for="global-content-{{ $field }}" class="mb-2 block text-xs font-bold text-gray-700">{{ $label }}</label>
                        @if($field === 'footer_description')
                            <textarea id="global-content-{{ $field }}" name="global_content[{{ $field }}]" rows="2" maxlength="3000" class="w-full rounded-lg border-gray-300 text-sm">{{ old('global_content.'.$field, data_get($draftConfig, 'global_content.'.$field, '')) }}</textarea>
                        @else
                            <input id="global-content-{{ $field }}" type="text" name="global_content[{{ $field }}]" value="{{ old('global_content.'.$field, data_get($draftConfig, 'global_content.'.$field, '')) }}" maxlength="240" class="w-full rounded-lg border-gray-300 text-sm">
                        @endif
                    </div>
                @endforeach
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="mb-5">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-red-600">Banner dan warna halaman</p>
                <h3 class="mt-1 text-lg font-black text-gray-900">Tampilan {{ $pageLabel }}</h3>
            </div>

            <div class="grid gap-5 lg:grid-cols-[1fr_1.2fr]">
                <div>
                    <label for="backdrop" class="mb-2 block text-sm font-bold">Gambar backdrop halaman</label>
                    @if($pageConfig['backdrop_path'] ?? null)
                        <div class="mb-3 overflow-hidden rounded-xl border border-gray-200 bg-gray-100">
                            <img src="{{ asset('storage/'.$pageConfig['backdrop_path']) }}" alt="Backdrop draft {{ $pageLabel }}" class="h-44 w-full object-cover" style="object-position: {{ $pageConfig['background_position'] ?? 'center' }}">
                            <p class="px-3 py-2 text-xs text-gray-500">Backdrop draft saat ini</p>
                        </div>
                    @else
                        <div class="mb-3 flex h-32 items-center justify-center rounded-xl border border-dashed border-gray-300 bg-gray-50 text-xs font-semibold text-gray-500">Belum ada backdrop khusus</div>
                    @endif
                    <input id="backdrop" type="file" name="backdrop" accept=".jpg,.jpeg,.png,.webp" class="w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-xs">
                    <p class="mt-1 text-[11px] text-gray-500">JPG, PNG, atau WEBP. Maksimal 4 MB.</p>
                    @error('backdrop')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    @if($pageConfig['backdrop_path'] ?? null)
                        <label class="mt-3 flex items-center gap-2 text-xs font-semibold text-red-700">
                            <input type="checkbox" name="remove_backdrop" value="1" class="rounded border-red-300 text-red-600">
                            Hapus backdrop dan gunakan warna latar
                        </label>
                    @endif
                </div>

                <div class="grid content-start gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="overlay_opacity" class="mb-2 flex justify-between text-xs font-bold"><span>Lapisan gelap backdrop</span><span id="opacity-value">{{ $pageConfig['overlay_opacity'] ?? 62 }}%</span></label>
                        <input id="overlay_opacity" type="range" name="overlay_opacity" min="0" max="90" value="{{ old('overlay_opacity', $pageConfig['overlay_opacity'] ?? 62) }}" class="w-full accent-red-600">
                    </div>
                    <div class="sm:col-span-2">
                        <label for="background_position" class="mb-2 block text-xs font-bold">Posisi gambar</label>
                        <select id="background_position" name="background_position" class="w-full rounded-lg border-gray-300 text-sm">
                            @foreach(['center' => 'Tengah', 'top' => 'Atas', 'bottom' => 'Bawah', 'left' => 'Kiri', 'right' => 'Kanan'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('background_position', $pageConfig['background_position'] ?? 'center') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    @foreach($colorLabels as $field => $label)
                        <div>
                            <label for="{{ $field }}" class="mb-2 block text-xs font-bold text-gray-700">{{ $label }}</label>
                            <input id="{{ $field }}" type="color" name="{{ $field }}" value="{{ old($field, $pageConfig[$field] ?? '#FFFFFF') }}" class="h-10 w-full rounded-lg border border-gray-300 bg-white p-1">
                            @error($field)<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="mb-5">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-red-600">Konten halaman</p>
                <h3 class="mt-1 text-lg font-black text-gray-900">Tulisan dan bagian yang ditampilkan</h3>
                <p class="mt-1 text-sm leading-6 text-gray-500">Ubah label, judul, deskripsi, dan tombol. Matikan bagian yang tidak ingin ditampilkan. Gunakan tombol panah untuk mengubah urutan bagian. Nama atlet, agenda, berita, dan data dinamis tetap dikelola dari modul datanya.</p>
            </div>

            <div id="website-sections" class="space-y-3">
                @foreach($sectionDefinitions as $sectionKey => $definition)
                    @php($sectionConfig = $pageConfig['sections'][$sectionKey] ?? $definition['defaults'])
                    <article data-section-card class="rounded-xl border border-gray-200 bg-gray-50 p-4" style="order: {{ $sectionConfig['order'] ?? ($loop->iteration * 10) }}">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white text-xs font-black text-gray-500">{{ $loop->iteration }}</span>
                                <div class="min-w-0">
                                    <button type="button" data-section-toggle aria-expanded="{{ $loop->first ? 'true' : 'false' }}" class="text-left text-sm font-black text-gray-900 hover:text-red-700">{{ $definition['label'] }}</button>
                                    <p class="text-[11px] text-gray-500">Bagian: {{ $sectionKey }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" data-move-up class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-bold text-gray-600 hover:bg-gray-100" aria-label="Pindah ke atas">↑</button>
                                <button type="button" data-move-down class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-bold text-gray-600 hover:bg-gray-100" aria-label="Pindah ke bawah">↓</button>
                                <label class="flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-bold text-gray-700">
                                    <input type="hidden" name="sections[{{ $sectionKey }}][visible]" value="0">
                                    <input type="checkbox" name="sections[{{ $sectionKey }}][visible]" value="1" @checked((bool) old('sections.'.$sectionKey.'.visible', $sectionConfig['visible'] ?? true)) class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                    Tampilkan
                                </label>
                            </div>
                        </div>
                        <input type="hidden" name="sections[{{ $sectionKey }}][order]" value="{{ old('sections.'.$sectionKey.'.order', $sectionConfig['order'] ?? ($loop->iteration * 10)) }}" data-section-order>

                        <div data-section-fields class="mt-4 grid gap-4 sm:grid-cols-2" @if(!$loop->first) hidden @endif>
                            @foreach($definition['defaults'] as $field => $defaultValue)
                                @php($fieldValue = old('sections.'.$sectionKey.'.'.$field, $sectionConfig[$field] ?? $defaultValue))
                                @php($fieldMaxLength = match($field) { 'eyebrow' => 120, 'title' => 240, 'button_label', 'secondary_button_label' => 100, default => 3000 })
                                <div class="{{ $field === 'description' ? 'sm:col-span-2' : '' }}">
                                    @php($displayFieldLabel = $fieldLabels[$field] ?? ucfirst(str_replace('_', ' ', $field)))
                                    @if(in_array($selectedPage, ['cabor_detail', 'agenda_detail', 'berita_detail', 'dokumentasi_detail'], true) && $sectionKey === 'hero' && in_array($field, ['title', 'description'], true))
                                        @php($displayFieldLabel .= ' pengganti (opsional, kosong = gunakan data asli)')
                                    @endif
                                    <label for="{{ $sectionKey }}-{{ $field }}" class="mb-1.5 block text-xs font-bold text-gray-700">{{ $displayFieldLabel }}</label>
                                    @if(in_array($field, ['button_target', 'secondary_button_target'], true))
                                        <select id="{{ $sectionKey }}-{{ $field }}" name="sections[{{ $sectionKey }}][{{ $field }}]" class="w-full rounded-lg border-gray-300 text-sm">
                                            @foreach($buttonTargets as $target => $targetLabel)
                                                <option value="{{ $target }}" @selected($fieldValue === $target)>{{ $targetLabel }}</option>
                                            @endforeach
                                        </select>
                                    @elseif(in_array($field, ['description', 'card_footer_text', 'closing_text'], true))
                                        <textarea id="{{ $sectionKey }}-{{ $field }}" name="sections[{{ $sectionKey }}][{{ $field }}]" rows="2" maxlength="3000" class="w-full rounded-lg border-gray-300 text-sm" placeholder="Tulis isi untuk bagian ini">{{ $fieldValue }}</textarea>
                                    @else
                                        <input id="{{ $sectionKey }}-{{ $field }}" type="text" name="sections[{{ $sectionKey }}][{{ $field }}]" value="{{ $fieldValue }}" maxlength="{{ $fieldMaxLength }}" class="w-full rounded-lg border-gray-300 text-sm" placeholder="Tulis {{ strtolower($fieldLabels[$field] ?? $field) }}">
                                    @endif
                                    @error('sections.'.$sectionKey.'.'.$field)<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                </div>
                            @endforeach
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <div class="flex flex-col gap-3 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">
            <p class="max-w-2xl text-xs leading-5 text-gray-500">Simpan Draft hanya menyimpan perubahan halaman ini di panel Admin. Website publik tetap memakai versi yang sudah dipublikasikan sampai Anda menekan tombol Publikasikan.</p>
            <button type="submit" class="rounded-xl bg-[#D71920] px-6 py-3 text-sm font-black text-white hover:bg-red-700">Simpan Draft {{ $pageLabel }}</button>
        </div>
    </form>

    <section class="flex flex-col gap-4 rounded-2xl border border-green-200 bg-green-50 p-5 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h3 class="font-black text-green-950">Publikasikan website</h3>
            <p class="mt-1 max-w-2xl text-xs leading-5 text-green-900">Publikasi menerapkan semua draft yang sudah disimpan ke website publik. Periksa preview halaman sebelum melanjutkan.</p>
        </div>
        <form method="POST" action="{{ route('admin.website-appearance.publish') }}" onsubmit="return confirm('Publikasikan seluruh draft website ke pengunjung?');">
            @csrf
            <input type="hidden" name="page_key" value="{{ $selectedPage }}">
            <button class="rounded-xl bg-green-700 px-5 py-3 text-sm font-bold text-white hover:bg-green-800">Publikasikan Semua Draft</button>
        </form>
    </section>
</div>

<script>
    const opacitySlider = document.getElementById('overlay_opacity');
    const opacityValue = document.getElementById('opacity-value');
    opacitySlider?.addEventListener('input', () => {
        if (opacityValue) opacityValue.textContent = `${opacitySlider.value}%`;
    });

    const sectionList = document.getElementById('website-sections');
    const syncSectionOrder = () => {
        if (!sectionList) return;
        [...sectionList.querySelectorAll('[data-section-card]')].forEach((card, index) => {
            const order = card.querySelector('[data-section-order]');
            if (order) order.value = String((index + 1) * 10);
            card.style.order = String((index + 1) * 10);
        });
    };

    sectionList?.addEventListener('click', (event) => {
        const card = event.target.closest('[data-section-card]');
        if (!card) return;
        const toggle = event.target.closest('[data-section-toggle]');
        if (toggle) {
            const fields = card.querySelector('[data-section-fields]');
            if (fields) {
                fields.hidden = !fields.hidden;
                toggle.setAttribute('aria-expanded', fields.hidden ? 'false' : 'true');
            }
            return;
        }
        if (event.target.closest('[data-move-up]') && card.previousElementSibling) {
            sectionList.insertBefore(card, card.previousElementSibling);
            syncSectionOrder();
        }
        if (event.target.closest('[data-move-down]') && card.nextElementSibling) {
            sectionList.insertBefore(card.nextElementSibling, card);
            syncSectionOrder();
        }
    });
    syncSectionOrder();
</script>
@endsection
