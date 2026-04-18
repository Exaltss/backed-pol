<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    // Helper: cek apakah file adalah video
    private function isVideo(string $path): bool
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return in_array($ext, ['mp4', 'mov', 'avi', '3gp', 'mkv', 'webm']);
    }

    public function index(Request $request)
    {
        try {
            $personnelId = $request->user()->personnel->id;
            $reports = Report::where('personnel_id', $personnelId)
                ->whereIn('tipe_laporan', ['aduan/kejadian', 'checkpoint'])
                ->latest()
                ->get();
            return response()->json(['success' => true, 'data' => $reports]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            // Validasi — terima foto DAN video
            $request->validate([
                'judul_kejadian' => 'required|string',
                'tipe_laporan'   => 'required|string',
                'latitude'       => 'required',
                'longitude'      => 'required',
                'foto_bukti'     => 'nullable|file|max:102400', // 100MB max
            ]);

            if (!$request->user()->personnel) {
                return response()->json(['success' => false, 'message' => 'Akun belum terhubung ke data personel.'], 403);
            }

            $path = null;
            if ($request->hasFile('foto_bukti') && $request->file('foto_bukti')->isValid()) {
                $file = $request->file('foto_bukti');
                $ext  = strtolower($file->getClientOriginalExtension());
                $isVid = in_array($ext, ['mp4', 'mov', 'avi', '3gp', 'mkv', 'webm']);

                // Simpan ke folder yang sesuai
                $folder = $isVid ? 'reports/videos' : 'reports/photos';

                // Simpan ke public/ langsung agar bisa diakses tanpa symlink
                $filename = time() . '_' . uniqid() . '.' . $ext;
                $file->move(public_path($folder), $filename);
                $path = $folder . '/' . $filename;

                Log::info("File tersimpan: public/$path (video: " . ($isVid ? 'ya' : 'tidak') . ")");
            }

            $report = Report::create([
                'personnel_id'      => $request->user()->personnel->id,
                'tipe_laporan'      => $request->tipe_laporan,
                'judul_kejadian'    => $request->judul_kejadian,
                'deskripsi'         => $request->deskripsi ?? '-',
                'prioritas'         => $request->prioritas ?? 'sedang',
                'latitude'          => $request->latitude,
                'longitude'         => $request->longitude,
                'foto_bukti'        => $path,
                'status_penanganan' => 'menunggu konfirmasi',
            ]);

            return response()->json(['success' => true, 'message' => 'Laporan berhasil dikirim', 'data' => $report], 201);

        } catch (\Exception $e) {
            Log::error('Gagal simpan laporan: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}