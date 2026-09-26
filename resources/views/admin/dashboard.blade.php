@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold"><i class="bi bi-shield-lock text-primary me-2"></i>Admin Dashboard</h3>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#registerUserModal">
            <i class="bi bi-person-plus me-1"></i> Register New User
        </button>
    </div>

    <!-- Alert Messages -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i>Action Failed:</div>
            <ul class="mb-0 ps-3 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Quick Stats (Clickable Cards) -->
    <div class="row g-3 mb-4">
        <!-- Clickable Total Lecturers Card -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 bg-white h-100" 
                 style="cursor: pointer; transition: transform 0.2s;" 
                 data-bs-toggle="modal" 
                 data-bs-target="#lecturersModal"
                 onmouseover="this.style.transform='scale(1.02)'" 
                 onmouseout="this.style.transform='scale(1)'">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase">Total Lecturers</span>
                        <h2 class="fw-bold text-primary mb-0">{{ $totalLecturers }}</h2>
                    </div>
                    <div class="bg-primary-subtle text-primary p-3 rounded-circle">
                        <i class="bi bi-person-badge fs-4"></i>
                    </div>
                </div>
                <div class="mt-2 text-primary small fw-semibold">
                    <i class="bi bi-info-circle me-1"></i> Click to view details
                </div>
            </div>
        </div>

        <!-- Clickable Total Students Card -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 bg-white h-100" 
                 style="cursor: pointer; transition: transform 0.2s;" 
                 data-bs-toggle="modal" 
                 data-bs-target="#studentsModal"
                 onmouseover="this.style.transform='scale(1.02)'" 
                 onmouseout="this.style.transform='scale(1)'">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase">Total Students</span>
                        <h2 class="fw-bold text-success mb-0">{{ $totalStudents }}</h2>
                    </div>
                    <div class="bg-success-subtle text-success p-3 rounded-circle">
                        <i class="bi bi-mortarboard fs-4"></i>
                    </div>
                </div>
                <div class="mt-2 text-success small fw-semibold">
                    <i class="bi bi-info-circle me-1"></i> Click to view details
                </div>
            </div>
        </div>

        <!-- Total Users Card -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 bg-white h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase">Total Users</span>
                        <h2 class="fw-bold text-dark mb-0">{{ $totalUsers }}</h2>
                    </div>
                    <div class="bg-light text-dark p-3 rounded-circle border">
                        <i class="bi bi-people fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

   <!-- Profile Change Monitoring Logs -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-clock-history me-2 text-primary"></i>Profile Audit & Activity Monitoring Logs
                </h5>
                <small class="text-muted">Track all personal detail modifications made by Lecturers, Students, and Admins</small>
            </div>
            <span class="badge bg-primary px-3 py-2">
                {{ isset($auditLogs) ? $auditLogs->total() : 0 }} Recorded Changes
            </span>
        </div>

        <div class="card-body p-3">
            <!-- LOG FILTER TABS -->
            <ul class="nav nav-pills mb-3 gap-2" id="logTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active btn-sm fw-semibold" onclick="filterLogs('all', this)">
                        <i class="bi bi-list-stars me-1"></i> All Logs
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link btn-sm fw-semibold text-info" onclick="filterLogs('lecturer', this)">
                        <i class="bi bi-person-badge me-1"></i> Lecturers
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link btn-sm fw-semibold text-success" onclick="filterLogs('student', this)">
                        <i class="bi bi-mortarboard me-1"></i> Students
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link btn-sm fw-semibold text-danger" onclick="filterLogs('admin', this)">
                        <i class="bi bi-shield-lock me-1"></i> Admins
                    </button>
                </li>
            </ul>

            <div class="row g-3" id="auditLogsContainer">
                @isset($auditLogs)
                    @forelse($auditLogs as $log)
                        <!-- Log Item Card with dynamic data-role attribute -->
                        <div class="col-12 log-item" data-role="{{ strtolower($log->user_role) }}">
                            <div class="card border border-light-subtle shadow-sm rounded-3">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <!-- Role Badge -->
                                            @if(strtolower($log->user_role) === 'admin')
                                                <span class="badge bg-danger text-white px-2 py-1 me-1">
                                                    <i class="bi bi-shield-lock-fill me-1"></i>Admin
                                                </span>
                                            @elseif(strtolower($log->user_role) === 'lecturer')
                                                <span class="badge bg-info text-dark px-2 py-1 me-1">
                                                    <i class="bi bi-person-badge-fill me-1"></i>Lecturer
                                                </span>
                                            @elseif(strtolower($log->user_role) === 'student')
                                                <span class="badge bg-success text-white px-2 py-1 me-1">
                                                    <i class="bi bi-mortarboard-fill me-1"></i>Student
                                                </span>
                                            @else
                                                <span class="badge bg-secondary text-white px-2 py-1 me-1">
                                                    <i class="bi bi-person-fill me-1"></i>{{ ucfirst($log->user_role) }}
                                                </span>
                                            @endif

                                            <h6 class="fw-bold mb-0 text-dark">{{ $log->user_name }}</h6>
                                        </div>

                                        <small class="text-muted fw-semibold">
                                            <i class="bi bi-calendar3 me-1"></i>{{ $log->created_at->format('d M Y, h:i A') }}
                                        </small>
                                    </div>

                                    <!-- Change Details Box -->
                                    <div class="p-2 bg-light rounded d-flex flex-wrap align-items-center justify-content-between gap-3 text-sm">
                                        <div>
                                            <span class="text-muted small d-block">MODIFIED FIELD:</span>
                                            <span class="badge bg-secondary text-white fw-bold">{{ $log->changed_field }}</span>
                                        </div>

                                        <div class="d-flex align-items-center gap-3">
                                            <div class="text-end">
                                                <span class="text-muted small d-block">OLD VALUE:</span>
                                                <span class="text-danger fw-semibold text-decoration-line-through">{{ $log->old_value ?? 'N/A' }}</span>
                                            </div>

                                            <i class="bi bi-arrow-right fs-5 text-primary"></i>

                                            <div>
                                                <span class="text-muted small d-block">NEW VALUE:</span>
                                                <span class="text-success fw-bold">{{ $log->new_value }}</span>
                                            </div>
                                        </div>

                                        <div class="border-start ps-3">
                                            <span class="text-muted small d-block">CHANGED BY:</span>
                                            <span class="fw-bold text-dark"><i class="bi bi-person-check me-1 text-primary"></i>{{ $log->changed_by_name }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-shield-check fs-1 text-secondary mb-2 d-block"></i>
                                <p class="mb-0">No profile changes have been recorded yet.</p>
                            </div>
                        </div>
                    @endforelse
                @endisset
            </div>

            <!-- Pagination -->
            @if(isset($auditLogs) && $auditLogs->hasPages())
                <div class="mt-3 d-flex justify-content-end">
                    {{ $auditLogs->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- MODAL: Lecturers Detail List -->
<div class="modal fade" id="lecturersModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-person-badge me-2"></i>Lecturers Directory</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Staff Number</th>
                            <th>Department</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lecturers as $lec)
                            <tr>
                                <td class="fw-bold">{{ $lec->name }}</td>
                                <td>{{ $lec->email }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $lec->lecturer->staff_number ?? 'N/A' }}</span></td>
                                <td>{{ $lec->lecturer->department ?? 'General' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center py-4 text-muted">No registered lecturers found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: Students Detail List -->
<div class="modal fade" id="studentsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-mortarboard me-2"></i>Students Directory</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Matric Number</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $stu)
                            <tr>
                                <td class="fw-bold">{{ $stu->name }}</td>
                                <td>{{ $stu->email }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $stu->student->matric_number ?? $stu->student->matrix_number ?? 'N/A' }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center py-4 text-muted">No registered students found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: Register New User -->
<div class="modal fade" id="registerUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('account.register') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Register User Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">User Role</label>
                        <select name="role" id="roleSelect" class="form-select" onchange="toggleFormFields(this.value)" required>
                            <option value="student">Student</option>
                            <option value="lecturer">Lecturer</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Full Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Email (@kptm.edu.my)</label>
                        <input type="email" name="email" class="form-control" placeholder="user@kptm.edu.my" required>
                    </div>
                    
                    <div id="studentFields" class="mb-3">
                        <label for="matric_number" class="form-label fw-semibold small text-secondary">Matric Number / Student ID</label>
                        <input type="text" name="matric_number" id="matric_number" class="form-control" placeholder="e.g. BPNxxxxxxxxx">
                    </div>

                    <div id="lecturerFields" class="mb-3" style="display: none;">
                        <label for="staff_number" class="form-label fw-semibold small text-secondary">Staff Number</label>
                        <input type="text" name="staff_number" id="staff_number" class="form-control" placeholder="e.g. STF-2026-001">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Password</label>
                        <input type="password" name="password" class="form-control" required minlength="6">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleFormFields(role) {
    const studentFields = document.getElementById('studentFields');
    const lecturerFields = document.getElementById('lecturerFields');

    if (studentFields) studentFields.style.display = (role === 'student') ? 'block' : 'none';
    if (lecturerFields) lecturerFields.style.display = (role === 'lecturer') ? 'block' : 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('roleSelect');
    if (roleSelect) toggleFormFields(roleSelect.value);
});
function filterLogs(role, btnElement) {
    // 1. Update Active Class on Filter Tab Buttons
    const buttons = document.querySelectorAll('#logTabs .nav-link');
    buttons.forEach(btn => btn.classList.remove('active'));
    btnElement.classList.add('active');

    // 2. Filter Log Item Cards based on data-role
    const logItems = document.querySelectorAll('.log-item');
    logItems.forEach(item => {
        const itemRole = item.getAttribute('data-role');
        if (role === 'all' || itemRole === role) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}
</script>
@endsection