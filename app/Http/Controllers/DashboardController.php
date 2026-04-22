<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Personnel;
use App\Models\Report;
use App\Models\Schedule;
use App\Models\Instruction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class DashboardController extends Controller
{
    // ── VIEW: HALAMAN UTAMA MONITORING ────────────────────────────
    public function index()
    {
        return view('dashboard.monitoring');
    }

    // ── UPDATE FOTO PROFIL DARI MOBILE ────────────────────────────
    public function updatePhoto(Request $request)
    {
        $request->validate([
            'foto' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $personnel = $request->user()->personnel;
        if (!$personnel) {
            return response()->json(['message' => 'Data personel tidak ditemukan'], 404);
        }

        if ($request->hasFile('foto')) {
            if ($personnel->foto_profil) {
                @unlink(public_path($personnel->foto_profil));
            }
            $file     = $request->file('foto');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('profile_photos'), $filename);
            $path = 'profile_photos/' . $filename;
            $personnel->update(['foto_profil' => $path]);

            return response()->json([
                'message' => 'Foto profil berhasil diperbarui',
                'url'     => url($path),
            ]);
        }

        return response()->json(['message' => 'Gagal mengunggah foto'], 400);
    }

    // ── VIEW: LAPORAN ─────────────────────────────────────────────
    public function laporan(Request $request)
    {
        $query      = Report::where('tipe_laporan', 'aduan/kejadian')->with('personnel');
        $personnels = Personnel::all();

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }
        if ($request->filled('personnel_id') && $request->personnel_id != 'all') {
            $query->where('personnel_id', $request->personnel_id);
        }

        $laporan = $query->latest()->get();
        return view('dashboard.laporan', compact('laporan', 'personnels'));
    }

    // ── EXPORT PDF: LAPORAN ───────────────────────────────────────
    public function exportPdf(Request $request)
    {
        $query = Report::where('tipe_laporan', 'aduan/kejadian')->with('personnel');

        if ($request->filled('from_date')) { $query->whereDate('created_at', '>=', $request->from_date); }
        if ($request->filled('to_date'))   { $query->whereDate('created_at', '<=', $request->to_date); }

        $targetName = 'Semua Personel';
        if ($request->filled('personnel_id') && $request->personnel_id != 'all') {
            $query->where('personnel_id', $request->personnel_id);
            $p = Personnel::find($request->personnel_id);
            $targetName = $p ? $p->nama_lengkap : 'Personel';
        }

        $data = [
            'title'   => 'REKAPITULASI LAPORAN ADUAN DIGITAL',
            'date'    => date('d/m/Y H:i'),
            'target'  => $targetName,
            'periode' => ($request->from_date ?? 'Awal') . ' s/d ' . ($request->to_date ?? 'Sekarang'),
            'laporan' => $query->latest()->get(),
        ];

        $pdf = Pdf::loadView('dashboard.pdf_laporan', $data)->setPaper('a4', 'landscape');
        return $pdf->download('Rekap_Laporan_' . str_replace(' ', '_', $targetName) . '_' . date('Ymd_His') . '.pdf');
    }

    public function checkNotification()
    {
        $count = Report::where('tipe_laporan', 'aduan/kejadian')
            ->where('status_penanganan', 'menunggu konfirmasi')
            ->count();
        return response()->json(['unread_count' => $count]);
    }

    // ── VIEW: CHECKPOINT ──────────────────────────────────────────
    public function checkpoint(Request $request)
    {
        $query      = Report::where('tipe_laporan', 'checkpoint')->with('personnel');
        $personnels = Personnel::all();

        if ($request->filled('from_date')) { $query->whereDate('created_at', '>=', $request->from_date); }
        if ($request->filled('to_date'))   { $query->whereDate('created_at', '<=', $request->to_date); }
        if ($request->filled('personnel_id') && $request->personnel_id != 'all') {
            $query->where('personnel_id', $request->personnel_id);
        }

        $checkpoints = $query->latest()->get();
        return view('dashboard.checkpoint', compact('checkpoints', 'personnels'));
    }

    // ── EXPORT PDF: CHECKPOINT ────────────────────────────────────
    public function exportCheckpointPdf(Request $request)
    {
        $query = Report::where('tipe_laporan', 'checkpoint')->with('personnel');

        if ($request->filled('from_date')) { $query->whereDate('created_at', '>=', $request->from_date); }
        if ($request->filled('to_date'))   { $query->whereDate('created_at', '<=', $request->to_date); }

        $targetName = 'Semua Personel';
        if ($request->filled('personnel_id') && $request->personnel_id != 'all') {
            $query->where('personnel_id', $request->personnel_id);
            $p = Personnel::find($request->personnel_id);
            $targetName = $p ? $p->nama_lengkap : 'Personel';
        }

        $data = [
            'title'   => 'REKAPITULASI PERISTIWA CHECKPOINT PERSONEL',
            'date'    => date('d/m/Y H:i'),
            'target'  => $targetName,
            'periode' => ($request->from_date ?? 'Awal') . ' s/d ' . ($request->to_date ?? 'Sekarang'),
            'laporan' => $query->latest()->get(),
        ];

        $pdf = Pdf::loadView('dashboard.pdf_checkpoint', $data)->setPaper('a4', 'landscape');
        return $pdf->download('Rekap_Checkpoint_' . str_replace(' ', '_', $targetName) . '_' . date('Ymd_His') . '.pdf');
    }

    // ── VIEW: JADWAL ──────────────────────────────────────────────
    public function jadwal()
    {
        $jadwal     = Schedule::with('personnel')->latest()->get();
        $personnels = Personnel::all();
        return view('dashboard.jadwal', compact('jadwal', 'personnels'));
    }

    // ── VIEW: INSTRUKSI ───────────────────────────────────────────
    public function instruksi()
    {
        $personnels = Personnel::all();
        $instruksi  = Instruction::with('personnel')->latest()->get();
        return view('dashboard.instruksi', compact('instruksi', 'personnels'));
    }

    public function storeJadwal(Request $request)
    {
        $validated = $request->validate([
            'personnel_id'  => 'required|exists:personnels,id',
            'tanggal'       => 'required|date',
            'shift'         => 'required|string',
            'lokasi_target' => 'required|string',
            'latitude'      => 'nullable',
            'longitude'     => 'nullable',
        ]);
        Schedule::create($validated);
        return back()->with('success', 'Jadwal berhasil ditambahkan');
    }

    public function destroyJadwal($id)
    {
        Schedule::findOrFail($id)->delete();
        return back()->with('success', 'Jadwal dihapus');
    }

    public function storeInstruksi(Request $request)
    {
        $request->validate([
            'judul'          => 'required',
            'tipe_instruksi' => 'required',
            'isi_instruksi'  => 'required',
        ]);

        Instruction::create([
            'judul'          => $request->judul,
            'tipe_instruksi' => $request->tipe_instruksi,
            'isi_instruksi'  => $request->isi_instruksi,
            'latitude'       => $request->latitude,
            'longitude'      => $request->longitude,
            'personnel_id'   => ($request->personnel_id === 'all' || !$request->personnel_id)
                                    ? null
                                    : $request->personnel_id,
        ]);

        return back()->with('success', 'Instruksi berhasil dikirim');
    }

    public function destroyInstruksi($id)
    {
        Instruction::findOrFail($id)->delete();
        return back()->with('success', 'Instruksi dihapus');
    }

    public function updateStatusLaporan($id, $status)
    {
        Report::findOrFail($id)->update(['status_penanganan' => $status]);
        return back()->with('success', 'Status laporan diperbarui');
    }

    public function destroyLaporan($id)
    {
        $l = Report::findOrFail($id);
        if ($l->foto_bukti) {
            @unlink(public_path($l->foto_bukti));
        }
        $l->delete();
        return back()->with('success', 'Laporan berhasil dihapus');
    }

    // =========================================================================
    // API PETA WEB (MONITORING)
    // =========================================================================

    public function getLocations()
    {
        $today = \Carbon\Carbon::today();

        $personnels = Personnel::select(
                'id', 'nama_lengkap', 'pangkat',
                'latitude', 'longitude', 'status_aktif',
                'foto_profil', 'nrp',
                'speed', 'heading'   // ← untuk smooth interpolation
            )
            ->where('status_aktif', '!=', 'offline')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        $result = $personnels->map(function ($p) use ($today) {
            $schedules = Schedule::where('personnel_id', $p->id)
                ->whereDate('tanggal', $today)
                ->orderBy('id', 'asc')
                ->get(['id', 'tanggal', 'shift', 'lokasi_target', 'latitude', 'longitude'])
                ->toArray();

            $instr = Instruction::where(function ($q) use ($p) {
                $q->whereNull('personnel_id')
                  ->orWhere('personnel_id', $p->id);
            })->latest()->first();

            return [
                'id'           => (int)    $p->id,
                'nama_lengkap' => (string) $p->nama_lengkap,
                'pangkat'      => (string) $p->pangkat,
                'latitude'     => (float)  $p->latitude,
                'longitude'    => (float)  $p->longitude,
                'status_aktif' => (string) $p->status_aktif,
                'foto_profil'  => $p->foto_profil,
                'nrp'          => (string) ($p->nrp ?? ''),
                'speed'        => (float)  ($p->speed   ?? 0),  // m/s
                'heading'      => (float)  ($p->heading ?? 0),  // derajat
                'schedules'    => $schedules,
                'latest_instruction' => $instr ? [
                    'id'             => (int)    $instr->id,
                    'judul'          => (string) $instr->judul,
                    'latitude'       => $instr->latitude,
                    'longitude'      => $instr->longitude,
                    'tipe_instruksi' => (string) $instr->tipe_instruksi,
                ] : null,
            ];
        })->values();

        return response()->json($result)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    // ── CHECKPOINT JSON ───────────────────────────────────────────
    public function getCheckpointsJson()
    {
        $checkpoints = Report::where('tipe_laporan', 'checkpoint')
            ->with('personnel')
            ->latest()
            ->take(200)
            ->get();

        $result = $checkpoints->map(function ($item) {
            $mediaUrl = null;
            $isVideo  = false;
            if ($item->foto_bukti) {
                $ext      = strtolower(pathinfo($item->foto_bukti, PATHINFO_EXTENSION));
                $isVideo  = in_array($ext, ['mp4', 'mov', 'avi', '3gp', 'mkv', 'webm']);
                $mediaUrl = url('/' . ltrim($item->foto_bukti, '/'));
            }

            return [
                'id'                => $item->id,
                'tipe_laporan'      => $item->tipe_laporan,
                'judul'             => $item->judul_kejadian ?? '-',
                'deskripsi'         => $item->deskripsi ?? '-',
                'prioritas'         => $item->prioritas ?? 'normal',
                'latitude'          => $item->latitude,
                'longitude'         => $item->longitude,
                'status'            => $item->status_penanganan,
                'waktu'             => $item->created_at->format('d/m/Y H:i') . ' WIB',
                'media_url'         => $mediaUrl,
                'is_video'          => $isVideo,
                'personnel_nama'    => $item->personnel->nama_lengkap ?? 'Petugas',
                'personnel_pangkat' => $item->personnel->pangkat ?? '-',
            ];
        })->values();

        return response()->json($result)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    // =========================================================================
    // API KHUSUS MOBILE (FLUTTER)
    // =========================================================================

    public function getLatestInstruction(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->personnel) {
            return response()->json(['id' => 0, 'judul' => '-', 'isi' => 'Data tidak valid', 'tipe' => 'normal']);
        }

        $pId = $user->personnel->id;
        $l   = Instruction::where(function ($q) use ($pId) {
            $q->whereNull('personnel_id')->orWhere('personnel_id', $pId);
        })->latest()->first();

        return response()->json([
            'id'        => $l ? $l->id           : 0,
            'judul'     => $l ? $l->judul         : '-',
            'isi'       => $l ? $l->isi_instruksi : '-',
            'tipe'      => $l ? $l->tipe_instruksi: 'normal',
            'latitude'  => $l ? $l->latitude      : null,
            'longitude' => $l ? $l->longitude     : null,
        ]);
    }

    public function getJadwalMobile(Request $request)
    {
        return response()->json([
            'success' => true,
            'data'    => Schedule::where('personnel_id', $request->user()->personnel->id)
                            ->latest()->take(10)->get(),
        ]);
    }

    public function getRingkasanLaporan(Request $request)
    {
        $pId = $request->user()->personnel->id;
        return response()->json([
            'success'          => true,
            'laporan_count'    => Report::where('personnel_id', $pId)->where('tipe_laporan', 'aduan/kejadian')->count(),
            'checkpoint_count' => Report::where('personnel_id', $pId)->where('tipe_laporan', 'checkpoint')->count(),
        ]);
    }
}