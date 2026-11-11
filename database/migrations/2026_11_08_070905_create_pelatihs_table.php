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
        Schema::create('pelatihs', function (Blueprint $table) {
            $table->id();

            // Relasi cabor
            $table->foreignId('cabang_olahraga_id')
                ->constrained('cabang_olahraga')
                ->restrictOnDelete();

            // Relasi akun pengguna
            $table->foreignId('user_id')
                ->nullable()
                ->unique()
                ->constrained('users')
                ->nullOnDelete();

            // Biodata
            $table->string('nama_lengkap', 150);

            $table->string('jenis_kelamin', 1);

            $table->string('tempat_lahir', 100)->nullable();

            $table->date('tanggal_lahir')->nullable();

            $table->text('alamat')->nullable();

            $table->string('no_telepon', 30)->nullable();

            $table->string('email')->nullable();

            // Kompetensi pelatih
            $table->string('pendidikan_terakhir', 150)->nullable();

            $table->string('spesialisasi', 150)->nullable();

            $table->text('sertifikasi')->nullable();

            $table->unsignedTinyInteger('pengalaman_tahun')
                ->nullable();

            // Foto profil
            $table->string('foto_path')->nullable();

            // Status
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
        Schema::dropIfExists('pelatihs');
    }
};
