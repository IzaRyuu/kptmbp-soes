<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grading Attempt - {{ $attempt->student->user->name ?? $attempt->student->name ?? 'Student' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 mb-1 fw-bold">Grading Student Attempt</h2>
            <p class="text-muted small mb-0">Review responses and update marks for this submission.</p>
        </div>
        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Submissions
        </a>
    </div>

    <!-- Student & Exam Details Header -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body bg-white rounded">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle mb-2">Student Profile</span>
                    <h4 class="fw-bold mb-1">{{ $attempt->student->user->name ?? $attempt->student->name ?? 'Student' }}</h4>
                    <p class="text-muted mb-0 small">
                        <strong>Exam:</strong> {{ $attempt->exam->title ?? $attempt->exam->name ?? 'Exam' }}
                        @if(isset($attempt->submitted_at))
                            <span class="mx-2">•</span> <strong>Submitted:</strong> {{ \Carbon\Carbon::parse($attempt->submitted_at)->format('d M Y, h:i A') }}
                        @endif
                    </p>
                </div>
                <div class="col-md-5 mt-3 mt-md-0 border-start ps-md-4">
                    <!-- Manual Score Key-In Form -->
                    <form action="{{ route('lecturer.attempt.saveGrade', $attempt->attempt_id ?? $attempt->id) }}" method="POST">
                        @csrf
                        <label for="manual_score" class="form-label fw-bold text-dark small mb-1">Final / Overridden Score</label>
                        <div class="input-group">
                            <input type="number" step="0.5" min="0" name="manual_score" id="manual_score" 
                                   class="form-control form-control-lg fw-bold text-success" 
                                   value="{{ number_format($attempt->total_score ?? $attempt->score ?? 0, 2) }}" required>
                            <button type="submit" class="btn btn-success px-4 fw-semibold">
                                <i class="bi bi-floppy me-1"></i> Save Marks
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Questions & Student Answers Section -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="mb-0 fw-bold"><i class="bi bi-journal-text me-2 text-primary"></i>Questions & Student Answers</h5>
        </div>
        <div class="card-body p-4">
            @foreach($attempt->exam->questions as $index => $question)
                @php
                    // Match student answer from collection or array
                    $studentAnswerRecord = null;
                    if (isset($attempt->answers)) {
                        $studentAnswerRecord = is_array($attempt->answers) 
                            ? ($attempt->answers[$question->question_id ?? $question->id] ?? null)
                            : $attempt->answers->firstWhere('question_id', $question->question_id ?? $question->id);
                    } elseif (isset($attempt->studentAnswers)) {
                        $studentAnswerRecord = $attempt->studentAnswers->firstWhere('question_id', $question->question_id ?? $question->id);
                    }
                    
                    $answerText = is_object($studentAnswerRecord) 
                        ? ($studentAnswerRecord->answer_text ?? $studentAnswerRecord->student_answer ?? $studentAnswerRecord->answer)
                        : $studentAnswerRecord;

                    $questionType = strtoupper($question->question_type ?? $question->type ?? 'MCQ');
                @endphp

                <div class="card border mb-3 shadow-none">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="fw-bold mb-0">
                                Q{{ $index + 1 }}. {{ $question->question_text ?? $question->content }}
                            </h6>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-light text-dark border">
                                    {{ $questionType }}
                                </span>
                                <span class="badge bg-secondary">
                                    {{ $question->points ?? 1 }} {{ Str::plural('pt', $question->points ?? 1) }}
                                </span>
                            </div>
                        </div>

                        {{-- SHORT ANSWER / ESSAY DISPLAY --}}
                        @if(in_array($questionType, ['SHORT_ANSWER', 'TEXT', 'ESSAY']))
                            <div class="mt-3 p-3 rounded {{ !empty($answerText) ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-light text-muted border' }}">
                                <span class="small fw-bold d-block mb-1 text-uppercase tracking-wide" style="font-size: 0.75rem;">
                                    <i class="bi bi-pencil-square me-1"></i> Student Response:
                                </span> 
                                <div class="fs-6 {{ !empty($answerText) ? 'fw-semibold' : 'fst-italic' }}">
                                    {{ !empty($answerText) ? $answerText : 'No Answer Provided' }}
                                </div>
                            </div>

                        {{-- MCQ OPTIONS DISPLAY --}}
                        @else
                            <div class="mt-3">
                                <span class="small fw-bold d-block mb-2 text-muted" style="font-size: 0.75rem;">
                                    <i class="bi bi-list-check me-1"></i> Options & Student Selection:
                                </span>
                                <div class="list-group list-group-flush border rounded">
                                    @foreach($question->options as $option)
                                        @php
                                            $isStudentChoice = is_object($studentAnswerRecord) && 
                                                (($studentAnswerRecord->selected_option_id ?? $studentAnswerRecord->option_id) == ($option->option_id ?? $option->id));
                                        @endphp
                                        <div class="list-group-item d-flex justify-content-between align-items-center {{ $isStudentChoice ? 'bg-light' : '' }}">
                                            <div>
                                                <i class="bi {{ $isStudentChoice ? 'bi-check-circle-fill text-primary' : 'bi-circle text-muted' }} me-2"></i>
                                                {{ $option->option_text ?? $option->text }}
                                            </div>
                                            @if($option->is_correct)
                                                <span class="badge bg-success-subtle text-success border border-success-subtle">Correct Answer</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

</body>
</html>