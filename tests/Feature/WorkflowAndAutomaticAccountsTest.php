<?php

namespace Tests\Feature;

use App\Models\Atlet;
use App\Models\CabangOlahraga;
use App\Models\IzinLatihan;
use App\Models\Pelatih;
use App\Models\Pengurus;
use App\Models\Prestasi;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WorkflowAndAutomaticAccountsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        Storage::fake('local');
        Storage::fake('public');
    }

    private function adminKoni(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole('admin_koni');

        return $user;
    }

    private function cabor(string $nama = 'Sepak Bola'): CabangOlahraga
    {
        return CabangOlahraga::create([
            'nama' => $nama,
            'status' => 'aktif',
        ]);
    }

    private function adminCabor(CabangOlahraga $cabor): User
    {
        $user = User::factory()->create([
            'name' => 'Admin Cabor',
            'email' => 'admin-'.str_replace(' ', '-', strtolower($cabor->nama)).'@example.test',
            'cabang_olahraga_id' => $cabor->id,
            'is_active' => true,
        ]);
        $user->assignRole('admin_cabor');

        return $user;
    }

    private function atletAccount(CabangOlahraga $cabor, string $email = 'atlet@example.test'): array
    {
        $user = User::factory()->create([
            'name' => 'Atlet Uji',
            'email' => $email,
            'is_active' => true,
        ]);
        $user->assignRole('atlet');

        $profile = Atlet::create([
            'cabang_olahraga_id' => $cabor->id,
            'user_id' => $user->id,
            'nama_lengkap' => 'Atlet Uji',
            'jenis_kelamin' => 'L',
            'email' => $email,
            'status' => 'aktif',
        ]);

        return [$user, $profile];
    }

    private function pelatihAccount(CabangOlahraga $cabor, string $email = 'pelatih@example.test'): array
    {
        $user = User::factory()->create([
            'name' => 'Pelatih Uji',
            'email' => $email,
            'is_active' => true,
        ]);
        $user->assignRole('pelatih');

        $profile = Pelatih::create([
            'cabang_olahraga_id' => $cabor->id,
            'user_id' => $user->id,
            'nama_lengkap' => 'Pelatih Uji',
            'jenis_kelamin' => 'L',
            'email' => $email,
            'status' => 'aktif',
        ]);

        return [$user, $profile];
    }

    public function test_rejected_achievements_show_status_and_reason_to_atlet_and_pelatih(): void
    {
        $cabor = $this->cabor();
        $admin = $this->adminCabor($cabor);

        [$atletUser, $atlet] = $this->atletAccount($cabor);
        [$pelatihUser, $pelatih] = $this->pelatihAccount($cabor);

        $items = [
            [$atletUser, $atlet, 'atlet', 'atlet'],
            [$pelatihUser, $pelatih, 'pelatih', 'pelatih'],
        ];

        foreach ($items as [$user, $profile, $kind, $relation]) {
            $prestasi = Prestasi::create([
                $relation.'_id' => $profile->id,
                'cabang_olahraga_id' => $cabor->id,
                'nama_kejuaraan' => 'Prestasi '.$kind,
                'tingkat' => 'kabupaten',
                'hasil' => 'Juara 1',
                'tanggal_prestasi' => now()->subDay()->toDateString(),
                'bukti_path' => 'prestasi/'.$kind.'/bukti.pdf',
                'bukti_original_name' => 'bukti.pdf',
                'status' => 'menunggu_verifikasi',
            ]);

            $this->actingAs($admin)->post(route('cabor.prestasi.review', $prestasi), [
                'status' => 'ditolak',
                'catatan_verifikasi' => 'Bukti belum sesuai ketentuan verifikasi.',
            ])->assertRedirect(route('cabor.prestasi.index'));

            $this->assertDatabaseHas('prestasis', [
                'id' => $prestasi->id,
                'status' => 'ditolak',
                'catatan_verifikasi' => 'Bukti belum sesuai ketentuan verifikasi.',
            ]);

            $this->actingAs($user)->get(route($kind.'.prestasi.index'))
                ->assertOk()
                ->assertSeeText('Ditolak')
                ->assertSeeText('Bukti belum sesuai ketentuan verifikasi.');
        }
    }

    public function test_rejecting_achievement_without_reason_returns_visible_validation_error(): void
    {
        $cabor = $this->cabor();
        $admin = $this->adminCabor($cabor);
        [, $atlet] = $this->atletAccount($cabor);

        $prestasi = Prestasi::create([
            'atlet_id' => $atlet->id,
            'cabang_olahraga_id' => $cabor->id,
            'nama_kejuaraan' => 'Prestasi Tanpa Catatan',
            'tingkat' => 'kabupaten',
            'hasil' => 'Juara 2',
            'tanggal_prestasi' => now()->subDay()->toDateString(),
            'bukti_path' => 'prestasi/atlet/bukti.pdf',
            'bukti_original_name' => 'bukti.pdf',
            'status' => 'menunggu_verifikasi',
        ]);

        $this->actingAs($admin)->from(route('cabor.prestasi.index'))
            ->post(route('cabor.prestasi.review', $prestasi), [
                'status' => 'ditolak',
            ])
            ->assertRedirect(route('cabor.prestasi.index'))
            ->assertSessionHasErrors('catatan_verifikasi');

        $this->assertDatabaseHas('prestasis', [
            'id' => $prestasi->id,
            'status' => 'menunggu_verifikasi',
        ]);
    }

    public function test_admin_cabor_can_reject_training_permission_and_owner_sees_decision(): void
    {
        $cabor = $this->cabor();
        [$atletUser] = $this->atletAccount($cabor);
        $admin = $this->adminCabor($cabor);

        $this->actingAs($atletUser)->post(route('atlet.izin-latihan.store'), [
            'tujuan' => 'KONI Kabupaten Klaten',
            'keperluan' => 'Mengikuti pemusatan latihan',
            'tanggal_mulai' => now()->addDays(2)->toDateString(),
            'file' => UploadedFile::fake()->create('izin.pdf', 100, 'application/pdf'),
        ])->assertRedirect(route('atlet.izin-latihan.index'));

        $izin = IzinLatihan::firstOrFail();

        $this->actingAs($admin)->post(route('cabor.izin-latihan.keputusan', $izin), [
            'status' => 'ditolak',
            'catatan_admin' => 'Jadwal latihan belum dapat disetujui.',
        ])->assertRedirect(route('cabor.izin-latihan.index'));

        $this->assertDatabaseHas('izin_latihans', [
            'id' => $izin->id,
            'status' => 'ditolak',
            'catatan_admin' => 'Jadwal latihan belum dapat disetujui.',
            'diproses_oleh' => $admin->id,
        ]);

        $this->actingAs($atletUser)->get(route('atlet.izin-latihan.index'))
            ->assertOk()
            ->assertSeeText('Ditolak')
            ->assertSeeText('Jadwal latihan belum dapat disetujui.');
    }

    public function test_admin_cabor_can_approve_training_permission(): void
    {
        $cabor = $this->cabor();
        [$atletUser] = $this->atletAccount($cabor);
        $admin = $this->adminCabor($cabor);

        $this->actingAs($atletUser)->post(route('atlet.izin-latihan.store'), [
            'tujuan' => 'KONI Kabupaten Klaten',
            'keperluan' => 'Mengikuti latihan resmi',
            'tanggal_mulai' => now()->addDays(4)->toDateString(),
            'file' => UploadedFile::fake()->create('izin-disetujui.pdf', 100, 'application/pdf'),
        ])->assertRedirect(route('atlet.izin-latihan.index'));

        $izin = IzinLatihan::firstOrFail();

        $this->actingAs($admin)->post(route('cabor.izin-latihan.keputusan', $izin), [
            'status' => 'disetujui',
        ])->assertRedirect(route('cabor.izin-latihan.index'));

        $this->assertDatabaseHas('izin_latihans', [
            'id' => $izin->id,
            'status' => 'disetujui',
            'diproses_oleh' => $admin->id,
        ]);

        $this->actingAs($atletUser)->get(route('atlet.izin-latihan.index'))
            ->assertOk()
            ->assertSeeText('Disetujui');
    }

    public function test_admin_cabor_cannot_decide_permission_from_another_branch(): void
    {
        $caborA = $this->cabor('Sepak Bola');
        $caborB = $this->cabor('Bola Voli');
        [$atletUser] = $this->atletAccount($caborB, 'atlet-b@example.test');
        $adminA = $this->adminCabor($caborA);

        $this->actingAs($atletUser)->post(route('atlet.izin-latihan.store'), [
            'tujuan' => 'KONI',
            'keperluan' => 'Latihan',
            'tanggal_mulai' => now()->addDays(2)->toDateString(),
            'file' => UploadedFile::fake()->create('izin.pdf', 100, 'application/pdf'),
        ])->assertRedirect();

        $izin = IzinLatihan::firstOrFail();

        $this->actingAs($adminA)->post(route('cabor.izin-latihan.keputusan', $izin), [
            'status' => 'disetujui',
        ])->assertNotFound();
    }

    public function test_adding_cabor_automatically_creates_admin_cabor_account(): void
    {
        $this->actingAs($this->adminKoni())->post(route('admin.cabor.store'), [
            'nama' => 'Renang Uji',
            'admin_name' => 'Admin Renang',
            'admin_email' => 'admin.renang@example.test',
            'status' => 'aktif',
        ])->assertRedirect(route('admin.cabor.index'));

        $cabor = CabangOlahraga::where('nama', 'Renang Uji')->firstOrFail();
        $user = User::where('email', 'admin.renang@example.test')->firstOrFail();

        $this->assertTrue($user->hasRole('admin_cabor'));
        $this->assertSame($cabor->id, $user->cabang_olahraga_id);
        $this->assertTrue(Hash::check(config('account_defaults.passwords.admin_cabor'), $user->password));
    }

    public function test_adding_atlet_automatically_creates_and_links_atlet_account(): void
    {
        $cabor = $this->cabor();

        $this->actingAs($this->adminKoni())->post(route('admin.atlet.store'), [
            'cabang_olahraga_id' => $cabor->id,
            'nama_lengkap' => 'Atlet Otomatis',
            'jenis_kelamin' => 'L',
            'email' => 'atlet.otomatis@example.test',
            'status' => 'aktif',
        ])->assertRedirect(route('admin.atlet.index'));

        $profile = Atlet::where('email', 'atlet.otomatis@example.test')->firstOrFail();
        $user = User::where('email', 'atlet.otomatis@example.test')->firstOrFail();

        $this->assertTrue($user->hasRole('atlet'));
        $this->assertSame($user->id, $profile->user_id);
        $this->assertTrue(Hash::check(config('account_defaults.passwords.atlet'), $user->password));
    }

    public function test_admin_cabor_adding_atlet_automatically_creates_atlet_account(): void
    {
        $cabor = $this->cabor();
        $admin = $this->adminCabor($cabor);

        $this->actingAs($admin)->post(route('cabor.atlet.store'), [
            'nama_lengkap' => 'Atlet dari Admin Cabor',
            'jenis_kelamin' => 'L',
            'email' => 'atlet.cabor@example.test',
            'status' => 'aktif',
        ])->assertRedirect(route('cabor.atlet.index'));

        $profile = Atlet::where('email', 'atlet.cabor@example.test')->firstOrFail();
        $user = User::where('email', 'atlet.cabor@example.test')->firstOrFail();

        $this->assertTrue($user->hasRole('atlet'));
        $this->assertSame($cabor->id, $profile->cabang_olahraga_id);
        $this->assertSame($user->id, $profile->user_id);
    }

    public function test_admin_cabor_adding_pelatih_automatically_creates_pelatih_account(): void
    {
        $cabor = $this->cabor();
        $admin = $this->adminCabor($cabor);

        $this->actingAs($admin)->post(route('cabor.pelatih.store'), [
            'nama_lengkap' => 'Pelatih dari Admin Cabor',
            'jenis_kelamin' => 'P',
            'email' => 'pelatih.cabor@example.test',
            'status' => 'aktif',
        ])->assertRedirect(route('cabor.pelatih.index'));

        $profile = Pelatih::where('email', 'pelatih.cabor@example.test')->firstOrFail();
        $user = User::where('email', 'pelatih.cabor@example.test')->firstOrFail();

        $this->assertTrue($user->hasRole('pelatih'));
        $this->assertSame($cabor->id, $profile->cabang_olahraga_id);
        $this->assertSame($user->id, $profile->user_id);
    }

    public function test_adding_pelatih_automatically_creates_and_links_pelatih_account(): void
    {
        $cabor = $this->cabor();

        $this->actingAs($this->adminKoni())->post(route('admin.pelatih.store'), [
            'cabang_olahraga_id' => $cabor->id,
            'nama_lengkap' => 'Pelatih Otomatis',
            'jenis_kelamin' => 'P',
            'email' => 'pelatih.otomatis@example.test',
            'status' => 'aktif',
        ])->assertRedirect(route('admin.pelatih.index'));

        $profile = Pelatih::where('email', 'pelatih.otomatis@example.test')->firstOrFail();
        $user = User::where('email', 'pelatih.otomatis@example.test')->firstOrFail();

        $this->assertTrue($user->hasRole('pelatih'));
        $this->assertSame($user->id, $profile->user_id);
        $this->assertTrue(Hash::check(config('account_defaults.passwords.pelatih'), $user->password));
    }

    public function test_adding_koni_level_official_with_checkbox_automatically_creates_staff_account(): void
    {
        $this->actingAs($this->adminKoni())->post(route('admin.struktur-organisasi.store'), [
            'nama_lengkap' => 'Staff KONI Otomatis',
            'jabatan' => 'Staff Administrasi',
            'email' => 'staff.otomatis@example.test',
            'buat_akun_staff' => 1,
            'urutan' => 1,
            'status' => 'aktif',
        ])->assertRedirect(route('admin.struktur-organisasi.index'));

        $pengurus = Pengurus::where('email', 'staff.otomatis@example.test')->firstOrFail();
        $user = User::where('email', 'staff.otomatis@example.test')->firstOrFail();

        $this->assertTrue($user->hasRole('staff_koni'));
        $this->assertSame($user->id, $pengurus->user_id);
        $this->assertTrue(Hash::check(config('account_defaults.passwords.staff_koni'), $user->password));
    }
}
