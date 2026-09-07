<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal - KPTM SoES</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f6f9; }
        .navbar-custom { background-color: #0f172a; }
        .card-exam { border: none; border-radius: 12px; transition: transform 0.2s; }
        .card-exam:hover { transform: translateY(-3px); }
    </style>
</head>
<body>

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

<div class="container py-5">
    
    <div class="row mb-4">
        <div class="col">
            <h2 class="fw-bold">Welcome, {{ $user->name }}</h2>
            <p class="text-muted">Domain Verified: <strong>@student.kptm.edu.my</strong></p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ session('success') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ session('error') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>
        </div>
    @endif


    <!-- Join Class (Search & Select) -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <h4 class="fw-bold mb-3">
                <i class="bi bi-door-open me-2 text-primary"></i>
                Join a Class
            </h4>

            <p class="text-muted">
                Enter the class code provided by your lecturer to search for your class section.
            </p>

            <!-- Step 1: Search Form -->
            <form action="{{ route('student.classes.search') }}" method="POST" class="mb-3">
                @csrf
                <div class="row g-2">
                    <div class="col-md-9">
                        <input
                            type="text"
                            name="class_code"
                            class="form-control"
                            placeholder="Example: CS101-SEC1"
                            value="{{ old('class_code') }}"
                            required
                        >
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-1"></i>
                            Search Class
                        </button>
                    </div>
                </div>
            </form>

            <!-- Step 2: Search Results List -->
            @if(session('searchResults'))
                <hr class="my-3">
                <h6 class="fw-bold text-dark mb-3">
                    <i class="bi bi-list-check me-1 text-primary"></i> Search Results:
                </h6>

                <div class="list-group">
                    @forelse(session('searchResults') as $class)
                        @php
                            $lecturerName = $class->lecturer->user->name 
                                ?? $class->lecturer->name 
                                ?? $class->lecturer->lecturer_name 
                                ?? 'Lecturer';
                        @endphp
                        <div class="list-group-item d-flex justify-content-between align-items-center p-3 border rounded mb-2">
                            <div>
                                <h6 class="fw-bold text-primary mb-1">
                                    {{ $class->class_name ?? $class->name }}
                                </h6>
                                <p class="mb-0 small text-muted">
                                    <i class="bi bi-person me-1"></i> Lecturer: <strong>{{ $lecturerName }}</strong> | 
                                    <i class="bi bi-tag me-1"></i> Code: <span class="badge bg-secondary">{{ $class->class_code ?? $class->code }}</span>
                                </p>
                            </div>
                            
                            <!-- Enroll into specific selected class -->
                            <form action="{{ route('student.classes.confirm-enroll') }}" method="POST">
                                @csrf
                                <input type="hidden" name="class_id" value="{{ $class->class_id ?? $class->id }}">
                                <button type="submit" class="btn btn-sm btn-success">
                                    <i class="bi bi-plus-circle me-1"></i> Enroll in this Class
                                </button>
                            </form>
                        </div>
                    @empty
                        <div class="alert alert-warning mb-0 small" role="alert">
                            <i class="bi bi-exclamation-triangle me-1"></i> No classes found with this code. Please check with your lecturer.
                        </div>
                    @endforelse
                </div>
            @endif
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 border-0">
            <h5 class="card-title fw-bold text-primary mb-0">
                <i class="bi bi-people me-2"></i>My Classes
            </h5>
        </div>
        <div class="card-body pt-0">
            <div class="row g-3">
                @forelse($studentClasses ?? $classes ?? [] as $classItem)
                    @php
                        // Resolve class object whether $classItem is a Pivot, Model, or Joined array
                        $cls = $classItem->class ?? $classItem;
                        
                        // Dynamic field resolution for class name and code
                        $cName = $cls->class_name ?? $cls->name ?? $cls->subject_name ?? null;
                        $cCode = $cls->class_code ?? $cls->code ?? $cls->subject_code ?? $classItem->class_code ?? null;
                    @endphp

                    <div class="col-md-6 col-lg-4">
                        <div class="card class-card h-100 border border-light-subtle rounded-3 shadow-sm transition-all">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center mb-2">
                                    <!-- Door Icon Box -->
                                    <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                        <i class="bi bi-door-open fs-4"></i>
                                    </div>
                                    
                                    <!-- Class Title & Code Badge -->
                                    <div class="overflow-hidden">
                                        <h6 class="fw-bold mb-1 text-truncate text-dark">
                                            {{ $cName ?? 'Class (' . ($cCode ?? 'N/A') . ')' }}
                                        </h6>
                                        @if($cCode)
                                            <span class="badge bg-secondary font-monospace px-2 py-1">
                                                {{ $cCode }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Lecturer Information -->
                                <div class="pt-2 border-top mt-2">
                                    <span class="text-muted small">
                                        <i class="bi bi-person-badge me-1"></i>Lecturer: 
                                        <strong>
                                            {{ $cls->lecturer->user->name ?? $cls->lecturer->name ?? $cls->lecturer_name ?? 'MUHAMMAD SHAFIQ BIN MOHD RAFI (KL)' }}
                                        </strong>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-journal-x fs-2 mb-2 d-block"></i>
                            You are not enrolled in any classes yet.
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Available & Upcoming Assessments -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 border-0">
            <h5 class="card-title fw-bold text-primary mb-0">
                <i class="bi bi-journal-text me-2"></i>Available & Upcoming Assessments
            </h5>
        </div>
        <div class="card-body pt-0">
            <div class="row g-3">
                @forelse($exams as $exam)
                    @php
                        $isUpcoming = \Carbon\Carbon::parse($exam->start_time)->isFuture();
                        $isAvailable = \Carbon\Carbon::parse($exam->start_time)->isPast() && \Carbon\Carbon::parse($exam->end_time)->isFuture();
                    @endphp

                    <!-- Grid Item -->
                    <div class="col-md-6 col-lg-4">
                        <div class="card assessment-card h-100 border border-light-subtle rounded-3 shadow-sm transition-all position-relative" 
                            style="cursor: pointer;" 
                            data-bs-toggle="modal" 
                            data-bs-target="#examModal{{ $exam->id ?? $exam->exam_id }}">
                            
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center mb-2">
                                    <!-- File Text Icon Box -->
                                    <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                        <i class="bi bi-file-earmark-text fs-4"></i>
                                    </div>
                                    
                                    <!-- Exam Title & Status Badge -->
                                    <div class="overflow-hidden flex-grow-1">
                                        <h6 class="fw-bold mb-1 text-truncate text-dark">
                                            {{ $exam->title }}
                                        </h6>
                                        @if($isUpcoming)
                                            <span class="badge bg-warning text-dark px-2 py-1">Upcoming</span>
                                        @elseif($isAvailable)
                                            <span class="badge bg-success px-2 py-1">Available</span>
                                        @else
                                            <span class="badge bg-secondary px-2 py-1">Ended</span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Class Name & Code -->
                                <div class="pt-2 border-top mt-2">
                                    @php
                                        $examClassCode = $exam->class_code ?? $exam->class->class_code ?? null;
                                        $matchedClass = isset($studentClasses) ? $studentClasses->firstWhere('class_code', $examClassCode) : null;
                                        $className = $exam->class->class_name ?? $exam->class->name ?? $matchedClass->class_name ?? $matchedClass->name ?? $exam->class_name ?? 'N/A';
                                    @endphp
                                    <span class="text-muted small">
                                        <i class="bi bi-door-open me-1"></i>Class: 
                                        <strong>{{ $className }}</strong> 
                                        ({{ $examClassCode ?? 'N/A' }})
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Exam Details Modal -->
                    <div class="modal fade" id="examModal{{ $exam->id ?? $exam->exam_id }}" tabindex="-1" aria-labelledby="examModalLabel{{ $exam->id ?? $exam->exam_id }}" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow">
                                <form action="{{ route('student.exams.take', $exam->id ?? $exam->exam_id) }}" method="GET" id="examForm{{ $exam->id ?? $exam->exam_id }}">
                                    <div class="modal-header border-bottom-0 pb-0">
                                        <h5 class="modal-title fw-bold text-dark" id="examModalLabel{{ $exam->id ?? $exam->exam_id }}">
                                            {{ $exam->title }}
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body py-3">

                                        @php
                                            $examClassCode = $exam->class_code ?? $exam->class->class_code ?? null;
                                            $matchedClass = isset($studentClasses) ? $studentClasses->firstWhere('class_code', $examClassCode) : null;
                                            $className = $exam->class->class_name ?? $exam->class->name ?? $matchedClass->class_name ?? $matchedClass->name ?? $exam->class_name ?? 'N/A';
                                            $examId = $exam->id ?? $exam->exam_id;
                                        @endphp

                                        <!-- Class Name & Code -->
                                        <p class="text-muted mb-2">
                                            <i class="bi bi-door-open me-2 text-primary"></i>Class: 
                                            <strong>{{ $exam->class->class_name ?? $exam->class->name ?? 'N/A' }}</strong> 
                                            ({{ $exam->class->class_code ?? $exam->class->code ?? $exam->class_code ?? 'N/A' }})
                                        </p>

                                        <!-- Duration -->
                                        <p class="text-muted mb-2">
                                            <i class="bi bi-clock me-2 text-primary"></i>Duration: 
                                            <strong>{{ $exam->duration_minutes ?? $exam->duration ?? $exam->time_limit ?? 0 }} minutes</strong>
                                        </p>

                                        <!-- Start & End Time -->
                                        <p class="text-muted mb-2">
                                            <i class="bi bi-calendar-event me-2 text-primary"></i>Start: 
                                            <strong>{{ \Carbon\Carbon::parse($exam->start_time)->format('d M Y, h:i A') }}</strong>
                                        </p>
                                        <p class="text-muted mb-3">
                                            <i class="bi bi-calendar-x me-2 text-primary"></i>End: 
                                            <strong>{{ \Carbon\Carbon::parse($exam->end_time)->format('d M Y, h:i A') }}</strong>
                                        </p>

                                        <!-- Question Count -->
                                        <div class="p-2 bg-light rounded text-muted small mb-3">
                                            <i class="bi bi-question-circle me-1"></i>
                                            Total Questions: <strong>{{ $exam->questions_count ?? optional($exam->questions)->count() ?? 0 }}</strong>
                                        </div>

                                        <!-- reCAPTCHA Widget -->
                                        @if($isAvailable)
                                            <hr class="my-3">
                                            <div class="text-center">
                                                <label class="form-label text-muted small mb-2 d-block">
                                                    Complete verification to start examination:
                                                </label>
                                                <div class="d-flex justify-content-center">
                                                    {!! NoCaptcha::display([
                                                        'data-callback' => 'onCaptchaSuccess' . $examId,
                                                        'data-expired-callback' => 'onCaptchaExpired' . $examId
                                                    ]) !!}
                                                </div>
                                                @error('g-recaptcha-response')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <!-- JavaScript Callbacks for this specific exam modal -->
                                            <script>
                                                function onCaptchaSuccess{{ $examId }}(token) {
                                                    document.getElementById('btnStartExam{{ $examId }}').disabled = false;
                                                }
                                                function onCaptchaExpired{{ $examId }}() {
                                                    document.getElementById('btnStartExam{{ $examId }}').disabled = true;
                                                }
                                            </script>
                                        @endif
                                    </div>
                                    <div class="modal-footer border-top-0 pt-0">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                        
                                        @if($isUpcoming)
                                            <button class="btn btn-secondary" disabled>
                                                <i class="bi bi-lock me-1"></i> Not Started Yet
                                            </button>
                                        @elseif($isAvailable)
                                            <!-- Button starts disabled until captcha is solved -->
                                            <button type="submit" id="btnStartExam{{ $examId }}" class="btn btn-primary" disabled>
                                                <i class="bi bi-pencil-square me-1"></i> Start Examination
                                            </button>
                                        @endif
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-journal-x fs-2 mb-2 d-block"></i>
                            No assessment available or scheduled.
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

</div>

<style>
.class-card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}
.class-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08) !important;
}
.assessment-card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}
.assessment-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08) !important;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Google reCAPTCHA Script -->
{!! NoCaptcha::renderJs() !!}

</body>
</html>