<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Laporan Digital</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; color: #333; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 1px solid #444; padding: 6px; text-align: left; word-wrap: break-word; vertical-align: top; }
        th { background-color: #f2f2f2; font-weight: bold; text-transform: uppercase; }
        .footer { margin-top: 40px; text-align: right; font-size: 11px; }
        .img-bukti { width: 90px; height: 70px; object-fit: cover; border: 1px solid #ccc; border-radius: 4px; display: block; margin: 0 auto; }
        .text-center { text-align: center; }
        .video-placeholder { background: #1a1a2e; color: #FFD700; padding: 10px 6px; text-align: center; border-radius: 4px; font-size: 9px; border: 1px solid #FFD700; }
    </style>
</head>
<body>
    <div class="header">
        <h2 style="margin:0;">POLRES TULUNGAGUNG</h2>
        <h3 style="margin:5px 0;">{{ $title }}</h3>
        <p style="margin:0;">Target: <b>{{ $target }}</b> | Periode: {{ $periode }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:25px;">No</th>
                <th style="width:70px;">Waktu</th>
                <th style="width:100px;">Personel</th>
                <th style="width:100px;">Judul</th>
                <th>Deskripsi</th>
                <th style="width:100px;" class="text-center">Media</th>
                <th style="width:70px;">Status</th>
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
            @endphp
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td>{{ $item->created_at->format('d/m/Y') }}<br>{{ $item->created_at->format('H:i') }} WIB</td>
                <td>{{ $item->personnel->nama_lengkap ?? '-' }}<br><small>({{ $item->personnel->pangkat ?? '' }})</small></td>
                <td>{{ $item->judul_kejadian }}</td>
                <td>{{ $item->deskripsi }}</td>
                <td class="text-center">
                    @if($isVid)
                        <div class="video-placeholder">
                            🎬 VIDEO<br>TERLAMPIR<br>
                            <span style="font-size:8px;color:#aaa;">{{ basename($item->foto_bukti) }}</span>
                        </div>
                    @elseif($base64)
                        <img src="{{ $base64 }}" class="img-bukti">
                    @else
                        <small style="color:#999;font-style:italic;">Tanpa Media</small>
                    @endif
                </td>
                <td class="text-center">{{ strtoupper($item->status_penanganan) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Tulungagung, {{ date('d F Y') }}</p>
        <p>Dicetak: {{ $date }} WIB</p>
        <br><br><br>
        <p><b>__________________________</b></p>
        <p>Admin Command Center</p>
    </div>
</body>
</html>