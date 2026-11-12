<?php

namespace Tests\Feature;

use App\Models\Atlet;
use App\Models\CabangOlahraga;
use App\Models\Pelatih;
use App\Models\TemplateSurat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PortalAtletPelatihTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin_koni', 'admin_cabor', 'staff_koni', 'atlet', 'pelatih'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Storage::fake('local');
        Storage::fake('public');
    }

    private function cabor(string $nama = 'Bulutangkis'): CabangOlahraga
    {
        return CabangOlahraga::create(['nama' => $nama, 'status' => 'aktif']);
    }

    private function atletAccount(CabangOlahraga $cabor, string $email = 'atlet@example.test'): array
    {
        $user = User::factory()->create(['name' => 'Atlet Uji', 'email' => $email, 'is_active' => true]);
        $user->assignRole('atlet');
        $atlet = Atlet::create([
            'cabang_olahraga_id' => $cabor->id,
            'user_id' => $user->id,
            'nama_lengkap' => 'Atlet Uji',
            'jenis_kelamin' => 'L',
            'email' => $email,
            'status' => 'aktif',
        ]);

        return [$user, $atlet];
    }

    private function pelatihAccount(CabangOlahraga $cabor, string $email = 'pelatih@example.test'): array
    {
        $user = User::factory()->create(['name' => 'Pelatih Uji', 'email' => $email, 'is_active' => true]);
        $user->assignRole('pelatih');
        $pelatih = Pelatih::create([
            'cabang_olahraga_id' => $cabor->id,
            'user_id' => $user->id,
            'nama_lengkap' => 'Pelatih Uji',
            'jenis_kelamin' => 'L',
            'status' => 'aktif',
        ]);

        return [$user, $pelatih];
    }

    private function adminCabor(CabangOlahraga $cabor, string $email = 'admin-cabor@example.test'): User
    {
        $user = User::factory()->create([
            'name' => 'Admin Cabor',
            'email' => $email,
            'cabang_olahraga_id' => $cabor->id,
            'is_active' => true,
        ]);
        $user->assignRole('admin_cabor');

        return $user;
    }

    public function test_atlet_can_update_only_own_biodata_and_profile_photo(): void
    {
        $cabor = $this->cabor();
        [$user, $atlet] = $this->atletAccount($cabor);

        $this->actingAs($user)->put(route('atlet.profil.update'), [
            'nama_lengkap' => 'Nama Atlet Baru',
            'jenis_kelamin' => 'P',
            'tempat_lahir' => 'Klaten',
            'tanggal_lahir' => '2002-04-10',
            'alamat' => 'Klaten, Jawa Tengah',
            'no_telepon' => '08123456789',
            'email' => 'kontak-atlet@example.test',
            'cabang_olahraga_id' => 99999,
            'status' => 'nonaktif',
            'foto' => UploadedFile::fake()->createWithContent('atlet.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a0YkAAAAASUVORK5CYII=')),
        ])->assertRedirect(route('atlet.profil'));

        $this->assertDatabaseHas('atlets', [
            'id' => $atlet->id,
            'nama_lengkap' => 'Nama Atlet Baru',
            'cabang_olahraga_id' => $cabor->id,
            'status' => 'aktif',
            'email' => 'kontak-atlet@example.test',
        ]);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Nama Atlet Baru']);
        $this->assertNotNull($atlet->fresh()->foto_path);
    }

    public function test_pelatih_can_update_own_biodata_and_coaching_information(): void
    {
        $cabor = $this->cabor();
        [$user, $pelatih] = $this->pelatihAccount($cabor);

        $this->actingAs($user)->put(route('pelatih.profil.update'), [
            'nama_lengkap' => 'Pelatih Diperbarui',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Klaten',
            'tanggal_lahir' => '1985-02-12',
            'alamat' => 'Alamat baru',
            'no_telepon' => '0811111111',
            'email' => 'pelatih.kontak@example.test',
            'pendidikan_terakhir' => 'S1 Kepelatihan',
            'spesialisasi' => 'Bulutangkis',
            'sertifikasi' => 'Level Nasional',
            'pengalaman_tahun' => 12,
        ])->assertRedirect(route('pelatih.profil'));

        $this->assertDatabaseHas('pelatihs', [
            'id' => $pelatih->id,
            'nama_lengkap' => 'Pelatih Diperbarui',
            'cabang_olahraga_id' => $cabor->id,
            'pendidikan_terakhir' => 'S1 Kepelatihan',
            'pengalaman_tahun' => 12,
        ]);
    }

    public function test_achievement_is_private_until_verified_by_own_cabor_admin(): void
    {
        $cabor = $this->cabor();
        [$atletUser] = $this->atletAccount($cabor);
        $admin = $this->adminCabor($cabor);

        $this->actingAs($atletUser)->post(route('atlet.prestasi.store'), [
            'nama_kejuaraan' => 'Kejuaraan Uji Klaten',
            'tingkat' => 'kabupaten',
            'kategori' => 'Tunggal Putra',
            'hasil' => 'Juara 1',
            'penyelenggara' => 'Pengkab',
            'tanggal_prestasi' => now()->subDays(10)->toDateString(),
            'keterangan' => 'Hasil kejuaraan daerah',
            'bukti' => UploadedFile::fake()->create('sertifikat.pdf', 120, 'application/pdf'),
        ])->assertRedirect(route('atlet.prestasi.index'));

        $this->assertDatabaseHas('prestasis', [
            'nama_kejuaraan' => 'Kejuaraan Uji Klaten',
            'status' => 'menunggu_verifikasi',
            'cabang_olahraga_id' => $cabor->id,
        ]);

        $this->get(route('public.cabor.show', $cabor))->assertOk()->assertDontSeeText('Kejuaraan Uji Klaten');

        $prestasi = \App\Models\Prestasi::firstOrFail();
        $this->actingAs($admin)->post(route('cabor.prestasi.review', $prestasi), [
            'status' => 'terverifikasi',
        ])->assertRedirect(route('cabor.prestasi.index'));

        $this->assertDatabaseHas('prestasis', [
            'id' => $prestasi->id,
            'status' => 'terverifikasi',
            'verified_by' => $admin->id,
        ]);
        $this->get(route('public.cabor.show', $cabor))->assertOk()->assertSeeText('Kejuaraan Uji Klaten')->assertSeeText('Juara 1');
    }


    public function test_coach_achievement_is_also_verified_by_admin_cabor_before_publication(): void
    {
        $cabor = $this->cabor();
        [$coachUser] = $this->pelatihAccount($cabor);
        $admin = $this->adminCabor($cabor);

        $this->actingAs($coachUser)->post(route('pelatih.prestasi.store'), [
            'nama_kejuaraan' => 'Penghargaan Pelatih Daerah',
            'tingkat' => 'provinsi',
            'hasil' => 'Pelatih Terbaik',
            'tanggal_prestasi' => now()->subDays(8)->toDateString(),
            'bukti' => UploadedFile::fake()->create('penghargaan.pdf', 120, 'application/pdf'),
        ])->assertRedirect(route('pelatih.prestasi.index'));

        $prestasi = \App\Models\Prestasi::firstOrFail();
        $this->assertSame(null, $prestasi->atlet_id);
        $this->assertNotNull($prestasi->pelatih_id);
        $this->get(route('public.cabor.show', $cabor))->assertOk()->assertDontSeeText('Penghargaan Pelatih Daerah');

        $this->actingAs($admin)->post(route('cabor.prestasi.review', $prestasi), [
            'status' => 'terverifikasi',
        ])->assertRedirect(route('cabor.prestasi.index'));

        $this->get(route('public.cabor.show', $cabor))->assertOk()->assertSeeText('Penghargaan Pelatih Daerah')->assertSeeText('Pelatih Uji');
    }

    public function test_admin_cabor_cannot_review_achievement_from_another_branch(): void
    {
        $caborA = $this->cabor('Bulutangkis');
        $caborB = $this->cabor('Atletik');
        [$atletUser] = $this->atletAccount($caborB, 'atlet-b@example.test');
        $adminA = $this->adminCabor($caborA);

        $this->actingAs($atletUser)->post(route('atlet.prestasi.store'), [
            'nama_kejuaraan' => 'Kejuaraan Atletik',
            'tingkat' => 'provinsi',
            'hasil' => 'Juara 2',
            'tanggal_prestasi' => now()->subDays(3)->toDateString(),
            'bukti' => UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf'),
        ])->assertRedirect();

        $prestasi = \App\Models\Prestasi::firstOrFail();
        $this->actingAs($adminA)->post(route('cabor.prestasi.review', $prestasi), [
            'status' => 'terverifikasi',
        ])->assertNotFound();
    }

    public function test_atlet_and_pelatih_can_upload_private_training_permission_documents(): void
    {
        $cabor = $this->cabor();
        [$atletUser] = $this->atletAccount($cabor);
        [$pelatihUser] = $this->pelatihAccount($cabor);

        foreach ([[$atletUser, 'atlet'], [$pelatihUser, 'pelatih']] as [$user, $kind]) {
            $route = $kind.'.izin-latihan.store';
            $index = $kind.'.izin-latihan.index';

            $this->actingAs($user)->post(route($route), [
                'tujuan' => 'KONI Kabupaten Klaten',
                'keperluan' => 'Mengikuti pemusatan latihan',
                'tanggal_mulai' => now()->addDays(3)->toDateString(),
                'tanggal_selesai' => now()->addDays(5)->toDateString(),
                'file' => UploadedFile::fake()->create('izin.pdf', 100, 'application/pdf'),
            ])->assertRedirect(route($index));

            $this->get(route($index))->assertOk()->assertSeeText('KONI Kabupaten Klaten');
        }

        $this->assertDatabaseCount('izin_latihans', 2);
        $this->actingAs($this->adminCabor($cabor))->get(route('cabor.izin-latihan.index'))
            ->assertOk()->assertSeeText('Atlet Uji')->assertSeeText('Pelatih Uji');
    }

    public function test_user_cannot_download_another_users_training_permission(): void
    {
        $cabor = $this->cabor();
        [$userA] = $this->atletAccount($cabor, 'a@example.test');
        [$userB] = $this->atletAccount($cabor, 'b@example.test');

        $this->actingAs($userB)->post(route('atlet.izin-latihan.store'), [
            'tujuan' => 'KONI',
            'keperluan' => 'Latihan',
            'tanggal_mulai' => now()->addDay()->toDateString(),
            'file' => UploadedFile::fake()->create('izin.pdf', 100, 'application/pdf'),
        ])->assertRedirect();

        $izin = \App\Models\IzinLatihan::firstOrFail();
        $this->actingAs($userA)->get(route('atlet.izin-latihan.download', $izin))->assertNotFound();
    }

    public function test_atlet_and_pelatih_only_see_active_letter_templates(): void
    {
        Storage::disk('local')->put('template-surat/izin-aktif.pdf', 'template aktif');
        Storage::disk('local')->put('template-surat/izin-nonaktif.pdf', 'template nonaktif');

        $active = TemplateSurat::create([
            'judul' => 'Template Izin Latihan Aktif',
            'file_path' => 'template-surat/izin-aktif.pdf',
            'file_original_name' => 'izin-aktif.pdf',
            'is_active' => true,
        ]);
        $inactive = TemplateSurat::create([
            'judul' => 'Template Rahasia',
            'file_path' => 'template-surat/izin-nonaktif.pdf',
            'file_original_name' => 'izin-nonaktif.pdf',
            'is_active' => false,
        ]);

        $cabor = $this->cabor();
        [$atletUser] = $this->atletAccount($cabor);
        [$pelatihUser] = $this->pelatihAccount($cabor);

        $this->actingAs($atletUser)->get(route('atlet.templates.index'))
            ->assertOk()->assertSeeText('Template Izin Latihan Aktif')->assertDontSeeText('Template Rahasia');
        $this->get(route('atlet.templates.download', $active))->assertOk();
        $this->get(route('atlet.templates.download', $inactive))->assertNotFound();

        $this->actingAs($pelatihUser)->get(route('pelatih.templates.index'))
            ->assertOk()->assertSeeText('Template Izin Latihan Aktif')->assertDontSeeText('Template Rahasia');
    }
}
