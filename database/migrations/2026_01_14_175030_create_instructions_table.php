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
    Schema::create('instructions', function (Blueprint $table) {
        $table->id();
        $table->string('judul');
        $table->text('isi_instruksi');
        // Jika null, berarti instruksi untuk SEMUA personel
        $table->foreignId('personnel_id')->nullable()->constrained('personnels')->onDelete('cascade');
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instructions');
    }
};
