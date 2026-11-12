<?php

namespace Tests\Feature;

use App\Models\Agenda;
use App\Models\Atlet;
use App\Models\CabangOlahraga;
use App\Models\IzinLatihan;
use App\Models\Prestasi;
use App\Models\Surat;
use App\Models\User;
use App\Notifications\WorkflowNotification;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WorkflowNotificationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        Storage::fake('local');
        Storage::fake('public');
    }

    private function user(string $role, ?CabangOlahraga $cabor = null, array $attributes = []): User
    {
        $user = User::factory()->create($attributes + [
            'cabang_olahraga_id' => $cabor?->id,
            'is_active' => true,
        ]);
        $user->assignRole($role);

        return $user;
    }

    private function cabor(string $name = 'Sepak Bola'): CabangOlahraga
    {
        return CabangOlahraga::create(['nama' => $name, 'status' => 'aktif']);
    }

    private function atlet(CabangOlahraga $cabor, ?string $email = null): array
    {
        $user = $this->user('atlet', null, array_filter([
            'name' => 'Atlet Notifikasi',
            'email' => $email,
        ]));
        $profile = Atlet::create([
            'cabang_olahraga_id' => $cabor->id,
            'user_id' => $user->id,
            'nama_lengkap' => 'Atlet Notifikasi',
            'jenis_kelamin' => 'L',
            'email' => $user->email,
            'status' => 'aktif',
        ]);

        return [$user, $profile];
    }

    private function adminCabor(CabangOlahraga $cabor): User
    {
        return $this->user('admin_cabor', $cabor, [
            'name' => 'Admin Cabor Notifikasi',
            'email' => 'admin-'.str_replace(' ', '-', strtolower($cabor->nama)).'@example.test',
        ]);
    }

    public function test_authenticated_user_can_open_and_mark_only_their_own_notifications_read(): void
    {
        $owner = $this->user('atlet');
        $other = $this->user('pelatih');
        $owner->notify(new WorkflowNotification(
            'Prestasi diperiksa',
            'Prestasi Anda perlu diperbaiki.',
            '/dashboard/atlet',
            'prestasi',
        ));

        $notification = $owner->notifications()->firstOrFail();

        $this->actingAs($owner)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSeeText('Prestasi diperiksa')
            ->assertSeeText('Prestasi Anda perlu diperbaiki.');

        $this->actingAs($other)
            ->post(route('notifications.open', $notification->id))
            ->assertNotFound();

        $this->actingAs($owner)
            ->post(route('notifications.open', $notification->id))
            ->assertRedirect('/dashboard/atlet');

        $this->assertNotNull($notification->fresh()->read_at);

        $owner->notify(new WorkflowNotification('Pemberitahuan 2', 'Pesan kedua.', '/dashboard/atlet'));
        $owner->notify(new WorkflowNotification('Pemberitahuan 3', 'Pesan ketiga.', '/dashboard/atlet'));

        $this->actingAs($owner)
            ->post(route('notifications.read-all'))
            ->assertRedirect(route('notifications.index'));

        $this->assertSame(0, $owner->unreadNotifications()->count());
    }

    public function test_achievement_submission_and_review_notify_cabor_admin_and_owner(): void
    {
        $cabor = $this->cabor();
        $admin = $this->adminCabor($cabor);
        [$atletUser] = $this->atlet($cabor, 'atlet-notifikasi@example.test');

        $this->actingAs($atletUser)->post(route('atlet.prestasi.store'), [
            'nama_kejuaraan' => 'Kejuaraan Notifikasi',
            'tingkat' => 'kabupaten',
            'hasil' => 'Juara 1',
            'tanggal_prestasi' => now()->subDay()->toDateString(),
            'bukti' => UploadedFile::fake()->create('bukti-prestasi.pdf', 100, 'application/pdf'),
        ])->assertRedirect(route('atlet.prestasi.index'));

        $this->assertSame(1, $admin->unreadNotifications()->count());
        $prestasi = Prestasi::query()->where('nama_kejuaraan', 'Kejuaraan Notifikasi')->firstOrFail();

        $this->actingAs($admin)->post(route('cabor.prestasi.review', $prestasi), [
            'status' => 'ditolak',
            'catatan_verifikasi' => 'Bukti belum terbaca dengan jelas.',
        ])->assertRedirect(route('cabor.prestasi.index'));

        $this->assertSame(1, $atletUser->unreadNotifications()->count());
        $this->assertStringContainsString(
            'Bukti belum terbaca dengan jelas.',
            $atletUser->unreadNotifications()->firstOrFail()->data['message'],
        );
    }

    public function test_training_permission_submission_and_decision_notify_both_sides(): void
    {
        $cabor = $this->cabor('Bola Voli');
        $admin = $this->adminCabor($cabor);
        [$atletUser] = $this->atlet($cabor, 'atlet-izin@example.test');

        $this->actingAs($atletUser)->post(route('atlet.izin-latihan.store'), [
            'tujuan' => 'Pemusatan latihan kabupaten',
            'keperluan' => 'Mengikuti latihan resmi.',
            'tanggal_mulai' => now()->addDays(3)->toDateString(),
            'file' => UploadedFile::fake()->create('izin-latihan.pdf', 100, 'application/pdf'),
        ])->assertRedirect(route('atlet.izin-latihan.index'));

        $this->assertSame(1, $admin->unreadNotifications()->count());
        $izin = IzinLatihan::query()->firstOrFail();

        $this->actingAs($admin)->post(route('cabor.izin-latihan.keputusan', $izin), [
            'status' => 'disetujui',
        ])->assertRedirect(route('cabor.izin-latihan.index'));

        $this->assertSame(1, $atletUser->unreadNotifications()->count());
        $this->assertStringContainsString(
            'telah disetujui',
            $atletUser->unreadNotifications()->firstOrFail()->data['message'],
        );
    }

    public function test_letter_workflow_notifies_the_next_responsible_role(): void
    {
        $cabor = $this->cabor('Bulutangkis');
        $adminCabor = $this->adminCabor($cabor);
        $staff = $this->user('staff_koni');
        $adminKoni = $this->user('admin_koni');

        $this->actingAs($adminCabor)->post(route('cabor.surat.store'), [
            'perihal' => 'Permohonan dukungan notifikasi',
            'jenis_surat' => 'permohonan',
            'tanggal_surat' => now()->toDateString(),
            'file' => UploadedFile::fake()->create('surat.pdf', 100, 'application/pdf'),
        ])->assertRedirect();
        $surat = Surat::query()->where('perihal', 'Permohonan dukungan notifikasi')->firstOrFail();

        $this->actingAs($adminCabor)->post(route('cabor.surat.submit', $surat))->assertRedirect();
        $this->assertSame(1, $staff->unreadNotifications()->count());
        $staff->unreadNotifications()->update(['read_at' => now()]);

        $this->actingAs($staff)->post(route('staff.surat.teruskan', $surat), [
            'catatan_staff' => 'Sudah diperiksa.',
        ])->assertRedirect();
        $this->assertSame(1, $adminKoni->unreadNotifications()->count());

        $this->actingAs($adminKoni)->post(route('admin.persuratan.keputusan', $surat), [
            'keputusan_admin' => 'diterima',
            'catatan_admin' => 'Disetujui.',
        ])->assertRedirect();
        $this->assertSame(1, $staff->unreadNotifications()->count());

        $this->actingAs($staff)->post(route('staff.surat.sampaikan', $surat))->assertRedirect();
        $this->assertSame(1, $adminCabor->unreadNotifications()->count());
    }

    public function test_agenda_submission_revision_and_publication_notify_creator_and_staff(): void
    {
        $cabor = $this->cabor('Atletik');
        $adminCabor = $this->adminCabor($cabor);
        $staff = $this->user('staff_koni');

        $this->actingAs($adminCabor)->post(route('cabor.agenda.store'), [
            'judul' => 'Agenda Notifikasi KONI',
            'jenis' => 'agenda',
            'tanggal_mulai' => now()->addDays(10)->toDateString(),
            'lokasi' => 'GOR Klaten',
        ])->assertRedirect(route('cabor.agenda.index'));
        $agenda = Agenda::query()->where('judul', 'Agenda Notifikasi KONI')->firstOrFail();

        $this->actingAs($adminCabor)->post(route('cabor.agenda.submit', $agenda))->assertRedirect();
        $this->assertSame(1, $staff->unreadNotifications()->count());

        $this->actingAs($staff)->post(route('staff.agenda.revisi', $agenda), [
            'catatan_revisi' => 'Mohon lengkapi keterangan lokasi.',
        ])->assertRedirect();
        $this->assertSame(1, $adminCabor->unreadNotifications()->count());

        $this->actingAs($adminCabor)->put(route('cabor.agenda.update', $agenda), [
            'judul' => 'Agenda Notifikasi KONI',
            'jenis' => 'agenda',
            'tanggal_mulai' => now()->addDays(10)->toDateString(),
            'lokasi' => 'GOR Klaten Utama',
        ])->assertRedirect();
        $agenda->refresh();
        $this->actingAs($adminCabor)->post(route('cabor.agenda.submit', $agenda))->assertRedirect();

        $this->actingAs($staff)->post(route('staff.agenda.publish', $agenda))->assertRedirect();
        $this->assertSame(2, $adminCabor->unreadNotifications()->count());
    }
}
