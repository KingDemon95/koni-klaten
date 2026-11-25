<?php

namespace Tests\Feature;

use App\Models\CabangOlahraga;
use App\Models\Surat;
use App\Models\SuratMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class SuratMessagingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin_koni', 'ketua_koni', 'sekretaris_koni', 'staff_koni', 'admin_cabor', 'atlet', 'pelatih'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Storage::fake('local');
    }

    private function user(string $role, ?CabangOlahraga $cabor = null): User
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

    private function surat(CabangOlahraga $cabor, User $creator, User $staff): Surat
    {
        $path = 'surat/original/surat-pesan-uji.pdf';
        Storage::disk('local')->put($path, 'dokumen asli');

        return Surat::create([
            'cabang_olahraga_id' => $cabor->id,
            'created_by' => $creator->id,
            'staff_id' => $staff->id,
            'perihal' => 'Disposisi dan balasan surat uji',
            'jenis_surat' => 'permohonan',
            'tanggal_surat' => now()->toDateString(),
            'file_path' => $path,
            'file_original_name' => 'surat-asli.pdf',
            'status' => Surat::STATUS_MENUNGGU_PENYAMPAIAN,
            'keputusan_admin' => 'diterima',
            'admin_koni_id' => $this->user('ketua_koni')->id,
            'decision_by_role' => 'ketua_koni',
            'diteruskan_at' => now(),
            'diputuskan_at' => now(),
        ]);
    }

    public function test_staff_disposition_notifies_every_admin_of_the_receiving_cabor_and_cabor_can_reply_with_file(): void
    {
        $cabor = $this->cabor('Bulutangkis Pesan');
        $otherCabor = $this->cabor('Panahan Pesan');
        $creator = $this->user('admin_cabor', $cabor);
        $secondCaborAdmin = $this->user('admin_cabor', $cabor);
        $unrelatedCaborAdmin = $this->user('admin_cabor', $otherCabor);
        $staff = $this->user('staff_koni');
        $surat = $this->surat($cabor, $creator, $staff);

        $this->actingAs($staff)
            ->post(route('staff.surat.sampaikan', $surat), [
                'catatan_disposisi' => 'Mohon tindak lanjuti hasil keputusan ini.',
                'lampiran_disposisi' => UploadedFile::fake()->create('disposisi.pdf', 20, 'application/pdf'),
            ])
            ->assertRedirect(route('staff.surat.show', $surat));

        $surat->refresh();
        $this->assertSame(Surat::STATUS_DITERIMA, $surat->status);
        $this->assertDatabaseHas('surat_messages', [
            'surat_id' => $surat->id,
            'sender_id' => $staff->id,
            'sender_role' => 'staff_koni',
            'message_type' => SuratMessage::TYPE_DISPOSISI,
            'body' => 'Mohon tindak lanjuti hasil keputusan ini.',
            'attachment_original_name' => 'disposisi.pdf',
        ]);

        $this->assertSame(1, $creator->unreadNotifications()->count());
        $this->assertSame(1, $secondCaborAdmin->unreadNotifications()->count());
        $this->assertSame(0, $unrelatedCaborAdmin->unreadNotifications()->count());

        $this->actingAs($secondCaborAdmin)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSeeText('Disposisi surat dari Staff KONI')
            ->assertSeeText('sudah disampaikan');

        $notification = $secondCaborAdmin->unreadNotifications()->firstOrFail();
        $this->assertSame('/pesan', $notification->data['url']);
        $this->actingAs($secondCaborAdmin)
            ->post(route('notifications.open', $notification->id))
            ->assertRedirect(route('messages.index'));

        $this->actingAs($secondCaborAdmin)
            ->get(route('messages.index'))
            ->assertOk()
            ->assertSeeText('Pesan Persuratan')
            ->assertSeeText('Mohon tindak lanjuti hasil keputusan ini.');

        $this->actingAs($secondCaborAdmin)
            ->get(route('cabor.surat.show', $surat))
            ->assertOk()
            ->assertSeeText('Pesan dan Disposisi')
            ->assertSeeText('Mohon tindak lanjuti hasil keputusan ini.');

        $this->actingAs($secondCaborAdmin)
            ->post(route('cabor.surat.pesan.store', $surat), [
                'body' => 'Cabor sudah menerima disposisi dan melampirkan surat balasan.',
                'attachment' => UploadedFile::fake()->create('surat-balasan.pdf', 30, 'application/pdf'),
            ])
            ->assertRedirect(route('cabor.surat.show', $surat));

        $message = SuratMessage::query()
            ->where('surat_id', $surat->id)
            ->where('message_type', SuratMessage::TYPE_BALASAN)
            ->firstOrFail();

        $this->assertSame($secondCaborAdmin->id, $message->sender_id);
        $this->assertSame('admin_cabor', $message->sender_role);
        $this->assertSame('Cabor sudah menerima disposisi dan melampirkan surat balasan.', $message->body);
        $this->assertSame(1, $staff->unreadNotifications()->count());

        $this->actingAs($staff)
            ->get(route('staff.surat.pesan.file', [$surat, $message]))
            ->assertOk();

        $this->actingAs($staff)
            ->get(route('messages.index'))
            ->assertOk()
            ->assertSeeText('Cabor sudah menerima disposisi dan melampirkan surat balasan.');

        $this->actingAs($staff)
            ->get(route('staff.surat.show', $surat))
            ->assertOk()
            ->assertSeeText('Cabor sudah menerima disposisi dan melampirkan surat balasan.')
            ->assertSeeText('surat-balasan.pdf');
    }

    public function test_cabor_cannot_read_or_download_messages_from_another_branch(): void
    {
        $ownerCabor = $this->cabor('Voli Pemilik Pesan');
        $otherCabor = $this->cabor('Atletik Bukan Pemilik Pesan');
        $creator = $this->user('admin_cabor', $ownerCabor);
        $staff = $this->user('staff_koni');
        $otherAdmin = $this->user('admin_cabor', $otherCabor);
        $surat = $this->surat($ownerCabor, $creator, $staff);

        $message = $surat->messages()->create([
            'sender_id' => $staff->id,
            'sender_role' => 'staff_koni',
            'message_type' => SuratMessage::TYPE_DISPOSISI,
            'body' => 'Pesan rahasia untuk cabang pemilik.',
        ]);

        $this->actingAs($otherAdmin)
            ->get(route('cabor.surat.show', $surat))
            ->assertNotFound();

        $this->actingAs($otherAdmin)
            ->get(route('cabor.surat.pesan.file', [$surat, $message]))
            ->assertNotFound();
    }
}
