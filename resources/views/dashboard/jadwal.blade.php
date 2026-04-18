@extends('layouts.admin')

@section('title', 'Jadwal Personel')
@section('header-title', 'Manajemen Penjadwalan Personel')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<div class="row">
    <div class="col-md-5">
        <div class="card card-custom p-3 bg-white mb-3 shadow-sm">
            <h6 class="fw-bold mb-3"><i class="bi bi-calendar-plus me-2"></i>Buat Jadwal Baru</h6>
            
            @if(session('success'))
                <div class="alert alert-success small p-2">{{ session('success') }}</div>
            @endif

            <form action="{{ route('jadwal.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label small fw-bold">Personel</label>
                    <select name="personnel_id" class="form-select">
                        @foreach($personnels as $p)
                            <option value="{{ $p->id }}">{{ $p->nama_lengkap }} ({{ $p->pangkat }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-bold">Tanggal</label>
                        <input type="date" name="tanggal" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-bold">Shift</label>
                        <select name="shift" class="form-select">
                            <option value="Pagi (08:00 - 16:00)">Pagi</option>
                            <option value="Siang (16:00 - 00:00)">Siang</option>
                            <option value="Malam (00:00 - 08:00)">Malam</option>
                        </select>
                    </div>
                </div>

                <div class="mb-2">
                    <label class="form-label small fw-bold text-primary">Pilih Lokasi Target di Peta:</label>
                    <div id="map-selection" style="height: 250px; border-radius: 8px; border: 1px solid #ddd;"></div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Nama Lokasi Target</label>
                    <input type="text" name="lokasi_target" id="lokasi_target" class="form-control" placeholder="Klik pada peta..." required readonly>
                </div>

                <input type="hidden" name="latitude" id="lat_input">
                <input type="hidden" name="longitude" id="lng_input">

                <button type="submit" class="btn btn-primary w-100 fw-bold">Simpan Jadwal Patroli</button>
            </form>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card card-custom p-3 bg-white shadow-sm">
            <h6 class="fw-bold mb-3"><i class="bi bi-list-task me-2"></i>Daftar Jadwal Terbaru</h6>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Waktu & Shift</th>
                            <th>Personel</th>
                            <th>Target Lokasi</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th> </tr>
                    </thead>
                    <tbody>
                        @forelse($jadwal as $j)
                        <tr>
                            <td>
                                <span class="fw-bold">{{ $j->tanggal }}</span><br>
                                <small class="text-muted">{{ $j->shift }}</small>
                            </td>
                            <td>{{ $j->personnel->nama_lengkap }}</td>
                            <td>
                                <i class="bi bi-geo-alt-fill text-danger"></i> {{ $j->lokasi_target }}
                            </td>
                            <td><span class="badge bg-secondary">{{ $j->status ?? 'Terjadwal' }}</span></td>
                            
                            <td class="text-center">
                                <form action="{{ route('jadwal.destroy', $j->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus jadwal ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-link text-danger p-0">
                                        <i class="bi bi-trash-fill fs-5"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center py-4">Belum ada jadwal yang dibuat.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    var map = L.map('map-selection').setView([-8.0667, 111.9000], 13);
    var gStreet = L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}',{
    maxZoom:21, subdomains:['mt0','mt1','mt2','mt3'], attribution:'© Google'
});
var gSat = L.tileLayer('https://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}',{
    maxZoom:21, subdomains:['mt0','mt1','mt2','mt3'], attribution:'© Google'
});
gStreet.addTo(map);
L.control.layers({"Peta Jalan":gStreet,"Satelit":gSat}).addTo(map);
L.control.zoom({position:'topleft'}).addTo(map);
map.zoomControl.remove(); // hapus default zoom

    var marker;

    map.on('click', function(e) {
        var lat = e.latlng.lat;
        var lng = e.latlng.lng;

        if (marker) {
            marker.setLatLng(e.latlng);
        } else {
            marker = L.marker(e.latlng).addTo(map);
        }

        document.getElementById('lat_input').value = lat;
        document.getElementById('lng_input').value = lng;

        document.getElementById('lokasi_target').value = "Mencari lokasi...";
        
        fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`)
            .then(response => response.json())
            .then(data => {
                var address = data.display_name;
                var shortAddress = address.split(',').slice(0, 3).join(',');
                document.getElementById('lokasi_target').value = shortAddress;
            })
            .catch(error => {
                document.getElementById('lokasi_target').value = lat.toFixed(5) + ", " + lng.toFixed(5);
            });
    });
</script>
@endsection