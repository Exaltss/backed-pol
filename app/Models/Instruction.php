<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Instruction extends Model
{
    use HasFactory;

    protected $fillable = [
        'judul',
        'tipe_instruksi', // Baru
        'isi_instruksi',
        'latitude',       // Baru
        'longitude',      // Baru
        'personnel_id'    // Jika null = Broadcast Semua
    ];

    public function personnel()
    {
        return $this->belongsTo(Personnel::class, 'personnel_id');
    }
}