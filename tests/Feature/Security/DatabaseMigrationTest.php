<?php

namespace Tests\Feature\Security;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_main_application_tables_exist(): void
    {
        $tables = [
            'users',
            'roles',
            'model_has_roles',
            'cabang_olahraga',
            'atlets',
            'pelatihs',
            'pengurus',
            'agendas',
            'surats',
            'surat_activities',
            'template_surats',
            'prestasis',
            'izin_latihans',
            'publikasis',
            'profil_koni',
        ];

        foreach ($tables as $table) {
            $this->assertTrue(
                Schema::hasTable($table),
                "Tabel {$table} tidak ditemukan."
            );
        }

        $this->assertDatabaseHas('profil_koni', [
            'nama_singkat' => 'KONI Klaten',
        ]);

        $this->assertTrue(
            Schema::hasColumn('prestasis', 'jenis_medali'),
            'Kolom jenis_medali tidak ditemukan pada tabel prestasis.'
        );
    }
}
