<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'personnel_id',
        'tipe_laporan',
        'judul_kejadian',
        'deskripsi',
        'prioritas',
        'latitude',
        'longitude',
        'foto_bukti',
        'status_penanganan',
    ];

    public function personnel()
    {
        return $this->belongsTo(Personnel::class);
    }
}