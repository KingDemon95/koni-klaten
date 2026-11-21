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
            'cabor' => 'Cabang Olahraga',
            'struktur' => 'Struktur Organisasi',
            'agenda' => 'Agenda dan Kejuaraan',
            'statistik' => 'Statistik Prestasi',
            'berita' => 'Berita',
            'dokumentasi' => 'Dokumentasi',
        ];
    }

    public static function defaultConfig(): array
    {
        $pages = [];

        foreach (array_keys(static::pageDefinitions()) as $pageKey) {
            $pages[$pageKey] = [
                'backdrop_path' => null,
                'overlay_opacity' => 62,
                'background_position' => 'center',
            ];
        }

        return [
            'primary_color' => '#D71920',
            'secondary_color' => '#17191F',
            'header_background' => '#FFFFFF',
            'footer_background' => '#17191F',
            'font_family' => 'figtree',
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
        return match (true) {
            $routeName === 'public.home' => 'home',
            str_starts_with((string) $routeName, 'public.cabor.') => 'cabor',
            str_starts_with((string) $routeName, 'public.struktur.') => 'struktur',
            str_starts_with((string) $routeName, 'public.agenda.') => 'agenda',
            str_starts_with((string) $routeName, 'public.statistik.') => 'statistik',
            str_starts_with((string) $routeName, 'public.berita.') => 'berita',
            str_starts_with((string) $routeName, 'public.dokumentasi.') => 'dokumentasi',
            default => 'home',
        };
    }
}
