<?php

namespace Tests\Feature;

use App\Models\CabangOlahraga;
use App\Models\Prestasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AdminPrestasiMonitoringTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::firstOrCreate(['name' => 'admin_koni', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'admin_cabor', 'guard_name' => 'web']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function admin(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole('admin_koni');

        return $user;
    }

    private function achievement(CabangOlahraga $cabor, array $overrides = []): Prestasi
    {
        return Prestasi::query()->create(array_merge([
            'cabang_olahraga_id' => $cabor->id,
            'nama_kejuaraan' => 'Pekan Olahraga Kabupaten',
            'tingkat' => 'kabupaten',
            'hasil' => 'Juara 1',
            'tanggal_prestasi' => '2026-10-10',
            'bukti_path' => 'prestasi/bukti.pdf',
            'bukti_original_name' => 'sertifikat.pdf',
            'status' => 'menunggu_verifikasi',
        ], $overrides));
    }

    public function test_admin_can_monitor_prestasi_from_all_branches_and_filter_by_status(): void
    {
        $admin = $this->admin();
        $caborA = CabangOlahraga::query()->create(['nama' => 'Bulu Tangkis', 'status' => 'aktif']);
        $caborB = CabangOlahraga::query()->create(['nama' => 'Atletik', 'status' => 'aktif']);
        $this->achievement($caborA, ['nama_kejuaraan' => 'Kejuaraan Bulu Tangkis']);
        $this->achievement($caborB, [
            'nama_kejuaraan' => 'Kejuaraan Atletik',
            'status' => 'terverifikasi',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.prestasi.index'))
            ->assertOk()
            ->assertSee('Kejuaraan Bulu Tangkis')
            ->assertSee('Kejuaraan Atletik');

        $this->actingAs($admin)
            ->get(route('admin.prestasi.index', ['status' => 'terverifikasi', 'cabor' => $caborB->id]))
            ->assertOk()
            ->assertSee('Kejuaraan Atletik')
            ->assertDontSee('Kejuaraan Bulu Tangkis');
    }

    public function test_admin_can_download_private_achievement_evidence(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('prestasi/bukti.pdf', 'sample proof');

        $admin = $this->admin();
        $cabor = CabangOlahraga::query()->create(['nama' => 'Renang', 'status' => 'aktif']);
        $prestasi = $this->achievement($cabor);

        $this->actingAs($admin)
            ->get(route('admin.prestasi.bukti', $prestasi))
            ->assertDownload('sertifikat.pdf');
    }
}
