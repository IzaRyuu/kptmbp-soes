<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KPTMBP SoES - Main Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
        }
        .login-card {
            max-width: 440px;
            width: 100%;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        .btn-google {
            background-color: #ffffff;
            color: #333333;
            border: 1px solid #e0e0e0;
            font-weight: 600;
            transition: all 0.2s ease-in-out;
        }
        .btn-google:hover {
            background-color: #f8f9fa;
            border-color: #cccccc;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center">

    <div class="card login-card p-4 my-5 bg-white">
        <div class="card-body text-center">
            
            <div class="mb-4">
                <h3 class="fw-bold text-dark mb-1">KPTMBP SoES</h3>
                <p class="text-muted small">Secure Online Examination System</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger text-start small mb-4 py-2 px-3">
                    {{ $errors->first() }}
                </div>
            @endif

            <p class="text-secondary small mb-4">
                Sign in using your institutional email account to access assessments and management portals.
            </p>

            <a href="{{ route('auth.google') }}" class="btn btn-google w-100 py-2 d-flex align-items-center justify-content-center gap-2 mb-3">
                <svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48">
                    <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                    <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                    <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                    <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                </svg>
                <span>Sign in with Google</span>
            </a>

            <div class="mt-4 pt-3 border-top text-center">
                <div class="d-flex justify-content-center gap-2">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                        Student: <strong>@student.kptm.edu.my</strong>
                    </span>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                        Lecturer: <strong>@uptm.edu.my</strong>
                    </span>
                </div>
            </div>

        </div>
    </div>

</body>
</html>