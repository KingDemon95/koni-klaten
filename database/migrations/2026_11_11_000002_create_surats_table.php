<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabang_olahraga_id')->nullable()->constrained('cabang_olahraga')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('staff_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('admin_koni_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('perihal', 200);
            $table->string('jenis_surat', 40)->default('permohonan');
            $table->string('nomor_surat', 100)->nullable();
            $table->date('tanggal_surat');
            $table->text('ringkasan')->nullable();

            $table->string('file_path');
            $table->string('file_original_name');
            $table->string('staff_attachment_path')->nullable();
            $table->string('staff_attachment_original_name')->nullable();
            $table->string('admin_response_path')->nullable();
            $table->string('admin_response_original_name')->nullable();

            $table->string('status', 40)->default('draft')->index();
            $table->string('keputusan_admin', 20)->nullable();
            $table->text('catatan_staff')->nullable();
            $table->text('catatan_admin')->nullable();
            $table->timestamp('diajukan_at')->nullable();
            $table->timestamp('diteruskan_at')->nullable();
            $table->timestamp('diputuskan_at')->nullable();
            $table->timestamp('disampaikan_at')->nullable();
            $table->timestamps();

            $table->index(['cabang_olahraga_id', 'status']);
            $table->index(['status', 'created_at']);
        });

        Schema::create('surat_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surat_id')->constrained('surats')->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 60);
            $table->string('status_from', 40)->nullable();
            $table->string('status_to', 40)->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->index(['surat_id', 'created_at']);
        });

        Schema::create('template_surats', function (Blueprint $table) {
            $table->id();
            $table->string('judul', 180);
            $table->text('deskripsi')->nullable();
            $table->string('file_path');
            $table->string('file_original_name');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('template_surats');
        Schema::dropIfExists('surat_activities');
        Schema::dropIfExists('surats');
    }
};
