<?php

namespace Tests\Feature;

use App\Models\DashboardSetting;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DashboardAppearanceTest extends TestCase
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

    public function test_admin_can_upload_dashboard_backdrop_and_change_display_settings(): void
    {
        $admin = $this->userWithRole('admin_koni');

        $this->actingAs($admin)->put(route('admin.dashboard-appearance.update'), [
            'backdrop' => UploadedFile::fake()->image('dashboard.jpg', 1200, 600),
            'overlay_opacity' => 65,
            'background_position' => 'top',
        ])->assertRedirect(route('admin.dashboard-appearance.edit'));

        $settings = DashboardSetting::query()->firstOrFail();
        $this->assertNotNull($settings->backdrop_path);
        $this->assertSame(65, $settings->overlay_opacity);
        $this->assertSame('top', $settings->background_position);
        Storage::disk('public')->assertExists($settings->backdrop_path);

        $this->actingAs($admin)->get(route('dashboard.admin-koni'))
            ->assertOk()
            ->assertSee('dashboard-backdrops/', false);
    }

    public function test_admin_can_remove_dashboard_backdrop(): void
    {
        $admin = $this->userWithRole('admin_koni');
        $settings = DashboardSetting::current();
        $path = UploadedFile::fake()->image('old.jpg')->store('dashboard-backdrops', 'public');
        $settings->update(['backdrop_path' => $path]);

        $this->actingAs($admin)->put(route('admin.dashboard-appearance.update'), [
            'overlay_opacity' => 55,
            'background_position' => 'center',
            'remove_backdrop' => 1,
        ])->assertRedirect(route('admin.dashboard-appearance.edit'));

        $this->assertNull($settings->fresh()->backdrop_path);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_non_admin_cannot_change_dashboard_backdrop(): void
    {
        $staff = $this->userWithRole('staff_koni');

        $this->actingAs($staff)->get(route('admin.dashboard-appearance.edit'))->assertForbidden();
    }
}
