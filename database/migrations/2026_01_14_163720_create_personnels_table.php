<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
{
    Schema::create('personnels', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Relasi ke Akun
        $table->string('nama_lengkap');
        $table->string('nrp')->unique();
        $table->string('pangkat');
        $table->string('foto_profil')->nullable();
        $table->enum('status_aktif', ['offline', 'online', 'patroli', 'siaga', 'darurat'])->default('offline');
        // Kolom untuk menyimpan lokasi terakhir (Realtime Tracking)
        $table->decimal('latitude', 10, 8)->nullable();
        $table->decimal('longitude', 11, 8)->nullable();
        $table->timestamp('last_location_update')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personnels');
    }
};
