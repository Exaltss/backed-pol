<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Digital Tracking Polres</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Menggunakan asset() Laravel untuk latar belakang halaman penuh */
        body {
            background-image: url('{{ asset("images/polres_bg.png") }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            color: white; 
            height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center;
            margin: 0;
        }

        /* Kartu Login: Sedikit transparan dan blur untuk keterbacaan */
        .login-card {
            background: rgba(34, 43, 54, 0.85); /* Latar belakang gelap semi-transparan */
            border: 2px solid rgba(255, 193, 7, 0.3); /* Border kuning tipis, transparan */
            border-radius: 15px; 
            width: 100%; 
            max-width: 400px; 
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.6);
            backdrop-filter: blur(5px); /* Memberikan efek blur pada gambar latar di belakang kartu */
        }

        /* --- Efek Glow Digital --- */

        /* 1. Efek Glow Teks Judul "TRACKING DIGITAL" */
        .digital-glow-title {
            color: #FFC107; /* Warna kuning emas */
            text-shadow: 0 0 10px #FFC107, 0 0 20px #FFC107, 0 0 30px #e0a800;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        /* 2. Efek Glow Kotak Input (Username, Password) */
        .form-control {
            background: rgba(44, 53, 66, 0.8);
            border: 1px solid rgba(62, 73, 89, 0.5);
            color: #FFC107 !important; /* Teks input kuning emas */
            box-shadow: 0 0 8px rgba(255, 193, 7, 0.4); /* Cahaya dasar input */
            transition: all 0.3s ease;
        }
        
        /* Pertahankan dan tingkatkan glow saat fokus */
        .form-control:focus {
            background: rgba(44, 53, 66, 0.9);
            color: white;
            border-color: #FFC107;
            box-shadow: 0 0 15px rgba(255, 193, 7, 0.8) !important; /* Cahaya lebih terang saat fokus */
        }

        /* 3. Efek Glow Tombol "MASUK KE DASHBOARD" */
        .btn-primary {
            background-color: #FFC107;
            border: none;
            color: black;
            font-weight: bold;
            box-shadow: 0 0 15px rgba(255, 193, 7, 0.7); /* Cahaya pada tombol */
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #e0a800;
            box-shadow: 0 0 25px rgba(255, 193, 7, 0.9); /* Cahaya lebih kuat saat hover */
        }
        
        /* Efek Glow untuk Sub-judul (Polres Tulungagung) */
.digital-glow-subtitle {
    color: #FFC107;
    text-shadow: 0 0 5px #FFC107; /* Glow lebih tipis dari judul utama agar seimbang */
    font-weight: 500;
    letter-spacing: 1px;
    margin-top: -5px; /* Menarik sedikit ke atas agar lebih rapat dengan judul */
}
    </style>
</head>
<body>

<div class="login-card">
   <div class="text-center mb-4">
    <h4 class="fw-bold digital-glow-title">TRACKING DIGITAL</h4>
    <p class="digital-glow-subtitle">Polres Tulungagung</p>
</div>

    @if($errors->any())
        <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
    @endif

    <form action="{{ url('/login') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label class="form-label small">Username</label>
            <input type="text" name="username" class="form-control" required autofocus placeholder="Contoh: adminpolres">
        </div>
        <div class="mb-3">
            <label class="form-label small">Password</label>
            <input type="password" name="password" class="form-control" required placeholder="********">
        </div>
        <button type="submit" class="btn btn-primary w-100 py-2 mt-2">MASUK KE DASHBOARD</button>
    </form>
</div>

</body>
</html>