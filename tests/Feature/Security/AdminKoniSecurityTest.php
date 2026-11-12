<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AdminKoniSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();

        $roles = [
            'admin_koni',
            'admin_cabor',
            'staff_koni',
            'atlet',
            'pelatih',
            'atlet_pelatih',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate([
                'name' => $role,
                'guard_name' => 'web',
            ]);
        }

        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();
    }

    /**
     * Membuat akun dengan role tertentu.
     */
    private function createUserWithRole(string $role): User
    {
        $user = User::factory()->create([
            'is_active' => true,
        ]);

        $user->assignRole($role);

        return $user;
    }

    /**
     * Admin KONI dapat membuka dashboard dan seluruh halaman utama.
     */
    public function test_admin_koni_can_access_all_management_pages(): void
    {
        $admin = $this->createUserWithRole('admin_koni');

        $this->actingAs($admin);

        $routes = [
            'dashboard.admin-koni',

            'admin.cabor.index',
            'admin.atlet.index',
            'admin.pelatih.index',
            'admin.struktur-organisasi.index',
            'admin.pengguna.index',
            'admin.agenda.index',
            'admin.prestasi.index',
            'admin.persuratan.index',
            'admin.templates.index',
            'admin.publikasi.index',
            'admin.settings',
        ];

        foreach ($routes as $routeName) {
            $response = $this->get(route($routeName));

            $response->assertOk();
        }
    }

    /**
     * Admin KONI tidak harus terhubung dengan cabang olahraga.
     */
    public function test_admin_koni_can_access_dashboard_without_branch(): void
    {
        $admin = $this->createUserWithRole('admin_koni');

        $this->assertNull($admin->cabang_olahraga_id);

        $response = $this
            ->actingAs($admin)
            ->get(route('dashboard.admin-koni'));

        $response->assertOk();
    }

    /**
     * Role selain Admin KONI tidak boleh masuk panel Admin KONI.
     */
    public function test_other_roles_cannot_access_admin_koni_panel(): void
    {
        $roles = [
            'admin_cabor',
            'staff_koni',
            'atlet',
            'pelatih',
            'atlet_pelatih',
        ];

        foreach ($roles as $role) {
            $user = $this->createUserWithRole($role);

            $this->actingAs($user)
                ->get(route('dashboard.admin-koni'))
                ->assertForbidden();

            $this->actingAs($user)
                ->get(route('admin.cabor.index'))
                ->assertForbidden();

            $this->actingAs($user)
                ->get(route('admin.pengguna.index'))
                ->assertForbidden();

            $this->actingAs($user)
                ->get(route('admin.agenda.index'))
                ->assertForbidden();

            $this->actingAs($user)
                ->get(route('admin.prestasi.index'))
                ->assertForbidden();

            $this->actingAs($user)
                ->get(route('admin.persuratan.index'))
                ->assertForbidden();

            $this->actingAs($user)
                ->get(route('admin.templates.index'))
                ->assertForbidden();

            $this->actingAs($user)
                ->get(route('admin.publikasi.index'))
                ->assertForbidden();
        }
    }
}
