<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surats', function (Blueprint $table): void {
            $table->string('decision_by_role', 40)->nullable()->after('admin_koni_id');
        });
    }

    public function down(): void
    {
        Schema::table('surats', function (Blueprint $table): void {
            $table->dropColumn('decision_by_role');
        });
    }
};
