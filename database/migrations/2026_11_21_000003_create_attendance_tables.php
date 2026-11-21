<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const SESSION_CABOR_INDEX = 'att_rec_session_cabor_idx';

    public function up(): void
    {
        // Make this migration safe to resume after MySQL committed the table
        // creation but rejected the original overlong generated index name.
        if (! Schema::hasTable('attendance_sessions')) {
            Schema::create('attendance_sessions', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('cabang_olahraga_id')->nullable()->constrained('cabang_olahraga')->nullOnDelete();
                $table->string('title', 160);
                $table->text('description')->nullable();
                $table->string('target_role', 20)->default('all');
                $table->string('token', 64)->unique();
                $table->dateTime('starts_at')->index();
                $table->dateTime('ends_at')->index();
                $table->string('status', 20)->default('open')->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('attendance_records')) {
            Schema::create('attendance_records', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('attendance_session_id')->constrained('attendance_sessions')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('cabang_olahraga_id')->nullable()->constrained('cabang_olahraga')->nullOnDelete();
                $table->string('participant_name', 150);
                $table->string('participant_email')->nullable();
                $table->string('role_snapshot', 30);
                $table->string('selfie_path');
                $table->dateTime('checked_in_at')->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();

                $table->unique(['attendance_session_id', 'user_id']);
            });
        }

        // Use a short explicit name. The previous generated name exceeded
        // MySQL's 64-character identifier limit.
        if (! $this->indexExists('attendance_records', self::SESSION_CABOR_INDEX)) {
            Schema::table('attendance_records', function (Blueprint $table): void {
                $table->index(
                    ['attendance_session_id', 'cabang_olahraga_id'],
                    self::SESSION_CABOR_INDEX
                );
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
        Schema::dropIfExists('attendance_sessions');
    }

    private function indexExists(string $table, string $indexName): bool
    {
        return collect(Schema::getIndexes($table))->contains(
            fn (array $index): bool => ($index['name'] ?? null) === $indexName
        );
    }
};
