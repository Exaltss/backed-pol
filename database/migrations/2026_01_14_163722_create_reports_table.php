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
    Schema::create('reports', function (Blueprint $table) {
        $table->id();
        $table->foreignId('personnel_id')->constrained('personnels')->onDelete('cascade');
        $table->enum('tipe_laporan', ['checkpoint', 'aduan/kejadian']);
        $table->string('judul_kejadian'); // Contoh: Kemacetan Pasar
        $table->text('deskripsi');
        $table->enum('prioritas', ['rendah', 'sedang', 'tinggi']);
        $table->decimal('latitude', 10, 8); // Lokasi kejadian
        $table->decimal('longitude', 11, 8);
        $table->string('foto_bukti')->nullable(); // Path gambar
        $table->enum('status_penanganan', ['menunggu', 'diproses', 'selesai'])->default('menunggu');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
