<?php

namespace Tests\Feature;

use App\Models\Atlet;
use App\Models\CabangOlahraga;
use App\Models\Pelatih;
use App\Models\Prestasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ReportsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin_koni', 'staff_koni', 'admin_cabor', 'atlet', 'pelatih'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function userWithRole(string $role, ?CabangOlahraga $cabor = null): User
    {
        $user = User::factory()->create([
            'cabang_olahraga_id' => $cabor?->id,
            'is_active' => true,
        ]);
        $user->assignRole($role);

        return $user;
    }

    private function cabor(string $name, string $status = 'aktif'): CabangOlahraga
    {
        return CabangOlahraga::create([
            'nama' => $name,
            'status' => $status,
        ]);
    }

    private function addAtlet(CabangOlahraga $cabor, string $name): Atlet
    {
        return Atlet::create([
            'cabang_olahraga_id' => $cabor->id,
            'nama_lengkap' => $name,
            'jenis_kelamin' => 'L',
            'status' => 'aktif',
        ]);
    }

    private function addPelatih(CabangOlahraga $cabor, string $name): Pelatih
    {
        return Pelatih::create([
            'cabang_olahraga_id' => $cabor->id,
            'nama_lengkap' => $name,
            'jenis_kelamin' => 'L',
            'status' => 'aktif',
        ]);
    }

    private function addPrestasi(
        CabangOlahraga $cabor,
        string $name,
        string $date,
        string $status = 'terverifikasi',
        string $medal = 'emas',
        ?Atlet $atlet = null,
        ?Pelatih $pelatih = null,
    ): Prestasi {
        return Prestasi::create([
            'atlet_id' => $atlet?->id,
            'pelatih_id' => $pelatih?->id,
            'cabang_olahraga_id' => $cabor->id,
            'nama_kejuaraan' => $name,
            'tingkat' => 'kabupaten',
            'hasil' => 'Juara 1',
            'jenis_medali' => $medal,
            'tanggal_prestasi' => $date,
            'bukti_path' => 'private/reports/'.$name.'.pdf',
            'bukti_original_name' => 'bukti.pdf',
            'status' => $status,
            'verified_at' => $status === 'terverifikasi' ? now() : null,
        ]);
    }

    public function test_admin_koni_can_view_filtered_organization_report(): void
    {
        $admin = $this->userWithRole('admin_koni');
        $cabor = $this->cabor('Bulutangkis Laporan');
        $inactive = $this->cabor('Cabor Nonaktif Laporan', 'nonaktif');
        $atlet = $this->addAtlet($cabor, 'Atlet Laporan');
        $this->addPelatih($cabor, 'Pelatih Laporan');

        $this->addPrestasi($cabor, 'Kejuaraan Laporan Tahun Ini', now()->startOfYear()->addDays(5)->toDateString(), 'terverifikasi', 'emas', $atlet);
        $this->addPrestasi($cabor, 'Kejuaraan Laporan Tahun Lalu', now()->subYear()->startOfYear()->addDays(5)->toDateString(), 'terverifikasi', 'perak', $atlet);
        $this->addPrestasi($cabor, 'Prestasi Belum Diverifikasi', now()->startOfYear()->addDays(6)->toDateString(), 'menunggu_verifikasi', 'perunggu', $atlet);
        $this->addPrestasi($inactive, 'Prestasi Cabor Nonaktif', now()->startOfYear()->addDays(7)->toDateString());

        $this->actingAs($admin)
            ->get(route('admin.laporan.index', ['tahun' => now()->year]))
            ->assertOk()
            ->assertViewHas('stats', function (array $stats): bool {
                return $stats['cabor_aktif'] === 1
                    && $stats['atlet_aktif'] === 1
                    && $stats['pelatih_aktif'] === 1
                    && $stats['prestasi_terverifikasi'] === 1
                    && $stats['emas'] === 1
                    && $stats['perak'] === 0
                    && $stats['menunggu_verifikasi'] === 1;
            })
            ->assertSeeText('Bulutangkis Laporan')
            ->assertSeeText('Kejuaraan Laporan Tahun Ini')
            ->assertDontSeeText('Cabor Nonaktif Laporan')
            ->assertDontSeeText('Prestasi Cabor Nonaktif')
            ->assertDontSeeText('Kejuaraan Laporan Tahun Lalu');
    }

    public function test_staff_koni_can_export_verified_achievements_as_csv(): void
    {
        $staff = $this->userWithRole('staff_koni');
        $cabor = $this->cabor('Atletik Laporan');
        $atlet = $this->addAtlet($cabor, 'Atlet CSV Laporan');
        $this->addPrestasi($cabor, 'Kejuaraan CSV Terverifikasi', now()->startOfYear()->addDays(10)->toDateString(), 'terverifikasi', 'perak', $atlet);
        $this->addPrestasi($cabor, 'Kejuaraan CSV Menunggu', now()->startOfYear()->addDays(11)->toDateString(), 'menunggu_verifikasi', 'emas', $atlet);

        $response = $this->actingAs($staff)
            ->get(route('staff.laporan.export', [
                'jenis' => 'prestasi',
                'tahun' => now()->year,
            ]));

        $response->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $csv = $response->streamedContent();

        $this->assertStringContainsString('Kejuaraan CSV Terverifikasi', $csv);
        $this->assertStringNotContainsString('Kejuaraan CSV Menunggu', $csv);
        $this->assertStringNotContainsString('private/reports/', $csv);
    }

    public function test_admin_cabor_report_is_always_limited_to_own_branch(): void
    {
        $ownCabor = $this->cabor('Cabor Milik Sendiri');
        $otherCabor = $this->cabor('Cabor Cabang Lain');
        $adminCabor = $this->userWithRole('admin_cabor', $ownCabor);
        $ownAtlet = $this->addAtlet($ownCabor, 'Atlet Cabor Sendiri');
        $otherAtlet = $this->addAtlet($otherCabor, 'Atlet Cabor Lain');
        $this->addPrestasi($ownCabor, 'Prestasi Milik Sendiri', now()->toDateString(), 'terverifikasi', 'emas', $ownAtlet);
        $this->addPrestasi($otherCabor, 'Prestasi Cabang Lain', now()->toDateString(), 'terverifikasi', 'emas', $otherAtlet);

        $this->actingAs($adminCabor)
            ->get(route('cabor.laporan.index', ['cabor_id' => $otherCabor->id]))
            ->assertOk()
            ->assertViewHas('stats', fn (array $stats): bool => $stats['cabor_aktif'] === 1 && $stats['prestasi_terverifikasi'] === 1)
            ->assertSeeText('Cabor Milik Sendiri')
            ->assertSeeText('Prestasi Milik Sendiri')
            ->assertDontSeeText('Cabor Cabang Lain')
            ->assertDontSeeText('Prestasi Cabang Lain');

        $csv = $this->actingAs($adminCabor)
            ->get(route('cabor.laporan.export', [
                'jenis' => 'cabor',
                'cabor_id' => $otherCabor->id,
            ]))
            ->streamedContent();

        $this->assertStringContainsString('Cabor Milik Sendiri', $csv);
        $this->assertStringNotContainsString('Cabor Cabang Lain', $csv);
    }

    public function test_report_csv_escapes_spreadsheet_formula_values(): void
    {
        $admin = $this->userWithRole('admin_koni');
        $this->cabor('=HYPERLINK("https://example.test")');

        $csv = $this->actingAs($admin)
            ->get(route('admin.laporan.export', ['jenis' => 'cabor']))
            ->streamedContent();

        $this->assertStringContainsString("'=HYPERLINK", $csv);
    }

    public function test_atlet_cannot_access_internal_reports(): void
    {
        $atlet = $this->userWithRole('atlet');

        $this->actingAs($atlet)
            ->get('/admin/laporan')
            ->assertForbidden();

        $this->actingAs($atlet)
            ->get('/staff/laporan')
            ->assertForbidden();

        $this->actingAs($atlet)
            ->get('/cabor/laporan')
            ->assertForbidden();
    }
}
