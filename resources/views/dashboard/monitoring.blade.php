@extends('layouts.admin')
@section('title','GPS Realtime')
@section('header-title','Peta Sebaran Personel')

@section('content')
<style>
    .leaflet-popup-content-wrapper{border-radius:8px!important;padding:0!important;overflow:hidden;box-shadow:0 5px 15px rgba(0,0,0,.3)!important;}
    .leaflet-popup-content{margin:0!important;width:320px!important;}
    .leaflet-popup-close-button{top:8px!important;right:8px!important;color:white!important;font-size:18px!important;}
    .gmaps-card{font-family:'Roboto',Arial,sans-serif;background:white;}
    .gmaps-header{color:white;padding:12px 15px;font-size:14px;font-weight:500;display:flex;align-items:center;}
    .gmaps-body{padding:13px;}
    .gmaps-title{font-size:15px;font-weight:700;color:#202124;margin-bottom:3px;}
    .gmaps-subtitle{font-size:12px;color:#5f6368;margin-bottom:8px;}
    .coord-box{background:#f8f9fa;border:1px solid #e9ecef;padding:5px;border-radius:4px;font-family:monospace;font-size:11px;color:#d63384;text-align:center;margin-top:5px;}
    .gmaps-footer{border-top:1px solid #E8EAED;display:flex;}
    .gmaps-btn{flex:1;text-align:center;padding:10px 0;font-size:12px;font-weight:600;text-decoration:none;background:white;transition:.2s;}
    .gmaps-btn:hover{background:#F1F3F4;}
    .gmaps-btn:first-child{border-right:1px solid #E8EAED;}

    .photo-marker-container{background:transparent;border:none;}
    .photo-marker{width:42px;height:42px;border-radius:50%;border:3px solid #fff;overflow:hidden;
        background:#222b36;box-shadow:0 3px 10px rgba(0,0,0,.4);
        display:flex;justify-content:center;align-items:center;}
    .photo-marker img{width:100%;height:100%;object-fit:cover;}
    .border-online  {border-color:#20c997!important;}
    .border-patroli {border-color:#0d6efd!important;}
    .border-siaga   {border-color:#ffc107!important;}
    .border-darurat {border-color:#dc3545!important;}

    @keyframes pulse-red{
        0%  {transform:scale(.95);box-shadow:0 0 0 0 rgba(220,53,69,.7);}
        70% {transform:scale(1.1);box-shadow:0 0 0 12px rgba(220,53,69,0);}
        100%{transform:scale(.95);box-shadow:0 0 0 0 rgba(220,53,69,.7);}
    }
    .pulse-emergency{animation:pulse-red 1.5s infinite;}

    .btn-stop-focus{
        position:absolute;bottom:30px;left:50%;transform:translateX(-50%);
        z-index:1000;border-radius:30px;padding:10px 25px;font-weight:bold;
        box-shadow:0 4px 15px rgba(220,53,69,.4);display:none;
    }
    #personnel-panel{
        position:absolute;top:10px;right:10px;width:310px;
        background:rgba(255,255,255,.97);border-radius:10px;
        z-index:999;box-shadow:0 5px 15px rgba(0,0,0,.2);overflow:hidden;
    }
    #panel-body{max-height:75vh;overflow-y:auto;padding:8px;}
    #panel-toggle-btn{
        cursor:pointer;background:#0F172A;color:white;border:none;
        width:100%;padding:8px 14px;
        display:flex;justify-content:space-between;align-items:center;
        font-size:13px;font-weight:600;
    }
    .map-container{height:calc(100vh - 120px);position:relative;}

    @media(max-width:768px){
        .map-container{height:60vh;}
        #personnel-panel{width:calc(100% - 20px);top:10px;bottom:auto;left:10px;right:10px;}
        #panel-body{max-height:35vh;}
    }
</style>

<div class="card card-custom p-0 overflow-hidden map-container">
    <div id="map" style="height:100%;width:100%;"></div>

    <button id="btn-stop-focus" class="btn btn-danger btn-stop-focus" onclick="stopFocus()">
        <i class="bi bi-x-circle-fill me-2"></i>Hentikan Fokus
    </button>

    <div id="personnel-panel">
        <button id="panel-toggle-btn" onclick="togglePanel()">
            <span>
                <i class="bi bi-people-fill me-2"></i>Status Personel &nbsp;
                <span id="sync-status" class="badge bg-success" style="font-size:10px;">LIVE</span>
            </span>
            <i class="bi bi-chevron-down" id="panel-chevron"></i>
        </button>
        <div id="panel-body">
            <div class="text-center text-muted small py-3">
                <div class="spinner-border spinner-border-sm text-secondary mb-2" role="status"></div>
                <div>Menghubungkan GPS...</div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.marker.slideto@0.2.0/Leaflet.Marker.SlideTo.js"></script>
<script>
(function () {

    // ── PETA ──────────────────────────────────────────────────────
    const gStreet = L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}',
        { maxZoom:21, subdomains:['mt0','mt1','mt2','mt3'], attribution:'© Google' });
    const gHybrid = L.tileLayer('https://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}',
        { maxZoom:21, subdomains:['mt0','mt1','mt2','mt3'], attribution:'© Google' });

    const map = L.map('map', {
        center:[-8.0739,111.9015], zoom:14, layers:[gStreet], zoomControl:false
    });
    L.control.zoom({ position:'topleft' }).addTo(map);
    L.control.layers({ "Peta Jalan":gStreet, "Satelit":gHybrid }).addTo(map);

    // ── STATE ──────────────────────────────────────────────────────
    const markers        = {};
    const bluePolylines  = {};
    const redPolylines   = {};
    const cpMarkers      = {};
    const cpLayer        = L.layerGroup().addTo(map);

    let focusedId            = null;
    let emergencyPersonnelId = null;
    let panelOpen            = true;

    const urlParams  = new URLSearchParams(window.location.search);
    const targetCpId = urlParams.get('cp_id') ? parseInt(urlParams.get('cp_id')) : null;
    let   cpFocused  = false;

    // ── HELPER: STATUS PROPS ───────────────────────────────────────
    function getStatusProps(raw) {
        const st = (raw || 'online').toLowerCase().trim();
        switch (st) {
            case 'patroli':
                return { label:'SEDANG PATROLI',
                    badge:'<span class="badge bg-primary">PATROLI</span>',
                    color:'#0d6efd', icon:'bi-shield-fill', border:'border-patroli', anim:'' };
            case 'siaga': case 'bersiaga':
                return { label:'BERSIAGA',
                    badge:'<span class="badge bg-warning text-dark">BERSIAGA</span>',
                    color:'#fd7e14', icon:'bi-pause-circle-fill', border:'border-siaga', anim:'' };
            case 'darurat':
                return { label:'KONDISI DARURAT!',
                    badge:'<span class="badge bg-danger">🚨 DARURAT!</span>',
                    color:'#dc3545', icon:'bi-exclamation-triangle-fill',
                    border:'border-darurat', anim:'pulse-emergency' };
            default:
                return { label:'ONLINE',
                    badge:'<span class="badge bg-success">ONLINE</span>',
                    color:'#20c997', icon:'bi-person-fill', border:'border-online', anim:'' };
        }
    }

    // ── HELPER: FOTO URL ───────────────────────────────────────────
    function buildFotoUrl(path, name) {
        if (!path) return 'https://ui-avatars.com/api/?name=' + encodeURIComponent(name) + '&background=222b36&color=fff';
        if (path.startsWith('http')) return path;
        if (path.startsWith('profile_photos/')) return '/' + path;
        return '/storage/' + path;
    }

    // ── HELPER: OSRM ROUTE ─────────────────────────────────────────
    function fetchRoute(sig, cb) {
        fetch('https://router.project-osrm.org/route/v1/driving/' + sig + '?overview=full&geometries=geojson')
            .then(r => r.json())
            .then(d => {
                if (d.code === 'Ok' && d.routes && d.routes.length > 0) {
                    cb(d.routes[0].geometry.coordinates.map(c => [c[1], c[0]]));
                }
            })
            .catch(() => {});
    }

    // ── UPDATE MAP (tiap 1 detik) ──────────────────────────────────
    function updateMap() {
        fetch('{{ url("/get-locations") }}')
            .then(r => r.json())
            .then(data => {
                if (!Array.isArray(data)) return;

                let sidebarHtml = '';
                const activeIds = [];

                // --- Cek darurat (di luar loop utama) ---
                let emergencyPerson = null;
                data.forEach(p => {
                    if ((p.status_aktif || '').toLowerCase() === 'darurat') {
                        emergencyPerson = p;
                    }
                });
                if (emergencyPerson && emergencyPersonnelId !== emergencyPerson.id) {
                    emergencyPersonnelId = emergencyPerson.id;
                    const eLat = parseFloat(emergencyPerson.latitude);
                    const eLng = parseFloat(emergencyPerson.longitude);
                    if (!isNaN(eLat) && !isNaN(eLng)) {
                        map.flyTo([eLat, eLng], 18, { animate:true, duration:1.2 });
                        focusedId = emergencyPerson.id;
                        const btn = document.getElementById('btn-stop-focus');
                        btn.style.display = 'block';
                        btn.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2 text-warning"></i>DARURAT: '
                            + emergencyPerson.nama_lengkap + ' — Hentikan Fokus';
                        setTimeout(() => {
                            if (markers[emergencyPerson.id]) markers[emergencyPerson.id].openPopup();
                        }, 1500);
                    }
                }
                if (!emergencyPerson) emergencyPersonnelId = null;

                // --- Loop per personel ---
                data.forEach(person => {
                    const pid  = person.id;
                    const pLat = parseFloat(person.latitude);
                    const pLng = parseFloat(person.longitude);
                    if (isNaN(pLat) || isNaN(pLng)) return;

                    activeIds.push(pid);

                    const sp       = getStatusProps(person.status_aktif);
                    const photoUrl = buildFotoUrl(person.foto_profil, person.nama_lengkap);
                    const noHp     = person.nrp || '';

                    const waBtn = noHp
                        ? '<a href="https://wa.me/' + noHp + '" target="_blank" onclick="event.stopPropagation()" class="text-success" style="font-size:11px;"><i class="bi bi-whatsapp me-1"></i>' + noHp + '</a>'
                        : '';

                    // Sidebar
                    sidebarHtml += '<div class="card mb-2 shadow-sm" style="cursor:pointer;border-left:3px solid '
                        + sp.color + ';' + (focusedId === pid ? 'background:#f1f8ff;' : '')
                        + '" onclick="window.flyTo(' + pLat + ',' + pLng + ',' + pid + ')">'
                        + '<div class="card-body p-2 d-flex align-items-center gap-2">'
                        + '<img src="' + photoUrl + '" class="rounded-circle flex-shrink-0" style="width:36px;height:36px;object-fit:cover;border:2px solid ' + sp.color + ';">'
                        + '<div class="flex-grow-1 overflow-hidden">'
                        + '<div class="fw-bold text-dark text-truncate" style="font-size:12px;">' + person.nama_lengkap + '</div>'
                        + '<div style="font-size:10px;color:#666;">' + person.pangkat + '</div>'
                        + waBtn
                        + '</div>' + sp.badge + '</div></div>';

                    // Popup personel
                    const svUrl   = 'https://www.google.com/maps?q=&layer=c&cbll=' + pLat + ',' + pLng;
                    const waPopup = noHp
                        ? '<a href="https://wa.me/' + noHp + '" target="_blank" class="btn btn-success btn-sm w-100 mt-1 mb-1"><i class="bi bi-whatsapp me-1"></i>' + noHp + '</a>'
                        : '';

                    const popup = '<div class="gmaps-card">'
                        + '<div class="gmaps-header" style="background:' + sp.color + ';"><i class="bi ' + sp.icon + ' me-2"></i>Info Personel</div>'
                        + '<div class="gmaps-body">'
                        + '<div class="text-center mb-2"><img src="' + photoUrl + '" style="width:70px;height:70px;border-radius:50%;border:3px solid ' + sp.color + ';object-fit:cover;"></div>'
                        + '<div class="gmaps-title">' + person.nama_lengkap + '</div>'
                        + '<div class="gmaps-subtitle">' + person.pangkat + ' &bull; ' + sp.label + '</div>'
                        + sp.badge + waPopup
                        + '<div style="font-size:11px;font-weight:bold;color:#1A73E8;margin-top:8px;">Koordinat:</div>'
                        + '<div class="coord-box">' + pLat.toFixed(6) + ', ' + pLng.toFixed(6) + '</div>'
                        + '</div>'
                        + '<div class="gmaps-footer">'
                        + '<a href="' + svUrl + '" target="_blank" class="gmaps-btn" style="color:#e67e22;"><i class="bi bi-camera-fill me-1"></i>360° View</a>'
                        + '<a href="javascript:void(0)" onclick="window.flyTo(' + pLat + ',' + pLng + ',' + pid + ')" class="gmaps-btn text-success"><i class="bi bi-geo-fill me-1"></i>Fokus</a>'
                        + '</div></div>';

                    const photoIcon = L.divIcon({
                        className: 'photo-marker-container',
                        html: '<div class="photo-marker ' + sp.border + ' ' + sp.anim + '">'
                            + '<img src="' + photoUrl + '" onerror="this.src=\'https://ui-avatars.com/api/?name=' + encodeURIComponent(person.nama_lengkap) + '\'">'
                            + '</div>',
                        iconSize:[42,42], iconAnchor:[21,21], popupAnchor:[0,-22],
                    });

                    if (markers[pid]) {
                        markers[pid].slideTo([pLat, pLng], { duration:800, keepAtCenter:false });
                        markers[pid].setIcon(photoIcon);
                        if (!markers[pid].isPopupOpen()) markers[pid].setPopupContent(popup);
                    } else {
                        markers[pid] = L.marker([pLat, pLng], { icon:photoIcon }).addTo(map).bindPopup(popup);
                    }

                    if (focusedId === pid) map.panTo([pLat, pLng]);

                    // Jalur biru (jadwal)
                    const wps = [[pLng, pLat]];
                    if (person.schedules && person.schedules.length > 0) {
                        person.schedules.forEach(j => {
                            if (j.latitude && j.longitude) {
                                wps.push([parseFloat(j.longitude), parseFloat(j.latitude)]);
                            }
                        });
                    }
                    if (wps.length > 1) {
                        const bSig = wps.map(w => w[0].toFixed(4) + ',' + w[1].toFixed(4)).join(';');
                        if (!bluePolylines[pid] || bluePolylines[pid].sig !== bSig) {
                            fetchRoute(bSig, ll => {
                                if (bluePolylines[pid] && bluePolylines[pid].layer) map.removeLayer(bluePolylines[pid].layer);
                                bluePolylines[pid] = { layer: L.polyline(ll, { color:'#007bff', weight:5, opacity:.7 }).addTo(map), sig:bSig };
                            });
                        }
                    }

                    // Jalur merah (instruksi)
                    if (person.latest_instruction && person.latest_instruction.latitude) {
                        const iLat = parseFloat(person.latest_instruction.latitude);
                        const iLng = parseFloat(person.latest_instruction.longitude);
                        const rSig = pLng.toFixed(4) + ',' + pLat.toFixed(4) + ';' + iLng.toFixed(4) + ',' + iLat.toFixed(4);
                        if (!redPolylines[pid] || redPolylines[pid].sig !== rSig) {
                            fetchRoute(rSig, ll => {
                                if (redPolylines[pid] && redPolylines[pid].layer) map.removeLayer(redPolylines[pid].layer);
                                redPolylines[pid] = { layer: L.polyline(ll, { color:'#dc3545', weight:4, dashArray:'5,10' }).addTo(map), sig:rSig };
                            });
                        }
                    }
                });

                // Hapus marker offline
                Object.keys(markers).forEach(idStr => {
                    const id = parseInt(idStr);
                    if (!activeIds.includes(id)) {
                        map.removeLayer(markers[id]); delete markers[id];
                        if (bluePolylines[id]) { map.removeLayer(bluePolylines[id].layer); delete bluePolylines[id]; }
                        if (redPolylines[id])  { map.removeLayer(redPolylines[id].layer);  delete redPolylines[id]; }
                    }
                });

                document.getElementById('panel-body').innerHTML =
                    sidebarHtml || '<div class="text-center text-muted py-3 small">Tidak ada personel aktif.</div>';
                document.getElementById('sync-status').innerText = 'LIVE ' + new Date().toLocaleTimeString();
            })
            .catch(() => {});
    }

    // ── CHECKPOINT DETAIL POPUP ────────────────────────────────────
    function fetchCheckpoints() {
        fetch('{{ url("/get-checkpoints-json") }}')
            .then(r => r.json())
            .then(data => {
                const reps = Array.isArray(data) ? data : (data.data || []);
                reps.forEach(l => {
                    if (!l.latitude || !l.longitude) return;

                    const pLat = parseFloat(l.latitude);
                    const pLng = parseFloat(l.longitude);

                    // Warna berdasarkan prioritas
                    let color = '#0d6efd';
                    const prio = (l.prioritas || '').toLowerCase();
                    if      (prio === 'rendah') color = '#28a745';
                    else if (prio === 'sedang') color = '#ffc107';
                    else if (prio === 'tinggi') color = '#dc3545';

                    // Media (foto atau video)
                    let mediaHtml = '';
                    if (l.media_url) {
                        if (l.is_video) {
                            mediaHtml = '<div style="background:#000;border-radius:6px;overflow:hidden;margin-bottom:10px;">'
                                + '<video controls style="width:100%;max-height:180px;display:block;">'
                                + '<source src="' + l.media_url + '" type="video/mp4">'
                                + '<source src="' + l.media_url + '" type="video/webm">'
                                + '</video></div>'
                                + '<div style="text-align:center;margin-bottom:8px;font-size:11px;">'
                                + '<a href="' + l.media_url + '" target="_blank" style="color:#0d6efd;">'
                                + '<i class="bi bi-download"></i> Download Video</a></div>';
                        } else {
                            mediaHtml = '<a href="' + l.media_url + '" target="_blank">'
                                + '<img src="' + l.media_url + '" '
                                + 'style="width:100%;max-height:180px;object-fit:cover;border-radius:6px;'
                                + 'margin-bottom:10px;border:1px solid #ddd;display:block;" '
                                + 'onerror="this.parentElement.style.display=\'none\'">'
                                + '</a>';
                        }
                    }

                    // Badge prioritas
                    let prioBadge = '<span style="background:#6c757d;color:#fff;padding:2px 8px;border-radius:4px;font-size:10px;">NORMAL</span>';
                    if      (prio === 'tinggi') prioBadge = '<span style="background:#dc3545;color:#fff;padding:2px 8px;border-radius:4px;font-size:10px;">TINGGI</span>';
                    else if (prio === 'sedang') prioBadge = '<span style="background:#ffc107;color:#000;padding:2px 8px;border-radius:4px;font-size:10px;">SEDANG</span>';
                    else if (prio === 'rendah') prioBadge = '<span style="background:#28a745;color:#fff;padding:2px 8px;border-radius:4px;font-size:10px;">RENDAH</span>';

                    const mapsUrl = 'https://maps.google.com/?q=' + pLat + ',' + pLng;
                    const svUrl   = 'https://www.google.com/maps?q=&layer=c&cbll=' + pLat + ',' + pLng;

                    const popupContent = '<div style="font-family:\'Roboto\',Arial,sans-serif;background:white;">'
                        // Header
                        + '<div style="background:' + color + ';color:white;padding:10px 14px;font-size:13px;font-weight:600;display:flex;align-items:center;">'
                        + '<i class="bi bi-geo-fill me-2"></i>Detail Checkpoint</div>'
                        // Body
                        + '<div style="padding:12px;">'
                        + mediaHtml
                        // Judul
                        + '<div style="font-size:15px;font-weight:700;color:#202124;margin-bottom:3px;">' + (l.judul || '-') + '</div>'
                        // Personel
                        + '<div style="font-size:12px;color:#5f6368;margin-bottom:8px;">'
                        + (l.personnel_nama || 'Petugas') + ' &bull; ' + (l.personnel_pangkat || '-') + '</div>'
                        // Deskripsi
                        + '<div style="font-size:12px;color:#333;background:#f8f9fa;padding:8px;border-radius:6px;'
                        + 'margin-bottom:8px;line-height:1.5;white-space:pre-wrap;">' + (l.deskripsi || '-') + '</div>'
                        // Waktu & prioritas
                        + '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">'
                        + '<span style="font-size:11px;color:#888;"><i class="bi bi-clock me-1"></i>' + (l.waktu || '') + '</span>'
                        + prioBadge + '</div>'
                        // Koordinat
                        + '<div class="coord-box">' + pLat.toFixed(6) + ', ' + pLng.toFixed(6) + '</div>'
                        + '</div>'
                        // Footer buttons
                        + '<div class="gmaps-footer">'
                        + '<a href="' + svUrl + '" target="_blank" class="gmaps-btn" style="color:#e67e22;">'
                        + '<i class="bi bi-camera-fill me-1"></i>360° View</a>'
                        + '<a href="javascript:void(0)" onclick="window.flyTo(' + pLat + ',' + pLng + ',null)" class="gmaps-btn text-primary">'
                        + '<i class="bi bi-geo-fill me-1"></i>Fokus Peta</a>'
                        + '</div></div>';

                    const ico = L.divIcon({
                        className: '',
                        html: '<div style="background:' + color + ';width:18px;height:18px;border-radius:50%;'
                            + 'border:2px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,.35);"></div>',
                        iconSize:[18,18], iconAnchor:[9,9],
                    });

                    if (cpMarkers[l.id]) {
                        // Update popup existing marker
                        cpMarkers[l.id].setPopupContent(popupContent);
                    } else {
                        // Buat marker baru
                        cpMarkers[l.id] = L.marker([pLat, pLng], { icon:ico })
                            .addTo(cpLayer)
                            .bindPopup(popupContent, { maxWidth:320 });

                        if (targetCpId && targetCpId === l.id && !cpFocused) {
                            cpFocused = true;
                            map.flyTo([pLat, pLng], 18);
                            setTimeout(() => cpMarkers[l.id].openPopup(), 1000);
                        }
                    }
                });
            })
            .catch(() => {});
    }

    // ── PANEL TOGGLE ───────────────────────────────────────────────
    window.togglePanel = function () {
        panelOpen = !panelOpen;
        document.getElementById('panel-body').style.display = panelOpen ? 'block' : 'none';
        document.getElementById('panel-chevron').className  = panelOpen ? 'bi bi-chevron-down' : 'bi bi-chevron-up';
    };

    window.flyTo = function (lat, lng, id) {
        map.flyTo([lat, lng], 18, { animate:true, duration:1 });
        focusedId = id;
        document.getElementById('btn-stop-focus').style.display = 'block';
    };

    window.stopFocus = function () {
        focusedId = null;
        emergencyPersonnelId = null;
        document.getElementById('btn-stop-focus').style.display = 'none';
        map.setZoom(14);
    };

    // ── JALANKAN ───────────────────────────────────────────────────
    setInterval(updateMap, 1000);
    updateMap();
    fetchCheckpoints();
    setInterval(fetchCheckpoints, 15000);

    // Refresh cache checkpoint tiap 5 menit
    setInterval(() => {
        Object.keys(cpMarkers).forEach(k => { cpLayer.removeLayer(cpMarkers[k]); delete cpMarkers[k]; });
        cpFocused = false;
        fetchCheckpoints();
    }, 300000);

})();
</script>
@endpush