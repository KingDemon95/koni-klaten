<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application database.
     */
    public function run(): void
    {
        // Pastikan seluruh role tersedia pada instalasi baru.
        $this->call(RoleSeeder::class);
    }
}
