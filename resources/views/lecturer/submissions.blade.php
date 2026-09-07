<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submissions - {{ $exam->title ?? 'Exam' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 mb-1 fw-bold">Submissions for {{ $exam->title ?? 'Exam' }}</h2>
            <p class="text-muted small mb-0">Review student attempts and manage grades.</p>
        </div>
        <a href="{{ route('lecturer.dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-4">Student Name</th>
                            <th scope="col">Submitted At</th>
                            <th scope="col">Current Score</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($exam->attempts as $attempt)
                            <tr>
                                <td class="fw-bold">{{ $attempt->student->user->name ?? 'N/A' }}</td>
                                <td>{{ $attempt->submitted_at ? \Carbon\Carbon::parse($attempt->submitted_at)->format('d M Y, h:i A') : 'In Progress' }}</td>
                                <td><span class="badge bg-primary fs-6">{{ $attempt->total_score }} Marks</span></td>
                                <td>
                                    <span class="badge {{ $attempt->status === 'submitted' ? 'bg-success' : 'bg-warning' }}">
                                        {{ ucfirst($attempt->status) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('lecturer.attempt.grade', $attempt->attempt_id) }}" class="btn btn-sm btn-outline-primary">
                                        Review & Grade
                                    </a>

                                    <!-- Delete Button Form -->
                                    <form action="{{ route('lecturer.attempt.delete', $attempt->attempt_id ?? $attempt->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this attempt?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No student submissions found yet.</td>
                            </tr>
                        @endforelse

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</body>
</html>