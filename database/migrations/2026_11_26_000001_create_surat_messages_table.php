<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_messages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('surat_id')->constrained('surats')->cascadeOnDelete();
            $table->foreignId('sender_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('sender_role', 40);
            $table->string('message_type', 20)->default('pesan');
            $table->text('body')->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('attachment_original_name')->nullable();
            $table->timestamps();

            $table->index(['surat_id', 'created_at'], 'surat_messages_surat_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_messages');
    }
};
