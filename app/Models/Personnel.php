<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Personnel extends Model
{
    use HasFactory;

    protected $table = 'personnels';

    protected $fillable = [
        'user_id',
        'nama_lengkap',
        'nrp',
        'pangkat',
        'foto_profil',
        'status_aktif',       // offline, online, patroli, siaga, darurat
        'latitude',
        'longitude',
        'last_location_update',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'personnel_id');
    }

    // --- TAMBAHKAN RELASI INI ---
    /**
     * Relasi ke Model Schedule (Satu personel punya banyak jadwal)
     */
    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'personnel_id');
    }
}