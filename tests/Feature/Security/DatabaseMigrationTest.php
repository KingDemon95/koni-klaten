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
        ];

        foreach ($tables as $table) {
            $this->assertTrue(
                Schema::hasTable($table),
                "Tabel {$table} tidak ditemukan."
            );
        }
    }
}
