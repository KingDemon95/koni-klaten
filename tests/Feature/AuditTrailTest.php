<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\CabangOlahraga;
use App\Models\Prestasi;
use App\Models\Surat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AuditTrailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin_koni', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'ketua_koni', 'guard_name' => 'web']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function admin(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole('admin_koni');

        return $user;
    }

    public function test_model_create_and_update_are_recorded_with_before_and_after_values(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        $cabor = CabangOlahraga::create([
            'nama' => 'Bulutangkis Audit',
            'email' => 'rahasia@example.test',
            'status' => 'aktif',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'event' => 'created',
            'subject_type' => CabangOlahraga::class,
            'subject_id' => $cabor->id,
            'cabang_olahraga_id' => $cabor->id,
        ]);

        $cabor->update([
            'nama' => 'Bulutangkis Audit Diperbarui',
            'email' => 'email-baru@example.test',
        ]);

        $log = AuditLog::query()
            ->where('subject_type', CabangOlahraga::class)
            ->where('subject_id', $cabor->id)
            ->where('event', 'updated')
            ->latest('id')
            ->firstOrFail();

        $this->assertSame('Bulutangkis Audit', $log->old_values['nama']);
        $this->assertSame('Bulutangkis Audit Diperbarui', $log->new_values['nama']);
        $this->assertArrayNotHasKey('email', $log->old_values ?? []);
        $this->assertArrayNotHasKey('email', $log->new_values ?? []);
    }

    public function test_password_and_contact_fields_are_not_saved_in_audit_snapshots(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        $target = User::factory()->create([
            'name' => 'Akun Target Audit',
            'email' => 'target-rahasia@example.test',
        ]);

        $log = AuditLog::query()
            ->where('subject_type', User::class)
            ->where('subject_id', $target->id)
            ->where('event', 'created')
            ->firstOrFail();

        $this->assertSame('Akun Target Audit', $log->subject_label);
        $this->assertArrayNotHasKey('password', $log->new_values ?? []);
        $this->assertArrayNotHasKey('email', $log->new_values ?? []);

        $target->update(['password' => 'kata-sandi-baru-yang-rahasia']);

        $passwordLog = AuditLog::query()
            ->where('subject_type', User::class)
            ->where('subject_id', $target->id)
            ->where('event', 'password_changed')
            ->latest('id')
            ->firstOrFail();

        $this->assertArrayNotHasKey('password', $passwordLog->new_values ?? []);
    }

    public function test_approval_and_rejection_status_changes_receive_specific_event_labels(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);
        $cabor = CabangOlahraga::create(['nama' => 'Atletik Audit', 'status' => 'aktif']);

        $prestasi = Prestasi::create([
            'cabang_olahraga_id' => $cabor->id,
            'nama_kejuaraan' => 'Kejuaraan Audit KONI',
            'tingkat' => 'kabupaten',
            'hasil' => 'Juara 1',
            'tanggal_prestasi' => now()->toDateString(),
            'bukti_path' => 'prestasi/audit/bukti.pdf',
            'bukti_original_name' => 'bukti.pdf',
            'status' => 'menunggu_verifikasi',
        ]);

        $prestasi->update(['status' => 'terverifikasi']);

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'event' => 'approved',
            'subject_type' => Prestasi::class,
            'subject_id' => $prestasi->id,
        ]);

        $prestasi->update(['status' => 'ditolak']);

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'event' => 'rejected',
            'subject_type' => Prestasi::class,
            'subject_id' => $prestasi->id,
        ]);
    }

    public function test_independent_leadership_letter_decision_is_recorded_as_approval(): void
    {
        $ketua = User::factory()->create(['is_active' => true]);
        $ketua->assignRole('ketua_koni');
        $this->actingAs($ketua);
        $cabor = CabangOlahraga::create(['nama' => 'Surat Audit', 'status' => 'aktif']);

        $surat = Surat::create([
            'cabang_olahraga_id' => $cabor->id,
            'created_by' => $ketua->id,
            'perihal' => 'Permohonan penggunaan gedung',
            'jenis_surat' => 'permohonan',
            'tanggal_surat' => now()->toDateString(),
            'file_path' => 'surat/audit/permohonan.pdf',
            'file_original_name' => 'permohonan.pdf',
            'status' => 'diteruskan_admin',
        ]);

        $surat->update([
            'status' => 'menunggu_penyampaian',
            'keputusan_admin' => 'diterima',
            'decision_by_role' => 'ketua_koni',
            'admin_koni_id' => $ketua->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $ketua->id,
            'actor_role' => 'ketua_koni',
            'event' => 'approved',
            'subject_type' => Surat::class,
            'subject_id' => $surat->id,
        ]);
    }

    public function test_audit_log_model_rejects_update_and_delete_operations(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);
        CabangOlahraga::create(['nama' => 'Cabor Permanen', 'status' => 'aktif']);
        $log = AuditLog::query()->firstOrFail();

        try {
            $log->forceFill(['actor_name' => 'Nama Diubah'])->save();
            $this->fail('Audit log seharusnya tidak dapat diubah.');
        } catch (LogicException) {
            $this->assertDatabaseMissing('audit_logs', ['actor_name' => 'Nama Diubah']);
        }

        try {
            $log->delete();
            $this->fail('Audit log seharusnya tidak dapat dihapus.');
        } catch (LogicException) {
            $this->assertDatabaseHas('audit_logs', ['id' => $log->id]);
        }
    }
}
