<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\PusherHelper;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    public function updateLocation(Request $request)
    {
        $request->validate([
            'latitude'     => 'required|numeric',
            'longitude'    => 'required|numeric',
            'status_aktif' => 'required|string',
            'speed'        => 'nullable|numeric',
            'heading'      => 'nullable|numeric',
        ]);

        $personnel = $request->user()->personnel;
        if (!$personnel) {
            return response()->json(['message' => 'Data personel tidak ditemukan'], 404);
        }

        $status = $request->status_aktif;

        $personnel->update([
            'latitude'             => $request->latitude,
            'longitude'            => $request->longitude,
            'status_aktif'         => $status,
            'speed'                => max(0, $request->speed   ?? 0),
            'heading'              => max(0, $request->heading ?? 0),
            'last_location_update' => now(),
        ]);

        $pusher = new PusherHelper();

        // ✅ Jika status offline → broadcast PersonnelOffline agar marker langsung hilang
        if ($status === 'offline') {
            try {
                $pusher->trigger('patrol-locations', 'PersonnelOffline', [
                    'id' => (int) $personnel->id,
                ]);
            } catch (\Exception $e) {
                \Log::warning('Pusher PersonnelOffline gagal: ' . $e->getMessage());
            }
        } else {
            // Broadcast lokasi normal
            $broadcastData = [
                'id'           => (int)    $personnel->id,
                'nama_lengkap' => (string) $personnel->nama_lengkap,
                'pangkat'      => (string) $personnel->pangkat,
                'latitude'     => (float)  $request->latitude,
                'longitude'    => (float)  $request->longitude,
                'status_aktif' => (string) $status,
                'foto_profil'  => $personnel->foto_profil,
                'nrp'          => (string) ($personnel->nrp ?? ''),
                'speed'        => (float)  max(0, $request->speed   ?? 0),
                'heading'      => (float)  max(0, $request->heading ?? 0),
                'ts'           => now()->timestamp * 1000,
            ];

            try {
                $pusher->trigger('patrol-locations', 'LocationUpdated', $broadcastData);
            } catch (\Exception $e) {
                \Log::warning('Pusher LocationUpdated gagal: ' . $e->getMessage());
            }
        }

        return response()->json(['message' => 'Lokasi berhasil diperbarui']);
    }
}