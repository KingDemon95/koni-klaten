<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('publikasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('judul', 180);
            $table->string('slug', 220)->unique();
            $table->string('jenis', 20)->index();
            $table->string('kategori', 80)->nullable()->index();
            $table->string('ringkasan', 500)->nullable();
            $table->longText('konten')->nullable();
            $table->string('media_path')->nullable();
            $table->string('media_original_name')->nullable();
            $table->string('thumbnail_path')->nullable();
            $table->string('thumbnail_original_name')->nullable();
            $table->string('status', 20)->default('draft')->index();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['jenis', 'status', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publikasis');
    }
};
