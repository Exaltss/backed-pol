<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Checkpoint</title>
    <style>
        @page { margin: 0.8cm; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 10px; color: #333; line-height: 1.4; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 15px; }
        .header h2 { margin: 0; font-size: 16px; text-transform: uppercase; }
        .info-rekap { width: 100%; margin-bottom: 15px; }
        .info-rekap td { padding: 2px 0; }
        .data-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .data-table th, .data-table td { border: 1px solid #333; padding: 6px; text-align: left; vertical-align: top; word-wrap: break-word; }
        .data-table th { background: #f2f2f2; text-align: center; text-transform: uppercase; font-weight: bold; }
        .text-center { text-align: center; }
        .coords { font-family: "Courier New", monospace; font-size: 9px; color: #0044cc; font-weight: bold; }
        .foto-img { max-width: 100px; height: auto; border: 1px solid #ccc; border-radius: 4px; }
        .video-placeholder { background: #1a1a2e; color: #FFD700; padding: 10px 6px; text-align: center; border-radius: 4px; font-size: 9px; border: 1px solid #FFD700; }
        .footer { margin-top: 30px; text-align: right; font-size: 9px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $title }}</h2>
        <p>Sistem Pantau Keamanan & GPS Realtime - Polres Tulungagung</p>
    </div>

    <table class="info-rekap">
        <tr>
            <td width="15%"><strong>Periode</strong></td>
            <td width="35%">: {{ $periode }}</td>
            <td width="15%"><strong>Waktu Cetak</strong></td>
            <td width="35%">: {{ $date }}</td>
        </tr>
        <tr>
            <td><strong>Target</strong></td>
            <td>: {{ $target }}</td>
            <td><strong>Total</strong></td>
            <td>: {{ $laporan->count() }} Data</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="14%">Waktu & Petugas</th>
                <th width="18%">Peristiwa & GPS</th>
                <th width="30%">Keterangan</th>
                <th width="12%">Prioritas</th>
                <th width="22%">Dokumentasi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($laporan as $i => $item)
            @php
                $isVid = $item->foto_bukti &&
                    in_array(strtolower(pathinfo($item->foto_bukti, PATHINFO_EXTENSION)), ['mp4','mov','avi','3gp','mkv','webm']);
                $base64 = null;
                if ($item->foto_bukti && !$isVid) {
                    $path = public_path($item->foto_bukti);
                    if (file_exists($path)) {
                        $type = pathinfo($path, PATHINFO_EXTENSION);
                        $base64 = 'data:image/' . $type . ';base64,' . base64_encode(file_get_contents($path));
                    }
                }
                $prio = strtolower($item->prioritas ?? '');
            @endphp
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td>
                    <strong>{{ $item->created_at->format('d/m/Y') }}</strong><br>
                    Jam: {{ $item->created_at->format('H:i') }} WIB<br><br>
                    <u>{{ $item->personnel->nama_lengkap ?? '-' }}</u>
                </td>
                <td>
                    <strong>{{ $item->judul_kejadian ?? '-' }}</strong><br><br>
                    <span class="coords">Lat: {{ $item->latitude }}<br>Lng: {{ $item->longitude }}</span>
                </td>
                <td>{!! nl2br(e($item->deskripsi ?? '-')) !!}</td>
                <td class="text-center">
                    @if($prio == 'tinggi')
                        <b style="color:red;">TINGGI</b>
                    @elseif($prio == 'rendah')
                        <b style="color:green;">RENDAH</b>
                    @else
                        <b>NORMAL</b>
                    @endif
                </td>
                <td class="text-center">
                    @if($isVid)
                        <div class="video-placeholder">
                            🎬 VIDEO<br>TERLAMPIR<br>
                            <span style="font-size:8px;color:#aaa;">{{ basename($item->foto_bukti) }}</span>
                        </div>
                    @elseif($base64)
                        <img src="{{ $base64 }}" class="foto-img">
                    @else
                        <small style="color:#ccc;">(Tanpa Media)</small>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak otomatis oleh Sistem Digital Monitoring Polres Tulungagung pada {{ date('d F Y') }}</p>
    </div>
</body>
</html>