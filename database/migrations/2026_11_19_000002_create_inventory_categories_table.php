<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cabang_olahraga_id')->nullable()->constrained('cabang_olahraga')->restrictOnDelete();
            $table->string('nama', 100);
            $table->text('deskripsi')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['cabang_olahraga_id', 'nama'], 'inventory_categories_scope_name_unique');
            $table->index('cabang_olahraga_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_categories');
    }
};
