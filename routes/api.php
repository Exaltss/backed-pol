<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TrackingController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// --- Rute Publik ---
Route::post('/login', [AuthController::class, 'login']);

// --- Rute Terproteksi (Harus Login / Sanctum) ---
Route::middleware('auth:sanctum')->group(function () {
    
    // 1. Ambil Data User Profile BESERTA Relasi Personnel
    Route::get('/user', function (Request $request) {
        return $request->user()->load('personnel');
    });
    
    Route::get('/locations', function () {
    // Ambil personil yang statusnya tidak offline
    return \App\Models\Personnel::where('status_aktif', '!=', 'offline')->get();
});

    // 2. UPDATE FOTO PROFIL PERSONEL (Baru)
    // Menghubungkan ke fungsi updatePhoto di DashboardController
    Route::post('/user/photo', [DashboardController::class, 'updatePhoto']);

    // 3. AMBIL LOKASI SEMUA PERSONEL AKTIF (UNTUK MAP FLUTTER)
    Route::get('/locations', [DashboardController::class, 'getLocations']);

    // 4. Ambil Jadwal Khusus Mobile
    Route::get('/jadwal-mobile', [DashboardController::class, 'getJadwalMobile']);

    // 5. Ambil Instruksi Terbaru (Polling)
    Route::get('/latest-instruction', [DashboardController::class, 'getLatestInstruction']);

    // 6. Ambil Ringkasan Laporan (Counter Checkpoint)
    Route::get('/ringkasan-laporan', [DashboardController::class, 'getRingkasanLaporan']);

    // 7. Update Lokasi Realtime (Tracking Saya)
    Route::post('/tracking', [TrackingController::class, 'updateLocation']);
    
    // 8. Rute Laporan & Checkpoint
    Route::get('/reports', [ReportController::class, 'index']);
    Route::post('/reports', [ReportController::class, 'store']);
    
    // 9. Logout
    Route::post('/logout', [AuthController::class, 'logout']);
});