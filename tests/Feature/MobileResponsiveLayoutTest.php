<?php

namespace Tests\Feature;

use App\Models\AttendanceSession;
use App\Models\CabangOlahraga;
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

    private function userWithRole(string $role, ?int $caborId = null): User
    {
        $user = User::factory()->create([
            'is_active' => true,
            'cabang_olahraga_id' => $caborId,
        ]);
        $user->assignRole($role);

        return $user;
    }

    /**
     * @param array<int, string> $targetRoles
     */
    private function createSession(User $creator, ?int $caborId, array $targetRoles): AttendanceSession
    {
        return AttendanceSession::query()->create([
            'created_by' => $creator->id,
            'cabang_olahraga_id' => $caborId,
            'title' => 'Uji QR Responsif',
            'description' => 'Pengujian tampilan QR pada perangkat seluler.',
            'target_role' => count($targetRoles) === 7 ? 'all' : 'custom',
            'target_roles' => $targetRoles,
            'token' => Str::random(48),
            'starts_at' => now()->subMinute(),
            'ends_at' => now()->addHour(),
            'status' => 'open',
        ]);
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

    public function test_absensi_saya_qr_is_mobile_safe_for_every_internal_role(): void
    {
        $creator = $this->userWithRole('admin_koni');
        $session = $this->createSession($creator, null, [
            'admin_koni',
            'staff_koni',
            'ketua_koni',
            'sekretaris_koni',
            'admin_cabor',
            'atlet',
            'pelatih',
        ]);

        $roleRoutes = [
            ['admin_koni', 'admin'],
            ['staff_koni', 'staff'],
            ['ketua_koni', 'admin'],
            ['sekretaris_koni', 'sekretaris'],
            ['admin_cabor', 'cabor'],
            ['atlet', 'atlet'],
            ['pelatih', 'pelatih'],
        ];

        $cabor = CabangOlahraga::query()->create([
            'nama' => 'Cabor Uji Responsif',
            'status' => 'aktif',
        ]);

        foreach ($roleRoutes as [$role, $prefix]) {
            $user = $role === 'admin_koni'
                ? $creator
                : $this->userWithRole($role, $role === 'admin_cabor' ? $cabor->id : null);

            $this->actingAs($user)
                ->get(route($prefix.'.absensi.index'))
                ->assertOk()
                ->assertSee('attendance-page', false)
                ->assertSee('attendance-qr-image', false)
                ->assertSee('width: 100% !important', false)
                ->assertSee('Unduh QR ke HP')
                ->assertSee('min-w-0 w-full max-w-full', false);
        }
    }

    public function test_detail_qr_is_mobile_safe_for_every_role_that_can_open_session_details(): void
    {
        $creator = $this->userWithRole('admin_koni');
        $cabor = CabangOlahraga::query()->create([
            'nama' => 'Cabor Detail Responsif',
            'status' => 'aktif',
        ]);
        $session = $this->createSession($creator, $cabor->id, [
            'admin_koni',
            'staff_koni',
            'ketua_koni',
            'sekretaris_koni',
            'admin_cabor',
        ]);

        $roleRoutes = [
            ['admin_koni', 'admin'],
            ['staff_koni', 'staff'],
            ['ketua_koni', 'admin'],
            ['sekretaris_koni', 'sekretaris'],
            ['admin_cabor', 'cabor'],
        ];

        foreach ($roleRoutes as [$role, $prefix]) {
            $user = $role === 'admin_koni'
                ? $creator
                : $this->userWithRole($role, $role === 'admin_cabor' ? $cabor->id : null);

            $this->actingAs($user)
                ->get(route($prefix.'.absensi.show', $session))
                ->assertOk()
                ->assertSee('attendance-page', false)
                ->assertSee('attendance-qr-frame', false)
                ->assertSee('attendance-qr-url', false)
                ->assertSee('width: 100% !important', false)
                ->assertSee('min-w-0 w-full max-w-full', false);
        }
    }
}
