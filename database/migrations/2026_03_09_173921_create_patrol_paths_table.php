<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('patrol_paths', function (Blueprint $table) {
        $table->id();
        $table->foreignId('personnel_id')->constrained('personnels')->onDelete('cascade');
        $table->string('nama_rute'); // Misal: Jalur Protokol Kota
        $table->json('coordinates'); // Array koordinat: [[lat, lng], [lat, lng], ...]
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patrol_paths');
    }
};
