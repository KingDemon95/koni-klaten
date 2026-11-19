<?php

namespace Tests\Feature\Security;

use App\Models\CabangOlahraga;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AuditTrailSecurityTest extends TestCase
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
        ] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
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

    public function test_admin_ketua_and_secretary_can_read_the_global_activity_log(): void
    {
        $admin = $this->user('admin_koni');
        $this->actingAs($admin);
        $cabor = $this->cabor('Cabor Riwayat Global');
        $cabor->update(['nama' => 'Aktivitas Global Tercatat']);

        $this->actingAs($admin)
            ->get(route('admin.audit.index'))
            ->assertOk()
            ->assertSeeText('Aktivitas Global Tercatat');

        $ketua = $this->user('ketua_koni');
        $this->actingAs($ketua)
            ->get(route('admin.audit.index'))
            ->assertOk()
            ->assertSeeText('Aktivitas Global Tercatat');

        $secretary = $this->user('sekretaris_koni');
        $this->actingAs($secretary)
            ->get(route('sekretaris.audit.index'))
            ->assertOk()
            ->assertSeeText('Aktivitas Global Tercatat');
    }

    public function test_admin_cabor_only_sees_activity_related_to_its_own_branch(): void
    {
        $ownCabor = $this->cabor('Cabor Milik Sendiri');
        $otherCabor = $this->cabor('Cabor Milik Pihak Lain');
        $ownAdmin = $this->user('admin_cabor', $ownCabor);
        $otherAdmin = $this->user('admin_cabor', $otherCabor);

        $this->actingAs($ownAdmin);
        $ownCabor->update(['nama' => 'Aktivitas Cabor Sendiri']);

        $this->actingAs($otherAdmin);
        $otherCabor->update(['nama' => 'Aktivitas Cabor Lain']);

        $this->actingAs($ownAdmin)
            ->get(route('cabor.audit.index'))
            ->assertOk()
            ->assertSeeText('Aktivitas Cabor Sendiri')
            ->assertDontSeeText('Aktivitas Cabor Lain');
    }

    public function test_staff_only_sees_activity_performed_by_their_own_account(): void
    {
        $staff = $this->user('staff_koni');
        $admin = $this->user('admin_koni');
        $cabor = $this->cabor('Cabor Audit Staff');

        $this->actingAs($staff);
        $cabor->update(['nama' => 'Perubahan oleh Staff']);

        $this->actingAs($admin);
        $cabor->update(['nama' => 'Perubahan oleh Admin']);

        $this->actingAs($staff)
            ->get(route('staff.audit.index'))
            ->assertOk()
            ->assertSeeText('Perubahan oleh Staff')
            ->assertDontSeeText('Perubahan oleh Admin');
    }

    public function test_atlet_and_pelatih_cannot_open_activity_log(): void
    {
        foreach (['atlet', 'pelatih'] as $role) {
            $user = $this->user($role);

            $this->actingAs($user)
                ->get('/admin/riwayat-aktivitas')
                ->assertForbidden();
        }
    }

    public function test_activity_log_has_no_write_routes_and_rejects_array_filters(): void
    {
        $admin = $this->user('admin_koni');

        $this->actingAs($admin)
            ->put('/admin/riwayat-aktivitas', [])
            ->assertStatus(405);

        $this->actingAs($admin)
            ->get(route('admin.audit.index', ['q' => ['invalid']]))
            ->assertStatus(422);

    }
}
