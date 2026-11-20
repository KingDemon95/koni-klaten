<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('must_change_password')->default(false)->after('password');
            $table->timestamp('password_changed_at')->nullable()->after('must_change_password');
        });

        if (Schema::hasTable('model_has_roles') && Schema::hasTable('roles')) {
            $temporaryPasswordUserIds = DB::table('model_has_roles')
                ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->where('model_has_roles.model_type', 'App\\Models\\User')
                ->whereIn('roles.name', [
                    'admin_cabor',
                    'atlet',
                    'pelatih',
                    'staff_koni',
                ])
                ->pluck('model_has_roles.model_id');

            if ($temporaryPasswordUserIds->isNotEmpty()) {
                DB::table('users')
                    ->whereIn('id', $temporaryPasswordUserIds)
                    ->update(['must_change_password' => true]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['must_change_password', 'password_changed_at']);
        });
    }
};
