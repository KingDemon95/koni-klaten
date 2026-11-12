<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prestasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('atlet_id')->nullable()->constrained('atlets')->cascadeOnDelete();
            $table->foreignId('pelatih_id')->nullable()->constrained('pelatihs')->cascadeOnDelete();
            $table->foreignId('cabang_olahraga_id')->constrained('cabang_olahraga')->restrictOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nama_kejuaraan', 180);
            $table->string('tingkat', 30)->index();
            $table->string('kategori', 120)->nullable();
            $table->string('hasil', 120);
            $table->string('penyelenggara', 180)->nullable();
            $table->date('tanggal_prestasi')->index();
            $table->text('keterangan')->nullable();
            $table->string('bukti_path');
            $table->string('bukti_original_name');
            $table->string('status', 30)->default('menunggu_verifikasi')->index();
            $table->text('catatan_verifikasi')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index(['cabang_olahraga_id', 'status']);
            $table->index(['atlet_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prestasis');
    }
};
