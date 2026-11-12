<?php

namespace Tests\Feature;

use App\Models\Agenda;
use App\Models\CabangOlahraga;
use App\Models\Publikasi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_contains_public_pages_and_published_content_only(): void
    {
        $activeCabor = CabangOlahraga::create([
            'nama' => 'Bulutangkis Sitemap',
            'status' => 'aktif',
        ]);
        $inactiveCabor = CabangOlahraga::create([
            'nama' => 'Cabor Rahasia Sitemap',
            'status' => 'nonaktif',
        ]);

        $publishedAgenda = Agenda::create([
            'cabang_olahraga_id' => $activeCabor->id,
            'judul' => 'Kejuaraan Terbit Sitemap',
            'slug' => 'kejuaraan-terbit-sitemap',
            'jenis' => 'kejuaraan',
            'tanggal_mulai' => now()->addDays(10)->toDateString(),
            'status' => Agenda::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);
        Agenda::create([
            'cabang_olahraga_id' => $activeCabor->id,
            'judul' => 'Agenda Draft Sitemap',
            'slug' => 'agenda-draft-sitemap',
            'jenis' => 'kegiatan',
            'tanggal_mulai' => now()->addDays(15)->toDateString(),
            'status' => Agenda::STATUS_DRAFT,
        ]);

        $publishedNews = Publikasi::create([
            'judul' => 'Berita Terbit Sitemap',
            'slug' => 'berita-terbit-sitemap',
            'jenis' => Publikasi::JENIS_BERITA,
            'konten' => 'Isi berita yang telah dipublikasikan.',
            'status' => Publikasi::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);
        Publikasi::create([
            'judul' => 'Berita Draft Sitemap',
            'slug' => 'berita-draft-sitemap',
            'jenis' => Publikasi::JENIS_BERITA,
            'konten' => 'Isi draft berita.',
            'status' => Publikasi::STATUS_DRAFT,
        ]);

        $this->get(route('public.sitemap'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee(route('public.home'), false)
            ->assertSee(route('public.cabor.show', $activeCabor), false)
            ->assertSee(route('public.agenda.show', $publishedAgenda->slug), false)
            ->assertSee(route('public.berita.show', $publishedNews->slug), false)
            ->assertDontSee(route('public.cabor.show', $inactiveCabor), false)
            ->assertDontSee('agenda-draft-sitemap')
            ->assertDontSee('berita-draft-sitemap');
    }

    public function test_web_responses_include_baseline_security_headers(): void
    {
        $this->get(route('public.home'))
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
    }

    public function test_public_mobile_menu_remains_available_until_desktop_navigation_breakpoint(): void
    {
        $layout = file_get_contents(resource_path('views/layouts/public.blade.php'));

        $this->assertIsString($layout);
        $this->assertStringContainsString('details class="relative xl:hidden"', $layout);
        $this->assertStringContainsString('text-xs font-semibold text-slate-600 xl:flex', $layout);
        $this->assertStringNotContainsString('details class="relative lg:hidden"', $layout);
    }

    public function test_application_uses_indonesia_western_time_by_default(): void
    {
        $this->assertSame('Asia/Jakarta', config('app.timezone'));
    }

    public function test_laravel_health_endpoint_is_available(): void
    {
        $this->get('/up')->assertOk();
    }

    public function test_robots_file_excludes_authenticated_panels_from_crawling(): void
    {
        $robots = file_get_contents(public_path('robots.txt'));

        $this->assertIsString($robots);
        $this->assertStringContainsString('Disallow: /admin/', $robots);
        $this->assertStringContainsString('Disallow: /staff/', $robots);
        $this->assertStringContainsString('Disallow: /cabor/', $robots);
        $this->assertStringContainsString('Disallow: /atlet/', $robots);
        $this->assertStringContainsString('Disallow: /pelatih/', $robots);
    }

    public function test_missing_public_page_uses_the_friendly_not_found_screen(): void
    {
        $this->get('/halaman-yang-tidak-tersedia')
            ->assertNotFound()
            ->assertSee('Halaman tidak ditemukan');
    }
}
