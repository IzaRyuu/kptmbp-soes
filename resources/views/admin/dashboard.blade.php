@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold"><i class="bi bi-shield-lock text-primary me-2"></i>Admin Dashboard</h3>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#registerUserModal">
            <i class="bi bi-person-plus me-1"></i> Register New User
        </button>
    </div>

    <!-- Quick Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 bg-white">
                <span class="text-muted small fw-bold text-uppercase">Total Lecturers</span>
                <h2 class="fw-bold text-primary mb-0">{{ \App\Models\Lecturer::count() }}</h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 bg-white">
                <span class="text-muted small fw-bold text-uppercase">Total Students</span>
                <h2 class="fw-bold text-success mb-0">{{ \App\Models\Student::count() }}</h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 bg-white">
                <span class="text-muted small fw-bold text-uppercase">Total Users</span>
                <h2 class="fw-bold text-dark mb-0">{{ \App\Models\User::count() }}</h2>
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
                    </tr>
                </thead>
                <tbody>
                    @foreach(\App\Models\User::latest()->get() as $u)
                        <tr>
                            <td class="fw-bold">{{ $u->name }}</td>
                            <td>{{ $u->email }}</td>
                            <td>
                                <span class="badge {{ $u->role === 'admin' ? 'bg-danger' : ($u->role === 'lecturer' ? 'bg-primary' : 'bg-success') }}">
                                    {{ ucfirst($u->role) }}
                                </span>
                            </td>
                            <td class="small text-muted">{{ $u->created_at->format('d M Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal for Registering User -->
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
                        <select name="role" id="roleSelect" class="form-select" onchange="toggleMatricInput(this.value)" required>
                            <option value="student">Student</option>
                            <option value="lecturer">Lecturer</option>
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
                    <div class="mb-3" id="matricGroup">
                        <label class="form-label small fw-bold">Matric Number / Student ID</label>
                        <input type="text" name="matric_number" class="form-control" placeholder="e.g. KL2505019076">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Password</label>
                        <input type="password" name="password" class="form-control" required>
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
function toggleMatricInput(role) {
    document.getElementById('matricGroup').style.display = (role === 'student') ? 'block' : 'none';
}
</script>
@endsection