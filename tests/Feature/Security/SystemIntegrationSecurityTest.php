<?php

namespace Tests\Feature\Security;

use App\Models\Atlet;
use App\Models\CabangOlahraga;
use App\Models\IzinLatihan;
use App\Models\Prestasi;
use App\Models\Publikasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class SystemIntegrationSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'admin_koni',
            'staff_koni',
            'admin_cabor',
            'atlet',
            'pelatih',
            'atlet_pelatih',
        ] as $role) {
            Role::firstOrCreate([
                'name' => $role,
                'guard_name' => 'web',
            ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Storage::fake('local');
    }

    private function cabor(string $nama, string $status = 'aktif'): CabangOlahraga
    {
        return CabangOlahraga::create([
            'nama' => $nama,
            'status' => $status,
        ]);
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

    private function atletProfile(User $user, CabangOlahraga $cabor, string $nama): Atlet
    {
        return Atlet::create([
            'user_id' => $user->id,
            'cabang_olahraga_id' => $cabor->id,
            'nama_lengkap' => $nama,
            'jenis_kelamin' => 'L',
            'status' => 'aktif',
        ]);
    }

    private function prestasi(
        CabangOlahraga $cabor,
        string $path,
        ?Atlet $atlet = null,
        string $status = 'menunggu_verifikasi',
    ): Prestasi {
        Storage::disk('local')->put($path, 'private evidence');

        return Prestasi::create([
            'atlet_id' => $atlet?->id,
            'cabang_olahraga_id' => $cabor->id,
            'nama_kejuaraan' => 'Kejuaraan Integrasi KONI',
            'tingkat' => 'kabupaten',
            'hasil' => 'Juara 1',
            'tanggal_prestasi' => now()->subDays(2)->toDateString(),
            'bukti_path' => $path,
            'bukti_original_name' => basename($path),
            'status' => $status,
        ]);
    }

    public function test_dashboard_dispatches_each_supported_role_to_its_own_panel(): void
    {
        $cabor = $this->cabor('Bulutangkis Integrasi');

        $cases = [
            ['admin_koni', null, 'dashboard.admin-koni'],
            ['staff_koni', null, 'dashboard.staff-koni'],
            ['admin_cabor', $cabor, 'dashboard.admin-cabor'],
            ['atlet', null, 'dashboard.atlet'],
            ['pelatih', null, 'dashboard.pelatih'],
            ['atlet_pelatih', null, 'dashboard.atlet-pelatih'],
        ];

        foreach ($cases as [$role, $assignedCabor, $destination]) {
            $user = $this->userWithRole($role, $assignedCabor);

            $this->actingAs($user)
                ->get(route('dashboard'))
                ->assertRedirect(route($destination));
        }
    }

    public function test_public_sections_are_guest_accessible_and_management_panels_require_login(): void
    {
        foreach ([
            'public.home',
            'public.cabor.index',
            'public.struktur.index',
            'public.statistik.index',
            'public.agenda.index',
            'public.berita.index',
            'public.dokumentasi.index',
        ] as $routeName) {
            $this->get(route($routeName))->assertOk();
        }

        foreach ([
            'admin.settings',
            'staff.publikasi.index',
            'cabor.prestasi.index',
            'atlet.profil',
            'pelatih.profil',
        ] as $routeName) {
            $this->get(route($routeName))->assertRedirect(route('login'));
        }
    }

    public function test_admin_koni_can_monitor_achievement_but_cannot_verify_it(): void
    {
        $cabor = $this->cabor('Atletik Integrasi');
        $admin = $this->userWithRole('admin_koni');
        $prestasi = $this->prestasi($cabor, 'prestasi/integrasi/admin-monitoring.pdf');

        $this->actingAs($admin)
            ->get(route('admin.prestasi.index'))
            ->assertOk()
            ->assertSeeText('Kejuaraan Integrasi KONI');

        $this->actingAs($admin)
            ->post(route('cabor.prestasi.review', $prestasi), [
                'status' => 'terverifikasi',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('prestasis', [
            'id' => $prestasi->id,
            'status' => 'menunggu_verifikasi',
        ]);
    }

    public function test_admin_cabor_cannot_download_private_files_from_another_branch(): void
    {
        $ownCabor = $this->cabor('Renang Integrasi');
        $otherCabor = $this->cabor('Panahan Integrasi');
        $adminCabor = $this->userWithRole('admin_cabor', $ownCabor);
        $otherUser = $this->userWithRole('atlet');
        $otherAtlet = $this->atletProfile($otherUser, $otherCabor, 'Atlet Cabang Lain');

        $prestasi = $this->prestasi(
            $otherCabor,
            'prestasi/integrasi/cabang-lain.pdf',
            $otherAtlet,
        );

        $izinPath = 'izin-latihan/integrasi/cabang-lain.pdf';
        Storage::disk('local')->put($izinPath, 'private permission document');
        $izin = IzinLatihan::create([
            'user_id' => $otherUser->id,
            'cabang_olahraga_id' => $otherCabor->id,
            'jenis_pemohon' => 'atlet',
            'nama_pemohon' => $otherAtlet->nama_lengkap,
            'tujuan' => 'KONI Kabupaten Klaten',
            'keperluan' => 'Pemusatan latihan',
            'tanggal_mulai' => now()->addDays(2)->toDateString(),
            'file_path' => $izinPath,
            'file_original_name' => 'izin-cabor-lain.pdf',
            'status' => 'diajukan',
        ]);

        $this->actingAs($adminCabor)
            ->get(route('cabor.prestasi.bukti', $prestasi))
            ->assertNotFound();

        $this->actingAs($adminCabor)
            ->get(route('cabor.izin-latihan.download', $izin))
            ->assertNotFound();
    }

    public function test_atlet_cannot_download_another_atlets_private_achievement_evidence(): void
    {
        $cabor = $this->cabor('Taekwondo Integrasi');
        $firstUser = $this->userWithRole('atlet');
        $secondUser = $this->userWithRole('atlet');
        $this->atletProfile($firstUser, $cabor, 'Atlet Pertama Integrasi');
        $secondAtlet = $this->atletProfile($secondUser, $cabor, 'Atlet Kedua Integrasi');
        $prestasi = $this->prestasi(
            $cabor,
            'prestasi/integrasi/milik-atlet-kedua.pdf',
            $secondAtlet,
        );

        $this->actingAs($firstUser)
            ->get(route('atlet.prestasi.bukti', $prestasi))
            ->assertNotFound();
    }

    public function test_unpublished_or_incompletely_published_media_is_not_publicly_served(): void
    {
        $draftPath = 'publikasi/integrasi/draft.jpg';
        $missingTimestampPath = 'publikasi/integrasi/tanpa-tanggal.jpg';
        Storage::disk('local')->put($draftPath, 'draft media');
        Storage::disk('local')->put($missingTimestampPath, 'not fully published media');

        $draft = Publikasi::create([
            'judul' => 'Dokumentasi Draft Integrasi',
            'slug' => 'dokumentasi-draft-integrasi',
            'jenis' => Publikasi::JENIS_FOTO,
            'media_path' => $draftPath,
            'media_original_name' => 'draft.jpg',
            'status' => Publikasi::STATUS_DRAFT,
        ]);

        $publishedWithoutTimestamp = Publikasi::create([
            'judul' => 'Dokumentasi Belum Lengkap Integrasi',
            'slug' => 'dokumentasi-belum-lengkap-integrasi',
            'jenis' => Publikasi::JENIS_FOTO,
            'media_path' => $missingTimestampPath,
            'media_original_name' => 'tanpa-tanggal.jpg',
            'status' => Publikasi::STATUS_PUBLISHED,
            'published_at' => null,
        ]);

        $this->get(route('public.publikasi.media', $draft->slug))->assertNotFound();
        $this->get(route('public.publikasi.media', $publishedWithoutTimestamp->slug))->assertNotFound();
    }

    public function test_only_active_users_can_continue_using_authenticated_panels(): void
    {
        $inactiveAdmin = $this->userWithRole('admin_koni');
        $inactiveAdmin->forceFill(['is_active' => false])->save();

        $this->actingAs($inactiveAdmin)
            ->get(route('dashboard.admin-koni'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
