<?php

namespace Tests\Feature\Admin;

use App\Models\Atlet;
use App\Models\CabangOlahraga;
use App\Models\Pelatih;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PenggunaControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    private function adminKoni(): User
    {
        $admin = User::factory()->create([
            'is_active' => true,
        ]);

        $admin->assignRole('admin_koni');

        return $admin;
    }

    private function cabor(): CabangOlahraga
    {
        return CabangOlahraga::create([
            'nama' => 'Bulutangkis Uji',
            'singkatan' => 'PBSI',
            'status' => 'aktif',
        ]);
    }

    public function test_admin_koni_can_create_an_atlet_account_and_link_its_profile(): void
    {
        $cabor = $this->cabor();

        $atlet = Atlet::create([
            'cabang_olahraga_id' => $cabor->id,
            'nama_lengkap' => 'Atlet Uji',
            'jenis_kelamin' => 'L',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($this->adminKoni())->post(
            route('admin.pengguna.store'),
            [
                'name' => 'Akun Atlet Uji',
                'email' => 'atlet-uji@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'atlet',
                'atlet_id' => $atlet->id,
                'is_active' => 1,
            ]
        );

        $response->assertRedirect(route('admin.pengguna.index'));

        $user = User::where('email', 'atlet-uji@example.com')->firstOrFail();

        $this->assertTrue($user->hasRole('atlet'));
        $this->assertSame($user->id, $atlet->fresh()->user_id);
    }

    public function test_admin_koni_can_create_a_coach_account_and_link_its_profile(): void
    {
        $cabor = $this->cabor();

        $pelatih = Pelatih::create([
            'cabang_olahraga_id' => $cabor->id,
            'nama_lengkap' => 'Pelatih Uji',
            'jenis_kelamin' => 'L',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($this->adminKoni())->post(
            route('admin.pengguna.store'),
            [
                'name' => 'Akun Pelatih Uji',
                'email' => 'pelatih-uji@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'pelatih',
                'pelatih_id' => $pelatih->id,
                'is_active' => 1,
            ]
        );

        $response->assertRedirect(route('admin.pengguna.index'));

        $user = User::where('email', 'pelatih-uji@example.com')->firstOrFail();

        $this->assertTrue($user->hasRole('pelatih'));
        $this->assertSame($user->id, $pelatih->fresh()->user_id);
    }
}
