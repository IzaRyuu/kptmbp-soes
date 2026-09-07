<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Assessment History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <!-- Responsive Top Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm py-2">
        <div class="container-fluid px-4">
            <!-- Brand Logo & Title -->
            <a class="navbar-brand fw-bold d-flex align-items-center me-4" href="{{ route('student.dashboard') }}">
                <i class="bi bi-mortarboard-fill text-primary me-2 fs-5"></i>
                <span>KPTMBP SoES Student Portal</span>
            </a>

            <!-- Mobile Toggler -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topStudentNavbar" aria-controls="topStudentNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navbar Menu & Right Student Profile -->
            <div class="collapse navbar-collapse" id="topStudentNavbar">
                <!-- Left Navigation Menu -->
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white d-flex align-items-center gap-1" 
                        href="#" 
                        id="studentMenuDropdown" 
                        role="button" 
                        data-bs-toggle="dropdown" 
                        aria-expanded="false">
                            <i class="bi bi-person me-1"></i> Student
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark shadow border-0" aria-labelledby="studentMenuDropdown">
                            <li>
                                <a class="dropdown-item py-2 {{ request()->routeIs('student.dashboard') ? 'active bg-primary' : '' }}" 
                                href="{{ route('student.dashboard') }}">
                                    <i class="bi bi-speedometer2 me-2"></i> Student Dashboard
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-2 {{ request()->routeIs('student.assessment.history') ? 'active bg-primary' : '' }}" 
                                href="{{ route('student.assessment.history') }}">
                                    <i class="bi bi-clock-history me-2"></i> Assessment History
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>

                <!-- Right Side: User Credentials & Logout -->
                <div class="d-flex align-items-center gap-3">
                    <span class="text-white small text-end d-none d-md-inline fw-semibold">
                        {{ auth()->user()->name }}
                    </span>

                    <!-- Logout Form -->
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

    <div class="container py-4">
        <!-- Back Button -->
        <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary mb-3 btn-sm">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>

        <!-- My Assessment History Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h5 class="fw-bold text-dark mb-4 d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle text-success fs-5"></i> My Assessment History
                </h5>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Exam Title</th>
                                <th>Submitted At</th>
                                <th>Status</th>
                                <th>Total Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($history as $item)
                                <tr>
                                    <td class="fw-semibold">
                                        {{ $item->exam->title ?? $item->exam_title ?? $item->title ?? 'test 7' }}
                                    </td>
                                    <td>
                                        {{ $item->submitted_at ?? $item->created_at ?? '2026-08-29 21:55:40' }}
                                    </td>
                                    <td>
                                        <span class="badge bg-info text-dark px-2 py-1">
                                            {{ $item->status ?? 'Submitted' }}
                                        </span>
                                    </td>
                                    <td class="fw-bold">
                                        {{ number_format($item->total_score ?? $item->score ?? 1.00, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        No assessment history records found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

<!-- Bootstrap JS Bundle (Includes Popper.js for dropdowns) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<style>
.class-card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}
.class-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08) !important;
}
</style>


</body>
</html>