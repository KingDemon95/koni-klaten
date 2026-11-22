<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteAppearanceSetting extends Model
{
    protected $fillable = [
        'draft_config',
        'published_config',
        'published_at',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'draft_config' => 'array',
            'published_config' => 'array',
            'published_at' => 'datetime',
        ];
    }

    public static function pageDefinitions(): array
    {
        return [
            'home' => 'Beranda',
            'cabor' => 'Daftar Cabang Olahraga',
            'cabor_detail' => 'Detail Cabang Olahraga',
            'struktur' => 'Struktur Organisasi',
            'agenda' => 'Daftar Agenda dan Kejuaraan',
            'agenda_detail' => 'Detail Agenda',
            'statistik' => 'Statistik Prestasi',
            'berita' => 'Daftar Berita',
            'berita_detail' => 'Detail Berita',
            'dokumentasi' => 'Daftar Dokumentasi',
            'dokumentasi_detail' => 'Detail Dokumentasi',
        ];
    }

    /**
     * Editable copy for each public page section. Dynamic records such as
     * athlete names, event titles, and article bodies remain database-driven.
     */
    public static function sectionDefinitions(?string $pageKey = null): array
    {
        $definitions = [
            'home' => [
                'hero' => ['label' => 'Banner utama', 'defaults' => ['eyebrow' => 'KOMITE OLAHRAGA NASIONAL INDONESIA', 'title' => 'Bersama membangun prestasi olahraga Kabupaten Klaten.', 'description' => 'Satu portal informasi untuk mengenal cabang olahraga, atlet, pelatih, dan kepengurusan KONI Kabupaten Klaten.', 'button_label' => 'Jelajahi Cabang Olahraga', 'button_target' => 'cabor', 'secondary_button_label' => 'Tentang KONI', 'secondary_button_target' => 'about', 'card_eyebrow' => 'PORTAL OLAHRAGA', 'card_title' => 'KONI Klaten', 'card_footer_eyebrow' => 'SATU WADAH INFORMASI', 'card_footer_text' => 'Kenali potensi dan ekosistem olahraga Klaten.', 'card_cabor_label' => 'Cabang olahraga', 'card_athlete_label' => 'Atlet aktif', 'card_coach_label' => 'Pelatih aktif', 'card_management_label' => 'Pengurus KONI']],
                'stats' => ['label' => 'Statistik ringkas', 'defaults' => ['eyebrow' => '', 'title' => '', 'description' => '', 'cabor_label' => 'Cabang olahraga', 'athlete_label' => 'Atlet aktif', 'coach_label' => 'Pelatih aktif', 'management_label' => 'Pengurus KONI', 'achievement_label' => 'Prestasi terverifikasi', 'medal_label' => 'Medali terdata']],
                'about' => ['label' => 'Tentang organisasi', 'defaults' => ['eyebrow' => 'TENTANG ORGANISASI', 'title' => 'Mengenal organisasi KONI', 'description' => '', 'history_label' => 'Sejarah', 'vision_label' => 'Visi', 'mission_label' => 'Misi', 'closing_text' => 'Melalui portal ini, masyarakat dapat mengenal cabang olahraga, melihat informasi atlet dan pelatih yang terdaftar, serta mengetahui susunan pengurus organisasi.', 'button_label' => 'Lihat struktur pengurus', 'button_target' => 'struktur']],
                'cabor_preview' => ['label' => 'Pratinjau cabang olahraga', 'defaults' => ['eyebrow' => 'EKOSISTEM OLAHRAGA', 'title' => 'Cabang olahraga', 'description' => 'Jelajahi cabang olahraga yang terdaftar di KONI Kabupaten Klaten.', 'button_label' => 'Lihat semua cabor', 'button_target' => 'cabor']],
                'management_preview' => ['label' => 'Pratinjau kepengurusan', 'defaults' => ['eyebrow' => 'KEPENGURUSAN', 'title' => 'Pengurus KONI Klaten', 'description' => 'Susunan pengurus tingkat kabupaten yang tercatat dalam sistem.', 'button_label' => 'Lihat struktur pengurus', 'button_target' => 'struktur']],
                'achievements_preview' => ['label' => 'Pratinjau prestasi', 'defaults' => ['eyebrow' => 'CAPAIAN OLAHRAGA', 'title' => 'Prestasi terverifikasi', 'description' => 'Capaian atlet dan pelatih yang telah diverifikasi oleh Admin Cabor.', 'button_label' => 'Lihat statistik lengkap', 'button_target' => 'statistik']],
                'agenda_preview' => ['label' => 'Pratinjau agenda', 'defaults' => ['eyebrow' => 'KALENDER OLAHRAGA', 'title' => 'Agenda & kejuaraan mendatang', 'description' => 'Informasi kegiatan dan kejuaraan yang telah dipublikasikan oleh pengelola KONI Klaten.', 'button_label' => 'Lihat semua agenda', 'button_target' => 'agenda']],
                'news_preview' => ['label' => 'Pratinjau berita', 'defaults' => ['eyebrow' => 'KABAR TERBARU', 'title' => 'Berita KONI Klaten', 'description' => 'Informasi dan perkembangan olahraga Kabupaten Klaten.', 'button_label' => 'Semua berita', 'button_target' => 'berita']],
                'documentation_preview' => ['label' => 'Pratinjau dokumentasi', 'defaults' => ['eyebrow' => 'GALERI KEGIATAN', 'title' => 'Dokumentasi olahraga', 'description' => 'Foto dan video kegiatan KONI Kabupaten Klaten.', 'button_label' => 'Lihat semua dokumentasi', 'button_target' => 'dokumentasi']],
                'member_cta' => ['label' => 'Ajakan portal anggota', 'defaults' => ['eyebrow' => 'PORTAL ANGGOTA', 'title' => 'Atlet, pelatih, dan pengelola cabor', 'description' => 'Masuk ke portal anggota untuk mengakses layanan sesuai hak akses akun.', 'button_label' => 'Masuk Portal Anggota', 'button_target' => 'login']],
            ],
            'cabor' => [
                'hero' => ['label' => 'Banner daftar cabor', 'defaults' => ['eyebrow' => 'DIREKTORI OLAHRAGA', 'title' => 'Cabang Olahraga', 'description' => 'Temukan cabang olahraga, informasi atlet, pelatih, dan struktur pengurus pada masing-masing cabang.']],
                'results' => ['label' => 'Daftar cabang olahraga', 'defaults' => ['eyebrow' => 'DIREKTORI KONI', 'title' => 'Daftar cabang olahraga', 'description' => 'Pilih cabang olahraga untuk melihat informasi atlet, pelatih, prestasi, dan kepengurusannya.', 'search_placeholder' => 'Cari nama atau singkatan cabang olahraga...', 'button_label' => 'Cari Cabor', 'reset_label' => 'Reset', 'count_label' => 'cabang olahraga', 'detail_link_label' => 'Lihat detail cabang olahraga']],
            ],
            'cabor_detail' => [
                'hero' => ['label' => 'Banner detail cabor', 'defaults' => ['eyebrow' => 'PROFIL CABANG OLAHRAGA', 'title' => '', 'description' => '', 'button_label' => 'Semua Cabang Olahraga', 'button_target' => 'cabor']],
                'athletes' => ['label' => 'Bagian atlet', 'defaults' => ['eyebrow' => 'SUMBER DAYA ATLET', 'title' => 'Atlet aktif', 'description' => '']],
                'achievements' => ['label' => 'Bagian prestasi', 'defaults' => ['eyebrow' => 'CAPAIAN OLAHRAGA', 'title' => 'Prestasi atlet dan pelatih terverifikasi', 'description' => '']],
                'coaches' => ['label' => 'Bagian pelatih', 'defaults' => ['eyebrow' => 'TENAGA PEMBINAAN', 'title' => 'Pelatih aktif', 'description' => '']],
                'management' => ['label' => 'Bagian pengurus cabor', 'defaults' => ['eyebrow' => 'ORGANISASI CABANG', 'title' => 'Struktur pengurus cabor', 'description' => '']],
            ],
            'struktur' => [
                'hero' => ['label' => 'Banner struktur organisasi', 'defaults' => ['eyebrow' => 'ORGANISASI', 'title' => 'Struktur Pengurus KONI Klaten', 'description' => 'Informasi susunan pengurus tingkat Kabupaten Klaten yang tercatat dan berstatus aktif.']],
                'directory' => ['label' => 'Daftar pengurus', 'defaults' => ['eyebrow' => 'KEPENGURUSAN KONI', 'title' => 'Daftar pengurus aktif', 'description' => 'Susunan pengurus yang tercatat dan berstatus aktif.']],
            ],
            'agenda' => [
                'hero' => ['label' => 'Banner agenda', 'defaults' => ['eyebrow' => 'INFORMASI OLAHRAGA', 'title' => 'Agenda & Kejuaraan', 'description' => 'Ikuti kegiatan, agenda organisasi, dan kejuaraan olahraga KONI Kabupaten Klaten.']],
                'filters' => ['label' => 'Pencarian agenda', 'defaults' => ['eyebrow' => '', 'title' => 'Temukan agenda', 'description' => '', 'search_placeholder' => 'Cari agenda, lokasi, atau cabor...', 'button_label' => 'Cari agenda']],
                'listing' => ['label' => 'Daftar agenda', 'defaults' => ['eyebrow' => 'KALENDER OLAHRAGA', 'title' => 'Agenda dan kejuaraan', 'description' => 'Jadwal kegiatan dan kejuaraan yang telah dipublikasikan oleh KONI Kabupaten Klaten.', 'detail_link_label' => 'Selengkapnya']],
            ],
            'agenda_detail' => [
                'hero' => ['label' => 'Banner detail agenda', 'defaults' => ['eyebrow' => 'AGENDA DAN KEJUARAAN', 'title' => '', 'description' => '', 'button_label' => 'Kembali ke Agenda', 'button_target' => 'agenda']],
                'details' => ['label' => 'Informasi agenda', 'defaults' => ['eyebrow' => 'INFORMASI KEGIATAN', 'title' => 'Informasi kegiatan', 'description' => '']],
            ],
            'statistik' => [
                'hero' => ['label' => 'Banner statistik', 'defaults' => ['eyebrow' => 'DATA DAN CAPAIAN OLAHRAGA', 'title' => 'Statistik Prestasi KONI Klaten', 'description' => 'Ringkasan data cabang olahraga, atlet, pelatih, dan prestasi terverifikasi.', 'button_label' => 'Terapkan filter tahun', 'filter_label' => 'Filter tahun kejuaraan', 'reset_label' => 'Reset', 'selected_year_prefix' => 'Menampilkan data tahun', 'all_years_text' => 'Menampilkan seluruh tahun yang tercatat.']],
                'overview' => ['label' => 'Ringkasan statistik', 'defaults' => ['eyebrow' => 'RINGKASAN', 'title' => 'Gambaran olahraga Klaten', 'description' => '', 'cabor_label' => 'Cabang olahraga aktif', 'athlete_label' => 'Atlet aktif', 'coach_label' => 'Pelatih aktif', 'achievement_label' => 'Prestasi terverifikasi']],
                'medals' => ['label' => 'Perolehan medali', 'defaults' => ['eyebrow' => 'PEROLEHAN MEDALI', 'title' => 'Medali yang tercatat', 'description' => 'Medali dihitung berdasarkan jenis medali yang dipilih saat prestasi diunggah. Prestasi lama tanpa klasifikasi medali tetap masuk total prestasi, tetapi tidak ditambahkan ke hitungan medali.', 'gold_label' => 'Emas', 'silver_label' => 'Perak', 'bronze_label' => 'Perunggu']],
                'trends' => ['label' => 'Tren prestasi', 'defaults' => ['eyebrow' => 'TREN PRESTASI', 'title' => 'Perkembangan lima tahun', 'description' => 'Jumlah prestasi terverifikasi per tahun, dari cabang olahraga aktif. Grafik ini tetap menampilkan lima tahun terakhir meskipun filter tahun dipilih.']],
                'levels' => ['label' => 'Tingkat kejuaraan', 'defaults' => ['eyebrow' => 'TINGKAT KEJUARAAN', 'title' => 'Sebaran prestasi', 'description' => '']],
                'ranking' => ['label' => 'Peringkat cabor', 'defaults' => ['eyebrow' => 'PERINGKAT CABANG OLAHRAGA', 'title' => 'Capaian berdasarkan cabor', 'description' => 'Diurutkan berdasarkan jumlah prestasi terverifikasi, kemudian perolehan medali.', 'achievement_label' => 'prestasi']],
                'recent' => ['label' => 'Prestasi terbaru', 'defaults' => ['eyebrow' => 'CATATAN PRESTASI', 'title' => 'Prestasi terbaru', 'description' => '']],
            ],
            'berita' => [
                'hero' => ['label' => 'Banner berita', 'defaults' => ['eyebrow' => 'KABAR OLAHRAGA KLATEN', 'title' => 'Berita KONI Klaten', 'description' => 'Informasi, kabar kegiatan, dan perkembangan olahraga Kabupaten Klaten.']],
                'filters' => ['label' => 'Pencarian berita', 'defaults' => ['eyebrow' => '', 'title' => 'Temukan berita', 'description' => '', 'search_placeholder' => 'Cari berita...', 'button_label' => 'Cari berita']],
                'listing' => ['label' => 'Daftar berita', 'defaults' => ['eyebrow' => 'PUBLIKASI KONI', 'title' => 'Berita terbaru', 'description' => 'Informasi dan perkembangan olahraga Kabupaten Klaten.', 'detail_link_label' => 'Baca selengkapnya']],
            ],
            'berita_detail' => [
                'hero' => ['label' => 'Banner detail berita', 'defaults' => ['eyebrow' => 'BERITA KONI', 'title' => '', 'description' => '', 'button_label' => 'Kembali ke Berita', 'button_target' => 'berita']],
                'article' => ['label' => 'Isi berita', 'defaults' => ['eyebrow' => 'PUBLIKASI', 'title' => 'Informasi berita', 'description' => '']],
            ],
            'dokumentasi' => [
                'hero' => ['label' => 'Banner dokumentasi', 'defaults' => ['eyebrow' => 'GALERI KEGIATAN', 'title' => 'Dokumentasi', 'description' => 'Foto dan video kegiatan olahraga, kejuaraan, serta aktivitas KONI Kabupaten Klaten.']],
                'filters' => ['label' => 'Pencarian dokumentasi', 'defaults' => ['eyebrow' => '', 'title' => 'Temukan dokumentasi', 'description' => '', 'search_placeholder' => 'Cari dokumentasi...', 'button_label' => 'Cari dokumentasi']],
                'gallery' => ['label' => 'Galeri dokumentasi', 'defaults' => ['eyebrow' => 'ARSIP KEGIATAN', 'title' => 'Galeri dokumentasi', 'description' => 'Dokumentasi foto dan video kegiatan KONI Kabupaten Klaten.']],
            ],
            'dokumentasi_detail' => [
                'hero' => ['label' => 'Banner detail dokumentasi', 'defaults' => ['eyebrow' => 'DOKUMENTASI KONI', 'title' => '', 'description' => '', 'button_label' => 'Kembali ke Dokumentasi', 'button_target' => 'dokumentasi']],
                'media' => ['label' => 'Media dokumentasi', 'defaults' => ['eyebrow' => 'ARSIP KEGIATAN', 'title' => 'Dokumentasi kegiatan', 'description' => '']],
            ],
        ];

        return $pageKey === null ? $definitions : ($definitions[$pageKey] ?? []);
    }

    public static function buttonTargetOptions(): array
    {
        return [
            'home' => 'Beranda',
            'about' => 'Bagian Tentang KONI',
            'cabor' => 'Cabang Olahraga',
            'struktur' => 'Struktur Pengurus',
            'agenda' => 'Agenda dan Kejuaraan',
            'statistik' => 'Statistik Prestasi',
            'berita' => 'Berita',
            'dokumentasi' => 'Dokumentasi',
            'login' => 'Login Portal Anggota',
        ];
    }

    public static function buttonUrl(?string $target): string
    {
        return match ($target) {
            'about' => route('public.home').'#tentang',
            'cabor' => route('public.cabor.index'),
            'struktur' => route('public.struktur.index'),
            'agenda' => route('public.agenda.index'),
            'statistik' => route('public.statistik.index'),
            'berita' => route('public.berita.index'),
            'dokumentasi' => route('public.dokumentasi.index'),
            'login' => \Illuminate\Support\Facades\Route::has('login') ? route('login') : route('public.home'),
            default => route('public.home'),
        };
    }

    public static function defaultConfig(): array
    {
        $pages = [];

        foreach (array_keys(static::pageDefinitions()) as $pageKey) {
            $sections = [];
            foreach (static::sectionDefinitions($pageKey) as $index => $definition) {
                $sections[$index] = array_merge(
                    $definition['defaults'],
                    ['visible' => true, 'order' => (count($sections) + 1) * 10],
                );
            }

            $pages[$pageKey] = [
                'backdrop_path' => null,
                'overlay_opacity' => 62,
                'background_position' => 'center',
                'hero_background_color' => '#17191F',
                'title_color' => '#FFFFFF',
                'description_color' => '#CBD5E1',
                'eyebrow_color' => '#FCA5A5',
                'accent_color' => '#D71920',
                'button_background' => '#D71920',
                'button_text_color' => '#FFFFFF',
                'sections' => $sections,
            ];
        }

        return [
            'primary_color' => '#D71920',
            'secondary_color' => '#17191F',
            'header_background' => '#FFFFFF',
            'footer_background' => '#17191F',
            'font_family' => 'figtree',
            'global_content' => [
                'nav_home' => 'Beranda',
                'nav_profile' => 'Profil KONI',
                'nav_cabor' => 'Cabang Olahraga',
                'nav_agenda' => 'Agenda & Kejuaraan',
                'nav_news' => 'Berita',
                'nav_documentation' => 'Dokumentasi',
                'nav_statistics' => 'Statistik Prestasi',
                'nav_structure' => 'Struktur Pengurus',
                'portal_button' => 'Portal Anggota',
                'footer_heading' => 'Jelajahi',
                'footer_description' => '',
                'footer_portal' => 'Login Portal Anggota',
                'footer_bottom' => 'Portal Informasi dan Manajemen Olahraga',
            ],
            'pages' => $pages,
        ];
    }

    public static function current(): self
    {
        $defaults = static::defaultConfig();

        return static::query()->firstOrCreate(
            ['id' => 1],
            [
                'draft_config' => $defaults,
                'published_config' => $defaults,
            ],
        );
    }

    public static function pageKeyForRoute(?string $routeName): string
    {
        return match ($routeName) {
            'public.home' => 'home',
            'public.cabor.index' => 'cabor',
            'public.cabor.show' => 'cabor_detail',
            'public.struktur.index' => 'struktur',
            'public.agenda.index' => 'agenda',
            'public.agenda.show' => 'agenda_detail',
            'public.statistik.index' => 'statistik',
            'public.berita.index' => 'berita',
            'public.berita.show' => 'berita_detail',
            'public.dokumentasi.index' => 'dokumentasi',
            'public.dokumentasi.show' => 'dokumentasi_detail',
            default => 'home',
        };
    }
}
