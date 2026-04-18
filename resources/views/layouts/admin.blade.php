<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title','Admin') - GPS Realtime Polres Tulungagung</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body{font-family:'Inter',sans-serif;background-color:#f1f5f9;overflow-x:hidden;}
        .sidebar{width:265px;height:100vh;background:#0F172A;color:white;position:fixed;top:0;left:0;display:flex;flex-direction:column;padding:20px;z-index:1040;transition:transform .3s ease;overflow-y:auto;}
        .sidebar-header{display:flex;align-items:center;margin-bottom:25px;padding-bottom:18px;border-bottom:1px solid #334155;}
        .btn-instruksi{background:#EF4444;color:white;border:none;width:100%;padding:10px;border-radius:8px;font-weight:600;text-align:left;display:flex;align-items:center;margin-bottom:18px;text-decoration:none;}
        .btn-instruksi:hover{background:#dc2626;color:white;}
        .menu-label{font-size:10px;text-transform:uppercase;color:#64748b;margin-bottom:8px;font-weight:700;letter-spacing:.5px;}
        .nav-link{color:#cbd5e1;padding:10px 13px;border-radius:8px;margin-bottom:3px;display:flex;align-items:center;font-size:13px;transition:all .2s;}
        .nav-link:hover,.nav-link.active{background:#1e293b;color:#38bdf8;font-weight:600;}
        .main-content{margin-left:265px;padding:20px;width:calc(100% - 265px);transition:all .3s;}
        .top-bar{background:white;padding:12px 20px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,.05);margin-bottom:18px;display:flex;justify-content:space-between;align-items:center;}
        .card-custom{border:none;border-radius:12px;box-shadow:0 4px 6px -1px rgba(0,0,0,.1);overflow:hidden;}
        .hamburger-btn{display:none;position:fixed;top:12px;left:12px;z-index:1050;background:#0F172A;border:none;color:white;border-radius:8px;padding:8px 10px;font-size:18px;}
        .sidebar-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1039;}

        @media(max-width:992px){
            .hamburger-btn{display:block;}
            .sidebar{transform:translateX(-100%);}
            .sidebar.mobile-open{transform:translateX(0);}
            .sidebar-overlay.active{display:block;}
            .main-content{margin-left:0;width:100%;padding:15px;padding-top:55px;}
            .top-bar{flex-direction:column;align-items:flex-start;gap:8px;}
        }
        @media(max-width:576px){
            .main-content{padding:10px;padding-top:55px;}
            .card-custom .table{font-size:12px;}
        }
    </style>
    @stack('styles')
</head>
<body>

<button class="hamburger-btn" id="hamburger-btn" onclick="toggleSidebar()">
    <i class="bi bi-list" id="hamburger-icon"></i>
</button>
<div class="sidebar-overlay" id="sidebar-overlay" onclick="toggleSidebar()"></div>

<div class="sidebar" id="sidebar">
        <div class="sidebar-header">
           <div style="width: 38px; height: 38px; border-radius: 50%; overflow: hidden; margin-right: 10px; flex-shrink: 0; background: #f8f9fa;">
        <img src="{{ asset( "../images/polres_app.png" )}}" alt="Foto" style="width: 100%; height: 100%; object-fit: cover;">
        </div>
        <div>
            <div style="font-size:13px;font-weight:700;">Admin Monitoring</div>
            <div style="font-size:11px;color:#94a3b8;">Polres Tulungagung</div>
        </div>
    </div>

    <a href="{{ route('instruksi') }}" class="btn-instruksi">
        <i class="bi bi-megaphone-fill me-2"></i>Instruksi Personel
    </a>

    <div class="menu-label">Monitoring</div>
    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->is('/') ? 'active' : '' }}">
        <i class="bi bi-geo-alt-fill me-3"></i>GPS Realtime
    </a>

    <div class="menu-label mt-3">Digital Report</div>
    <a href="{{ route('laporan') }}" class="nav-link {{ request()->is('laporan-digital') ? 'active' : '' }}">
        <i class="bi bi-file-earmark-text-fill me-3"></i>Data Laporan
    </a>
    <a href="{{ route('checkpoint') }}" class="nav-link {{ request()->is('checkpoint-log') ? 'active' : '' }}">
        <i class="bi bi-geo-fill me-3"></i>Checkpoint Log
    </a>
    <a href="{{ route('jadwal') }}" class="nav-link {{ request()->is('jadwal-personel') ? 'active' : '' }}">
        <i class="bi bi-calendar-check-fill me-3"></i>Jadwal Personel
    </a>

    <div class="menu-label mt-3">Manajemen</div>
    <a href="{{ route('personel.index') }}" class="nav-link {{ request()->is('manajemen-personel') ? 'active' : '' }}">
        <i class="bi bi-people-fill me-3"></i>Data Personel
    </a>

    <div class="mt-auto pt-3 border-top" style="border-color:#334155!important;">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="nav-link w-100 text-start border-0 bg-transparent text-danger">
                <i class="bi bi-box-arrow-right me-3"></i>Logout
            </button>
        </form>
    </div>
</div>

<div class="main-content">
    <div class="top-bar">
        <h5 class="m-0 fw-bold text-dark">@yield('header-title','Dashboard')</h5>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-success">Admin</span>
            <span class="text-muted small">{{ Auth::user()->username }}</span>
        </div>
    </div>
    @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function toggleSidebar(){
        var s = document.getElementById('sidebar');
        var o = document.getElementById('sidebar-overlay');
        var btn = document.getElementById('hamburger-btn'); // Menangkap elemen tombol hamburger

        s.classList.toggle('mobile-open');
        o.classList.toggle('active');
        
        // Logika untuk menyembunyikan/menampilkan tombol hamburger
        if(s.classList.contains('mobile-open')) {
            btn.style.display = 'none'; // Sembunyikan saat sidebar terbuka
        } else {
            btn.style.display = ''; // Tampilkan kembali saat sidebar ditutup
        }
    }
</script>
@stack('scripts')
</body>
</html>