<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecturer Portal - KPTM SoES</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f6f9; }
        .sidebar { min-height: 100vh; background-color: #1e293b; color: #fff; }
        .sidebar .nav-link { color: #cbd5e1; border-radius: 8px; margin-bottom: 4px; cursor: pointer; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: #334155; color: #fff; }
        .card-stat { border: none; border-radius: 12px; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse p-3"> 
            <!-- Stacked & Centered Logo Header -->
            <div class="d-flex align-items-center mb-4 px-2">
                <!-- Transparent Background with Increased Width -->
                <img src="{{ asset('images/logo.png') }}" 
                    alt="KPTMBP SoES Logo" 
                    style="height: 40px; width: auto; max-width: 80px; object-fit: contain;" 
                    class="me-2">
                    
                <!-- Title -->
                <span class="fs-5 fw-bold text-white">SoES</span>
            </div>
            <hr class="text-secondary">
            
            <div class="nav nav-pills flex-column mb-auto" id="v-pills-tab" role="tablist">
                <button class="nav-link active text-start mb-2" id="exams-tab" data-bs-toggle="pill" data-bs-target="#tab-exams" type="button">
                    <i class="bi bi-journal-text me-2"></i> Exam List & CRUD
                </button>
                <button class="nav-link text-start mb-2" id="students-tab" data-bs-toggle="pill" data-bs-target="#tab-students" type="button">
                    <i class="bi bi-people me-2"></i> Manage Students
                </button>
                <button class="nav-link text-start mb-2" id="students-violation" data-bs-toggle="pill" data-bs-target="#tab-violation" type="button">
                    <i class="bi bi-exclamation-triangle me-2"></i> Violation
                </button>
                <button class="nav-link text-start mb-2" id="logs-tab" data-bs-toggle="pill" data-bs-target="#tab-logs" type="button">
                    <i class="bi bi-activity me-2"></i> System Monitoring
                </button>
            </div>

            <hr class="text-secondary">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-danger w-100 btn-sm">
                    <i class="bi bi-box-arrow-right me-1"></i> Sign Out
                </button>
            </form>
        </nav>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-4 border-bottom">
                <div>
                    <h1 class="h3 fw-bold">Lecturer Management Portal</h1>
                    <p class="text-muted mb-0">Welcome, <strong>{{ $user->name ?? 'Lecturer' }}</strong> ({{ $lecturer->staff_number ?? 'Staff' }})</p>
                </div>
                <div>
                    <button class="btn btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#createClassModal">
                        <i class="bi bi-door-open me-1"></i> Add Target Class
                    </button>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createExamModal">
                        <i class="bi bi-plus-circle me-1"></i> Create New Exam
                    </button>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <!-- Total Exams Card -->
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 p-3 h-100">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary-subtle text-primary p-3 rounded-4">
                                <i class="bi bi-file-earmark-check fs-3"></i>
                            </div>
                            <div>
                                <span class="text-muted small fw-medium">Total Exams</span>
                                <h3 class="fw-bold mb-0">{{ $totalExams }}</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Registered Students Card -->
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 p-3 h-100">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-success-subtle text-success p-3 rounded-4">
                                <i class="bi bi-people fs-3"></i>
                            </div>
                            <div>
                                <span class="text-muted small fw-medium">Registered Students</span>
                                <h3 class="fw-bold mb-0">{{ $totalStudents }}</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Assigned Classes Card -->
                <div class="col-md-4">
                    <div class="card shadow-sm border-0 cursor-pointer" data-bs-toggle="modal" data-bs-target="#manageClassesModal" style="cursor: pointer;">
                        <div class="card-body d-flex align-items-center">
                            <div class="bg-warning bg-opacity-10 p-3 rounded me-3 text-warning">
                                <i class="bi bi-door-open fs-3 text-warning"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Assigned Classes</small>
                                <h3 class="fw-bold mb-0">
                                    {{ $assignedClassesCount ?? $assignedClasses->count() }}
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-content" id="v-pills-tabContent">
                
                <div class="tab-pane fade show active" id="tab-exams" role="tabpanel">
                    <div class="card bg-white shadow-sm border-0 rounded-3">
                        <div class="card-header bg-transparent fw-bold py-3">
                            <i class="bi bi-journal-text me-1"></i> Managed Exams
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Title</th>
                                            <th>Duration</th>
                                            <th>Start Time</th>
                                            <th>End Time</th>
                                            <th>Randomized</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($exams as $exam)
                                            <tr>
                                                <td class="fw-semibold">
                                                    {{ $exam->title }}
                                                    <br>
                                                    <small class="text-muted">
                                                        Target Class: <strong>{{ $exam->class->class_name ?? 'N/A' }}</strong>
                                                    </small>
                                                </td>
                                                <td>{{ $exam->duration_minutes }} mins</td>
                                                <td>{{ date('d M Y, h:i A', strtotime($exam->start_time)) }}</td>
                                                <td>{{ date('d M Y, h:i A', strtotime($exam->end_time)) }}</td>
                                                <td>
                                                    <span class="badge {{ $exam->is_randomized ? 'bg-success' : 'bg-secondary' }}">
                                                        {{ $exam->is_randomized ? 'Yes' : 'No' }}
                                                    </span>
                                                </td>

                                                <td class="text-end">
                                                    {{-- UPDATED SUBMISSIONS BUTTON --}}
                                                    <a href="{{ route('lecturer.exam.submissions', $exam->exam_id) }}" class="btn btn-sm btn-outline-info me-1">
                                                        <i class="bi bi-file-earmark-text me-1"></i> Submissions ({{ $exam->attempts_count ?? optional($exam->attempts)->count() ?? 0 }})
                                                    </a>

                                                    <a href="{{ route('lecturer.questions.index', $exam->exam_id) }}" class="btn btn-sm btn-outline-primary me-1">
                                                        <i class="bi bi-gear me-1"></i> Manage Questions ({{ $exam->questions->count() }})
                                                    </a>

                                                    <button class="btn btn-sm btn-outline-warning me-1" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editExamModal{{ $exam->exam_id }}">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>

                                                    <form action="{{ route('lecturer.exams.destroy', $exam->exam_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this exam?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>

                                                    <div class="modal fade" id="editExamModal{{ $exam->exam_id }}" tabindex="-1">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <form action="{{ route('lecturer.exams.update', $exam->exam_id) }}" method="POST">
                                                                    @csrf
                                                                    @method('PUT')
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title fw-bold">Edit Exam</h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                    </div>
                                                                    <div class="modal-body text-start">
                                                                        <div class="mb-3">
                                                                            <label class="form-label fw-bold">Exam Title</label>
                                                                            <input type="text" name="title" class="form-control" value="{{ $exam->title }}" required>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label class="form-label fw-bold">Duration (Minutes)</label>
                                                                            <input type="number" name="duration_minutes" class="form-control" value="{{ $exam->duration_minutes }}" required>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label class="form-label fw-bold">Start Time</label>
                                                                            <input type="datetime-local" name="start_time" class="form-control" value="{{ date('Y-m-d\TH:i', strtotime($exam->start_time)) }}" required>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label class="form-label fw-bold">End Time</label>
                                                                            <input type="datetime-local" name="end_time" class="form-control" value="{{ date('Y-m-d\TH:i', strtotime($exam->end_time)) }}" required>
                                                                        </div>
                                                                        <div class="form-check">
                                                                            <input class="form-check-input" type="checkbox" name="is_randomized" value="1" id="randEdit{{ $exam->exam_id }}" {{ $exam->is_randomized ? 'checked' : '' }}>
                                                                            <label class="form-check-label" for="randEdit{{ $exam->exam_id }}">Randomize Questions Order</label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                        <button type="submit" class="btn btn-warning">Save Changes</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-4 text-muted">No exams available. Click "Create New Exam" above.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-students" role="tabpanel">
                    <div class="space-y-6">
                        <div class="container-fluid py-4">
                            <h3 class="fw-bold mb-4">Manage Students</h3>

                            <!-- Grid of Clickable Class Cards -->
                            <div class="row g-4 mb-4">
                                @forelse($assignedClasses as $class)
                                    @php
                                        // Primary key fallback (class_id vs id)
                                        $classId = $class->class_id ?? $class->id;
                                        
                                        // Name fallback (class_name vs name)
                                        $className = $class->class_name ?? $class->name ?? 'Unnamed Class';
                                        
                                        // Code fallback
                                        $classCode = $class->class_code ?? $class->code ?? $class->subject_code ?? 'NO CODE';
                                        
                                        // Enrolled students count calculation
                                        $studentCount = isset($class->students) ? $class->students->count() : 0;
                                    @endphp

                                    <div class="col-md-4">
                                        <div class="card shadow-sm border-0 h-100 class-card cursor-pointer" 
                                            data-bs-toggle="collapse" 
                                            data-bs-target="#class-students-{{ $classId }}" 
                                            aria-expanded="false" 
                                            style="cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;">
                                            <div class="card-body d-flex align-items-center">
                                                <div class="rounded-3 p-3 bg-warning-subtle text-warning me-3">
                                                    <i class="bi bi-door-open fs-3"></i>
                                                </div>
                                                <div>
                                                    <h5 class="fw-bold mb-1">{{ $className }}</h5>
                                                    <span class="badge bg-secondary font-monospace">
                                                        {{ $classCode }}
                                                    </span>
                                                    <p class="text-muted small mb-0 mt-1">
                                                        <i class="bi bi-people me-1"></i>{{ $studentCount }} Student(s)
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12">
                                        <div class="alert alert-info border-0 shadow-sm">
                                            <i class="bi bi-info-circle me-2"></i>No assigned classes found.
                                        </div>
                                    </div>
                                @endforelse
                            </div>

                            <!-- Expandable Student Details Tables -->
                            <div class="accordion" id="studentsAccordion">
                                @foreach($assignedClasses as $class)
                                    <div id="class-students-{{ $class->class_id }}" class="collapse mb-4" data-bs-parent="#studentsAccordion">
                                        <div class="card border shadow-sm">
                                            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                                                <h5 class="fw-bold mb-0">
                                                    <i class="bi bi-folder2-open text-primary me-2"></i>{{ $class->class_name }} — Student List
                                                </h5>
                                                <span class="badge bg-primary rounded-pill px-3">{{ $class->students->count() }} Enrolled</span>
                                            </div>
                                            <div class="card-body p-0">
                                                @if($class->students->count() > 0)
                                                    <div class="table-responsive">
                                                        <table class="table table-hover align-middle mb-0">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    <th>Name</th>
                                                                    <th>Email</th>
                                                                    <th>Matrix No.</th>
                                                                    <th>Status</th>
                                                                    <th class="text-center">Account Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($class->students as $student)
                                                                    <tr>
                                                                        <td class="fw-semibold">
                                                                            {{ $student->user->name ?? $student->name ?? 'N/A' }}
                                                                        </td>
                                                                        <td>
                                                                            {{ $student->user->email ?? $student->email ?? 'N/A' }}
                                                                        </td>
                                                                        <td>
                                                                            <span class="fw-bold text-danger">
                                                                                {{ $student->matrix_no ?? $student->matrix_number ?? $student->user->matrix_no ?? 'N/A' }}
                                                                            </span>
                                                                        </td>
                                                                        
                                                                        <!-- 1. Status Column -->
                                                                        <td>
                                                                            @if($student->is_suspended ?? false)
                                                                                <span class="badge bg-danger px-2 py-1">Suspended</span>
                                                                            @else
                                                                                <span class="badge bg-success px-2 py-1">Active</span>
                                                                            @endif
                                                                        </td>

                                                                        <!-- 2. Account Action Column -->
                                                                        <td class="text-center">
                                                                            <div class="d-flex justify-content-center gap-2">
                                                                                <!-- Suspend / Unsuspend Button -->
                                                                                <form action="{{ route('lecturer.students.toggleSuspend', $student->id ?? $student->student_id) }}" method="POST">
                                                                                    @csrf
                                                                                    <button type="submit" 
                                                                                            class="btn btn-sm {{ ($student->is_suspended ?? false) ? 'btn-outline-success' : 'btn-outline-danger' }} px-3"
                                                                                            onclick="return confirm('Are you sure you want to {{ ($student->is_suspended ?? false) ? 'unsuspend' : 'suspend' }} this student?')">
                                                                                        {{ ($student->is_suspended ?? false) ? 'Unsuspend' : 'Suspend' }}
                                                                                    </button>
                                                                                </form>

                                                                                <!-- Kick Button -->
                                                                                <form action="{{ route('lecturer.students.kick', ['classId' => $class->id ?? $class->class_id, 'studentId' => $student->id ?? $student->student_id]) }}" method="POST">
                                                                                    @csrf
                                                                                    @method('DELETE')
                                                                                    <button type="submit" 
                                                                                            class="btn btn-sm btn-danger px-3"
                                                                                            onclick="return confirm('Are you sure you want to kick this student from {{ $class->name }}?')">
                                                                                        Kick
                                                                                    </button>
                                                                                </form>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach     
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                @else
                                                    <div class="p-4 text-center text-muted">
                                                        <i class="bi bi-people fs-2 d-block mb-2"></i>
                                                        No students registered in this class yet.
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-violation" role="tabpanel">
                    <div class="space-y-6">

                        {{-- ================================================= --}}
                        {{-- TAB SWITCHING VIOLATIONS TABLE --}}
                        {{-- ================================================= --}}
                        {{-- Bulk Delete Form Wraps the Entire Card --}}
                        <form action="{{ route('lecturer.violations.bulk-delete') }}" method="POST" id="bulk-delete-violations-form">
                            @csrf
                            @method('DELETE')

                            <div class="card shadow-sm border mb-4">
                                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0 fw-bold text-danger">
                                        🚨 Security & Tab-Switching Violations
                                    </h5>
                                    <div class="d-flex align-items-center gap-2">
                                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="executeBulkDelete()">
                                            <i class="bi bi-trash me-1"></i> Delete Selected
                                        </button>
                                        <span id="violation-count-badge" class="badge bg-danger fs-6">
                                            {{ isset($violations) ? $violations->count() : 0 }} Detected
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle text-nowrap mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 45px;" class="text-center ps-3">
                                                        <input type="checkbox" id="select-all-violations" class="form-check-input">
                                                    </th>
                                                    <th style="width: 180px;">Timestamp</th>
                                                    <th style="width: 260px;">Student Name</th>
                                                    <th style="width: 150px;">Exam Title</th>
                                                    <th style="width: 160px;">Violation Type</th>
                                                    <th class="text-center" style="width: 100px;">Severity</th>
                                                </tr>
                                            </thead>
                                            <tbody id="violations-table-body">
                                                @isset($violations)
                                                    @forelse($violations as $violation)
                                                        @php 
                                                            $vId = $violation->violation_id ?? $violation->id ?? $violation->exam_violation_id; 
                                                        @endphp
                                                        <tr id="violation-row-{{ $vId }}">
                                                            <td class="text-center ps-3">
                                                                <input type="checkbox" name="violation_ids[]" value="{{ $vId }}" class="form-check-input violation-checkbox">
                                                            </td>
                                                            <td class="text-muted fw-medium">
                                                                {{ \Carbon\Carbon::parse($violation->occurred_at ?? $violation->created_at)->format('d M Y, h:i A') }}
                                                            </td>
                                                            <td class="fw-bold text-dark">
                                                                {{ $violation->student->user->name ?? $violation->student->name ?? 'Unknown Student' }}
                                                            </td>
                                                            <td class="text-secondary">
                                                                {{ $violation->exam->title ?? 'N/A' }}
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-warning text-dark fw-semibold px-2 py-1">
                                                                    {{ str_replace('_', ' ', strtoupper($violation->violation_type)) }}
                                                                </span>
                                                            </td>
                                                            <td class="text-center">
                                                                <span class="badge bg-danger px-2 py-1">HIGH</span>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="6" class="text-center text-muted py-4">No exam violations or tab switches detected.</td>
                                                        </tr>
                                                    @endforelse
                                                @endisset
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </form>

                    </div>
                </div>

                {{-- Hidden Form for Single Row Deletion --}}
                <form id="single-delete-violation-form" method="POST" style="display: none;">
                    @csrf
                    @method('DELETE')
                </form>

                <div class="tab-pane fade" id="tab-logs" role="tabpanel">
                    <div class="card bg-white shadow-sm border-0 rounded-3">
                        <div class="card-header bg-transparent fw-bold py-3">
                            <i class="bi bi-activity me-1"></i> Recent Activity Logs
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-striped mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Timestamp</th>
                                        <th>User</th>
                                        <th>Event Type</th>
                                        <th>Description</th>
                                        <th>IP Address</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($activityLogs as $log)
                                        <tr>
                                            <td><small class="text-muted">{{ $log->created_at->format('d M Y, h:i A') }}</small></td>
                                            <td>{{ $log->user->name ?? 'System' }}</td>
                                            <td><span class="badge bg-dark">{{ $log->event_type }}</span></td>
                                            <td>{{ $log->description }}</td>
                                            <td><code>{{ $log->ip_address }}</code></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">No system activity logged yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>

        </main>
    </div>
</div>

<div class="modal fade" id="createExamModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('lecturer.exams.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Create New Exam</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body"> 

                    <div class="mb-3">
                        <label class="form-label fw-bold">Target Class</label>
                        <select name="class_id" class="form-select" required>
                            <option value="" disabled selected>-- Select Target Class --</option>
                            @foreach($myClasses as $class)
                                <option value="{{ $class->class_id }}">{{ $class->class_name }} ({{ $class->class_code ?? 'Section' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Exam Title</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Midterm Examination" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Duration (Minutes)</label>
                        <input type="number" name="duration_minutes" class="form-control" placeholder="60" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Start Time</label>
                        <input type="datetime-local" name="start_time" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">End Time</label>
                        <input type="datetime-local" name="end_time" class="form-control" required>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="is_randomized" value="1" id="is_randomized" checked>
                        <label class="form-check-label" for="is_randomized">Randomize Questions</label>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Exam & Add Questions</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Target Class Modal -->
<div class="modal fade" id="createClassModal" tabindex="-1" aria-labelledby="createClassModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('lecturer.classes.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="createClassModalLabel">Add Target Class</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Class Name</label>
                        <input type="text" class="form-control" name="name" placeholder="e.g. Digital Forensic" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Class Code</label>
                        <input type="text" class="form-control" name="class_code" placeholder="e.g. DF101-01" required>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Class</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Manage Classes Modal -->
<div class="modal fade" id="manageClassesModal" tabindex="-1" aria-labelledby="manageClassesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" id="manageClassesModalLabel">
                    <i class="bi bi-door-open me-2 text-warning"></i>Manage Assigned Classes
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if($classes->isEmpty())
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-inbox fs-1"></i>
                        <p class="mt-2 mb-0">No classes created yet.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>Class Name</th>
                                    <th>Class Code</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($classes as $index => $class)
                                    @php
                                        $classId = $class->class_id ?? $class->id;
                                    @endphp
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td class="fw-semibold">
                                            {{ $class->name ?? $class->class_name ?? 'N/A' }}
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary font-monospace fs-6 px-2 py-1">
                                                {{ $class->class_code ?? $class->code ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <!-- Trigger Edit Modal -->
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-primary me-1" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editClassModal{{ $classId }}">
                                                <i class="bi bi-pencil"></i> Edit
                                            </button>

                                            <!-- Trigger Delete Action -->
                                            <form action="{{ route('lecturer.classes.destroy', ['id' => $classId]) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this class?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Edit Class Modals (Placed at bottom of file to prevent modal backdrop overlap) -->
<!-- Line 560+: Bottom of resources/views/lecturer/dashboard.blade.php -->
<!-- Edit Class Modals -->
@if(isset($classes) && count($classes) > 0)
    @foreach($classes as $class)
        @php
            $classId = $class->class_id ?? $class->id;
        @endphp

        <div class="modal fade" id="editClassModal{{ $classId }}" tabindex="-1" aria-labelledby="editClassModalLabel{{ $classId }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold text-dark" id="editClassModalLabel{{ $classId }}">
                            <i class="bi bi-pencil-square me-2 text-primary"></i>Edit Class
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    
                    <form action="{{ route('lecturer.classes.update', ['id' => $classId]) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="name_{{ $classId }}" class="form-label fw-semibold">Class Name</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="name_{{ $classId }}" 
                                       name="name" 
                                       value="{{ $class->name ?? $class->class_name ?? '' }}" 
                                       required>
                            </div>
                            <div class="mb-3">
                                <label for="class_code_{{ $classId }}" class="form-label fw-semibold">Class Code</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="class_code_{{ $classId }}" 
                                       name="class_code" 
                                       value="{{ $class->class_code ?? $class->code ?? '' }}" 
                                       required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endif

<script>
    function toggleEdit(btn) {
        const row = btn.closest('tr');
        const input = row.querySelector('.edit-input');
        const saveBtn = row.querySelector('.save-btn');

        if (input.hasAttribute('readonly')) {
            input.removeAttribute('readonly');
            input.classList.remove('border-0', 'bg-transparent');
            input.classList.add('border');
            input.focus();
            saveBtn.classList.remove('d-none');
            btn.innerHTML = '<i class="bi bi-x-circle"></i> Cancel';
            btn.classList.replace('btn-outline-primary', 'btn-outline-secondary');
        } else {
            input.setAttribute('readonly', 'readonly');
            input.classList.add('border-0', 'bg-transparent');
            input.classList.remove('border');
            saveBtn.classList.add('d-none');
            btn.innerHTML = '<i class="bi bi-pencil"></i> Edit';
            btn.classList.replace('btn-outline-secondary', 'btn-outline-primary');
        }
    }
</script>

<!-- Assign IDs to your table body and badge count -->
<!-- Inside your badge HTML: <span id="violation-count-badge" class="badge bg-danger fs-6">...</span> -->
<!-- Inside your <tbody> HTML: <tbody id="violations-table-body">...</tbody> -->

{{-- 1. Live Fetching Function --}}
<script>
let isSelecting = false;

// 1. Core Fetch Function (Fetches fresh records and updates UI)
function fetchViolations(force = false) {
    const checkedBoxes = document.querySelectorAll('.violation-checkbox:checked');
    
    if (!force && (checkedBoxes.length > 0 || isSelecting)) {
        return; 
    }

    fetch("{{ route('lecturer.api.violations') }}")
        .then(response => response.json())
        .then(data => {
            updateBadge(data.count);

            const tbody = document.getElementById('violations-table-body');
            if (!tbody) return;

            if (!data.violations || data.violations.length === 0) {
                tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted py-4">No exam violations or tab switches detected.</td></tr>`;
                return;
            }

            let rowsHtml = '';
            data.violations.forEach(v => {
                // Ensure we catch whatever column name your database uses for ID
                const vId = v.violation_id ?? v.id ?? v.exam_violation_id ?? v.id_violation;

                rowsHtml += `
                    <tr id="violation-row-${vId}">
                        <td class="text-center ps-3">
                            <input type="checkbox" name="violation_ids[]" value="${vId}" class="form-check-input violation-checkbox">
                        </td>
                        <td class="text-muted fw-medium">${v.timestamp}</td>
                        <td class="fw-bold text-dark">${v.student_name}</td>
                        <td class="text-secondary">${v.exam_title}</td>
                        <td>
                            <span class="badge bg-warning text-dark fw-semibold px-2 py-1">${v.violation_type}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-danger px-2 py-1">HIGH</span>
                        </td>
                    </tr>
                `;
            });

            tbody.innerHTML = rowsHtml;
        })
        .catch(err => console.error("Error updating violations:", err));
}

// 2. On-Click Single Delete (Instant Refresh)
function deleteSingleViolation(id) {
    if (!id || id === 'undefined') return;

    if (confirm('Delete this violation log?')) {
        fetch(`/lecturer/violations/${id}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ _method: 'DELETE' })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Instantly re-fetch data on click
                isSelecting = false;
                fetchViolations();
            } else {
                alert(data.message || 'Failed to delete record.');
            }
        })
        .catch(err => console.error("Delete Error:", err));
    }
}

function executeBulkDelete() {
    const checkedBoxes = document.querySelectorAll('.violation-checkbox:checked');
    const ids = Array.from(checkedBoxes)
                     .map(cb => cb.value)
                     .filter(id => id && id !== 'undefined');

    if (ids.length === 0) {
        alert('Please select at least one violation to delete.');
        return;
    }

    if (!confirm(`Delete ${ids.length} selected violation log(s)?`)) {
        return;
    }

    fetch("{{ route('lecturer.violations.bulk-delete') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            _method: 'DELETE',
            violation_ids: ids
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // 1. INSTANTLY REMOVE DELETED ROWS FROM DOM (No refresh needed!)
            ids.forEach(id => {
                const row = document.getElementById(`violation-row-${id}`);
                if (row) row.remove();
            });

            // 2. Clear all selection flags & uncheck select-all header
            isSelecting = false;
            const selectAll = document.getElementById('select-all-violations');
            if (selectAll) selectAll.checked = false;

            // 3. Force-trigger fresh fetch to update the red badge counter
            fetchViolations(true); 
        } else {
            alert(data.message || 'Failed to delete selected records.');
        }
    })
    .catch(err => console.error("Bulk Delete Error:", err));
}

// Helper: Badge Counter Update
function updateBadge(count) {
    const badge = document.getElementById('violation-count-badge');
    if (badge) badge.innerText = `${count} Detected`;
}

// Select All Checkbox Handler
document.addEventListener('change', function(e) {
    if (e.target && e.target.id === 'select-all-violations') {
        const isChecked = e.target.checked;
        isSelecting = isChecked;
        document.querySelectorAll('.violation-checkbox').forEach(cb => cb.checked = isChecked);
    }
});

// Polling interval
setInterval(fetchViolations, 3000);
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<style>
    .class-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
</style>

</body>
</html>