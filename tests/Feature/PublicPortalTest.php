<?php

namespace Tests\Feature;

use App\Models\Atlet;
use App\Models\CabangOlahraga;
use App\Models\Pelatih;
use App\Models\Pengurus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_is_public_and_shows_active_public_information(): void
    {
        $cabor = CabangOlahraga::create([
            'nama' => 'Bulutangkis',
            'singkatan' => 'PBSI',
            'deskripsi' => 'Pembinaan olahraga bulutangkis.',
            'status' => 'aktif',
        ]);

        Atlet::create([
            'cabang_olahraga_id' => $cabor->id,
            'nama_lengkap' => 'Atlet Publik',
            'jenis_kelamin' => 'L',
            'status' => 'aktif',
        ]);

        Pelatih::create([
            'cabang_olahraga_id' => $cabor->id,
            'nama_lengkap' => 'Pelatih Publik',
            'jenis_kelamin' => 'L',
            'status' => 'aktif',
        ]);

        Pengurus::create([
            'nama_lengkap' => 'Pengurus Kabupaten',
            'jabatan' => 'Ketua Umum',
            'status' => 'aktif',
        ]);

        $this->get(route('public.home'))
            ->assertOk()
            ->assertSee('KONI KLATEN')
            ->assertSee('Bulutangkis')
            ->assertSee('Pengurus Kabupaten')
            ->assertSee('Atlet aktif');
    }

    public function test_public_cabor_search_ignores_array_query_values_safely(): void
    {
        CabangOlahraga::create([
            'nama' => 'Renang',
            'status' => 'aktif',
        ]);

        $this->get('/cabang-olahraga?q%5B%5D=renang')
            ->assertOk()
            ->assertSee('Renang');
    }

    public function test_public_cabor_directory_only_lists_active_branches(): void
    {
        CabangOlahraga::create([
            'nama' => 'Renang Aktif',
            'status' => 'aktif',
        ]);

        CabangOlahraga::create([
            'nama' => 'Cabor Nonaktif Rahasia',
            'status' => 'nonaktif',
        ]);

        $this->get(route('public.cabor.index'))
            ->assertOk()
            ->assertSee('Renang Aktif')
            ->assertDontSee('Cabor Nonaktif Rahasia');
    }

    public function test_public_cabor_detail_shows_active_people_but_not_private_contact_data(): void
    {
        $cabor = CabangOlahraga::create([
            'nama' => 'Atletik',
            'status' => 'aktif',
        ]);

        Atlet::create([
            'cabang_olahraga_id' => $cabor->id,
            'nama_lengkap' => 'Atlet Terpublikasi',
            'jenis_kelamin' => 'P',
            'email' => 'private-atlet@example.test',
            'no_telepon' => '081234567890',
            'status' => 'aktif',
        ]);

        Atlet::create([
            'cabang_olahraga_id' => $cabor->id,
            'nama_lengkap' => 'Atlet Nonaktif Rahasia',
            'jenis_kelamin' => 'L',
            'status' => 'nonaktif',
        ]);

        Pelatih::create([
            'cabang_olahraga_id' => $cabor->id,
            'nama_lengkap' => 'Pelatih Terpublikasi',
            'jenis_kelamin' => 'L',
            'spesialisasi' => 'Lari jarak pendek',
            'status' => 'aktif',
        ]);

        Pengurus::create([
            'cabang_olahraga_id' => $cabor->id,
            'nama_lengkap' => 'Pengurus Atletik',
            'jabatan' => 'Ketua Cabor',
            'status' => 'aktif',
        ]);

        $this->get(route('public.cabor.show', $cabor))
            ->assertOk()
            ->assertSee('Atlet Terpublikasi')
            ->assertSee('Pelatih Terpublikasi')
            ->assertSee('Pengurus Atletik')
            ->assertDontSee('Atlet Nonaktif Rahasia')
            ->assertDontSee('private-atlet@example.test')
            ->assertDontSee('081234567890');
    }

    public function test_inactive_cabor_cannot_be_opened_from_public_portal(): void
    {
        $cabor = CabangOlahraga::create([
            'nama' => 'Cabor Ditutup',
            'status' => 'nonaktif',
        ]);

        $this->get(route('public.cabor.show', $cabor))
            ->assertNotFound();
    }

    public function test_public_structure_page_only_shows_koni_level_management(): void
    {
        Pengurus::create([
            'nama_lengkap' => 'Pengurus KONI',
            'jabatan' => 'Ketua Umum',
            'status' => 'aktif',
        ]);

        $cabor = CabangOlahraga::create([
            'nama' => 'Voli',
            'status' => 'aktif',
        ]);

        Pengurus::create([
            'cabang_olahraga_id' => $cabor->id,
            'nama_lengkap' => 'Pengurus Cabor',
            'jabatan' => 'Ketua Cabor',
            'status' => 'aktif',
        ]);

        $this->get(route('public.struktur.index'))
            ->assertOk()
            ->assertSee('Pengurus KONI')
            ->assertDontSee('Pengurus Cabor');
    }
}
