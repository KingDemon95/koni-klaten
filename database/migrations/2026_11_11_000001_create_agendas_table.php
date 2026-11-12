<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agendas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabang_olahraga_id')
                ->nullable()
                ->constrained('cabang_olahraga')
                ->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('judul', 180);
            $table->string('slug', 220)->unique();
            $table->string('jenis', 20)->default('kegiatan')->index();
            $table->text('deskripsi')->nullable();
            $table->date('tanggal_mulai')->index();
            $table->date('tanggal_selesai')->nullable();
            $table->time('waktu_mulai')->nullable();
            $table->time('waktu_selesai')->nullable();
            $table->string('lokasi', 180)->nullable();
            $table->string('poster_path')->nullable();
            $table->string('status', 20)->default('draft')->index();
            $table->text('catatan_revisi')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'tanggal_mulai']);
            $table->index(['cabang_olahraga_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agendas');
    }
};
