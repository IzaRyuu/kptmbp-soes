<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $exam->title }} - Examination</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>

<body class="bg-light">

<nav class="navbar navbar-dark bg-dark px-4 py-3">

    <span class="navbar-brand fw-bold">
        <i class="bi bi-mortarboard-fill me-2"></i>
        KPTMBP SoES
    </span>

    <span class="text-white">
        {{ $student->matrix_number ?? $user->name }}
    </span>

</nav>


<div class="container py-5">

    {{-- Exam Header --}}
    <div class="card shadow-sm border-0 mb-4">

        <div class="card-body">

            <h2 class="fw-bold">
                {{ $exam->title }}
            </h2>

            <p class="text-muted mb-1">
                Class:
                <strong>
                    {{ $exam->class->class_name ?? 'N/A' }}
                </strong>
            </p>

            <p class="text-muted mb-0">
                Duration:
                <strong>
                    {{ $exam->duration_minutes }} minutes
                </strong>
            </p>

            <div class="card mb-4 border-warning bg-light">
                <div class="card-body d-flex justify-content-between align-items-center py-2">
                    <span class="fw-bold text-dark"><i class="bi bi-clock-history me-1"></i> Time Remaining:</span>
                    <span id="exam-timer" class="badge bg-danger fs-5 px-3 py-2">00:00:00</span>
                </div>
            </div>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- EXAM SUBMISSION FORM --}}
    {{-- ========================================================= --}}

    <form
        action="{{ route('student.exam.submit', $exam->exam_id) }}"
        method="POST"
    >

        @csrf


        {{-- Questions --}}

        @foreach($exam->questions as $index => $question)
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <h5 class="fw-bold">
                        Question {{ $index + 1 }} 
                        <span class="badge bg-secondary ms-2">{{ $question->points ?? 1 }} Point</span>
                    </h5>
                    <p class="mt-3">{{ $question->question_text ?? $question->content }}</p>

                    {{-- 1. MCQ OPTIONS --}}
                    @if(in_array(strtoupper($question->question_type ?? $question->type), ['MCQ', 'MULTIPLE_CHOICE']))
                        <div class="mt-3">
                            @foreach($question->options as $option)
                                <div class="form-check mb-2">
                                    <input class="form-check-input" 
                                        type="radio" 
                                        name="answers[{{ $question->question_id ?? $question->id }}]" 
                                        id="option_{{ $option->option_id ?? $option->id }}" 
                                        value="{{ $option->option_id ?? $option->id }}">
                                    <label class="form-check-label" for="option_{{ $option->option_id ?? $option->id }}">
                                        {{ $option->option_text ?? $option->text }}
                                    </label>
                                </div>
                            @endforeach
                        </div>

                    {{-- 2. SHORT ANSWER TEXTAREA (Handles SHORT_ANSWER, TEXT, ESSAY, etc.) --}}
                    @else
                        <div class="mt-3">
                            <textarea 
                                name="answers[{{ $question->question_id ?? $question->id }}]" 
                                class="form-control" 
                                rows="4" 
                                placeholder="Type your answer here..."></textarea>
                        </div>
                    @endif

                </div>
            </div>
        @endforeach


        {{-- ========================================================= --}}
        {{-- SUBMIT BUTTON --}}
        {{-- ========================================================= --}}

        <div class="text-end mb-5">

            <button
                type="submit"
                class="btn btn-success btn-lg"
                onclick="return confirm('Are you sure you want to submit this examination?');"
            >
                <i class="bi bi-check-circle me-1"></i>
                Submit Examination
            </button>

        </div>


    </form>

</div>
<!-- Custom Warning Toast Banner -->
<div id="violation-toast" class="alert alert-danger position-fixed top-0 start-50 translate-middle-x mt-3 shadow-lg d-none" style="z-index: 9999; min-width: 320px;">
    <strong>⚠️ Security Alert!</strong> Tab switching is prohibited. Violation logged.
</div>

<script>
let tabSwitchCount = 0;
let isSubmitting = false;

// Prevent false positive on form submit
const examForm = document.querySelector('form');
if (examForm) {
    examForm.addEventListener('submit', function() {
        isSubmitting = true;
    });
}

function handleTabSwitch() {
    // Ignore trigger if exam form is being submitted
    if (isSubmitting) return;

    // Double check window focus to avoid false positives
    if (document.hidden || !document.hasFocus()) {
        tabSwitchCount++;

        // 1. Display non-blocking banner instead of window.alert()
        const toast = document.getElementById('violation-toast');
        if (toast) {
            toast.innerText = `⚠️ Warning! Tab switching is prohibited. Violation #${tabSwitchCount} logged.`;
            toast.classList.remove('d-none');
            setTimeout(() => toast.classList.add('d-none'), 4000);
        }

        // 2. Notify backend via AJAX
        fetch("{{ route('student.exam.logViolation', $exam->exam_id) }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                violation_type: 'tab_switch'
            })
        }).catch(error => console.error('Violation logging failed:', error));
    }
}

// Attach event listeners safely
document.addEventListener('visibilitychange', function() {
    if (document.visibilityState === 'hidden') {
        handleTabSwitch();
    }
});

window.addEventListener('blur', function() {
    // Triggers if student clicks onto another application window
    handleTabSwitch();
});
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        let remainingSeconds = parseInt("{{ $remainingSeconds }}", 10) || 0;
        const timerElement = document.getElementById('exam-timer');
        const examForm = document.getElementById('exam-form');
        let autoSubmitted = false;

        function updateTimerDisplay() {
            if (remainingSeconds <= 0) {
                if (!autoSubmitted) {
                    autoSubmitted = true;
                    if (timerElement) timerElement.innerText = "00:00:00";

                    // Show alert; upon pressing OK, submit form or redirect to dashboard
                    alert('Time is up! Your exam is being submitted automatically.');

                    if (examForm) {
                        // Submit form first, controller will process and redirect to dashboard
                        examForm.submit();
                    } else {
                        window.location.href = "{{ route('student.dashboard') }}";
                    }
                }
                return;
            }

            let hours = Math.floor(remainingSeconds / 3600);
            let minutes = Math.floor((remainingSeconds % 3600) / 60);
            let seconds = remainingSeconds % 60;

            if (timerElement) {
                timerElement.innerText = 
                    String(hours).padStart(2, '0') + ':' +
                    String(minutes).padStart(2, '0') + ':' +
                    String(seconds).padStart(2, '0');
            }

            remainingSeconds--;
        }

        updateTimerDisplay();
        setInterval(updateTimerDisplay, 1000);
    });
</script>

</body>
</html>