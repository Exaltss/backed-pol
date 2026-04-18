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
    Schema::table('instructions', function (Blueprint $table) {
        // Menambah kolom koordinat target (nullable, karena tidak semua instruksi butuh lokasi)
        $table->decimal('latitude', 10, 8)->nullable()->after('isi_instruksi');
        $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
        // Kolom tipe instruksi
        $table->string('tipe_instruksi')->default('pesan')->after('judul'); 
    });
}

public function down(): void
{
    Schema::table('instructions', function (Blueprint $table) {
        $table->dropColumn(['latitude', 'longitude', 'tipe_instruksi']);
    });
}
};
