<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_fields', function (Blueprint $table): void {
            $table->id();
            $table->string('entity_type', 20)->index();
            $table->string('label', 120);
            $table->string('field_key', 80);
            $table->string('type', 20)->default('text');
            $table->json('options')->nullable();
            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['entity_type', 'field_key']);
            $table->index(['entity_type', 'is_active', 'sort_order']);
        });

        Schema::table('atlets', function (Blueprint $table): void {
            $table->json('custom_data')->nullable();
        });

        Schema::table('pelatihs', function (Blueprint $table): void {
            $table->json('custom_data')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('pelatihs', function (Blueprint $table): void {
            $table->dropColumn('custom_data');
        });

        Schema::table('atlets', function (Blueprint $table): void {
            $table->dropColumn('custom_data');
        });

        Schema::dropIfExists('custom_fields');
    }
};
