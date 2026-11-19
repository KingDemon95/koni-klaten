<?php

namespace Tests\Feature;

use App\Models\CabangOlahraga;
use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\InventoryLoan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class InventoryManagementTest extends TestCase
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

    private function category(?int $caborId = null, string $name = 'Peralatan Latihan'): InventoryCategory
    {
        return InventoryCategory::create([
            'cabang_olahraga_id' => $caborId,
            'nama' => $name,
            'created_by' => null,
        ]);
    }

    private function item(InventoryCategory $category, ?int $caborId = null, array $overrides = []): InventoryItem
    {
        return InventoryItem::create(array_merge([
            'inventory_category_id' => $category->id,
            'cabang_olahraga_id' => $caborId,
            'nama_barang' => 'Bola Latihan',
            'jumlah' => 10,
            'satuan' => 'buah',
            'kondisi' => 'baik',
            'lokasi' => 'Gudang Utama',
            'status' => 'aktif',
        ], $overrides));
    }

    public function test_admin_koni_can_create_central_inventory_and_system_generates_code(): void
    {
        $admin = $this->user('admin_koni');
        $category = $this->category();

        $this->actingAs($admin)
            ->post(route('admin.inventaris.store'), [
                'scope' => 'koni',
                'inventory_category_id' => $category->id,
                'nama_barang' => 'Matras Senam',
                'jumlah' => 5,
                'satuan' => 'unit',
                'kondisi' => 'baik',
                'lokasi' => 'Gudang KONI',
                'status' => 'aktif',
            ])
            ->assertRedirect();

        $created = InventoryItem::query()->where('nama_barang', 'Matras Senam')->firstOrFail();
        $this->assertNull($created->cabang_olahraga_id);
        $this->assertStringStartsWith('INV-KONI-', $created->kode_barang);
        $this->assertSame($admin->id, $created->created_by);
    }

    public function test_admin_koni_can_create_inventory_for_a_selected_active_branch(): void
    {
        $admin = $this->user('admin_koni');
        $cabor = $this->cabor('Panahan');
        $category = $this->category($cabor->id, 'Busur dan Anak Panah');

        $this->actingAs($admin)
            ->post(route('admin.inventaris.store'), [
                'scope' => 'cabor',
                'cabang_olahraga_id' => $cabor->id,
                'inventory_category_id' => $category->id,
                'nama_barang' => 'Busur Kompetisi',
                'jumlah' => 3,
                'satuan' => 'unit',
                'kondisi' => 'baik',
                'lokasi' => 'Gudang Panahan',
                'status' => 'aktif',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('inventory_items', [
            'nama_barang' => 'Busur Kompetisi',
            'cabang_olahraga_id' => $cabor->id,
        ]);
    }

    public function test_admin_cabor_can_manage_only_its_own_inventory(): void
    {
        $ownCabor = $this->cabor('Karate');
        $otherCabor = $this->cabor('Taekwondo');
        $user = $this->user('admin_cabor', $ownCabor->id);
        $ownCategory = $this->category($ownCabor->id, 'Perlengkapan Karate');
        $otherCategory = $this->category($otherCabor->id, 'Perlengkapan Taekwondo');
        $ownItem = $this->item($ownCategory, $ownCabor->id, ['nama_barang' => 'Pelindung Karate']);
        $otherItem = $this->item($otherCategory, $otherCabor->id, ['nama_barang' => 'Pelindung Taekwondo']);
        $centralItem = $this->item($this->category(null, 'Perlengkapan KONI'), null, ['nama_barang' => 'Laptop KONI']);

        $this->actingAs($user)
            ->get(route('cabor.inventaris.index'))
            ->assertOk()
            ->assertSee('Pelindung Karate')
            ->assertDontSee('Pelindung Taekwondo')
            ->assertDontSee('Laptop KONI');

        $this->actingAs($user)
            ->get(route('cabor.inventaris.show', $otherItem))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('cabor.inventaris.show', $ownItem))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('cabor.inventaris.show', $centralItem))
            ->assertForbidden();
    }

    public function test_admin_cabor_creation_is_forced_to_its_own_branch_even_if_request_contains_another_branch(): void
    {
        $ownCabor = $this->cabor('Anggar');
        $otherCabor = $this->cabor('Dayung');
        $user = $this->user('admin_cabor', $ownCabor->id);
        $category = $this->category($ownCabor->id, 'Peralatan Anggar');

        $this->actingAs($user)
            ->post(route('cabor.inventaris.store'), [
                'scope' => 'cabor',
                'cabang_olahraga_id' => $otherCabor->id,
                'inventory_category_id' => $category->id,
                'nama_barang' => 'Pedang Anggar',
                'jumlah' => 2,
                'satuan' => 'unit',
                'kondisi' => 'baik',
                'lokasi' => 'Gudang Anggar',
                'status' => 'aktif',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('inventory_items', [
            'nama_barang' => 'Pedang Anggar',
            'cabang_olahraga_id' => $ownCabor->id,
        ]);
        $this->assertDatabaseMissing('inventory_items', [
            'nama_barang' => 'Pedang Anggar',
            'cabang_olahraga_id' => $otherCabor->id,
        ]);
    }

    public function test_category_cannot_be_deleted_while_items_use_it(): void
    {
        $admin = $this->user('admin_koni');
        $category = $this->category();
        $this->item($category);

        $this->actingAs($admin)
            ->delete(route('admin.inventaris.kategori.destroy', $category))
            ->assertRedirect();

        $this->assertDatabaseHas('inventory_categories', ['id' => $category->id]);
    }

    public function test_mutation_updates_current_location_and_keeps_audit_history(): void
    {
        $admin = $this->user('admin_koni');
        $item = $this->item($this->category());

        $this->actingAs($admin)
            ->post(route('admin.inventaris.mutasi', $item), [
                'lokasi_tujuan' => 'Gudang Barat',
                'tanggal_mutasi' => now()->toDateTimeString(),
                'catatan' => 'Pemindahan untuk penataan gudang.',
            ])
            ->assertRedirect(route('admin.inventaris.show', $item));

        $this->assertDatabaseHas('inventory_items', [
            'id' => $item->id,
            'lokasi' => 'Gudang Barat',
        ]);
        $this->assertDatabaseHas('inventory_movements', [
            'inventory_item_id' => $item->id,
            'lokasi_asal' => 'Gudang Utama',
            'lokasi_tujuan' => 'Gudang Barat',
            'moved_by' => $admin->id,
        ]);
    }

    public function test_loan_reduces_available_quantity_and_return_restores_it(): void
    {
        $admin = $this->user('admin_koni');
        $item = $this->item($this->category(), null, ['jumlah' => 6]);

        $this->actingAs($admin)
            ->post(route('admin.inventaris.peminjaman.store', $item), [
                'peminjam' => 'Tim Atletik',
                'kontak_peminjam' => '08123456789',
                'unit_peminjam' => 'Atletik',
                'jumlah' => 2,
                'tanggal_pinjam' => now()->toDateString(),
                'batas_pengembalian' => now()->addDays(7)->toDateString(),
            ])
            ->assertRedirect(route('admin.inventaris.show', $item));

        $loan = InventoryLoan::query()->firstOrFail();
        $this->assertSame(4, $item->fresh()->availableQuantity());

        $this->actingAs($admin)
            ->post(route('admin.inventaris.peminjaman.return', $loan), [
                'kondisi_pengembalian' => 'baik',
                'catatan_pengembalian' => 'Dikembalikan lengkap.',
            ])
            ->assertRedirect(route('admin.inventaris.show', $item));

        $this->assertDatabaseHas('inventory_loans', [
            'id' => $loan->id,
            'status' => 'dikembalikan',
            'returned_by' => $admin->id,
        ]);
        $this->assertDatabaseHas('inventory_items', [
            'id' => $item->id,
            'kondisi' => 'baik',
        ]);
        $this->assertSame(6, $item->fresh()->availableQuantity());
    }

    public function test_loan_cannot_exceed_available_stock(): void
    {
        $admin = $this->user('admin_koni');
        $item = $this->item($this->category(), null, ['jumlah' => 2]);

        $this->actingAs($admin)
            ->post(route('admin.inventaris.peminjaman.store', $item), [
                'peminjam' => 'Tim Uji',
                'jumlah' => 3,
                'tanggal_pinjam' => now()->toDateString(),
                'batas_pengembalian' => now()->addDay()->toDateString(),
            ])
            ->assertStatus(422);

        $this->assertDatabaseCount('inventory_loans', 0);
    }

    public function test_inventory_report_and_csv_export_are_available_to_admin(): void
    {
        $admin = $this->user('admin_koni');
        $this->item($this->category(), null, ['nama_barang' => 'Papan Skor']);

        $this->actingAs($admin)
            ->get(route('admin.inventaris.laporan'))
            ->assertOk()
            ->assertSee('Papan Skor');

        $this->actingAs($admin)
            ->get(route('admin.inventaris.laporan.export'))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }
}
