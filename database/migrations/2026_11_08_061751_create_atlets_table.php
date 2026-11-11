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
        Schema::create('atlets', function (Blueprint $table) {
            $table->id();

            // Relasi cabang olahraga
            $table->foreignId('cabang_olahraga_id')
                ->constrained('cabang_olahraga')
                ->restrictOnDelete();

            // Relasi akun pengguna (opsional)
            $table->foreignId('user_id')
                ->nullable()
                ->unique()
                ->constrained('users')
                ->nullOnDelete();

            // Biodata atlet
            $table->string('nama_lengkap', 150);

            $table->string('jenis_kelamin', 1);

            $table->string('tempat_lahir', 100)->nullable();

            $table->date('tanggal_lahir')->nullable();

            $table->text('alamat')->nullable();

            $table->string('no_telepon', 30)->nullable();

            $table->string('email')->nullable();

            // Foto profil
            $table->string('foto_path')->nullable();

            // Status atlet
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
        Schema::dropIfExists('atlets');
    }
};
