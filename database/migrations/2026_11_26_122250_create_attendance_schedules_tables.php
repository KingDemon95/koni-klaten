<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_schedules', function (Blueprint $table): void {
            $table->id();
            $table->string('schedule_type', 20);
            $table->foreignId('cabang_olahraga_id')->nullable()
                ->constrained('cabang_olahraga')->nullOnDelete();
            $table->foreignId('created_by')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->string('title', 160);
            $table->date('effective_from');
            $table->date('effective_until')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(
                ['schedule_type', 'cabang_olahraga_id', 'is_active'],
                'attendance_schedules_type_cabor_active_idx'
            );
            $table->index(
                ['effective_from', 'effective_until'],
                'attendance_schedules_effective_dates_idx'
            );
        });

        Schema::create('attendance_schedule_slots', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('attendance_schedule_id')
                ->constrained('attendance_schedules')->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week');
            $table->string('label', 80)->nullable();
            $table->time('starts_at');
            $table->time('ends_at');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(
                ['attendance_schedule_id', 'day_of_week', 'is_active'],
                'attendance_schedule_slots_day_active_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_schedule_slots');
        Schema::dropIfExists('attendance_schedules');
    }
};
