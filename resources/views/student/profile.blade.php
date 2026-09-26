<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Student Portal</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style> body { background-color: #f4f6f9; } </style>
</head>
<body>

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm py-2">
    <div class="container-fluid px-4">
        <a class="navbar-brand fw-bold d-flex align-items-center me-4" href="{{ route('student.dashboard') }}">
            <i class="bi bi-mortarboard-fill text-primary me-2 fs-5"></i>
            <span>KPTMBP SoES Student Portal</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topStudentNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="topStudentNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white d-flex align-items-center gap-1" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person me-1"></i> Student
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark shadow border-0">
                        <li>
                            <a class="dropdown-item py-2" href="{{ route('student.dashboard') }}">
                                <i class="bi bi-speedometer2 me-2"></i> Student Dashboard
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2" href="{{ route('student.assessment.history') }}">
                                <i class="bi bi-clock-history me-2"></i> Assessment History
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item py-2 active bg-primary" href="{{ route('student.profile.edit') }}">
                                <i class="bi bi-person-gear me-2"></i> My Profile
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-3">
                <span class="text-white small fw-semibold">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm d-flex align-items-center gap-1">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            
            <div class="mb-3">
                <a href="{{ route('student.dashboard') }}" class="text-decoration-none text-muted small">
                    <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
                </a>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="fw-bold mb-0 text-primary">
                        <i class="bi bi-person-circle me-2"></i>My Profile Settings
                    </h5>
                    <p class="text-muted small mb-0">Update your personal information and account security.</p>
                </div>
                <div class="card-body p-4">

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('student.profile.update') }}" method="POST">
                        @csrf

                        <!-- Personal Details -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Full Name</label>
                                <input type="text" class="form-control" name="name" value="{{ old('name', $user->name) }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Matric Number</label>
                                <input type="text" class="form-control" name="matric_number" value="{{ old('matric_number', $student->matric_number ?? '') }}" required>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Email Address</label>
                                <input type="email" class="form-control bg-light text-muted" value="{{ $user->email }}" readonly>
                                <div class="form-text">Domain Verified Student Account</div>
                            </div>
                        </div>

                        <hr class="my-4 text-muted">

                        <!-- Password -->
                        <h6 class="fw-bold mb-1"><i class="bi bi-shield-lock me-2 text-warning"></i>Change Security Password</h6>
                        <p class="text-muted small mb-3">Leave these fields empty if you do not want to change your password.</p>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Current Password</label>
                                <input type="password" class="form-control" name="current_password" placeholder="••••••••">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">New Password</label>
                                <input type="password" class="form-control" name="new_password" placeholder="Min 8 characters">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Confirm New Password</label>
                                <input type="password" class="form-control" name="new_password_confirmation" placeholder="Repeat new password">
                            </div>
                        </div>

                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-primary px-4 fw-semibold">
                                <i class="bi bi-check-lg me-1"></i> Save Changes
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>