<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Api\AuthController as ApiAuthController;
use App\Http\Controllers\Api\TrackingController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WebAuthController;
use App\Http\Controllers\PersonnelController;

Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [WebAuthController::class, 'login']);
Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/get-locations', [DashboardController::class, 'getLocations'])->name('dashboard.data');
    Route::get('/get-checkpoints-json', [DashboardController::class, 'getCheckpointsJson'])->name('dashboard.checkpoints');
    Route::get('/laporan-digital', [DashboardController::class, 'laporan'])->name('laporan');
    Route::get('/laporan-digital/export-pdf', [DashboardController::class, 'exportPdf'])->name('laporan.pdf');
    Route::get('/check-new-reports', [DashboardController::class, 'checkNotification'])->name('laporan.check');
    Route::get('/checkpoint-log', [DashboardController::class, 'checkpoint'])->name('checkpoint');
    Route::get('/checkpoint-log/export-pdf', [DashboardController::class, 'exportCheckpointPdf'])->name('checkpoint.pdf');
    Route::delete('/dashboard/laporan/{id}', [DashboardController::class, 'destroyLaporan'])->name('laporan.destroy');
    Route::get('/dashboard/laporan/update/{id}/{status}', [DashboardController::class, 'updateStatusLaporan'])->name('laporan.status');
    Route::get('/jadwal-personel', [DashboardController::class, 'jadwal'])->name('jadwal');
    Route::post('/jadwal-personel', [DashboardController::class, 'storeJadwal'])->name('jadwal.store');
    Route::delete('/dashboard/jadwal/{id}', [DashboardController::class, 'destroyJadwal'])->name('jadwal.destroy');
    Route::get('/instruksi', [DashboardController::class, 'instruksi'])->name('instruksi');
    Route::post('/instruksi', [DashboardController::class, 'storeInstruksi'])->name('instruksi.store');
    Route::delete('/dashboard/instruksi/{id}', [DashboardController::class, 'destroyInstruksi'])->name('instruksi.destroy');
    Route::get('/get-latest-instruction', [DashboardController::class, 'getLatestInstruction'])->name('instruksi.latest');

    // === MANAJEMEN PERSONEL (BARU) ===
    Route::get('/manajemen-personel', [PersonnelController::class, 'index'])->name('personel.index');
    Route::post('/manajemen-personel', [PersonnelController::class, 'store'])->name('personel.store');
    Route::post('/manajemen-personel/{id}/update', [PersonnelController::class, 'update'])->name('personel.update');
    Route::post('/manajemen-personel/{id}/delete', [PersonnelController::class, 'destroy'])->name('personel.destroy');
});

// === API ROUTES (Flutter) ===
Route::post('/api/login', [ApiAuthController::class, 'login']);
Route::middleware('auth:sanctum')->prefix('api')->group(function () {
    Route::post('/logout', [ApiAuthController::class, 'logout']);
    Route::get('/user', [ApiAuthController::class, 'user']);
    Route::post('/user/photo', [DashboardController::class, 'updatePhoto']);
    Route::get('/locations', [DashboardController::class, 'getLocations']);
    Route::get('/jadwal-mobile', [DashboardController::class, 'getJadwalMobile']);
    Route::get('/latest-instruction', [DashboardController::class, 'getLatestInstruction']);
    Route::get('/ringkasan-laporan', [DashboardController::class, 'getRingkasanLaporan']);
    Route::post('/tracking', [TrackingController::class, 'updateLocation']);
    Route::get('/reports', [ReportController::class, 'index']);
    Route::post('/reports', [ReportController::class, 'store']);
});

// === HELPER ROUTES ===
Route::get('/bersihkan-cache', function () {
    try {
        Artisan::call('config:clear'); Artisan::call('cache:clear');
        Artisan::call('view:clear');   Artisan::call('route:clear');
        return "✅ Cache Berhasil Dibersihkan!";
    } catch (\Exception $e) { return "❌ Gagal: " . $e->getMessage(); }
});

// ⚠️ HAPUS ROUTE INI SETELAH DIJALANKAN SEKALI!
Route::get('/jalankan-seeder-xyz', function () {
    $data = [
        ['nama'=>'Bripda Ahmad Fauzi',    'pangkat'=>'Bripda', 'no_hp'=>'6281234567890','user'=>'personel01'],
        ['nama'=>'Briptu Siti Rahayu',    'pangkat'=>'Briptu', 'no_hp'=>'6281234567891','user'=>'personel02'],
        ['nama'=>'Brigpol Bambang Susilo','pangkat'=>'Brigpol','no_hp'=>'6281234567892','user'=>'personel03'],
        ['nama'=>'Bripda Wahyu Santoso',  'pangkat'=>'Bripda', 'no_hp'=>'6281234567893','user'=>'personel04'],
        ['nama'=>'Briptu Dewi Lestari',   'pangkat'=>'Briptu', 'no_hp'=>'6281234567894','user'=>'personel05'],
        ['nama'=>'Aipda Eko Prasetyo',    'pangkat'=>'Aipda',  'no_hp'=>'6281234567895','user'=>'personel06'],
        ['nama'=>'Aiptu Hendra Kurniawan','pangkat'=>'Aiptu',  'no_hp'=>'6281234567896','user'=>'personel07'],
        ['nama'=>'Bripda Rizky Maulana',  'pangkat'=>'Bripda', 'no_hp'=>'6281234567897','user'=>'personel08'],
        ['nama'=>'Briptu Novita Sari',    'pangkat'=>'Briptu', 'no_hp'=>'6281234567898','user'=>'personel09'],
        ['nama'=>'Brigpol Agus Wijaya',   'pangkat'=>'Brigpol','no_hp'=>'6281234567899','user'=>'personel10'],
    ];
    $n = 0;
    foreach ($data as $p) {
        if (!\App\Models\User::where('username',$p['user'])->exists()) {
            $u = \App\Models\User::create(['username'=>$p['user'],'password'=>Hash::make('patrol123'),'role'=>'personel']);
            \App\Models\Personnel::create(['user_id'=>$u->id,'nama_lengkap'=>$p['nama'],'nrp'=>$p['no_hp'],'pangkat'=>$p['pangkat'],'status_aktif'=>'offline']);
            $n++;
        }
    }
    return "✅ $n personel dibuat. Password: <b>patrol123</b>. <br>⚠️ HAPUS route /jalankan-seeder-xyz dari web.php sekarang!";
});

// Buat symlink storage (jalankan 1x saja)
Route::get('/buat-symlink', function () {
    try {
        Artisan::call('storage:link');
        return "✅ Symlink storage berhasil dibuat! Foto sekarang bisa tampil.";
    } catch (\Exception $e) {
        // Jika symlink sudah ada, tidak apa-apa
        return "✅ Sudah ada atau berhasil: " . $e->getMessage();
    }
});

Route::get('/cleanup-storage-xyz', function () {
    $dir  = public_path('profile_photos');
    $rDir = public_path('reports');       // foto laporan
    $n = 0;
    foreach ([$dir, $rDir] as $d) {
        if (!is_dir($d)) continue;
        foreach (glob($d . '/*') as $f) {
            if (filemtime($f) < strtotime('-90 days')) {
                unlink($f); $n++;
            }
        }
    }
    return "✅ $n file lama dihapus.";
});

Route::get('/run-speed-migration-xyz', function () {
    \Illuminate\Support\Facades\Schema::table('personnels', function ($t) {
        if (!\Illuminate\Support\Facades\Schema::hasColumn('personnels', 'speed')) {
            $t->float('speed', 8, 2)->default(0);
        }
        if (!\Illuminate\Support\Facades\Schema::hasColumn('personnels', 'heading')) {
            $t->float('heading', 8, 2)->default(0);
        }
    });
    return '✅ Kolom speed & heading berhasil ditambahkan!';
});