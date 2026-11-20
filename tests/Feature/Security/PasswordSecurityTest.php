<?php

namespace Tests\Feature\Security;

use App\Models\Atlet;
use App\Models\AuditLog;
use App\Models\CabangOlahraga;
use App\Models\User;
use App\Support\AccountProvisioner;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    private function temporaryUser(string $role = 'atlet'): User
    {
        $user = User::factory()->create([
            'is_active' => true,
            'must_change_password' => true,
        ]);
        $user->assignRole($role);

        return $user;
    }

    public function test_temporary_password_user_is_redirected_to_password_change_page(): void
    {
        $user = $this->temporaryUser();

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect();

        $this->get('/dashboard')
            ->assertRedirect(route('account.password.edit'));

        $this->get(route('account.password.edit'))
            ->assertOk()
            ->assertSeeText('Penggantian password wajib dilakukan')
            ->assertSeeText('Password saat ini');
    }

    public function test_temporary_password_blocks_other_pages_but_allows_logout(): void
    {
        $user = $this->temporaryUser();

        $this->actingAs($user)
            ->get('/notifikasi')
            ->assertRedirect(route('account.password.edit'));

        $this->actingAs($user)
            ->get('/notifikasi', ['Accept' => 'application/json'])
            ->assertStatus(423)
            ->assertJsonPath('password_change_required', true);

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect();

        $this->assertGuest();
    }

    public function test_user_can_change_temporary_password_and_audit_log_hides_the_secret(): void
    {
        $user = $this->temporaryUser();
        $newPassword = 'KataSandiBaru123';

        $this->actingAs($user)
            ->put(route('account.password.update'), [
                'current_password' => 'password',
                'password' => $newPassword,
                'password_confirmation' => $newPassword,
            ])
            ->assertRedirect(route('dashboard'));

        $user->refresh();

        $this->assertFalse($user->must_change_password);
        $this->assertNotNull($user->password_changed_at);
        $this->assertTrue(Hash::check($newPassword, $user->password));

        $log = AuditLog::query()
            ->where('subject_type', User::class)
            ->where('subject_id', $user->id)
            ->where('event', 'password_changed')
            ->latest('id')
            ->firstOrFail();

        $this->assertStringNotContainsString($newPassword, json_encode($log->toArray()));
    }

    public function test_wrong_current_password_keeps_password_change_requirement(): void
    {
        $user = $this->temporaryUser();

        $this->actingAs($user)
            ->from(route('account.password.edit'))
            ->put(route('account.password.update'), [
                'current_password' => 'password-yang-salah',
                'password' => 'KataSandiBaru123',
                'password_confirmation' => 'KataSandiBaru123',
            ])
            ->assertRedirect(route('account.password.edit'))
            ->assertSessionHasErrors('current_password');

        $this->assertTrue($user->fresh()->must_change_password);
    }

    public function test_password_must_contain_ten_characters_mixed_case_and_numbers(): void
    {
        $user = $this->temporaryUser();

        $this->actingAs($user)
            ->from(route('account.password.edit'))
            ->put(route('account.password.update'), [
                'current_password' => 'password',
                'password' => '1234567890',
                'password_confirmation' => '1234567890',
            ])
            ->assertRedirect(route('account.password.edit'))
            ->assertSessionHasErrors('password');

        $this->assertTrue($user->fresh()->must_change_password);
    }

    public function test_automatic_accounts_for_all_shared_password_roles_require_password_change(): void
    {
        foreach (['admin_cabor', 'atlet', 'pelatih', 'staff_koni'] as $role) {
            $user = AccountProvisioner::create(
                name: 'Akun '.str_replace('_', ' ', $role),
                email: $role.'-'.uniqid().'@example.test',
                role: $role,
            );

            $this->assertTrue(
                $user->fresh()->must_change_password,
                "Akun {$role} harus mengganti password awal."
            );
        }
    }

    public function test_admin_created_user_account_requires_password_change(): void
    {
        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole('admin_koni');

        $cabor = CabangOlahraga::create([
            'nama' => 'Cabang Uji Keamanan Password',
            'status' => 'aktif',
        ]);

        $atlet = Atlet::create([
            'cabang_olahraga_id' => $cabor->id,
            'nama_lengkap' => 'Atlet Uji Password',
            'jenis_kelamin' => 'L',
            'status' => 'aktif',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.pengguna.store'), [
                'name' => 'Akun Atlet Uji Password',
                'email' => 'akun-atlet-password@example.test',
                'password' => 'PasswordAdmin123',
                'password_confirmation' => 'PasswordAdmin123',
                'role' => 'atlet',
                'atlet_id' => $atlet->id,
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.pengguna.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'akun-atlet-password@example.test',
            'must_change_password' => true,
        ]);
    }

    public function test_admin_password_reset_requires_the_owner_to_change_it_again(): void
    {
        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole('admin_koni');

        $cabor = CabangOlahraga::create([
            'nama' => 'Cabang Uji Reset Password',
            'status' => 'aktif',
        ]);

        $owner = User::factory()->create([
            'name' => 'Pemilik Akun Atlet',
            'email' => 'pemilik-akun-atlet@example.test',
            'is_active' => true,
            'must_change_password' => false,
        ]);
        $owner->assignRole('atlet');

        $atlet = Atlet::create([
            'cabang_olahraga_id' => $cabor->id,
            'user_id' => $owner->id,
            'nama_lengkap' => 'Atlet Pemilik Akun',
            'jenis_kelamin' => 'L',
            'status' => 'aktif',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.pengguna.update', $owner), [
                'name' => $owner->name,
                'email' => $owner->email,
                'password' => 'PasswordReset123',
                'password_confirmation' => 'PasswordReset123',
                'role' => 'atlet',
                'atlet_id' => $atlet->id,
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.pengguna.index'));

        $this->assertDatabaseHas('users', [
            'id' => $owner->id,
            'must_change_password' => true,
            'password_changed_at' => null,
        ]);
    }

    public function test_guest_cannot_open_account_password_settings(): void
    {
        $this->get(route('account.password.edit'))
            ->assertRedirect(route('login'));

        $this->put(route('account.password.update'), [])
            ->assertRedirect(route('login'));
    }
}
