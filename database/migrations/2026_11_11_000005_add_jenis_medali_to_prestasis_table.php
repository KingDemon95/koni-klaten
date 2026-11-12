<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prestasis', function (Blueprint $table) {
            // Nullable agar data prestasi lama tetap kompatibel. Jenis medali
            // hanya dihitung jika dicatat secara eksplisit oleh pengunggah.
            $table->string('jenis_medali', 20)->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('prestasis', function (Blueprint $table) {
            $table->dropIndex(['jenis_medali']);
            $table->dropColumn('jenis_medali');
        });
    }
};
