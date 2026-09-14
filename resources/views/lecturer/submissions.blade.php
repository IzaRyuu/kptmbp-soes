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
        <div class="d-flex gap-2">
            <!-- EXPORT ALL CLASS MARKS TO PDF BUTTON -->
            <button type="button" class="btn btn-outline-danger btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#exportClassPdfModal">
                <i class="bi bi-file-earmark-pdf-fill me-1"></i> Export Class PDF
            </button>

            <a href="{{ route('lecturer.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
            </a>
        </div>
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
                            @php
                                // Extract email prefix before @ to use as Student ID / No Matriks
                                $email = $attempt->student->user->email ?? $attempt->student->email ?? '';
                                $extractedMatricId = !empty($email) ? strtoupper(strtok($email, '@')) : 'N/A';
                                
                                // Fallback to database ID columns if email extraction fails
                                $studentId = $extractedMatricId !== 'N/A' ? $extractedMatricId : ($attempt->student->student_id ?? $attempt->student->id_number ?? 'N/A');
                            @endphp
                            <tr>
                                <td class="fw-bold ps-4">
                                    {{ $attempt->student->user->name ?? $attempt->student->name ?? 'N/A' }} 
                                    <span class="text-muted fw-normal">({{ $studentId }})</span>
                                </td>
                                <td>{{ $attempt->submitted_at ? \Carbon\Carbon::parse($attempt->submitted_at)->format('d M Y, h:i A') : 'In Progress' }}</td>
                                <td><span class="badge bg-primary fs-6">{{ $attempt->total_score ?? 0 }} Marks</span></td>
                                <td>
                                    <span class="badge {{ $attempt->status === 'submitted' ? 'bg-success' : 'bg-warning' }}">
                                        {{ ucfirst($attempt->status) }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('lecturer.attempt.grade', $attempt->attempt_id) }}" class="btn btn-sm btn-outline-primary">
                                        Review & Grade
                                    </a>

                                    <form action="{{ route('lecturer.attempt.delete', $attempt->attempt_id ?? $attempt->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this attempt?');" class="d-inline">
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

<!-- MODAL FOR ALL STUDENTS PDF OPTIONS -->
<div class="modal fade" id="exportClassPdfModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-file-earmark-pdf text-danger me-2"></i>Export Class Results PDF</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Lecturer Toggle Option -->
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="toggleStudentNames" checked onchange="toggleStudentNamesColumn()">
                    <label class="form-check-label fw-semibold" for="toggleStudentNames">Include Student Names in PDF Report</label>
                </div>
                <hr>
                
                <!-- ALL STUDENTS PDF PREVIEW -->
                <div id="classResultsPdfPreview" class="p-4 border rounded bg-white shadow-sm">
                    <div class="text-center border-bottom pb-3 mb-3">
                        <h4 class="fw-bold mb-0">Class Results Summary: {{ $exam->title ?? 'Exam' }}</h4>
                        <p class="text-muted small mb-0">Generated on {{ date('d M Y, h:i A') }}</p>
                    </div>

                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th class="pdf-name-col">Student Name</th>
                                <th>Student ID / No Matriks</th>
                                <th>Submitted At</th>
                                <th class="text-end">Final Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($exam->attempts as $index => $attempt)
                                @php
                                    $email = $attempt->student->user->email ?? $attempt->student->email ?? '';
                                    $extractedMatricId = !empty($email) ? strtoupper(strtok($email, '@')) : 'N/A';
                                    $studentId = $extractedMatricId !== 'N/A' ? $extractedMatricId : ($attempt->student->student_id ?? $attempt->student->id_number ?? 'N/A');
                                @endphp
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td class="fw-bold pdf-name-col">
                                        {{ $attempt->student->user->name ?? $attempt->student->name ?? 'N/A' }}
                                    </td>
                                    <td class="fw-semibold">
                                        {{ $studentId }}
                                    </td>
                                    <td class="small">
                                        {{ $attempt->submitted_at ? \Carbon\Carbon::parse($attempt->submitted_at)->format('d M Y, h:i A') : 'In Progress' }}
                                    </td>
                                    <td class="text-end fw-bold text-success fs-6">
                                        {{ $attempt->total_score ?? 0 }} Marks
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No student submissions available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger fw-semibold" onclick="printClassResultsPdf()">
                    <i class="bi bi-printer me-1"></i> Save as PDF / Print
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function toggleStudentNamesColumn() {
        const isChecked = document.getElementById('toggleStudentNames').checked;
        const nameColumns = document.querySelectorAll('#classResultsPdfPreview .pdf-name-col');
        
        nameColumns.forEach(col => {
            col.style.display = isChecked ? '' : 'none';
        });
    }

    function printClassResultsPdf() {
        const content = document.getElementById('classResultsPdfPreview').innerHTML;
        const printWindow = window.open('', '', 'height=700,width=900');
        
        printWindow.document.write('<html><head><title>Class Results Summary</title>');
        printWindow.document.write('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">');
        printWindow.document.write('<style>body{padding: 30px;} @media print { .table { border-collapse: collapse !important; } }</style>');
        printWindow.document.write('</head><body>');
        printWindow.document.write(content);
        printWindow.document.write('</body></html>');
        
        printWindow.document.close();
        printWindow.focus();

        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 500);
    }
</script>
</body>
</html>