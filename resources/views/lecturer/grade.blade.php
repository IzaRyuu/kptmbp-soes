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
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><i class="bi bi-journal-text me-2 text-primary"></i>Questions & Student Answers</h5>
        </div>
        <div class="card-body p-4">

            <!-- 1. OPEN FORM HERE BEFORE THE LOOP -->
            <form action="{{ route('lecturer.attempt.saveGrade', $attempt->attempt_id ?? $attempt->id) }}" method="POST">
                @csrf

                @foreach($attempt->exam->questions as $index => $question)
                    @php
                        // Match student answer logic...
                    @endphp

                    <div class="card border mb-3 shadow-none">
                        <div class="card-body">
                            <!-- Question Text, Options, Reference Answer, Word Count, & Input Marks -->
                        </div>
                    </div>
                @endforeach

                <!-- 2. ADD SAVE BUTTON AT THE BOTTOM OF THE LOOP -->
                <div class="d-flex justify-content-end mt-4 pt-3 border-top">
                    <button type="submit" class="btn btn-success btn-lg px-4 fw-semibold shadow-sm">
                        <i class="bi bi-floppy me-2"></i> Save All Marks
                    </button>
                </div>

            <!-- 3. CLOSE FORM HERE -->
            </form>

                        {{-- SHORT ANSWER / ESSAY DISPLAY --}}
                        @if(in_array($questionType, ['SHORT_ANSWER', 'TEXT', 'ESSAY']))
                            <!-- Reference Answer / Keywords Box -->
                            <div class="mt-3 p-3 rounded bg-light border">
                                <span class="small fw-bold d-block mb-1 text-primary text-uppercase tracking-wide" style="font-size: 0.75rem;">
                                    <i class="bi bi-journal-check me-1"></i> Expected Answer / Keywords:
                                </span>
                                <div class="fs-6 text-dark fw-medium">
                                    {!! !empty($question->correct_answer_text) ? nl2br(e($question->correct_answer_text)) : ($question->answer_key ?? $question->expected_answer ?? 'No reference answer provided.') !!}
                                </div>
                            </div>

                            <!-- Student Submitted Response Box with Word Count -->
                            @php
                                $trimmedText = trim(strip_tags($answerText ?? ''));
                                $wordCount = !empty($trimmedText) ? count(preg_split('/\s+/', $trimmedText)) : 0;
                            @endphp

                            <div class="mt-2 p-3 rounded {{ !empty($answerText) ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-light text-muted border' }}">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="small fw-bold text-uppercase tracking-wide" style="font-size: 0.75rem;">
                                        <i class="bi bi-pencil-square me-1"></i> Student Response:
                                    </span>
                                    <span class="badge bg-white text-dark border fw-semibold">
                                        <i class="bi bi-fonts me-1 text-primary"></i> Word Count: {{ $wordCount }} @if(!empty($question->word_limit)) / {{ $question->word_limit }} max @endif
                                    </span>
                                </div> 
                                <div class="fs-6 {{ !empty($answerText) ? 'fw-semibold text-break' : 'fst-italic' }}">
                                    {{ !empty($answerText) ? $answerText : 'No Answer Provided' }}
                                </div>
                            </div>

                            <!-- Individual Question Marking Input Box -->
                            <div class="mt-3 p-3 bg-white rounded border d-flex justify-content-between align-items-center">
                                <div>
                                    <label for="marks_{{ $question->question_id ?? $question->id }}" class="form-label fw-bold mb-0 text-dark small">
                                        <i class="bi bi-award-fill text-warning me-1"></i> Award Marks for Q{{ $index + 1 }}:
                                    </label>
                                    <div class="text-muted small">Max Points: {{ $question->points ?? 1 }}</div>
                                </div>
                                <div class="input-group" style="width: 160px;">
                                    <input type="number" 
                                           step="0.5" 
                                           min="0" 
                                           max="{{ $question->points ?? 1 }}" 
                                           name="question_marks[{{ $question->question_id ?? $question->id }}]" 
                                           id="marks_{{ $question->question_id ?? $question->id }}"
                                           class="form-control text-center fw-bold text-success border-success" 
                                           placeholder="0"
                                           value="{{ $studentAnswerRecord->score ?? $studentAnswerRecord->marks ?? '' }}">
                                    <span class="input-group-text bg-light text-muted">/ {{ $question->points ?? 1 }}</span>
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