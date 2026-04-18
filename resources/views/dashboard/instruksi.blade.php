@extends('layouts.admin')

@section('title', 'Instruksi Personel')
@section('header-title', 'Kirim Instruksi & Komando')

@section('content')

<style>
    /* Reset Style Popup Leaflet */
    .leaflet-popup-content-wrapper {
        border-radius: 8px !important;
        padding: 0 !important;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3) !important;
    }
    .leaflet-popup-content {
        margin: 0 !important;
        width: 300px !important;
    }
    .leaflet-popup-close-button {
        top: 8px !important;
        right: 8px !important;
        color: white !important;
        text-shadow: 0 0 2px rgba(0,0,0,0.5);
        font-size: 18px !important;
    }
    
    /* Layout Kartu Info di dalam Peta */
    .gmaps-card {
        font-family: 'Roboto', Arial, sans-serif;
        background: white;
    }
    .gmaps-header {
        background-color: #4285F4; /* Google Blue */
        color: white;
        padding: 12px 15px;
        font-size: 14px;
        font-weight: 500;
        display: flex;
        align-items: center;
    }
    .gmaps-body { padding: 15px; }
    .gmaps-title {
        font-size: 16px;
        font-weight: 600;
        color: #202124;
        margin-bottom: 5px;
        line-height: 1.3;
    }
    .gmaps-text {
        font-size: 13px;
        color: #3C4043;
        line-height: 1.5;
        margin-bottom: 5px;
    }
    .coord-box {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        padding: 6px;
        border-radius: 4px;
        font-family: monospace;
        font-size: 12px;
        color: #d63384;
        text-align: center;
        margin-top: 5px;
    }
    .gmaps-footer {
        border-top: 1px solid #E8EAED;
        display: flex;
    }
    .gmaps-btn {
        flex: 1;
        text-align: center;
        padding: 12px 0;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        color: #1A73E8;
        background: white;
        transition: 0.2s;
    }
    .gmaps-btn:hover { background: #F1F3F4; color: #174EA6; }
    .gmaps-btn:first-child { border-right: 1px solid #E8EAED; }
</style>

{{-- GANTI kartu SOS yang lama dengan ini: --}}
<div class="card card-custom bg-danger text-white mb-4 shadow">
    <div class="card-body">
        <h5 class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i>SINYAL DARURAT</h5>
        <p class="mb-3 small">Kirim peringatan bahaya ke <strong>seluruh</strong> atau <strong>satu personel</strong>.</p>
        <form action="{{ route('instruksi.store') }}" method="POST"
              onsubmit="return confirm('Yakin kirim SOS DARURAT?');">
            @csrf
            <input type="hidden" name="is_darurat" value="1">
            <input type="hidden" name="tipe_instruksi" value="darurat">
            <input type="hidden" name="judul" value="🚨 SINYAL SOS DARURAT">
            <input type="hidden" name="isi_instruksi" value="SINYAL DARURAT DIKIRIM — SEGERA BERSIAGA DAN HUBUNGI KOMANDO!">
            <div class="row g-2 align-items-end">
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-white">Target Kirim:</label>
                    <select name="personnel_id" class="form-select text-dark fw-bold">
                        <option value="all">🔴 SEMUA PERSONEL (Broadcast)</option>
                        @foreach($personnels as $p)
                            <option value="{{ $p->id }}">{{ $p->nama_lengkap }} — {{ $p->pangkat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <button type="submit" class="btn btn-light text-danger fw-bold px-4 py-2 w-100">
                        <i class="bi bi-broadcast me-2"></i>KIRIM SOS SEKARANG
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="row">
    <div class="col-md-5">
        <div class="card card-custom bg-white p-4 h-100">
            <h6 class="fw-bold mb-3">Buat Instruksi Baru</h6>
            
            <form action="{{ route('instruksi.store') }}" method="POST">
                @csrf
                
                <div class="mb-3">
                    <label class="form-label fw-bold small">Target Personel</label>
                    <select name="personnel_id" class="form-select">
                        <option value="all">-- Semua Personel --</option>
                        @foreach($personnels as $p)
                            <option value="{{ $p->id }}">{{ $p->nama_lengkap }} ({{ $p->pangkat }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small">Jenis Instruksi</label>
                    <select name="tipe_instruksi" id="tipe_instruksi" class="form-select" onchange="toggleMap()">
                        <option value="pesan">Pesan Teks Biasa</option>
                        <option value="lokasi">Menuju Lokasi (Rute)</option>
                        <option value="patroli">Perintah Patroli Khusus</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small">Isi Perintah</label>
                    <select name="isi_instruksi" id="pilihan_instruksi" class="form-select mb-2" onchange="fillMessage()">
                        <option value="">-- Pilih Template Instruksi --</option>
                        <option value="Lakukan penyekatan di titik koordinat berikut.">Lakukan Penyekatan (Blokade)</option>
                        <option value="Segera merapat ke lokasi TKP untuk bantuan.">Bantuan Personel (Backup)</option>
                        <option value="Laksanakan patroli dialogis di area ini.">Patroli Dialogis</option>
                        <option value="Bubarkan kerumunan di lokasi terpilih.">Pembubaran Massa</option>
                        <option value="custom">-- Ketik Manual --</option>
                    </select>
                    
                    <input type="text" name="judul" id="judul" class="form-control mb-2" placeholder="Judul Instruksi" required>
                    <textarea name="isi_instruksi" id="text_instruksi" class="form-control" rows="3" placeholder="Detail instruksi..." required></textarea>
                </div>

                <input type="hidden" name="latitude" id="lat">
                <input type="hidden" name="longitude" id="lng">

                <button type="submit" class="btn btn-primary w-100 py-2">
                    <i class="bi bi-send-fill me-2"></i>Kirim Instruksi
                </button>
            </form>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card card-custom bg-white p-0 h-100" id="map-wrapper" style="display:none;">
            <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Klik peta untuk pilih tujuan.</small>
                <small class="text-primary fw-bold" id="status-pick">Menunggu Pilihan...</small>
            </div>
            <div id="map-picker" style="height: 380px; width: 100%;"></div>
        </div>
        
        <div class="card card-custom bg-light p-5 text-center h-100 d-flex justify-content-center align-items-center" id="map-placeholder">
            <div class="text-muted">
                <i class="bi bi-map fs-1 text-secondary"></i>
                <p class="mt-2 small">Pilih Jenis Instruksi <strong>"Menuju Lokasi"</strong><br>untuk membuka peta.</p>
            </div>
        </div>
    </div>
</div>

<h5 class="fw-bold mt-4 mb-3">Riwayat Instruksi Terkirim</h5>
<div class="card card-custom bg-white p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th width="15%">Waktu</th>
                    <th width="20%">Target</th>
                    <th width="25%">Judul & Tipe</th>
                    <th>Isi / Lokasi</th>
                    <th width="10%" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($instruksi as $ins)
                <tr class="{{ $ins->tipe_instruksi == 'darurat' ? 'table-danger' : '' }}">
                    <td>
                        <span class="fw-bold">{{ $ins->created_at->format('d M') }}</span><br>
                        <small class="text-muted">{{ $ins->created_at->format('H:i') }}</small>
                    </td>
                    <td>
                        @if($ins->personnel)
                            <span class="badge bg-primary text-wrap">{{ $ins->personnel->nama_lengkap }}</span>
                        @else
                            <span class="badge bg-dark">SEMUA PERSONEL</span>
                        @endif
                    </td>
                    <td>
                        <strong class="d-block text-truncate" style="max-width: 150px;">{{ $ins->judul }}</strong>
                        <span class="badge {{ $ins->tipe_instruksi == 'darurat' ? 'bg-danger' : 'bg-secondary' }}" style="font-size: 10px;">
                            {{ strtoupper($ins->tipe_instruksi) }}
                        </span>
                    </td>
                    <td>
                        <span class="d-block text-truncate" style="max-width: 200px;">{{ $ins->isi_instruksi }}</span>
                        @if($ins->latitude)
                            <a href="https://www.google.com/maps?q={{$ins->latitude}},{{$ins->longitude}}" target="_blank" class="btn btn-xs btn-outline-primary py-0 mt-1" style="font-size: 10px;">
                                <i class="bi bi-geo-alt-fill"></i> Lihat Rute
                            </a>
                        @endif
                    </td>
                    <td class="text-center">
                        <form action="{{ route('instruksi.destroy', $ins->id) }}" method="POST" onsubmit="return confirm('Batalkan instruksi ini? Data akan dihapus.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Batalkan/Hapus">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    // 1. DEFINISI LAYER GOOGLE MAPS
    var googleStreets = L.tileLayer('http://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}',{
        maxZoom: 21, subdomains:['mt0','mt1','mt2','mt3'], attribution: 'Â© Google'
    });
    var googleHybrid = L.tileLayer('http://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}',{
        maxZoom: 21, subdomains:['mt0','mt1','mt2','mt3'], attribution: 'Â© Google'
    });

    // 2. INISIALISASI PETA
    var mapPicker = L.map('map-picker', {
        center: [-8.0739, 111.9015], // Default Tulungagung
        zoom: 13,
        layers: [googleStreets], // Default Street View
        zoomControl: false
    });
    
    L.control.zoom({ position: 'topleft' }).addTo(mapPicker);
    L.control.layers({ "Peta Jalan": googleStreets, "Satelit": googleHybrid }).addTo(mapPicker);
    
    var pickedMarker = null;
    var detailPopup = L.popup();

    // 3. EVENT KLIK PETA (PILIH LOKASI + INFO DETAIL)
    mapPicker.on('click', function(e) {
        var lat = e.latlng.lat;
        var lng = e.latlng.lng;

        // A. Update Form Input Hidden
        document.getElementById('lat').value = lat;
        document.getElementById('lng').value = lng;
        
        // Update Status Teks
        document.getElementById('status-pick').innerText = "Titik Terpilih!";
        document.getElementById('status-pick').className = "text-success fw-bold";

        // B. Tampilkan Marker
        if (pickedMarker) {
            pickedMarker.setLatLng(e.latlng);
        } else {
            pickedMarker = L.marker(e.latlng, {draggable: true}).addTo(mapPicker);
            
            // Jika marker digeser, update juga koordinatnya
            pickedMarker.on('dragend', function(event){
                var position = event.target.getLatLng();
                document.getElementById('lat').value = position.lat;
                document.getElementById('lng').value = position.lng;
            });
        }

        // C. Tampilkan Popup Detail (Loading)
        detailPopup.setLatLng(e.latlng).setContent('<div class="p-3 text-center">Mengambil info lokasi...</div>').openOn(mapPicker);

        // D. Ambil Detail Alamat (Nominatim)
        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`)
        .then(res => res.json())
        .then(data => {
            var name = data.address.amenity || data.address.building || "Lokasi Terpilih";
            var fullAddress = data.display_name;
            var mapsUrl = `http://maps.google.com/maps?q=&layer=c&cbll=${lat},${lng}`;
            var routeUrl = `http://maps.google.com/maps?q=${lat},${lng}`;

            var content = `
            <div class="gmaps-card">
                <div class="gmaps-header">
                    <i class="bi bi-geo-alt-fill me-2"></i> Konfirmasi Titik Instruksi
                </div>
                <div class="gmaps-body">
                    <div class="gmaps-title">${name}</div>
                    <div class="gmaps-text">${fullAddress}</div>
                    <div class="coord-box">${lat.toFixed(6)}, ${lng.toFixed(6)}</div>
                </div>
                <div class="gmaps-footer">
                    <a href="${mapsUrl}" target="_blank" class="gmaps-btn text-primary">
                        <i class="bi bi-person-bounding-box me-1"></i> Cek Street View
                    </a>
                    <a href="${routeUrl}" target="_blank" class="gmaps-btn text-success">
                        <i class="bi bi-cursor-fill me-1"></i> Cek Rute
                    </a>
                </div>
            </div>`;
            
            detailPopup.setContent(content);
        });
    });

    // 4. TOGGLE MAP (Show/Hide)
    function toggleMap() {
        var tipe = document.getElementById('tipe_instruksi').value;
        var mapWrapper = document.getElementById('map-wrapper');
        var mapPlaceholder = document.getElementById('map-placeholder');

        if (tipe === 'lokasi') {
            mapWrapper.style.display = 'block';
            mapPlaceholder.style.display = 'none';
            setTimeout(function(){ mapPicker.invalidateSize(); }, 300);
        } else {
            mapWrapper.style.display = 'none';
            mapPlaceholder.style.display = 'flex';
            document.getElementById('lat').value = '';
            document.getElementById('lng').value = '';
        }
    }

    // 5. AUTO FILL PESAN (TEMPLATE)
    function fillMessage() {
        var select = document.getElementById('pilihan_instruksi');
        var text = document.getElementById('text_instruksi');
        var judul = document.getElementById('judul');
        
        if (select.value === 'custom') {
            text.value = '';
            judul.value = '';
        } else if (select.value !== '') {
            text.value = select.value;
            judul.value = select.options[select.selectedIndex].text;
        }
    }
</script>
@endpush