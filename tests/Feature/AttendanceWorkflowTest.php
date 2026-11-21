<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\CabangOlahraga;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;
use Tests\TestCase;

class AttendanceWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        Storage::fake('local');
        Storage::fake('public');
    }

    private function userWithRole(string $role, ?int $caborId = null): User
    {
        $user = User::factory()->create([
            'is_active' => true,
            'cabang_olahraga_id' => $caborId,
        ]);
        $user->assignRole($role);

        return $user;
    }

    private function createSession(
        User $admin,
        ?int $caborId = null,
        string $targetRole = 'all',
        ?array $targetRoles = null,
    ): AttendanceSession {
        return AttendanceSession::query()->create([
            'created_by' => $admin->id,
            'cabang_olahraga_id' => $caborId,
            'title' => 'Latihan Bersama KONI',
            'description' => 'Sesi uji absensi',
            'target_role' => $targetRole,
            'target_roles' => $targetRoles,
            'token' => Str::random(48),
            'starts_at' => now()->subMinutes(5),
            'ends_at' => now()->addHour(),
            'status' => 'open',
        ]);
    }

    private function selfieDataUrl(): string
    {
        $jpeg = '/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAUDBAQEAwUEBAQFBQUGBwwIBwcHBw8LCwkMEQ8SEhEPERETFhwXExQaFRERGCEYGh0dHx8fExciJCIeJBweHx7/2wBDAQUFBQcGBw4ICA4eFBEUHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh7/wAARCAFAAUADASIAAhEBAxEB/8QAHwAAAQUBAQEBAQEAAAAAAAAAAAECAwQFBgcICQoL/8QAtRAAAgEDAwIEAwUFBAQAAAF9AQIDAAQRBRIhMUEGE1FhByJxFDKBkaEII0KxwRVS0fAkM2JyggkKFhcYGRolJicoKSo0NTY3ODk6Q0RFRkdISUpTVFVWV1hZWmNkZWZnaGlqc3R1dnd4eXqDhIWGh4iJipKTlJWWl5iZmqKjpKWmp6ipqrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uHi4+Tl5ufo6erx8vP09fb3+Pn6/8QAHwEAAwEBAQEBAQEBAQAAAAAAAAECAwQFBgcICQoL/8QAtREAAgECBAQDBAcFBAQAAQJ3AAECAxEEBSExBhJBUQdhcRMiMoEIFEKRobHBCSMzUvAVYnLRChYkNOEl8RcYGRomJygpKjU2Nzg5OkNERUZHSElKU1RVVldYWVpjZGVmZ2hpanN0dXZ3eHl6goOEhYaHiImKkpOUlZaXmJmaoqOkpaanqKmqsrO0tba3uLm6wsPExcbHyMnK0tPU1dbX2Nna4uPk5ebn6Onq8vP09fb3+Pn6/9oADAMBAAIRAxEAPwD16iiivHPbCiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKAP//Z';

        return 'data:image/jpeg;base64,'.$jpeg;
    }

    public function test_admin_can_create_session_and_view_qr_code(): void
    {
        $admin = $this->userWithRole('admin_koni');
        $cabor = CabangOlahraga::query()->create(['nama' => 'Bulutangkis Absensi', 'status' => 'aktif']);

        $response = $this->actingAs($admin)->post(route('admin.absensi.store'), [
            'title' => 'Latihan Pagi',
            'description' => 'Latihan rutin',
            'cabang_olahraga_id' => $cabor->id,
            'target_roles' => ['admin_koni', 'staff_koni', 'ketua_koni', 'sekretaris_koni', 'admin_cabor', 'atlet', 'pelatih'],
            'starts_at' => now()->subMinute()->format('Y-m-d H:i:s'),
            'ends_at' => now()->addHour()->format('Y-m-d H:i:s'),
        ]);

        $session = AttendanceSession::query()->firstOrFail();
        $response->assertRedirect(route('admin.absensi.show', $session));

        $this->actingAs($admin)->get(route('admin.absensi.show', $session))
            ->assertOk()
            ->assertSee('QR Code Harian')
            ->assertSee('absensi/scan/'.$session->dailyScanToken(), false);
    }

    public function test_athlete_can_check_in_once_with_a_camera_selfie(): void
    {
        $admin = $this->userWithRole('admin_koni');
        $cabor = CabangOlahraga::query()->create(['nama' => 'Atletik Absensi', 'status' => 'aktif']);
        $athlete = $this->userWithRole('atlet', $cabor->id);
        $session = $this->createSession($admin, $cabor->id, 'atlet');

        $this->actingAs($athlete)->get(route('attendance.check-in.show', $session->token))
            ->assertOk()
            ->assertSee('Ambil foto selfie');

        $this->actingAs($athlete)->post(route('attendance.check-in.store', $session->token), [
            'selfie_data' => $this->selfieDataUrl(),
        ])->assertRedirect(route('attendance.check-in.show', $session->token));

        $record = AttendanceRecord::query()->firstOrFail();
        $this->assertSame($athlete->id, $record->user_id);
        $this->assertSame('atlet', $record->role_snapshot);
        Storage::disk('local')->assertExists($record->selfie_path);

        $this->actingAs($athlete)->post(route('attendance.check-in.store', $session->token), [
            'selfie_data' => $this->selfieDataUrl(),
        ])->assertSessionHas('error');

        $this->assertSame(1, AttendanceRecord::query()->count());
    }

    public function test_coach_cannot_check_in_to_an_athlete_only_session(): void
    {
        $admin = $this->userWithRole('admin_koni');
        $coach = $this->userWithRole('pelatih');
        $session = $this->createSession($admin, null, 'atlet');

        $this->actingAs($coach)
            ->get(route('attendance.check-in.show', $session->token))
            ->assertForbidden();
    }

    public function test_athlete_from_another_branch_cannot_check_in_to_a_branch_session(): void
    {
        $admin = $this->userWithRole('admin_koni');
        $caborA = CabangOlahraga::query()->create(['nama' => 'Cabor A', 'status' => 'aktif']);
        $caborB = CabangOlahraga::query()->create(['nama' => 'Cabor B', 'status' => 'aktif']);
        $athlete = $this->userWithRole('atlet', $caborB->id);
        $session = $this->createSession($admin, $caborA->id, 'atlet');

        $this->actingAs($athlete)
            ->get(route('attendance.check-in.show', $session->token))
            ->assertForbidden();
    }

    public function test_attendance_selfie_is_private_to_admin(): void
    {
        $admin = $this->userWithRole('admin_koni');
        $athlete = $this->userWithRole('atlet');
        $secretary = $this->userWithRole('sekretaris_koni');
        $session = $this->createSession($admin);
        $path = 'absensi/'.$session->id.'/private-selfie.jpg';
        Storage::disk('local')->put($path, 'private-image-content');
        $record = AttendanceRecord::query()->create([
            'attendance_session_id' => $session->id,
            'user_id' => $athlete->id,
            'participant_name' => $athlete->name,
            'participant_email' => $athlete->email,
            'role_snapshot' => 'atlet',
            'selfie_path' => $path,
            'checked_in_at' => now(),
        ]);

        $this->actingAs($athlete)->get(route('admin.absensi.selfie', $record))->assertForbidden();
        $this->actingAs($secretary)->get(route('sekretaris.absensi.selfie', $record))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.absensi.selfie', $record))->assertOk();
    }

    public function test_staff_can_check_in_and_check_out_with_two_required_selfies(): void
    {
        $admin = $this->userWithRole('admin_koni');
        $staff = $this->userWithRole('staff_koni');
        $session = $this->createSession($admin, null, 'staff_koni', ['staff_koni']);
        $token = $session->dailyScanToken();

        $this->actingAs($staff)->get(route('attendance.check-in.show', $token))
            ->assertOk()
            ->assertSee('Ambil selfie check-in');

        $this->actingAs($staff)->post(route('attendance.check-in.store', $token), [
            'attendance_action' => 'check_in',
            'selfie_data' => $this->selfieDataUrl(),
        ])->assertRedirect(route('attendance.check-in.show', $token));

        $record = AttendanceRecord::query()->firstOrFail();
        $this->assertSame('staff_koni', $record->role_snapshot);
        $this->assertNull($record->checked_out_at);
        Storage::disk('local')->assertExists($record->selfie_path);

        $this->actingAs($staff)->get(route('attendance.check-in.show', $token))
            ->assertOk()
            ->assertSee('Ambil selfie check-out');

        $this->actingAs($staff)->post(route('attendance.check-in.store', $token), [
            'attendance_action' => 'check_out',
            'selfie_data' => $this->selfieDataUrl(),
        ])->assertRedirect(route('attendance.check-in.show', $token));

        $record->refresh();
        $this->assertNotNull($record->checked_out_at);
        $this->assertNotNull($record->check_out_selfie_path);
        Storage::disk('local')->assertExists($record->check_out_selfie_path);

        $this->actingAs($staff)->get(route('attendance.check-in.show', $token))
            ->assertOk()
            ->assertSee('Absensi selesai');
    }

    public function test_each_internal_role_can_use_a_session_that_targets_its_role(): void
    {
        $admin = $this->userWithRole('admin_koni');
        $roles = ['admin_koni', 'staff_koni', 'ketua_koni', 'sekretaris_koni', 'admin_cabor', 'atlet', 'pelatih'];

        foreach ($roles as $role) {
            $participant = $this->userWithRole($role);
            $session = $this->createSession($admin, null, $role, [$role]);

            $this->actingAs($participant)
                ->get(route('attendance.check-in.show', $session->dailyScanToken()))
                ->assertOk();
        }
    }

    public function test_public_role_cannot_open_an_attendance_qr(): void
    {
        Role::firstOrCreate(['name' => 'public', 'guard_name' => 'web']);

        $admin = $this->userWithRole('admin_koni');
        $publicUser = $this->userWithRole('public');
        $session = $this->createSession($admin, null, 'all', ['admin_koni', 'staff_koni', 'ketua_koni', 'sekretaris_koni', 'admin_cabor', 'atlet', 'pelatih']);

        $this->actingAs($publicUser)
            ->get(route('attendance.check-in.show', $session->dailyScanToken()))
            ->assertForbidden();
    }

    public function test_daily_qr_token_changes_when_local_date_changes(): void
    {
        $admin = $this->userWithRole('admin_koni');
        $session = $this->createSession($admin, null, 'all', ['admin_koni', 'staff_koni']);

        $todayToken = $session->dailyScanToken();
        \Illuminate\Support\Carbon::setTestNow(now()->addDay());

        try {
            $tomorrowToken = $session->dailyScanToken();
            $this->assertNotSame($todayToken, $tomorrowToken);
            $this->assertNull(AttendanceSession::decodeDailyScanToken($todayToken));
        } finally {
            \Illuminate\Support\Carbon::setTestNow();
        }
    }

    public function test_staff_can_create_and_view_an_attendance_session(): void
    {
        $staff = $this->userWithRole('staff_koni');

        $response = $this->actingAs($staff)->post(route('staff.absensi.store'), [
            'title' => 'Rapat Staff',
            'description' => 'Kegiatan khusus staff',
            'target_roles' => ['staff_koni'],
            'starts_at' => now()->subMinute()->format('Y-m-d H:i:s'),
            'ends_at' => now()->addHour()->format('Y-m-d H:i:s'),
        ]);

        $session = AttendanceSession::query()->firstOrFail();
        $response->assertRedirect(route('staff.absensi.show', $session));

        $this->actingAs($staff)->get(route('staff.absensi.show', $session))
            ->assertOk()
            ->assertSee('QR Code Harian')
            ->assertSee('Staff KONI');
    }

}
