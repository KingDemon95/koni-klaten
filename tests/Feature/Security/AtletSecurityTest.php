<?php

namespace Tests\Feature\Security;

use App\Models\Atlet;
use App\Models\CabangOlahraga;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AtletSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $roles = [
            'admin_koni',
            'staff_koni',
            'admin_cabor',
            'atlet_pelatih',
            'atlet',
            'pelatih',
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

    private function createCabor(): CabangOlahraga
    {
        return CabangOlahraga::create([
            'nama' => 'Bulutangkis',
            'singkatan' => 'PBSI',
            'status' => 'aktif',
        ]);
    }

    private function createAtletAccount(
        CabangOlahraga $cabor,
        string $accountName,
        string $email,
        string $namaAtlet
    ): array {
        $user = User::factory()->create([
            'name' => $accountName,
            'email' => $email,
            'is_active' => true,
        ]);

        $user->assignRole('atlet');

        $atlet = Atlet::create([
            'cabang_olahraga_id' => $cabor->id,
            'user_id' => $user->id,
            'nama_lengkap' => $namaAtlet,
            'jenis_kelamin' => 'L',
            'email' => $email,
            'status' => 'aktif',
        ]);

        return [$user, $atlet];
    }

    /**
     * Atlet dapat membuka dashboard dan profil sendiri.
     */
    public function test_atlet_can_access_dashboard_and_own_profile(): void
    {
        $cabor = $this->createCabor();

        [$user] = $this->createAtletAccount(
            $cabor,
            'Naila Fath',
            'naila@example.test',
            'Naila Fath'
        );

        $this->actingAs($user)
            ->get(route('dashboard.atlet'))
            ->assertOk()
            ->assertSeeText('Selamat Datang, Naila Fath!')
            ->assertSeeText('Bulutangkis');

        $this->actingAs($user)
            ->get(route('atlet.profil'))
            ->assertOk()
            ->assertSeeText('Profil Pribadi Atlet')
            ->assertSeeText('Naila Fath')
            ->assertSeeText('naila@example.test')
            ->assertSeeText('Email Akun Login');
    }

    /**
     * Atlet tidak boleh melihat profil milik atlet lain.
     */
    public function test_atlet_can_only_see_own_profile(): void
    {
        $cabor = $this->createCabor();

        [$userA] = $this->createAtletAccount(
            $cabor,
            'Atlet A',
            'atleta@example.test',
            'Nama Rahasia Atlet A'
        );

        [, $atletB] = $this->createAtletAccount(
            $cabor,
            'Atlet B',
            'atletb@example.test',
            'Nama Rahasia Atlet B'
        );

        $response = $this->actingAs($userA)
            ->get(route('atlet.profil', [
                'atlet_id' => $atletB->id,
            ]));

        $response->assertOk()
            ->assertSeeText('Nama Rahasia Atlet A')
            ->assertDontSee('Nama Rahasia Atlet B')
            ->assertDontSee('atletb@example.test');
    }

    /**
     * Akun Atlet tanpa profil tetap dapat membuka portal.
     */
    public function test_atlet_without_profile_sees_fallback_message(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
        ]);

        $user->assignRole('atlet');

        $this->actingAs($user)
            ->get(route('dashboard.atlet'))
            ->assertOk()
            ->assertSeeText('Profil Atlet Belum Terhubung');

        $this->actingAs($user)
            ->get(route('atlet.profil'))
            ->assertOk()
            ->assertSeeText('Data Profil Belum Tersedia');
    }

    /**
     * Atlet tidak dapat membuka dashboard role lainnya.
     */
    public function test_atlet_cannot_access_other_role_dashboards(): void
    {
        $user = $this->createAtletAccount(
            $this->createCabor(),
            'Atlet Test',
            'atlet-test@example.test',
            'Atlet Test'
        )[0];

        $routes = [
            'dashboard.admin-koni',
            'dashboard.admin-cabor',
            'dashboard.staff-koni',
            'dashboard.pelatih',
        ];

        foreach ($routes as $routeName) {
            $this->actingAs($user)
                ->get(route($routeName))
                ->assertForbidden();
        }
    }

    /**
     * Role lain tidak dapat membuka portal Atlet.
     */
    public function test_other_roles_cannot_access_atlet_portal(): void
    {
        $roles = [
            'admin_koni',
            'admin_cabor',
            'staff_koni',
            'pelatih',
            'atlet_pelatih',
        ];

        $routes = [
            'dashboard.atlet',
            'atlet.profil',
        ];

        foreach ($roles as $role) {
            $user = User::factory()->create([
                'is_active' => true,
            ]);

            $user->assignRole($role);

            foreach ($routes as $routeName) {
                $this->actingAs($user)
                    ->get(route($routeName))
                    ->assertForbidden();
            }
        }
    }

    /**
     * Portal Atlet tidak menyediakan route perubahan data.
     */
    public function test_atlet_routes_do_not_allow_write_methods(): void
    {
        $writeMethods = [
            'POST',
            'PUT',
            'PATCH',
            'DELETE',
        ];

        $atletRoutes = collect(
            Route::getRoutes()->getRoutes()
        )->filter(function ($route) {
            return str_starts_with(
                $route->uri(),
                'atlet/'
            );
        });

        $this->assertNotEmpty($atletRoutes);

        foreach ($atletRoutes as $route) {
            $methods = array_intersect(
                $route->methods(),
                $writeMethods
            );

            $this->assertEmpty(
                $methods,
                'Route perubahan data ditemukan: '
                . $route->uri()
            );
        }
    }

    /**
     * Akun Atlet nonaktif tidak boleh mengakses portal.
     */
    public function test_inactive_atlet_account_is_logged_out(): void
    {
        $user = User::factory()->create([
            'is_active' => false,
        ]);

        $user->assignRole('atlet');

        $this->actingAs($user)
            ->get(route('dashboard.atlet'))
            ->assertRedirect();

        $this->assertGuest();
    }
}
