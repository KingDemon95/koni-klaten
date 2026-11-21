<?php

namespace Tests\Feature;

use App\Models\Atlet;
use App\Models\CabangOlahraga;
use App\Models\CustomField;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomMasterDataTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole($role);

        return $user;
    }

    public function test_admin_can_create_a_dynamic_field_and_save_it_with_an_athlete(): void
    {
        $admin = $this->userWithRole('admin_koni');

        $this->actingAs($admin)->post(route('admin.master-data.store'), [
            'entity_type' => 'atlet',
            'label' => 'Nomor Induk Atlet',
            'type' => 'text',
            'is_required' => 1,
            'sort_order' => 1,
        ])->assertRedirect(route('admin.master-data.index'));

        $field = CustomField::query()->where('field_key', 'nomor_induk_atlet')->firstOrFail();
        $cabor = CabangOlahraga::query()->create([
            'nama' => 'Bulutangkis Uji',
            'status' => 'aktif',
        ]);

        $this->actingAs($admin)->post(route('admin.atlet.store'), [
            'cabang_olahraga_id' => $cabor->id,
            'nama_lengkap' => 'Atlet Dinamis',
            'jenis_kelamin' => 'L',
            'email' => 'atlet-dinamis@example.test',
            'status' => 'aktif',
            'custom_data' => [
                $field->field_key => 'KL-AT-001',
            ],
        ])->assertRedirect(route('admin.atlet.index'));

        $atlet = Atlet::query()->where('email', 'atlet-dinamis@example.test')->firstOrFail();
        $this->assertSame('KL-AT-001', $atlet->custom_data[$field->field_key]);
    }

    public function test_required_dynamic_field_is_validated_on_profile_creation(): void
    {
        $admin = $this->userWithRole('admin_koni');
        $cabor = CabangOlahraga::query()->create(['nama' => 'Atletik Uji', 'status' => 'aktif']);

        CustomField::query()->create([
            'entity_type' => 'atlet',
            'label' => 'Nomor Registrasi',
            'field_key' => 'nomor_registrasi',
            'type' => 'text',
            'is_required' => true,
            'is_active' => true,
        ]);

        $this->actingAs($admin)->from(route('admin.atlet.create'))->post(route('admin.atlet.store'), [
            'cabang_olahraga_id' => $cabor->id,
            'nama_lengkap' => 'Atlet Tanpa Nomor',
            'jenis_kelamin' => 'P',
            'email' => 'atlet-tanpa-nomor@example.test',
            'status' => 'aktif',
        ])->assertSessionHasErrors('custom_data.nomor_registrasi');

        $this->assertDatabaseMissing('atlets', ['email' => 'atlet-tanpa-nomor@example.test']);
    }

    public function test_non_admin_cannot_manage_dynamic_fields(): void
    {
        $ketua = $this->userWithRole('ketua_koni');

        $this->actingAs($ketua)
            ->get(route('admin.master-data.index'))
            ->assertForbidden();
    }

    public function test_deactivating_a_field_keeps_existing_profile_values(): void
    {
        $admin = $this->userWithRole('admin_koni');
        $field = CustomField::query()->create([
            'entity_type' => 'atlet',
            'label' => 'Nomor Registrasi',
            'field_key' => 'nomor_registrasi',
            'type' => 'text',
            'is_active' => true,
        ]);

        $cabor = CabangOlahraga::query()->create(['nama' => 'Renang Uji', 'status' => 'aktif']);
        $atlet = Atlet::query()->create([
            'cabang_olahraga_id' => $cabor->id,
            'nama_lengkap' => 'Atlet Arsip',
            'jenis_kelamin' => 'L',
            'status' => 'aktif',
            'custom_data' => ['nomor_registrasi' => 'ARSIP-01'],
        ]);

        $this->actingAs($admin)->delete(route('admin.master-data.destroy', $field))
            ->assertRedirect(route('admin.master-data.index'));

        $this->assertSame('ARSIP-01', $atlet->fresh()->custom_data['nomor_registrasi']);
        $this->assertDatabaseHas('custom_fields', [
            'id' => $field->id,
            'is_active' => false,
        ]);
    }
}
