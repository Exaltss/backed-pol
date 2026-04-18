<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'personnel_id',
        'tanggal',
        'shift',
        'lokasi_target',
        'latitude',  // Tambahkan ini
        'longitude', // Tambahkan ini
        'status', 
    ];

    public function personnel()
    {
        return $this->belongsTo(Personnel::class);
    }
}