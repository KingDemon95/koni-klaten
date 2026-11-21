<?php

use App\Models\WebsiteAppearanceSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('website_appearance_settings')) {
            Schema::create('website_appearance_settings', function (Blueprint $table): void {
                $table->id();
                $table->json('draft_config');
                $table->json('published_config');
                $table->dateTime('published_at')->nullable();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! DB::table('website_appearance_settings')->where('id', 1)->exists()) {
            $defaults = WebsiteAppearanceSetting::defaultConfig();

            DB::table('website_appearance_settings')->insert([
                'id' => 1,
                'draft_config' => json_encode($defaults, JSON_THROW_ON_ERROR),
                'published_config' => json_encode($defaults, JSON_THROW_ON_ERROR),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('website_appearance_settings');
    }
};
