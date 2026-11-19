<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table): void {
            $table->id();
            $table->string('kode_barang', 60)->nullable()->unique();
            $table->foreignId('inventory_category_id')->constrained('inventory_categories')->restrictOnDelete();
            $table->foreignId('cabang_olahraga_id')->nullable()->constrained('cabang_olahraga')->restrictOnDelete();
            $table->string('nama_barang', 180);
            $table->text('deskripsi')->nullable();
            $table->unsignedInteger('jumlah')->default(1);
            $table->string('satuan', 30)->default('unit');
            $table->string('kondisi', 30)->default('baik')->index();
            $table->string('lokasi', 150);
            $table->date('tanggal_perolehan')->nullable();
            $table->decimal('harga_perolehan', 15, 2)->nullable();
            $table->string('status', 20)->default('aktif')->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['cabang_olahraga_id', 'status']);
            $table->index(['inventory_category_id', 'kondisi']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
