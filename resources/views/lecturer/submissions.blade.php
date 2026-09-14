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
                            @php
                                $studentName = $attempt->student->user->name ?? $attempt->student->name ?? 'N/A';
                                $studentId = $attempt->student->student_id ?? $attempt->student->id_number ?? $attempt->student_id ?? 'N/A';
                                $submittedAtText = $attempt->submitted_at ? \Carbon\Carbon::parse($attempt->submitted_at)->format('d M Y, h:i A') : 'In Progress';
                                $scoreText = $attempt->total_score ?? 0;
                                $examTitle = $exam->title ?? 'Exam';
                            @endphp
                            <tr>
                                <td class="fw-bold ps-4">
                                    {{ $studentName }}
                                    @if($studentId !== 'N/A')
                                        <span class="text-muted fw-normal">({{ $studentId }})</span>
                                    @endif
                                </td>
                                <td>{{ $submittedAtText }}</td>
                                <td><span class="badge bg-primary fs-6">{{ $scoreText }} Marks</span></td>
                                <td>
                                    <span class="badge {{ $attempt->status === 'submitted' ? 'bg-success' : 'bg-warning' }}">
                                        {{ ucfirst($attempt->status) }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-inline-flex gap-2">
                                        <!-- Save PDF Button -->
                                        <button 
                                            type="button" 
                                            class="btn btn-sm btn-outline-danger"
                                            onclick="openPdfModal('{{ e($studentName) }}', '{{ e($studentId) }}', '{{ e($scoreText) }}', '{{ e($examTitle) }}', '{{ e($submittedAtText) }}')">
                                            <i class="bi bi-file-earmark-pdf me-1"></i> Save PDF
                                        </button>

                                        <a href="{{ route('lecturer.attempt.grade', $attempt->attempt_id) }}" class="btn btn-sm btn-outline-primary">
                                            Review & Grade
                                        </a>

                                        <!-- Delete Button Form -->
                                        <form action="{{ route('lecturer.attempt.delete', $attempt->attempt_id ?? $attempt->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this attempt?');" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
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

<!-- MODAL FOR PDF OPTIONS & PRINTING -->
<div class="modal fade" id="pdfOptionsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-file-earmark-pdf text-danger me-2"></i>Generate Student Result Slip</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Lecturer Toggle Option -->
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="includeNameToggle" checked onchange="togglePdfNameVisibility()">
                    <label class="form-check-label fw-semibold" for="includeNameToggle">Include Student Name on PDF</label>
                </div>
                <hr>
                
                <!-- PREVIEW BOX -->
                <div id="pdfSlipPreview" class="p-4 border rounded bg-white shadow-sm">
                    <div class="text-center border-bottom pb-3 mb-3">
                        <h4 class="fw-bold mb-0" id="previewExamTitle">Exam Title</h4>
                        <small class="text-muted">Official Grade Result Slip</small>
                    </div>
                    <div class="mb-2" id="previewNameRow">
                        <strong class="text-muted small uppercase">Student Name:</strong>
                        <div class="fw-bold fs-5 text-dark" id="previewStudentName">-</div>
                    </div>
                    <div class="mb-3">
                        <strong class="text-muted small uppercase">Student ID:</strong>
                        <div class="fw-bold fs-5 text-dark" id="previewStudentId">-</div>
                    </div>
                    <div class="mb-3">
                        <strong class="text-muted small uppercase">Submitted At:</strong>
                        <div class="text-dark small" id="previewSubmittedAt">-</div>
                    </div>
                    <div class="p-3 bg-light rounded text-center border">
                        <span class="text-muted small fw-bold uppercase d-block">Final Mark</span>
                        <span class="fs-2 fw-bold text-success" id="previewStudentScore">0</span>
                        <span class="fs-6 text-muted">Marks</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger fw-semibold" onclick="printPdfResultSlip()">
                    <i class="bi bi-printer me-1"></i> Save as PDF / Print
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    let modalInstance = null;

    function openPdfModal(name, id, score, examTitle, submittedAt) {
        document.getElementById('previewStudentName').innerText = name;
        document.getElementById('previewStudentId').innerText = id;
        document.getElementById('previewStudentScore').innerText = score;
        document.getElementById('previewExamTitle').innerText = examTitle;
        document.getElementById('previewSubmittedAt').innerText = submittedAt;
        
        // Reset checkbox to checked
        document.getElementById('includeNameToggle').checked = true;
        document.getElementById('previewNameRow').style.display = 'block';

        modalInstance = new bootstrap.Modal(document.getElementById('pdfOptionsModal'));
        modalInstance.show();
    }

    function togglePdfNameVisibility() {
        const isChecked = document.getElementById('includeNameToggle').checked;
        document.getElementById('previewNameRow').style.display = isChecked ? 'block' : 'none';
    }

    function printPdfResultSlip() {
        const content = document.getElementById('pdfSlipPreview').innerHTML;
        const printWindow = window.open('', '', 'height=600,width=800');
        
        printWindow.document.write('<html><head><title>Result Slip</title>');
        printWindow.document.write('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">');
        printWindow.document.write('</head><body class="p-5">');
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