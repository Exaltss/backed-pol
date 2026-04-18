<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    public function updateLocation(Request $request) {
        // Validasi data yang masuk
        $request->validate([
            'latitude' => 'required',
            'longitude' => 'required',
            'status_aktif' => 'required' // Pastikan ini 'status_aktif'
        ]);

        // Ambil personel yang sedang login
        $personnel = $request->user()->personnel;

        if (!$personnel) {
            return response()->json(['message' => 'Data personel tidak ditemukan'], 404);
        }
        
        // Update koordinat dan status
        $personnel->update([
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'status_aktif' => $request->status_aktif,
            'last_location_update' => now()
        ]);

        return response()->json(['message' => 'Lokasi berhasil diperbarui']);
    }
}