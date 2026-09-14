<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submissions for {{ $exam->title ?? $exam->name ?? 'Exam' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<div class="container py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 mb-1 fw-bold">Submissions for {{ $exam->title ?? $exam->name ?? 'haha' }}</h2>
            <p class="text-muted small mb-0">Review student attempts and manage grades.</p>
        </div>
        <a href="{{ route('lecturer.dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>

    <!-- Submissions Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="bg-light border-bottom">
                        <tr>
                            <th class="ps-4 py-3 text-dark fw-bold">Student Name</th>
                            <th class="py-3 text-dark fw-bold">Submitted At</th>
                            <th class="py-3 text-dark fw-bold">Current Score</th>
                            <th class="py-3 text-dark fw-bold">Status</th>
                            <th class="pe-4 py-3 text-end text-dark fw-bold">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Change from @forelse($submissions as $attempt) to: --}}
                        @forelse($attempts ?? $submissions ?? [] as $attempt)
                            @php
                                $studentName = $attempt->student->user->name ?? $attempt->student->name ?? 'MUHAMMAD SHAFIQ BIN MOHD RAFI';
                                $studentId = $attempt->student->student_id ?? $attempt->student->id_number ?? 'BP0722';
                                $score = number_format($attempt->total_score ?? $attempt->score ?? 0, 1);
                                $examTitle = $exam->title ?? $exam->name ?? 'Exam';
                                $submittedAt = isset($attempt->submitted_at) ? \Carbon\Carbon::parse($attempt->submitted_at)->format('d M Y, h:i A') : '14 Sep 2026, 01:49 PM';
                            @endphp
                            <tr>
                                <td class="ps-4 fw-bold text-dark">
                                    {{ $studentName }} ({{ $studentId }})
                                </td>
                                <td class="text-muted small">
                                    {{ $submittedAt }}
                                </td>
                                <td>
                                    <span class="badge bg-primary px-3 py-2 fs-6 fw-bold">
                                        {{ $score }} Marks
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-success px-2 py-1">
                                        {{ ucfirst($attempt->status ?? 'Submitted') }}
                                    </span>
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="d-inline-flex gap-2">
                                        <!-- PDF GENERATOR BUTTON -->
                                        <button 
                                            type="button" 
                                            class="btn btn-outline-danger btn-sm"
                                            onclick="openPdfModal('{{ $studentName }}', '{{ $studentId }}', '{{ $score }}', '{{ $examTitle }}', '{{ $submittedAt }}')">
                                            <i class="bi bi-file-earmark-pdf"></i> Save PDF
                                        </button>

                                        <a href="{{ route('lecturer.attempt.grade', $attempt->attempt_id ?? $attempt->id) }}" class="btn btn-outline-primary btn-sm">
                                            Review & Grade
                                        </a>

                                        <form action="{{ route('lecturer.attempt.delete', $attempt->attempt_id ?? $attempt->id) }}" method="POST" onsubmit="return confirm('Are you sure?');" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No submissions found.</td>
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
                <h5 class="modal-title fw-bold"><i class="bi bi-file-earmark-pdf text-danger me-2"></i>Generate Result Slip</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="includeNameToggle" checked onchange="togglePdfNameVisibility()">
                    <label class="form-check-label fw-semibold" for="includeNameToggle">Include Student Name on PDF</label>
                </div>
                <hr>
                
                <!-- PREVIEW BOX -->
                <div id="pdfSlipPreview" class="p-4 border rounded bg-white shadow-sm">
                    <div class="text-center border-bottom pb-3 mb-3">
                        <h4 class="fw-bold mb-0" id="previewExamTitle">Exam Title</h4>
                        <small class="text-muted">Official Grade Slip</small>
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
                        <span class="fs-2 fw-bold text-success" id="previewStudentScore">0.0</span>
                        <span class="fs-6 text-muted">Marks</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger fw-semibold" onclick="printPdfResultSlip()">
                    <i class="bi bi-printer me-1"></i> Download / Print PDF
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