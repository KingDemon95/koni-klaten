<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('attendance_sessions', 'target_roles')) {
            Schema::table('attendance_sessions', function (Blueprint $table): void {
                $table->json('target_roles')->nullable()->after('target_role');
            });
        }

        if (! Schema::hasColumn('attendance_records', 'checked_out_at')) {
            Schema::table('attendance_records', function (Blueprint $table): void {
                $table->dateTime('checked_out_at')->nullable()->after('checked_in_at');
            });
        }

        if (! Schema::hasColumn('attendance_records', 'check_out_selfie_path')) {
            Schema::table('attendance_records', function (Blueprint $table): void {
                $table->string('check_out_selfie_path')->nullable()->after('selfie_path');
            });
        }

        // Migrate existing session audiences to the explicit role list.
        // Legacy "all" sessions now include every internal role; Public is excluded.
        $allInternalRoles = [
            'admin_koni',
            'staff_koni',
            'ketua_koni',
            'sekretaris_koni',
            'admin_cabor',
            'atlet',
            'pelatih',
        ];

        foreach (DB::table('attendance_sessions')->whereNull('target_roles')->get(['id', 'target_role']) as $session) {
            $roles = match ($session->target_role) {
                'atlet' => ['atlet'],
                'pelatih' => ['pelatih'],
                'admin_koni', 'staff_koni', 'ketua_koni', 'sekretaris_koni', 'admin_cabor' => [$session->target_role],
                default => $allInternalRoles,
            };

            DB::table('attendance_sessions')
                ->where('id', $session->id)
                ->update(['target_roles' => json_encode($roles, JSON_THROW_ON_ERROR)]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('attendance_records', 'check_out_selfie_path')) {
            Schema::table('attendance_records', function (Blueprint $table): void {
                $table->dropColumn('check_out_selfie_path');
            });
        }

        if (Schema::hasColumn('attendance_records', 'checked_out_at')) {
            Schema::table('attendance_records', function (Blueprint $table): void {
                $table->dropColumn('checked_out_at');
            });
        }

        if (Schema::hasColumn('attendance_sessions', 'target_roles')) {
            Schema::table('attendance_sessions', function (Blueprint $table): void {
                $table->dropColumn('target_roles');
            });
        }
    }
};
