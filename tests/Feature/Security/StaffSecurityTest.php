<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class StaffSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $roles = [
            'admin_koni',
            'staff_koni',
            'admin_cabor',
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
     * Staff dapat membuka data master read-only dan modul operasional yang dikelolanya.
     */
    public function test_staff_can_access_all_read_only_pages(): void
    {
        $staff = $this->createUserWithRole('staff_koni');

        $this->actingAs($staff);

        $routes = [
            'dashboard.staff-koni',
            'staff.cabor.index',
            'staff.atlet.index',
            'staff.pelatih.index',
            'staff.pengurus.index',
            'staff.agenda.index',
            'staff.surat.index',
            'staff.templates.index',
            'staff.publikasi.index',
        ];

        foreach ($routes as $routeName) {
            $response = $this->get(route($routeName));

            $response->assertOk();
        }
    }

    /**
     * Staff tidak boleh membuka panel Admin KONI
     * maupun Admin Cabor.
     */
    public function test_staff_cannot_access_admin_panels(): void
    {
        $staff = $this->createUserWithRole('staff_koni');

        $routes = [
            'dashboard.admin-koni',
            'admin.cabor.index',
            'admin.atlet.index',
            'admin.pengguna.index',
            'admin.agenda.index',
            'admin.persuratan.index',
            'admin.templates.index',
            'dashboard.admin-cabor',
        ];

        foreach ($routes as $routeName) {
            $response = $this
                ->actingAs($staff)
                ->get(route($routeName));

            $response->assertForbidden();
        }
    }

    /**
     * Role selain Staff tidak boleh membuka panel Staff.
     */
    public function test_other_roles_cannot_access_staff_pages(): void
    {
        $roles = [
            'admin_koni',
            'admin_cabor',
            'atlet',
            'pelatih',
            'atlet_pelatih',
        ];

        foreach ($roles as $role) {
            $user = $this->createUserWithRole($role);

            $this->actingAs($user)
                ->get(route('dashboard.staff-koni'))
                ->assertForbidden();

            $this->actingAs($user)
                ->get(route('staff.cabor.index'))
                ->assertForbidden();

            $this->actingAs($user)
                ->get(route('staff.agenda.index'))
                ->assertForbidden();

            $this->actingAs($user)
                ->get(route('staff.surat.index'))
                ->assertForbidden();

            $this->actingAs($user)
                ->get(route('staff.templates.index'))
                ->assertForbidden();

            $this->actingAs($user)
                ->get(route('staff.publikasi.index'))
                ->assertForbidden();
        }
    }

    /**
     * Data master Staff tetap read-only. Agenda, persuratan, template surat, dan publikasi
     * dikecualikan karena Staff memang diberi kewenangan mengelolanya.
     */
    public function test_staff_routes_do_not_allow_write_methods(): void
    {
        $writeMethods = [
            'POST',
            'PUT',
            'PATCH',
            'DELETE',
        ];

        $staffRoutes = collect(
            Route::getRoutes()->getRoutes()
        )->filter(function ($route) {
            return str_starts_with(
                $route->uri(),
                'staff/'
            )
                && !str_starts_with($route->uri(), 'staff/agenda')
                && !str_starts_with($route->uri(), 'staff/surat')
                && !str_starts_with($route->uri(), 'staff/templates-surat')
                && !str_starts_with($route->uri(), 'staff/publikasi');
        });

        $this->assertNotEmpty($staffRoutes);

        foreach ($staffRoutes as $route) {
            $methods = array_intersect(
                $route->methods(),
                $writeMethods
            );

            $this->assertEmpty(
                $methods,
                'Ditemukan route perubahan data Staff: '
                . $route->uri()
            );
        }
    }

    /**
     * Parameter pencarian dan filter berbentuk array ditolak.
     */
    public function test_staff_rejects_array_query_parameters(): void
    {
        $staff = $this->createUserWithRole('staff_koni');

        $this->actingAs($staff)
            ->get(route('staff.cabor.index', [
                'q' => ['invalid'],
            ]))
            ->assertStatus(422);

        $this->actingAs($staff)
            ->get(route('staff.atlet.index', [
                'cabor_id' => ['invalid'],
            ]))
            ->assertStatus(422);
    }
}
