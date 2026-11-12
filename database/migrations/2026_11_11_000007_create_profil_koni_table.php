<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profil_koni', function (Blueprint $table) {
            $table->id();
            $table->string('nama_organisasi', 180);
            $table->string('nama_singkat', 80);
            $table->string('tagline', 255)->nullable();
            $table->text('deskripsi')->nullable();
            $table->longText('sejarah')->nullable();
            $table->text('visi')->nullable();
            $table->text('misi')->nullable();
            $table->text('alamat')->nullable();
            $table->string('telepon', 40)->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('instagram')->nullable();
            $table->string('facebook')->nullable();
            $table->string('youtube')->nullable();
            $table->string('logo_path')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        DB::table('profil_koni')->insert([
            'nama_organisasi' => 'Komite Olahraga Nasional Indonesia Kabupaten Klaten',
            'nama_singkat' => 'KONI Klaten',
            'tagline' => 'Bersama membangun prestasi olahraga Kabupaten Klaten.',
            'deskripsi' => 'Komite Olahraga Nasional Indonesia (KONI) Kabupaten Klaten merupakan wadah koordinasi pembinaan dan pengembangan olahraga prestasi di tingkat kabupaten.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('profil_koni');
    }
};
