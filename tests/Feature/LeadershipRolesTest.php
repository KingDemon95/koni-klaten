<?php

namespace Tests\Feature;

use App\Models\CabangOlahraga;
use App\Models\Surat;
use App\Models\TemplateSurat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class LeadershipRolesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'admin_koni',
            'ketua_koni',
            'sekretaris_koni',
            'staff_koni',
            'admin_cabor',
            'atlet',
            'pelatih',
            'atlet_pelatih',
        ] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Storage::fake('local');
    }

    private function userWithRole(string $role, ?int $caborId = null): User
    {
        $user = User::factory()->create([
            'cabang_olahraga_id' => $caborId,
            'is_active' => true,
        ]);
        $user->assignRole($role);

        return $user;
    }

    private function cabor(string $name = 'Bulutangkis Kepengurusan'): CabangOlahraga
    {
        return CabangOlahraga::create([
            'nama' => $name,
            'status' => 'aktif',
        ]);
    }

    private function forwardedSurat(CabangOlahraga $cabor, User $creator, User $staff, string $perihal): Surat
    {
        $path = 'surat/original/'.str()->slug($perihal).'.pdf';
        Storage::disk('local')->put($path, 'dokumen surat uji');

        return Surat::create([
            'cabang_olahraga_id' => $cabor->id,
            'created_by' => $creator->id,
            'staff_id' => $staff->id,
            'perihal' => $perihal,
            'jenis_surat' => 'permohonan',
            'nomor_surat' => '001/KONI/UJI/2026',
            'tanggal_surat' => now()->toDateString(),
            'ringkasan' => 'Surat untuk pengujian persetujuan pimpinan.',
            'file_path' => $path,
            'file_original_name' => basename($path),
            'status' => Surat::STATUS_DITERUSKAN,
            'diteruskan_at' => now(),
        ]);
    }

    public function test_admin_koni_can_create_ketua_and_secretary_accounts(): void
    {
        $admin = $this->userWithRole('admin_koni');

        foreach ([
            ['Ketua Pengujian', 'ketua-uji@example.test', 'ketua_koni'],
            ['Sekretaris Pengujian', 'sekretaris-uji@example.test', 'sekretaris_koni'],
        ] as [$name, $email, $role]) {
            $this->actingAs($admin)
                ->post(route('admin.pengguna.store'), [
                    'name' => $name,
                    'email' => $email,
                    'password' => 'Password123!',
                    'password_confirmation' => 'Password123!',
                    'role' => $role,
                    'is_active' => 1,
                ])
                ->assertRedirect(route('admin.pengguna.index'));

            $created = User::query()->where('email', $email)->firstOrFail();
            $this->assertTrue($created->hasRole($role));
        }
    }

    public function test_ketua_can_manage_organization_data_but_cannot_manage_user_accounts(): void
    {
        $ketua = $this->userWithRole('ketua_koni');

        $this->actingAs($ketua)
            ->post(route('admin.cabor.store'), [
                'nama' => 'Karate Ketua KONI',
                'admin_name' => 'Admin Karate',
                'admin_email' => 'admin-karate-ketua@example.test',
                'status' => 'aktif',
            ])
            ->assertRedirect(route('admin.cabor.index'));

        $this->assertDatabaseHas('cabang_olahraga', ['nama' => 'Karate Ketua KONI']);
        $this->assertTrue(User::query()->where('email', 'admin-karate-ketua@example.test')->firstOrFail()->hasRole('admin_cabor'));

        $this->actingAs($ketua)
            ->get(route('admin.pengguna.index'))
            ->assertForbidden();

        $this->actingAs($ketua)
            ->get(route('admin.settings'))
            ->assertForbidden();
    }

    public function test_secretary_can_view_organization_data_and_manage_letter_templates_only(): void
    {
        $secretary = $this->userWithRole('sekretaris_koni');
        $cabor = $this->cabor('Panahan Sekretariat');

        $this->actingAs($secretary)
            ->get(route('dashboard.sekretaris-koni'))
            ->assertOk();

        $this->actingAs($secretary)
            ->get(route('sekretaris.cabor.index'))
            ->assertOk()
            ->assertSee('Panahan Sekretariat');

        foreach ([
            'sekretaris.atlet.index',
            'sekretaris.pelatih.index',
            'sekretaris.pengurus.index',
            'sekretaris.laporan.index',
        ] as $routeName) {
            $this->actingAs($secretary)->get(route($routeName))->assertOk();
        }

        $this->actingAs($secretary)
            ->post(route('admin.cabor.store'), [])
            ->assertForbidden();

        $this->actingAs($secretary)
            ->get(route('admin.pengguna.index'))
            ->assertForbidden();

        $this->actingAs($secretary)
            ->post(route('sekretaris.templates.store'), [
                'judul' => 'Template Undangan Rapat',
                'deskripsi' => 'Format undangan rapat pengurus.',
                'file' => UploadedFile::fake()->create('undangan.docx', 20, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
            ])
            ->assertRedirect(route('sekretaris.templates.index'));

        $template = TemplateSurat::query()->where('judul', 'Template Undangan Rapat')->firstOrFail();
        $this->assertSame($secretary->id, $template->uploaded_by);

        $this->actingAs($secretary)
            ->put(route('sekretaris.templates.update', $template), [
                'judul' => 'Template Undangan Rapat Resmi',
                'deskripsi' => 'Template resmi sekretariat.',
                'is_active' => 1,
            ])
            ->assertRedirect(route('sekretaris.templates.index'));

        $this->assertDatabaseHas('template_surats', [
            'id' => $template->id,
            'judul' => 'Template Undangan Rapat Resmi',
        ]);

        $this->actingAs($secretary)
            ->delete(route('sekretaris.templates.destroy', $template))
            ->assertRedirect(route('sekretaris.templates.index'));

        $this->assertDatabaseMissing('template_surats', ['id' => $template->id]);
    }

    public function test_secretary_and_chair_can_decide_independently_and_only_one_decision_is_recorded(): void
    {
        $cabor = $this->cabor('Atletik Persetujuan Pimpinan');
        $creator = $this->userWithRole('admin_cabor', $cabor->id);
        $staff = $this->userWithRole('staff_koni');
        $ketua = $this->userWithRole('ketua_koni');
        $secretary = $this->userWithRole('sekretaris_koni');

        $secretaryDecision = $this->forwardedSurat($cabor, $creator, $staff, 'Surat keputusan Sekretaris mandiri');

        $this->actingAs($secretary)
            ->get(route('sekretaris.surat.show', $secretaryDecision))
            ->assertOk()
            ->assertSee('Surat keputusan Sekretaris mandiri');

        $this->actingAs($secretary)
            ->post(route('sekretaris.surat.keputusan', $secretaryDecision), [
                'keputusan_admin' => 'diterima',
                'catatan_admin' => 'Disetujui oleh Sekretaris KONI.',
            ])
            ->assertRedirect(route('sekretaris.surat.show', $secretaryDecision));

        $this->assertDatabaseHas('surats', [
            'id' => $secretaryDecision->id,
            'status' => Surat::STATUS_MENUNGGU_PENYAMPAIAN,
            'keputusan_admin' => 'diterima',
            'admin_koni_id' => $secretary->id,
            'decision_by_role' => 'sekretaris_koni',
        ]);

        // Ketua tidak perlu memberi persetujuan kedua setelah Sekretaris memutuskan.
        $this->actingAs($ketua)
            ->post(route('admin.persuratan.keputusan', $secretaryDecision), [
                'keputusan_admin' => 'ditolak',
                'catatan_admin' => 'Percobaan keputusan kedua.',
            ])
            ->assertStatus(422);

        $this->actingAs($staff)
            ->post(route('staff.surat.sampaikan', $secretaryDecision))
            ->assertRedirect(route('staff.surat.show', $secretaryDecision));

        $this->assertDatabaseHas('surats', [
            'id' => $secretaryDecision->id,
            'status' => Surat::STATUS_DITERIMA,
        ]);

        $chairDecision = $this->forwardedSurat($cabor, $creator, $staff, 'Surat keputusan Ketua mandiri');

        $this->actingAs($ketua)
            ->post(route('admin.persuratan.keputusan', $chairDecision), [
                'keputusan_admin' => 'ditolak',
                'catatan_admin' => 'Dokumen belum memenuhi persyaratan.',
            ])
            ->assertRedirect(route('admin.persuratan.show', $chairDecision));

        $this->assertDatabaseHas('surats', [
            'id' => $chairDecision->id,
            'status' => Surat::STATUS_MENUNGGU_PENYAMPAIAN,
            'keputusan_admin' => 'ditolak',
            'admin_koni_id' => $ketua->id,
            'decision_by_role' => 'ketua_koni',
        ]);
    }
}
