<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('izin_latihans', function (Blueprint $table) {
            $table->text('catatan_admin')->nullable()->after('status');
            $table->foreignId('diproses_oleh')
                ->nullable()
                ->after('catatan_admin')
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('diproses_at')->nullable()->after('diproses_oleh');
        });

        Schema::table('pengurus', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->nullable()
                ->unique()
                ->after('id')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pengurus', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropUnique(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('izin_latihans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('diproses_oleh');
            $table->dropColumn(['catatan_admin', 'diproses_at']);
        });
    }
};
