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

    <!-- User Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 fw-bold">System Registered Users</div>
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Registered Date</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $u)
                        @php $uId = $u->user_id ?? $u->id; @endphp
                        <tr>
                            <td class="fw-bold">{{ $u->name }}</td>
                            <td>{{ $u->email }}</td>
                            <td>
                                <span class="badge {{ $u->role === 'admin' ? 'bg-danger' : ($u->role === 'lecturer' ? 'bg-primary' : 'bg-success') }}">
                                    {{ ucfirst($u->role) }}
                                </span>
                            </td>
                            <td class="small text-muted">{{ $u->created_at ? $u->created_at->format('d M Y') : 'N/A' }}</td>
                            <td class="text-end pe-4">
                                @if(auth()->id() != $uId)
                                    <button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteUserModal{{ $uId }}">
                                        <i class="bi bi-trash me-1"></i> Delete Profile
                                    </button>

                                    <!-- Delete Confirmation Modal -->
                                    <div class="modal fade" id="deleteUserModal{{ $uId }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-sm">
                                            <div class="modal-content text-center p-3">
                                                <div class="text-danger mb-2">
                                                    <i class="bi bi-exclamation-octagon fs-1"></i>
                                                </div>
                                                <h5 class="fw-bold mb-1">Delete User?</h5>
                                                <p class="text-muted small mb-3">Are you sure you want to delete <strong>{{ $u->name }}</strong>? This action cannot be undone.</p>
                                                <form action="{{ route('admin.user.delete', $uId) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger w-100 mb-2">Yes, Delete Account</button>
                                                    <button type="button" class="btn btn-light w-100" data-bs-dismiss="modal">Cancel</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="badge bg-light text-muted border">Current Admin</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
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
</script>
@endsection