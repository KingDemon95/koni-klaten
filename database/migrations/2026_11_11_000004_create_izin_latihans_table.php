<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('izin_latihans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('cabang_olahraga_id')->constrained('cabang_olahraga')->restrictOnDelete();
            $table->string('jenis_pemohon', 20);
            $table->string('nama_pemohon', 150);
            $table->string('tujuan', 180);
            $table->text('keperluan');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai')->nullable();
            $table->string('file_path');
            $table->string('file_original_name');
            $table->string('status', 30)->default('diajukan')->index();
            $table->timestamps();

            $table->index(['cabang_olahraga_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('izin_latihans');
    }
};
