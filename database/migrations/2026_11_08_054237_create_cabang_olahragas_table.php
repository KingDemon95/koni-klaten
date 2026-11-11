<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cabang_olahraga', function (Blueprint $table) {
            $table->id();

            $table->string('nama', 150)->unique();
            $table->string('singkatan', 30)->nullable();

            $table->text('deskripsi')->nullable();
            $table->string('logo_path')->nullable();

            $table->string('nama_ketua', 150)->nullable();
            $table->string('email')->nullable();
            $table->string('telepon', 30)->nullable();
            $table->text('alamat')->nullable();

            $table->string('status', 20)
                ->default('aktif')
                ->index();

            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cabang_olahraga');
    }
};
