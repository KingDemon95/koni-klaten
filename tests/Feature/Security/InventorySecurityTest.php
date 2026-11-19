<?php

namespace Tests\Feature\Security;

use App\Models\CabangOlahraga;
use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class InventorySecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin_koni', 'ketua_koni', 'sekretaris_koni', 'staff_koni', 'admin_cabor', 'atlet', 'pelatih'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function user(string $role, ?int $caborId = null): User
    {
        $user = User::factory()->create([
            'cabang_olahraga_id' => $caborId,
            'is_active' => true,
        ]);
        $user->assignRole($role);
        return $user;
    }

    private function cabor(string $name): CabangOlahraga
    {
        return CabangOlahraga::create(['nama' => $name, 'status' => 'aktif']);
    }

    private function item(?int $caborId, string $name): InventoryItem
    {
        $category = InventoryCategory::create([
            'cabang_olahraga_id' => $caborId,
            'nama' => 'Kategori '.$name,
        ]);

        return InventoryItem::create([
            'inventory_category_id' => $category->id,
            'cabang_olahraga_id' => $caborId,
            'nama_barang' => $name,
            'jumlah' => 4,
            'satuan' => 'unit',
            'kondisi' => 'baik',
            'lokasi' => 'Gudang',
            'status' => 'aktif',
        ]);
    }

    public function test_staff_can_see_and_operate_central_koni_assets_only(): void
    {
        $staff = $this->user('staff_koni');
        $cabor = $this->cabor('Renang');
        $centralItem = $this->item(null, 'Laptop Sekretariat');
        $caborItem = $this->item($cabor->id, 'Papan Start Renang');

        $this->actingAs($staff)
            ->get(route('staff.inventaris.index'))
            ->assertOk()
            ->assertSee('Laptop Sekretariat')
            ->assertDontSee('Papan Start Renang');

        $this->actingAs($staff)
            ->get(route('staff.inventaris.show', $caborItem))
            ->assertForbidden();

        $this->actingAs($staff)
            ->post(route('staff.inventaris.peminjaman.store', $centralItem), [
                'peminjam' => 'Panitia KONI',
                'jumlah' => 1,
                'tanggal_pinjam' => now()->toDateString(),
                'batas_pengembalian' => now()->addDays(2)->toDateString(),
            ])
            ->assertRedirect();

        $this->actingAs($staff)
            ->post(route('staff.inventaris.peminjaman.store', $caborItem), [
                'peminjam' => 'Panitia Cabor',
                'jumlah' => 1,
                'tanggal_pinjam' => now()->toDateString(),
                'batas_pengembalian' => now()->addDays(2)->toDateString(),
            ])
            ->assertForbidden();
    }

    public function test_secretary_has_read_only_access_to_all_inventory(): void
    {
        $secretary = $this->user('sekretaris_koni');
        $cabor = $this->cabor('Voli');
        $centralItem = $this->item(null, 'Proyektor KONI');
        $caborItem = $this->item($cabor->id, 'Net Voli');

        $this->actingAs($secretary)
            ->get(route('sekretaris.inventaris.index'))
            ->assertOk()
            ->assertSee('Proyektor KONI')
            ->assertSee('Net Voli');

        $this->actingAs($secretary)
            ->get(route('sekretaris.inventaris.show', $caborItem))
            ->assertOk();

        $this->actingAs($secretary)
            ->post('/sekretaris/inventaris/'.$centralItem->id.'/peminjaman', [])
            ->assertNotFound();
    }

    public function test_atlet_and_pelatih_cannot_access_inventory_panels(): void
    {
        foreach (['atlet', 'pelatih'] as $role) {
            $user = $this->user($role);
            $this->actingAs($user)
                ->get(route('staff.inventaris.index'))
                ->assertForbidden();
        }
    }

    public function test_ketua_can_manage_inventory_and_secretary_cannot_change_items(): void
    {
        $ketua = $this->user('ketua_koni');
        $secretary = $this->user('sekretaris_koni');
        $item = $this->item(null, 'Perangkat Audio');

        $this->actingAs($ketua)
            ->get(route('admin.inventaris.show', $item))
            ->assertOk();

        $this->actingAs($ketua)
            ->put(route('admin.inventaris.update', $item), [
                'inventory_category_id' => $item->inventory_category_id,
                'nama_barang' => 'Perangkat Audio KONI',
                'jumlah' => 4,
                'satuan' => 'unit',
                'kondisi' => 'baik',
                'lokasi' => 'Gudang Audio',
                'status' => 'aktif',
            ])
            ->assertRedirect();

        $this->actingAs($secretary)
            ->get(route('sekretaris.inventaris.show', $item))
            ->assertOk();

        // GET detail tersedia untuk Sekretaris, tetapi route PUT tidak didaftarkan.
        // Laravel mengembalikan 405 untuk URI yang tersedia dengan HTTP method yang tidak didukung.
        $this->actingAs($secretary)
            ->put('/sekretaris/inventaris/'.$item->id, [])
            ->assertStatus(405);

        // Pastikan permintaan Sekretaris tidak mengubah data inventaris.
        $this->assertSame('Perangkat Audio KONI', $item->fresh()->nama_barang);
    }
}
