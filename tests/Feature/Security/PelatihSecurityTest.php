<?php

namespace Tests\Feature\Security;

use App\Models\CabangOlahraga;
use App\Models\Pelatih;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PelatihSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'pelatih',
            'atlet',
            'admin_koni',
            'admin_cabor',
            'staff_koni',
        ] as $role) {
            Role::firstOrCreate([
                'name' => $role,
                'guard_name' => 'web',
            ]);
        }

        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();
    }

    /**
     * Membuat akun Pelatih beserta profilnya.
     */
    private function createPelatihAccount(
        string $nama,
        string $email,
        string $namaCabor = 'Bulutangkis',
        array $profileOverrides = []
    ): array {
        $cabor = CabangOlahraga::create([
            'nama' => $namaCabor,
            'status' => 'aktif',
        ]);

        $user = User::factory()->create([
            'name' => $nama,
            'email' => $email,
            'is_active' => true,
        ]);

        $user->assignRole('pelatih');

        $pelatih = Pelatih::create(array_merge([
            'cabang_olahraga_id' => $cabor->id,
            'user_id' => $user->id,
            'nama_lengkap' => $nama,
            'jenis_kelamin' => 'L',
            'pendidikan_terakhir' => 'S1 Pendidikan Kepelatihan',
            'spesialisasi' => 'Bulutangkis',
            'sertifikasi' => 'Pelatih Nasional Level B',
            'pengalaman_tahun' => 8,
            'status' => 'aktif',
        ], $profileOverrides));

        return compact('user', 'pelatih', 'cabor');
    }

    /**
     * Pelatih dapat membuka dashboard dan profil sendiri.
     */
    public function test_pelatih_can_access_dashboard_and_own_profile(): void
    {
        $data = $this->createPelatihAccount(
            'Pelatih Pengujian',
            'pelatih-uji@example.com'
        );

        $this->actingAs($data['user']);

        $this->get(route('dashboard.pelatih'))
            ->assertOk()
            ->assertSeeText('Pelatih Pengujian')
            ->assertSeeText('Bulutangkis');

        $this->get(route('pelatih.profil'))
            ->assertOk()
            ->assertSeeText('Pelatih Pengujian')
            ->assertSeeText('S1 Pendidikan Kepelatihan')
            ->assertSeeText('Bulutangkis')
            ->assertSeeText('Pelatih Nasional Level B')
            ->assertSeeText('8 tahun');
    }

    /**
     * Pelatih hanya dapat melihat profil miliknya sendiri.
     */
    public function test_pelatih_can_only_see_own_profile(): void
    {
        $pelatihA = $this->createPelatihAccount(
            'Pelatih Pertama',
            'pelatih-a@example.com',
            'Bulutangkis'
        );

        $this->createPelatihAccount(
            'Pelatih Kedua',
            'pelatih-b@example.com',
            'Atletik'
        );

        $response = $this->actingAs($pelatihA['user'])
            ->get(route('pelatih.profil'));

        $response->assertOk()
            ->assertSeeText('Pelatih Pertama')
            ->assertDontSee('Pelatih Kedua')
            ->assertDontSee('pelatih-b@example.com');
    }

    /**
     * Akun Pelatih tanpa profil mendapatkan pesan fallback.
     */
    public function test_pelatih_without_profile_sees_fallback_message(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
        ]);

        $user->assignRole('pelatih');

        $this->actingAs($user);

        $this->get(route('dashboard.pelatih'))
            ->assertOk()
            ->assertSeeText('Profil Pelatih Belum Terhubung');

        $this->get(route('pelatih.profil'))
            ->assertOk()
            ->assertSeeText('Profil Pelatih Belum Tersedia');
    }

    /**
     * Pelatih tidak boleh membuka dashboard role lain.
     */
    public function test_pelatih_cannot_access_other_role_dashboards(): void
    {
        $data = $this->createPelatihAccount(
            'Pelatih Keamanan',
            'pelatih-security@example.com'
        );

        $this->actingAs($data['user']);

        $routes = [
            'dashboard.admin-koni',
            'dashboard.admin-cabor',
            'dashboard.staff-koni',
            'dashboard.atlet',
            'atlet.prestasi.index',
            'atlet.izin-latihan.index',
            'atlet.templates.index',
        ];

        foreach ($routes as $routeName) {
            $this->get(route($routeName))
                ->assertForbidden();
        }
    }

    /**
     * Akun Pelatih nonaktif tidak boleh masuk portal.
     */
    public function test_inactive_pelatih_account_is_logged_out(): void
    {
        $user = User::factory()->create([
            'is_active' => false,
        ]);

        $user->assignRole('pelatih');

        $this->actingAs($user);

        $this->get(route('dashboard.pelatih'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    /**
     * Route tulis Pelatih hanya tersedia untuk biodata, prestasi, dan dokumen izin latihan miliknya.
     */
    public function test_pelatih_write_routes_are_limited_to_own_portal_features(): void
    {
        $writeMethods = ['POST', 'PUT', 'PATCH', 'DELETE'];
        $allowedNames = [
            'pelatih.profil.update',
            'pelatih.prestasi.store',
            'pelatih.prestasi.update',
            'pelatih.prestasi.destroy',
            'pelatih.izin-latihan.store',
            'pelatih.izin-latihan.destroy',
        ];

        $writeRoutes = collect(\Illuminate\Support\Facades\Route::getRoutes()->getRoutes())
            ->filter(fn ($route) => str_starts_with($route->uri(), 'pelatih/'))
            ->filter(fn ($route) => count(array_intersect($route->methods(), $writeMethods)) > 0);

        $actualNames = $writeRoutes->values()->map(fn ($route) => $route->getName())->all();

        $this->assertNotEmpty($actualNames);
        $this->assertEqualsCanonicalizing($allowedNames, $actualNames);
    }

}
