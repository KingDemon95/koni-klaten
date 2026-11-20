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
            'inventory_categories',
            'inventory_items',
            'inventory_movements',
            'inventory_loans',
            'audit_logs',
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
            Schema::hasColumn('surats', 'decision_by_role'),
            'Kolom decision_by_role tidak ditemukan pada tabel surats.'
        );

        $this->assertTrue(
            Schema::hasColumn('prestasis', 'jenis_medali'),
            'Kolom jenis_medali tidak ditemukan pada tabel prestasis.'
        );

        $this->assertTrue(
            Schema::hasColumn('users', 'must_change_password'),
            'Kolom must_change_password tidak ditemukan pada tabel users.'
        );

        $this->assertTrue(
            Schema::hasColumn('users', 'password_changed_at'),
            'Kolom password_changed_at tidak ditemukan pada tabel users.'
        );
    }
}
