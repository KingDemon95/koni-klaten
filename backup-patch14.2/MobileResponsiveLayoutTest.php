<?php

namespace Tests\Feature;

use App\Models\AttendanceSession;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class MobileResponsiveLayoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_public_header_keeps_mobile_menu_and_portal_link_inside_the_viewport(): void
    {
        $this->get(route('public.home'))
            ->assertOk()
            ->assertSee('public-header-inner', false)
            ->assertSee('public-mobile-menu-panel', false)
            ->assertSee('public-portal-mobile', false)
            ->assertSee('public-portal-desktop', false);
    }

    public function test_attendance_qr_and_admin_topbar_use_mobile_safe_containers(): void
    {
        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole('admin_koni');

        $session = AttendanceSession::query()->create([
            'created_by' => $admin->id,
            'cabang_olahraga_id' => null,
            'title' => 'Uji QR Responsif',
            'description' => 'Pengujian tampilan QR pada perangkat seluler.',
            'target_role' => 'all',
            'token' => Str::random(40),
            'starts_at' => now()->subMinute(),
            'ends_at' => now()->addHour(),
            'status' => 'open',
        ]);

        $this->actingAs($admin)->get(route('admin.absensi.show', $session))
            ->assertOk()
            ->assertSee('admin-topbar', false)
            ->assertSee('attendance-qr-frame', false)
            ->assertSee('width: 100% !important', false)
            ->assertSee('attendance-qr-url', false);
    }
}
