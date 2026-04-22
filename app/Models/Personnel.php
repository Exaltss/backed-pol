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
        'status_aktif',
        'latitude',
        'longitude',
        'last_location_update',
        'speed',    // ← kecepatan (m/s) untuk interpolasi smooth
        'heading',  // ← arah hadap (0-360 derajat) untuk interpolasi smooth
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'personnel_id');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'personnel_id');
    }
}