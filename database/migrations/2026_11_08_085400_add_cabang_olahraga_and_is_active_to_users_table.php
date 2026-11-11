<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            // Relasi akun Admin Cabor
            $table->foreignId('cabang_olahraga_id')
                ->nullable()
                ->constrained('cabang_olahraga')
                ->restrictOnDelete();

            // Status akun
            $table->boolean('is_active')
                ->default(true);

        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->dropConstrainedForeignId(
                'cabang_olahraga_id'
            );

            $table->dropColumn('is_active');

        });
    }
};
