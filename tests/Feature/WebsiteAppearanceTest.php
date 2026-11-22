<?php

namespace Tests\Feature;

use App\Models\CabangOlahraga;
use App\Models\DashboardSetting;
use App\Models\User;
use App\Models\WebsiteAppearanceSetting;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WebsiteAppearanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        Storage::fake('public');
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole($role);

        return $user;
    }

    private function appearancePayload(string $pageKey = 'home'): array
    {
        $defaults = WebsiteAppearanceSetting::defaultConfig();
        $page = $defaults['pages'][$pageKey];

        return [
            'page_key' => $pageKey,
            'primary_color' => '#A01224',
            'secondary_color' => '#20242B',
            'header_background' => '#FFFFFF',
            'footer_background' => '#17191F',
            'font_family' => 'figtree',
            'global_content' => $defaults['global_content'],
            'overlay_opacity' => $page['overlay_opacity'],
            'background_position' => $page['background_position'],
            'hero_background_color' => $page['hero_background_color'],
            'title_color' => $page['title_color'],
            'description_color' => $page['description_color'],
            'eyebrow_color' => $page['eyebrow_color'],
            'section_background_color' => $page['section_background_color'],
            'section_heading_color' => $page['section_heading_color'],
            'section_text_color' => $page['section_text_color'],
            'accent_color' => $page['accent_color'],
            'button_background' => $page['button_background'],
            'button_text_color' => $page['button_text_color'],
            'sections' => $page['sections'],
        ];
    }

    public function test_admin_can_save_separate_public_page_backdrops_as_draft_and_preview_them(): void
    {
        $admin = $this->userWithRole('admin_koni');
        $payload = $this->appearancePayload('home');
        $payload['backdrop'] = UploadedFile::fake()->image('home.jpg', 1200, 600);
        $payload['overlay_opacity'] = 70;
        $payload['background_position'] = 'top';
        $payload['sections']['hero']['title'] = 'KONI Klaten Maju Bersama';
        $payload['global_content']['nav_home'] = 'Halaman Utama';
        $payload['sections']['cabor_preview']['visible'] = false;

        $this->actingAs($admin)->put(route('admin.website-appearance.update'), $payload)
            ->assertRedirect(route('admin.website-appearance.edit', ['page' => 'home']));

        $setting = WebsiteAppearanceSetting::query()->firstOrFail();
        $homePath = $setting->draft_config['pages']['home']['backdrop_path'];

        $this->assertNotNull($homePath);
        $this->assertNull($setting->published_config['pages']['home']['backdrop_path']);
        $this->assertSame('KONI Klaten Maju Bersama', $setting->draft_config['pages']['home']['sections']['hero']['title']);
        $this->assertFalse($setting->draft_config['pages']['home']['sections']['cabor_preview']['visible']);
        Storage::disk('public')->assertExists($homePath);

        $this->get(route('public.home'))->assertOk()->assertDontSee($homePath)->assertDontSee('KONI Klaten Maju Bersama')->assertSee('Beranda')->assertDontSee('Halaman Utama');

        $this->actingAs($admin)->get(route('admin.website-appearance.preview', 'home'))
            ->assertRedirect(route('public.home').'?appearance_preview=1');

        $this->actingAs($admin)->get(route('public.home').'?appearance_preview=1')
            ->assertOk()
            ->assertSee($homePath)
            ->assertSee('KONI Klaten Maju Bersama')
            ->assertSee('Halaman Utama')
            ->assertDontSee('Cabang olahraga yang terdaftar di KONI Kabupaten Klaten.');
    }

    public function test_admin_can_edit_content_colors_and_visibility_independently_for_each_public_page(): void
    {
        $admin = $this->userWithRole('admin_koni');
        $homePayload = $this->appearancePayload('home');
        $homePayload['sections']['hero']['title'] = 'Judul khusus beranda';
        $homePayload['global_content']['nav_home'] = 'Halaman Utama';

        $this->actingAs($admin)->put(route('admin.website-appearance.update'), $homePayload)->assertRedirect();

        $caborPayload = $this->appearancePayload('cabor');
        $caborPayload['global_content']['nav_home'] = 'Halaman Utama';
        $caborPayload['hero_background_color'] = '#123456';
        $caborPayload['title_color'] = '#F0F0F0';
        $caborPayload['accent_color'] = '#008866';
        $caborPayload['sections']['hero']['title'] = 'Direktori Olahraga Klaten';
        $caborPayload['sections']['results']['visible'] = false;

        $this->actingAs($admin)->put(route('admin.website-appearance.update'), $caborPayload)->assertRedirect();

        $setting = WebsiteAppearanceSetting::query()->firstOrFail();
        $this->assertSame('Judul khusus beranda', $setting->draft_config['pages']['home']['sections']['hero']['title']);
        $this->assertSame('Halaman Utama', $setting->draft_config['global_content']['nav_home']);
        $this->assertSame('Direktori Olahraga Klaten', $setting->draft_config['pages']['cabor']['sections']['hero']['title']);
        $this->assertSame('#123456', $setting->draft_config['pages']['cabor']['hero_background_color']);
        $this->assertTrue($setting->draft_config['pages']['home']['sections']['cabor_preview']['visible']);
        $this->assertFalse($setting->draft_config['pages']['cabor']['sections']['results']['visible']);

        $this->actingAs($admin)->get(route('public.cabor.index').'?appearance_preview=1')
            ->assertOk()
            ->assertSee('Direktori Olahraga Klaten')
            ->assertSee('#123456');
    }

    public function test_detail_page_preview_uses_a_real_public_record_when_available(): void
    {
        $admin = $this->userWithRole('admin_koni');
        $cabor = CabangOlahraga::query()->create([
            'nama' => 'Atletik',
            'singkatan' => 'ATL',
            'deskripsi' => 'Cabang olahraga atletik.',
            'status' => 'aktif',
        ]);

        $this->actingAs($admin)->get(route('admin.website-appearance.preview', 'cabor_detail'))
            ->assertRedirect(route('public.cabor.show', $cabor).'?appearance_preview=1');
    }

    public function test_publishing_applies_public_page_backdrops_without_changing_internal_dashboard_backdrop(): void
    {
        $admin = $this->userWithRole('admin_koni');
        $internalSettings = DashboardSetting::current();
        $internalPath = UploadedFile::fake()->image('internal.jpg')->store('dashboard-backdrops', 'public');
        $internalSettings->update(['backdrop_path' => $internalPath]);

        $payload = $this->appearancePayload('home');
        $payload['backdrop'] = UploadedFile::fake()->image('public-home.jpg', 1200, 600);
        $payload['sections']['hero']['title'] = 'Website KONI Terpublikasi';
        $this->actingAs($admin)->put(route('admin.website-appearance.update'), $payload)->assertRedirect();

        $this->actingAs($admin)->post(route('admin.website-appearance.publish'), ['page_key' => 'home'])
            ->assertRedirect(route('admin.website-appearance.edit', ['page' => 'home']));

        $setting = WebsiteAppearanceSetting::query()->firstOrFail();
        $publishedPath = $setting->published_config['pages']['home']['backdrop_path'];

        $this->assertSame($publishedPath, $setting->draft_config['pages']['home']['backdrop_path']);
        $this->assertNotNull($setting->published_at);
        $this->get(route('public.home'))->assertOk()->assertSee($publishedPath)->assertSee('Website KONI Terpublikasi');

        $this->actingAs($admin)->get(route('dashboard.admin-koni'))
            ->assertOk()
            ->assertSee($internalPath)
            ->assertDontSee($publishedPath);
    }

    public function test_non_admin_cannot_edit_publish_or_preview_public_website_appearance(): void
    {
        $staff = $this->userWithRole('staff_koni');

        $this->actingAs($staff)->get(route('admin.website-appearance.edit'))->assertForbidden();
        $this->actingAs($staff)->put(route('admin.website-appearance.update'), $this->appearancePayload())->assertForbidden();
        $this->actingAs($staff)->post(route('admin.website-appearance.publish'))->assertForbidden();
        $this->actingAs($staff)->get(route('admin.website-appearance.preview', 'home'))->assertForbidden();
    }
}
