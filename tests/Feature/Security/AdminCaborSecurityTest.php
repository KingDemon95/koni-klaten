<?php

namespace Tests\Feature\Security;

use App\Models\Atlet;
use App\Models\CabangOlahraga;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AdminCaborSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate([
            'name' => 'admin_cabor',
            'guard_name' => 'web',
        ]);

        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();
    }

    /**
     * Membuat cabang olahraga untuk pengujian.
     */
    private function createCabor(
        string $nama,
        string $status = 'aktif'
    ): CabangOlahraga {
        return CabangOlahraga::create([
            'nama' => $nama,
            'status' => $status,
        ]);
    }

    /**
     * Membuat akun Admin Cabor.
     */
    private function createAdminCabor(
        ?CabangOlahraga $cabor = null
    ): User {
        $user = User::factory()->create([
            'cabang_olahraga_id' => $cabor?->id,
            'is_active' => true,
        ]);

        $user->assignRole('admin_cabor');

        return $user;
    }

    /**
     * Admin Cabor tanpa cabang harus ditolak.
     */
    public function test_admin_cabor_without_branch_is_forbidden(): void
    {
        $user = $this->createAdminCabor();

        $response = $this
            ->actingAs($user)
            ->get(route('dashboard.admin-cabor'));

        $response->assertForbidden();
    }

    /**
     * Admin Cabor dengan cabang nonaktif harus ditolak.
     */
    public function test_admin_cabor_with_inactive_branch_is_forbidden(): void
    {
        $cabor = $this->createCabor(
            'Cabor Nonaktif Uji',
            'nonaktif'
        );

        $user = $this->createAdminCabor($cabor);

        $response = $this
            ->actingAs($user)
            ->get(route('dashboard.admin-cabor'));

        $response->assertForbidden();
    }

    /**
     * Admin Cabor dengan cabang aktif boleh membuka dashboard.
     */
    public function test_admin_cabor_with_active_branch_can_access_dashboard(): void
    {
        $cabor = $this->createCabor(
            'Cabor Aktif Uji'
        );

        $user = $this->createAdminCabor($cabor);

        $response = $this
            ->actingAs($user)
            ->get(route('dashboard.admin-cabor'));

        $response->assertOk();

        $response->assertViewHas(
            'cabor',
            fn ($data) => $data->is($cabor)
        );
    }

    /**
     * Admin Cabor tidak boleh membuka panel Admin KONI.
     */
    public function test_admin_cabor_cannot_access_admin_koni_cabor_management(): void
    {
        $cabor = $this->createCabor(
            'Cabor Keamanan Uji'
        );

        $user = $this->createAdminCabor($cabor);

        $response = $this
            ->actingAs($user)
            ->get(route('admin.cabor.index'));

        $response->assertForbidden();
    }

    /**
     * Admin Cabor hanya boleh melihat atlet cabangnya sendiri.
     */
    public function test_admin_cabor_only_sees_athletes_from_own_branch(): void
    {
        $caborA = $this->createCabor(
            'Bulutangkis Uji'
        );

        $caborB = $this->createCabor(
            'Taekwondo Uji'
        );

        $user = $this->createAdminCabor($caborA);

        Atlet::create([
            'cabang_olahraga_id' => $caborA->id,
            'nama_lengkap' => 'Atlet Milik Bulutangkis',
            'jenis_kelamin' => 'L',
            'status' => 'aktif',
        ]);

        Atlet::create([
            'cabang_olahraga_id' => $caborB->id,
            'nama_lengkap' => 'Atlet Milik Taekwondo',
            'jenis_kelamin' => 'L',
            'status' => 'aktif',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('cabor.atlet.index'));

        $response->assertOk();

        $response->assertViewHas('items', function ($items) {
            $names = $items
                ->getCollection()
                ->pluck('nama_lengkap')
                ->all();

            return $names === [
                'Atlet Milik Bulutangkis',
            ];
        });
    }
}
