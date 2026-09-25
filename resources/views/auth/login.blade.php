<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KPTMBP SoES - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #1e427d;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-card {
            width: 100%;
            max-width: 480px;
            padding: 2.5rem;
            border-radius: 16px;
            background: #ffffff;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        .btn-google {
            border: 1px solid #e0e0e0;
            background-color: #ffffff;
            color: #333333;
            font-weight: 600;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            transition: all 0.2s ease-in-out;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
        }
        .btn-google:hover {
            background-color: #f8f9fa;
            border-color: #cccccc;
            color: #000000;
        }
        .badge-domain {
            background-color: #e8f0fe;
            color: #1a73e8;
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>

<div class="login-card text-center">
    <h2 class="fw-bold text-dark mb-1">KPTMBP SoES</h2>
    <p class="text-muted small mb-4">Secure Online Examination System</p>

    <p class="text-secondary small mb-4 px-2">
        Sign in using your institutional email account to access assessments and management portals.
    </p>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show text-start mb-3 small" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="mb-4">
        <a href="{{ route('auth.google') }}" class="btn-google w-100 shadow-sm">
            <svg width="18" height="18" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48">
                <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.28-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24s.92 7.54 2.56 10.78l7.97-6.19z"/>
                <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
            </svg>
            <span>Sign in with Google</span>
        </a>
    </div>

    <hr class="my-4 text-muted opacity-25">

    <div class="d-flex justify-content-center align-items-center gap-2">
        <span class="badge-domain">
            Institutional Email: <strong>@kptm.edu.my</strong>
        </span>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>