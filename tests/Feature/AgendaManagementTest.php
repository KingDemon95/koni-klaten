<?php

namespace Tests\Feature;

use App\Models\Agenda;
use App\Models\CabangOlahraga;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AgendaManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin_koni', 'staff_koni', 'admin_cabor', 'atlet', 'pelatih', 'atlet_pelatih'] as $role) {
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

    private function cabor(string $nama): CabangOlahraga
    {
        return CabangOlahraga::create(['nama' => $nama, 'status' => 'aktif']);
    }

    private function agenda(CabangOlahraga $cabor, string $status = Agenda::STATUS_DIAJUKAN): Agenda
    {
        return Agenda::create([
            'cabang_olahraga_id' => $cabor->id,
            'judul' => 'Kejuaraan Uji ' . uniqid(),
            'slug' => 'kejuaraan-uji-' . uniqid(),
            'jenis' => 'kejuaraan',
            'tanggal_mulai' => now()->addDays(7)->toDateString(),
            'lokasi' => 'GOR Klaten',
            'status' => $status,
        ]);
    }

    public function test_admin_cabor_can_create_and_submit_agenda_for_own_branch(): void
    {
        $cabor = $this->cabor('Bulutangkis Uji');
        $adminCabor = $this->userWithRole('admin_cabor', $cabor);

        $this->actingAs($adminCabor)
            ->post(route('cabor.agenda.store'), [
                'judul' => 'Kejuaraan Bulutangkis Klaten',
                'jenis' => 'kejuaraan',
                'tanggal_mulai' => now()->addDays(10)->toDateString(),
                'lokasi' => 'GOR Klaten',
                'deskripsi' => 'Kejuaraan tingkat kabupaten.',
            ])
            ->assertRedirect(route('cabor.agenda.index'));

        $agenda = Agenda::query()->where('judul', 'Kejuaraan Bulutangkis Klaten')->firstOrFail();

        $this->assertSame($cabor->id, $agenda->cabang_olahraga_id);
        $this->assertSame(Agenda::STATUS_DRAFT, $agenda->status);

        $this->actingAs($adminCabor)
            ->post(route('cabor.agenda.submit', $agenda))
            ->assertRedirect(route('cabor.agenda.index'));

        $this->assertDatabaseHas('agendas', [
            'id' => $agenda->id,
            'status' => Agenda::STATUS_DIAJUKAN,
        ]);
    }

    public function test_admin_cabor_cannot_access_another_branch_agenda(): void
    {
        $ownCabor = $this->cabor('Renang Uji');
        $otherCabor = $this->cabor('Atletik Uji');
        $adminCabor = $this->userWithRole('admin_cabor', $ownCabor);
        $agenda = $this->agenda($otherCabor);

        $this->actingAs($adminCabor)
            ->get(route('cabor.agenda.edit', $agenda))
            ->assertNotFound();
    }

    public function test_staff_can_create_and_directly_publish_agenda(): void
    {
        $staff = $this->userWithRole('staff_koni');
        $cabor = $this->cabor('Voli Uji');

        $this->actingAs($staff)
            ->post(route('staff.agenda.store'), [
                'judul' => 'Turnamen Voli Klaten',
                'jenis' => 'kejuaraan',
                'cabang_olahraga_id' => $cabor->id,
                'tanggal_mulai' => now()->addDays(12)->toDateString(),
                'lokasi' => 'Lapangan Utama Klaten',
            ])
            ->assertRedirect(route('staff.agenda.index'));

        $agenda = Agenda::query()->where('judul', 'Turnamen Voli Klaten')->firstOrFail();

        $this->actingAs($staff)
            ->post(route('staff.agenda.publish', $agenda))
            ->assertRedirect(route('staff.agenda.index'));

        $this->assertDatabaseHas('agendas', [
            'id' => $agenda->id,
            'status' => Agenda::STATUS_PUBLISHED,
            'published_by' => $staff->id,
        ]);

        $this->get(route('public.agenda.show', $agenda->slug))->assertOk()->assertSee('Turnamen Voli Klaten');
    }

    public function test_staff_can_send_revision_note_to_admin_cabor(): void
    {
        $staff = $this->userWithRole('staff_koni');
        $cabor = $this->cabor('Taekwondo Uji');
        $agenda = $this->agenda($cabor);

        $this->actingAs($staff)
            ->post(route('staff.agenda.revisi', $agenda), [
                'catatan_revisi' => 'Mohon lengkapi lokasi dan jadwal pertandingan.',
            ])
            ->assertRedirect(route('staff.agenda.index'));

        $this->assertDatabaseHas('agendas', [
            'id' => $agenda->id,
            'status' => Agenda::STATUS_REVISI,
            'catatan_revisi' => 'Mohon lengkapi lokasi dan jadwal pertandingan.',
        ]);
    }

    public function test_admin_koni_can_publish_agenda(): void
    {
        $admin = $this->userWithRole('admin_koni');
        $cabor = $this->cabor('Karate Uji');
        $agenda = $this->agenda($cabor, Agenda::STATUS_DRAFT);

        $this->actingAs($admin)
            ->post(route('admin.agenda.publish', $agenda))
            ->assertRedirect(route('admin.agenda.index'));

        $this->assertDatabaseHas('agendas', [
            'id' => $agenda->id,
            'status' => Agenda::STATUS_PUBLISHED,
            'published_by' => $admin->id,
        ]);
    }

    public function test_public_agenda_pages_only_show_published_items(): void
    {
        $cabor = $this->cabor('Panahan Uji');
        $published = $this->agenda($cabor, Agenda::STATUS_PUBLISHED);
        $draft = $this->agenda($cabor, Agenda::STATUS_DRAFT);

        $this->get(route('public.agenda.index'))
            ->assertOk()
            ->assertSee($published->judul)
            ->assertDontSee($draft->judul);

        $this->get(route('public.agenda.show', $draft->slug))->assertNotFound();
    }
}
