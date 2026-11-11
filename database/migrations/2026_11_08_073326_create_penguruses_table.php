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
        Schema::create('pengurus', function (Blueprint $table) {
            $table->id();

            // Relasi ke cabor, NULL berarti pengurus KONI
            $table->foreignId('cabang_olahraga_id')
                ->nullable()
                ->constrained('cabang_olahraga')
                ->restrictOnDelete();

            // Biodata
            $table->string('nama_lengkap', 150);

            $table->string('jabatan', 120);

            $table->string('bidang', 120)->nullable();

            $table->string('jenis_kelamin', 1)->nullable();

            // Periode kepengurusan
            $table->unsignedSmallInteger('periode_mulai')
                ->nullable();

            $table->unsignedSmallInteger('periode_selesai')
                ->nullable();

            // Kontak
            $table->string('no_telepon', 30)->nullable();

            $table->string('email')->nullable();

            // Foto
            $table->string('foto_path')->nullable();

            // Urutan tampil
            $table->unsignedSmallInteger('urutan')
                ->default(0);

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
        Schema::dropIfExists('pengurus');
    }
};
