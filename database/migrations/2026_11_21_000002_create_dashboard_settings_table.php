<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dashboard_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('backdrop_path')->nullable();
            $table->unsignedTinyInteger('overlay_opacity')->default(55);
            $table->string('background_position', 20)->default('center');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        DB::table('dashboard_settings')->insert([
            'id' => 1,
            'overlay_opacity' => 55,
            'background_position' => 'center',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboard_settings');
    }
};
