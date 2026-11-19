<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_loans', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->restrictOnDelete();
            $table->string('peminjam', 150);
            $table->string('kontak_peminjam', 60)->nullable();
            $table->string('unit_peminjam', 150)->nullable();
            $table->unsignedInteger('jumlah');
            $table->date('tanggal_pinjam');
            $table->date('batas_pengembalian');
            $table->timestamp('tanggal_dikembalikan')->nullable();
            $table->string('status', 20)->default('dipinjam')->index();
            $table->string('kondisi_pengembalian', 30)->nullable();
            $table->text('catatan')->nullable();
            $table->text('catatan_pengembalian')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('returned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['inventory_item_id', 'status']);
            $table->index(['status', 'batas_pengembalian']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_loans');
    }
};
