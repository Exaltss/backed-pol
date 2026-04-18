@extends('layouts.admin')

@section('title', 'Checkpoint Log Report')
@section('header-title', 'Laporan Lokasi Checkpoint')

@section('content')
<div class="card card-custom p-4 bg-white shadow-sm border-0">
    
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="mb-4 bg-light p-3 rounded border">
        <form action="{{ route('checkpoint') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label text-muted small fw-bold">Dari Tanggal</label>
                <input type="date" name="from_date" class="form-control form-control-sm" value="{{ request('from_date') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted small fw-bold">Sampai Tanggal</label>
                <input type="date" name="to_date" class="form-control form-control-sm" value="{{ request('to_date') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted small fw-bold">Pilih Personel</label>
                <select name="personnel_id" class="form-select form-select-sm">
                    <option value="all">-- Semua Personel --</option>
                    @foreach($personnels as $p)
                        <option value="{{ $p->id }}" {{ request('personnel_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->nama_lengkap }} ({{ $p->pangkat }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 text-end">
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="bi bi-funnel-fill"></i> Filter
                    </button>
                    <a href="{{ route('checkpoint.pdf', request()->all()) }}" class="btn btn-sm btn-danger">
                        <i class="bi bi-file-earmark-pdf-fill"></i> Export PDF
                    </a>
                </div>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Waktu</th>
                    <th>Personel</th>
                    <th>Lokasi Checkpoint</th>
                    <th>Keterangan Singkat</th>
                    <th>Prioritas</th>
                    <th class="text-center" style="min-width: 240px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($checkpoints as $item)
                <tr>
                    <td>{{ $item->created_at->format('H:i - d M Y') }}</td>
                    <td>{{ $item->personnel->nama_lengkap ?? 'Personel' }}</td>
                    <td>
                        <i class="bi bi-geo-alt-fill text-danger"></i> 
                        {{ $item->judul_kejadian ?? $item->judul_laporan ?? 'Titik Checkpoint' }}
                    </td>
                    <td style="max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        {{ $item->deskripsi ?? $item->isi_laporan ?? '-' }}
                    </td>
                    <td>
                        @if($item->prioritas == 'tinggi' || str_contains(strtolower($item->deskripsi ?? ''), 'tinggi'))
                            <span class="badge bg-danger">TINGGI</span>
                        @elseif($item->prioritas == 'rendah' || str_contains(strtolower($item->deskripsi ?? ''), 'rendah'))
                            <span class="badge bg-success">RENDAH</span>
                        @else
                            <span class="badge bg-info">NORMAL</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-info text-white mb-1" style="border-radius: 8px;" data-bs-toggle="modal" data-bs-target="#detailModal{{ $item->id }}" title="Lihat Detail">
                            <i class="bi bi-eye-fill"></i>
                        </button>

                        <a href="{{ action([\App\Http\Controllers\DashboardController::class, 'index']) }}?cp_id={{ $item->id }}" class="btn btn-sm btn-primary text-white mb-1" style="border-radius: 8px;" title="Lihat di Peta">
                            <i class="bi bi-map-fill"></i> Peta
                        </a>

                        <form action="{{ route('laporan.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Peringatan: Apakah Anda yakin ingin menghapus data checkpoint ini beserta foto buktinya?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger text-white mb-1" style="border-radius: 8px;" title="Hapus Data">
                                <i class="bi bi-trash-fill"></i> Hapus
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
                
                @if($checkpoints->isEmpty())
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        <i class="bi bi-journal-x fs-1 d-block mb-2"></i>
                        Belum ada data checkpoint.
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

@foreach($checkpoints as $item)
<div class="modal fade" id="detailModal{{ $item->id }}" tabindex="-1" aria-labelledby="detailModalLabel{{ $item->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="detailModalLabel{{ $item->id }}">
                    <i class="bi bi-card-checklist me-2"></i> Detail Laporan Checkpoint
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3 text-center">
                        <p class="fw-bold mb-2 text-muted">Foto Bukti:</p>
                        @if($item->foto_bukti)
                            <a href="{{ asset($item->foto_bukti) }}" target="_blank" title="Klik untuk perbesar">
                                <img src="{{ asset($item->foto_bukti) }}" alt="Foto Checkpoint" class="img-fluid rounded shadow-sm" style="max-height: 350px; width: 100%; object-fit: cover; border: 1px solid #ddd;">
                            </a>
                        @else
                            <div class="bg-light d-flex flex-column align-items-center justify-content-center rounded shadow-sm" style="height: 250px; border: 2px dashed #ccc;">
                                <i class="bi bi-image text-muted mb-2" style="font-size: 3rem;"></i>
                                <span class="text-muted">Tidak ada foto dilampirkan</span>
                            </div>
                        @endif
                    </div>
                    
                    <div class="col-md-6">
                        <p class="fw-bold mb-2 text-muted">Informasi Laporan:</p>
                        <table class="table table-borderless table-sm">
                            <tr>
                                <th style="width: 35%; color: #555;">Personel</th>
                                <td class="fw-bold">: {{ $item->personnel->nama_lengkap ?? 'Personel' }}</td>
                            </tr>
                            <tr>
                                <th style="color: #555;">Waktu</th>
                                <td>: {{ $item->created_at->format('d M Y, H:i') }} WIB</td>
                            </tr>
                            <tr>
                                <th style="color: #555;">Judul</th>
                                <td>: {{ $item->judul_kejadian ?? $item->judul_laporan ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th style="color: #555;">Koordinat GPS</th>
                                <td>: 
                                    <a href="https://maps.google.com/?q={{ $item->latitude }},{{ $item->longitude }}" target="_blank" class="text-decoration-none">
                                        {{ $item->latitude }}, {{ $item->longitude }} <i class="bi bi-box-arrow-up-right ms-1" style="font-size: 10px;"></i>
                                    </a>
                                </td>
                            </tr>
                        </table>
                        
                        <div class="mt-3">
                            <p class="fw-bold mb-1" style="color: #555;">Keterangan / Kronologi Lengkap:</p>
                            <div class="p-3 rounded border" style="background-color: #f8f9fa; white-space: pre-wrap; font-size: 14px; min-height: 100px;">{{ $item->deskripsi ?? $item->isi_laporan ?? 'Tidak ada keterangan tambahan.' }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endforeach
<script>
// Auto-reload tabel setiap 15 detik tanpa full-refresh halaman
setInterval(function(){
    fetch(window.location.href)
    .then(r => r.text())
    .then(html => {
        var parser = new DOMParser();
        var doc = parser.parseFromString(html, 'text/html');
        var newTable = doc.querySelector('.table-responsive');
        var curTable = document.querySelector('.table-responsive');
        if(newTable && curTable) curTable.innerHTML = newTable.innerHTML;
    }).catch(()=>{});
}, 15000);
</script>
@endsection