<?php

namespace Tests\Feature;

use App\Models\CabangOlahraga;
use App\Models\ProfilKoni;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AdminKoniDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['admin_koni', 'staff_koni', 'admin_cabor', 'atlet', 'pelatih'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole($role);

        return $user;
    }

    public function test_admin_dashboard_shows_live_organization_and_account_summary(): void
    {
        $admin = $this->userWithRole('admin_koni');
        $this->userWithRole('staff_koni');
        CabangOlahraga::query()->create([
            'nama' => 'Bulu Tangkis',
            'singkatan' => 'PBSI',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard.admin-koni'));

        $response->assertOk()
            ->assertViewHas('stats', function (array $stats): bool {
                return $stats['cabor_aktif'] === 1
                    && $stats['pengguna'] === 2
                    && $stats['pengguna_aktif'] === 2;
            })
            ->assertViewHas('roleCounts', function (array $counts): bool {
                return $counts['Admin KONI'] === 1 && $counts['Staff KONI'] === 1;
            })
            ->assertSee('Ringkasan organisasi')
            ->assertSee('Komposisi akun berdasarkan role');
    }

    public function test_admin_can_update_organization_profile_and_public_home_uses_it(): void
    {
        $admin = $this->userWithRole('admin_koni');

        $response = $this->actingAs($admin)->put(route('admin.settings.update'), [
            'nama_organisasi' => 'KONI Kabupaten Klaten',
            'nama_singkat' => 'KONI Klaten',
            'tagline' => 'Berprestasi bersama.',
            'deskripsi' => 'Organisasi pembinaan olahraga prestasi Kabupaten Klaten.',
            'sejarah' => 'Sejarah organisasi KONI Klaten.',
            'visi' => 'Menjadi organisasi olahraga berprestasi.',
            'misi' => 'Membina atlet dan pelatih.\nMengembangkan olahraga.',
            'alamat' => 'Klaten, Jawa Tengah',
            'telepon' => '0272-000000',
            'email' => 'info@koni-klaten.id',
            'website' => 'https://example.com',
            'instagram' => 'https://instagram.com/koni-klaten',
            'facebook' => 'https://facebook.com/koni-klaten',
            'youtube' => 'https://youtube.com/@koni-klaten',
        ]);

        $response->assertRedirect(route('admin.settings'));
        $this->assertDatabaseHas('profil_koni', [
            'nama_singkat' => 'KONI Klaten',
            'visi' => 'Menjadi organisasi olahraga berprestasi.',
            'updated_by' => $admin->id,
        ]);

        $this->get(route('public.home'))
            ->assertOk()
            ->assertSee('Organisasi pembinaan olahraga prestasi Kabupaten Klaten.')
            ->assertSee('Menjadi organisasi olahraga berprestasi.');
    }

    public function test_admin_can_upload_organization_logo(): void
    {
        Storage::fake('public');
        $admin = $this->userWithRole('admin_koni');

        $this->actingAs($admin)->put(route('admin.settings.update'), [
            'nama_organisasi' => 'KONI Kabupaten Klaten',
            'nama_singkat' => 'KONI Klaten',
            'logo' => UploadedFile::fake()->image('logo.png', 240, 240),
        ])->assertRedirect(route('admin.settings'));

        $profil = ProfilKoni::query()->firstOrFail();
        $this->assertNotNull($profil->logo_path);
        Storage::disk('public')->assertExists($profil->logo_path);
    }

    public function test_non_admin_roles_cannot_view_or_update_organization_settings(): void
    {
        $staff = $this->userWithRole('staff_koni');

        $this->actingAs($staff)
            ->get(route('admin.settings'))
            ->assertForbidden();

        $this->actingAs($staff)
            ->put(route('admin.settings.update'), [
                'nama_organisasi' => 'Tidak boleh diubah',
                'nama_singkat' => 'Tidak boleh',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('profil_koni', [
            'nama_organisasi' => 'Tidak boleh diubah',
        ]);
    }
}
