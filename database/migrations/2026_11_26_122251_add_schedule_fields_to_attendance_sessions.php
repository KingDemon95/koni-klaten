<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_sessions', function (Blueprint $table): void {
            $table->foreignId('schedule_slot_id')
                ->nullable()
                ->after('cabang_olahraga_id')
                ->constrained('attendance_schedule_slots')
                ->nullOnDelete();

            $table->date('attendance_date')
                ->nullable()
                ->after('schedule_slot_id');

            $table->string('attendance_mode', 20)
                ->nullable()
                ->after('attendance_date');

            $table->unique(
                ['schedule_slot_id', 'attendance_date'],
                'attendance_sessions_schedule_slot_date_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('attendance_sessions', function (Blueprint $table): void {
            $table->dropUnique(
                'attendance_sessions_schedule_slot_date_unique'
            );

            $table->dropConstrainedForeignId('schedule_slot_id');

            $table->dropColumn([
                'attendance_date',
                'attendance_mode',
            ]);
        });
    }
};
