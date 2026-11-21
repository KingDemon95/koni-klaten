<?php

namespace Tests\Feature;

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

    private function appearancePayload(): array
    {
        return [
            'primary_color' => '#A01224',
            'secondary_color' => '#20242B',
            'header_background' => '#FFFFFF',
            'footer_background' => '#17191F',
            'font_family' => 'figtree',
        ];
    }

    public function test_admin_can_save_separate_public_page_backdrops_as_draft_and_preview_them(): void
    {
        $admin = $this->userWithRole('admin_koni');

        $this->actingAs($admin)->put(route('admin.website-appearance.update'), [
            ...$this->appearancePayload(),
            'backdrops' => [
                'home' => UploadedFile::fake()->image('home.jpg', 1200, 600),
                'cabor' => UploadedFile::fake()->image('cabor.jpg', 1200, 600),
            ],
            'overlay_opacity' => ['home' => 70, 'cabor' => 55],
            'background_position' => ['home' => 'top', 'cabor' => 'bottom'],
        ])->assertRedirect(route('admin.website-appearance.edit'));

        $setting = WebsiteAppearanceSetting::query()->firstOrFail();
        $homePath = $setting->draft_config['pages']['home']['backdrop_path'];
        $caborPath = $setting->draft_config['pages']['cabor']['backdrop_path'];

        $this->assertNotNull($homePath);
        $this->assertNotNull($caborPath);
        $this->assertNotSame($homePath, $caborPath);
        $this->assertNull($setting->published_config['pages']['home']['backdrop_path']);
        Storage::disk('public')->assertExists($homePath);
        Storage::disk('public')->assertExists($caborPath);

        $this->get(route('public.home'))->assertOk()->assertDontSee($homePath);
        $this->get(route('public.cabor.index'))->assertOk()->assertDontSee($caborPath);

        $this->actingAs($admin)->get(route('admin.website-appearance.preview', 'home'))
            ->assertRedirect(route('public.home').'?appearance_preview=1');

        $this->actingAs($admin)->get(route('public.home').'?appearance_preview=1')
            ->assertOk()
            ->assertSee($homePath)
            ->assertSee('MODE PREVIEW DRAFT');

        $this->actingAs($admin)->get(route('public.cabor.index').'?appearance_preview=1')
            ->assertOk()
            ->assertSee($caborPath);
    }

    public function test_publishing_applies_public_page_backdrops_without_changing_internal_dashboard_backdrop(): void
    {
        $admin = $this->userWithRole('admin_koni');
        $internalSettings = DashboardSetting::current();
        $internalPath = UploadedFile::fake()->image('internal.jpg')->store('dashboard-backdrops', 'public');
        $internalSettings->update(['backdrop_path' => $internalPath]);

        $this->actingAs($admin)->put(route('admin.website-appearance.update'), [
            ...$this->appearancePayload(),
            'backdrops' => [
                'home' => UploadedFile::fake()->image('public-home.jpg', 1200, 600),
            ],
            'overlay_opacity' => ['home' => 64],
            'background_position' => ['home' => 'center'],
        ])->assertRedirect(route('admin.website-appearance.edit'));

        $this->actingAs($admin)->post(route('admin.website-appearance.publish'))
            ->assertRedirect(route('admin.website-appearance.edit'));

        $setting = WebsiteAppearanceSetting::query()->firstOrFail();
        $publishedPath = $setting->published_config['pages']['home']['backdrop_path'];

        $this->assertSame($publishedPath, $setting->draft_config['pages']['home']['backdrop_path']);
        $this->assertNotNull($setting->published_at);
        $this->get(route('public.home'))->assertOk()->assertSee($publishedPath);

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
