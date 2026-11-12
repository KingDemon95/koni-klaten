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

class SuratWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin_koni', 'staff_koni', 'admin_cabor', 'atlet', 'pelatih', 'atlet_pelatih'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Storage::fake('local');
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

    private function cabor(string $name): CabangOlahraga
    {
        return CabangOlahraga::create(['nama' => $name, 'status' => 'aktif']);
    }

    private function createAndSubmitSurat(User $adminCabor, string $perihal = 'Permohonan rekomendasi kegiatan'): Surat
    {
        $this->actingAs($adminCabor)
            ->post(route('cabor.surat.store'), [
                'perihal' => $perihal,
                'jenis_surat' => 'permohonan',
                'nomor_surat' => '001/KONI-UJI/XI/2026',
                'tanggal_surat' => now()->toDateString(),
                'ringkasan' => 'Permohonan dukungan kegiatan cabang olahraga.',
                'file' => UploadedFile::fake()->create('surat-permohonan.pdf', 20, 'application/pdf'),
            ])
            ->assertSessionHasNoErrors();

        $surat = Surat::query()->where('perihal', $perihal)->firstOrFail();
        $this->assertSame(Surat::STATUS_DRAFT, $surat->status);

        $this->actingAs($adminCabor)
            ->post(route('cabor.surat.submit', $surat))
            ->assertRedirect(route('cabor.surat.show', $surat));

        $surat->refresh();
        $this->assertSame(Surat::STATUS_DIAJUKAN, $surat->status);

        return $surat;
    }

    public function test_admin_cabor_staff_and_admin_koni_follow_the_required_letter_route(): void
    {
        $cabor = $this->cabor('Bulutangkis Surat Uji');
        $adminCabor = $this->userWithRole('admin_cabor', $cabor);
        $staff = $this->userWithRole('staff_koni');
        $admin = $this->userWithRole('admin_koni');

        $surat = $this->createAndSubmitSurat($adminCabor);

        // Admin KONI belum boleh melihat surat sebelum Staff meneruskannya.
        $this->actingAs($admin)
            ->get(route('admin.persuratan.show', $surat))
            ->assertNotFound();

        $this->actingAs($staff)
            ->get(route('staff.surat.index'))
            ->assertOk()
            ->assertSee($surat->perihal);

        $this->actingAs($staff)
            ->post(route('staff.surat.teruskan', $surat), [
                'catatan_staff' => 'Dokumen telah diperiksa dan diteruskan.',
            ])
            ->assertRedirect(route('staff.surat.show', $surat));

        $this->assertDatabaseHas('surats', [
            'id' => $surat->id,
            'status' => Surat::STATUS_DITERUSKAN,
            'staff_id' => $staff->id,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.persuratan.index'))
            ->assertOk()
            ->assertSee($surat->perihal);

        $this->actingAs($admin)
            ->post(route('admin.persuratan.keputusan', $surat), [
                'keputusan_admin' => 'diterima',
                'catatan_admin' => 'Permohonan disetujui.',
            ])
            ->assertRedirect(route('admin.persuratan.show', $surat));

        $this->assertDatabaseHas('surats', [
            'id' => $surat->id,
            'status' => Surat::STATUS_MENUNGGU_PENYAMPAIAN,
            'keputusan_admin' => 'diterima',
        ]);

        // Admin Cabor belum boleh melihat isi keputusan sebelum Staff menyampaikan hasil.
        $this->actingAs($adminCabor)
            ->get(route('cabor.surat.show', $surat))
            ->assertOk()
            ->assertDontSee('Permohonan disetujui.')
            ->assertDontSee('Diterima');

        $this->actingAs($staff)
            ->post(route('staff.surat.sampaikan', $surat))
            ->assertRedirect(route('staff.surat.show', $surat));

        $surat->refresh();
        $this->assertSame(Surat::STATUS_DITERIMA, $surat->status);
        $this->assertNotNull($surat->disampaikan_at);

        $this->actingAs($adminCabor)
            ->get(route('cabor.surat.show', $surat))
            ->assertOk()
            ->assertSee('Permohonan disetujui.')
            ->assertSee('Diterima');

        $this->assertDatabaseCount('surat_activities', 5);
    }

    public function test_admin_revision_must_be_returned_by_staff_before_admin_cabor_can_see_it(): void
    {
        $cabor = $this->cabor('Atletik Surat Revisi Uji');
        $adminCabor = $this->userWithRole('admin_cabor', $cabor);
        $staff = $this->userWithRole('staff_koni');
        $admin = $this->userWithRole('admin_koni');
        $surat = $this->createAndSubmitSurat($adminCabor, 'Permohonan yang perlu revisi');

        $this->actingAs($staff)
            ->post(route('staff.surat.teruskan', $surat), [])
            ->assertRedirect(route('staff.surat.show', $surat));

        $this->actingAs($admin)
            ->post(route('admin.persuratan.keputusan', $surat), [
                'keputusan_admin' => 'revisi',
                'catatan_admin' => 'Mohon lampirkan surat pengantar resmi.',
            ])
            ->assertRedirect(route('admin.persuratan.show', $surat));

        $surat->refresh();
        $this->assertSame(Surat::STATUS_PERLU_REVISI_STAFF, $surat->status);

        $this->actingAs($adminCabor)
            ->get(route('cabor.surat.show', $surat))
            ->assertNotFound();

        $this->actingAs($staff)
            ->post(route('staff.surat.revisi', $surat), [
                'catatan_staff' => 'Admin KONI meminta surat pengantar resmi. Mohon dilengkapi.',
            ])
            ->assertRedirect(route('staff.surat.show', $surat));

        $this->assertDatabaseHas('surats', [
            'id' => $surat->id,
            'status' => Surat::STATUS_REVISI,
        ]);

        $this->actingAs($adminCabor)
            ->get(route('cabor.surat.show', $surat))
            ->assertOk()
            ->assertSee('Mohon lampirkan surat pengantar resmi.')
            ->assertSee('Mohon dilengkapi.');
    }

    public function test_staff_cannot_see_or_forward_an_unsubmitted_draft(): void
    {
        $cabor = $this->cabor('Renang Surat Draft Uji');
        $adminCabor = $this->userWithRole('admin_cabor', $cabor);
        $staff = $this->userWithRole('staff_koni');

        $this->actingAs($adminCabor)
            ->post(route('cabor.surat.store'), [
                'perihal' => 'Draft surat yang belum diajukan',
                'jenis_surat' => 'permohonan',
                'tanggal_surat' => now()->toDateString(),
                'file' => UploadedFile::fake()->create('draft.pdf', 10, 'application/pdf'),
            ]);

        $draft = Surat::query()->where('perihal', 'Draft surat yang belum diajukan')->firstOrFail();

        $this->actingAs($staff)
            ->get(route('staff.surat.show', $draft))
            ->assertNotFound();

        $this->actingAs($staff)
            ->post(route('staff.surat.teruskan', $draft))
            ->assertNotFound();
    }

    public function test_admin_cabor_cannot_access_letters_from_another_branch(): void
    {
        $ownBranch = $this->cabor('Karate Surat Sendiri');
        $otherBranch = $this->cabor('Panahan Surat Lain');
        $adminCabor = $this->userWithRole('admin_cabor', $ownBranch);
        $creator = $this->userWithRole('admin_cabor', $otherBranch);
        $surat = $this->createAndSubmitSurat($creator, 'Surat milik cabang lain');

        $this->actingAs($adminCabor)
            ->get(route('cabor.surat.show', $surat))
            ->assertNotFound();
    }

    public function test_staff_and_admin_can_manage_templates_while_admin_cabor_only_sees_active_templates(): void
    {
        $cabor = $this->cabor('Voli Template Uji');
        $adminCabor = $this->userWithRole('admin_cabor', $cabor);
        $staff = $this->userWithRole('staff_koni');
        $admin = $this->userWithRole('admin_koni');

        $active = TemplateSurat::create([
            'judul' => 'Template Surat Aktif',
            'deskripsi' => 'Template yang boleh diunduh.',
            'file_path' => 'template-surat/aktif.pdf',
            'file_original_name' => 'aktif.pdf',
            'uploaded_by' => $staff->id,
            'is_active' => true,
        ]);
        $inactive = TemplateSurat::create([
            'judul' => 'Template Surat Nonaktif',
            'deskripsi' => 'Template arsip.',
            'file_path' => 'template-surat/nonaktif.pdf',
            'file_original_name' => 'nonaktif.pdf',
            'uploaded_by' => $admin->id,
            'is_active' => false,
        ]);
        Storage::disk('local')->put($active->file_path, 'active');
        Storage::disk('local')->put($inactive->file_path, 'inactive');

        $this->actingAs($adminCabor)
            ->get(route('cabor.templates.index'))
            ->assertOk()
            ->assertSee('Template Surat Aktif')
            ->assertDontSee('Template Surat Nonaktif');

        $this->actingAs($staff)
            ->get(route('staff.templates.index'))
            ->assertOk()
            ->assertSee('Template Surat Aktif')
            ->assertSee('Template Surat Nonaktif');

        $this->actingAs($admin)
            ->post(route('admin.templates.store'), [
                'judul' => 'Template Baru dari Admin',
                'deskripsi' => 'Format surat baru.',
                'file' => UploadedFile::fake()->create('template-baru.pdf', 20, 'application/pdf'),
            ])
            ->assertRedirect(route('admin.templates.index'));

        $this->assertDatabaseHas('template_surats', [
            'judul' => 'Template Baru dari Admin',
            'uploaded_by' => $admin->id,
            'is_active' => true,
        ]);
    }
}
