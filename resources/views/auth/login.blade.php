<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Digital Tracking Polres</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #151B25; color: white; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { background: #222B36; border: none; border-radius: 15px; width: 100%; max-width: 400px; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        .form-control { background: #2C3542; border: 1px solid #3e4959; color: white; }
        .form-control:focus { background: #2C3542; color: white; border-color: #FFC107; box-shadow: none; }
        .btn-primary { background-color: #FFC107; border: none; color: black; font-weight: bold; }
        .btn-primary:hover { background-color: #e0a800; }
    </style>
</head>
<body>

<div class="login-card">
    <div class="text-center mb-4">
        <h4 class="fw-bold">TRACKING DIGITAL</h4>
        <p class="text-muted">Polres Tulungagung</p>
    </div>

    @if($errors->any())
        <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
    @endif

    <form action="{{ url('/login') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label class="form-label small">Username</label>
            <input type="text" name="username" class="form-control" required autofocus>
        </div>
        <div class="mb-3">
            <label class="form-label small">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100 py-2 mt-2">MASUK KE DASHBOARD</button>
    </form>
</div>

</body>
</html>