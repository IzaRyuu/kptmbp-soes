<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Questions - {{ $exam->title }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<div class="container py-4">
    <a href="{{ route('lecturer.dashboard') }}" class="btn btn-outline-secondary mb-3">
        <i class="bi bi-arrow-left"></i> Back to Dashboard
    </a>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <h3 class="fw-bold mb-1">{{ $exam->title }}</h3>
            <p class="text-muted mb-0">
                Assigned Class: <strong>{{ $exam->class->class_name ?? 'N/A' }}</strong> | 
                Duration: <strong>{{ $exam->duration_minutes ?? $exam->duration }} mins</strong>
            </p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Left Side: Add Question Form -->
        <div class="col-lg-5">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white fw-bold">
                    <i class="bi bi-plus-circle me-1"></i> Add Question
                </div>
                <div class="card-body">
                    <form action="{{ route('lecturer.questions.store', $exam->exam_id ?? $exam->id) }}" method="POST" id="questionForm">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-bold">Question Text</label>
                            <textarea name="question_text" class="form-control @error('question_text') is-invalid @enderror" rows="3" required>{{ old('question_text') }}</textarea>
                            @error('question_text')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Type</label>
                            <select name="question_type" class="form-select @error('question_type') is-invalid @enderror" id="questionTypeSelect">
                                <option value="mcq" {{ old('question_type') === 'mcq' ? 'selected' : '' }}>Multiple Choice (MCQ)</option>
                                <option value="short_answer" {{ old('question_type') === 'short_answer' ? 'selected' : '' }}>Short Answer</option>
                            </select>
                            @error('question_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Points</label>
                            <input type="number" name="points" class="form-control @error('points') is-invalid @enderror" value="{{ old('points', 1) }}" min="1" required>
                            @error('points')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- MCQ Options Block -->
                        <div id="mcqOptionsBlock" class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label fw-bold mb-0">Answer Options</label>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="addOptionBtn">
                                    <i class="bi bi-plus"></i> Add Option
                                </button>
                            </div>
                            <small class="text-muted d-block mb-2">Select the radio button next to the correct answer.</small>
                            
                            <div id="optionsContainer">
                                @php $oldOptions = old('options', ['', '', '', '']); @endphp
                                @foreach($oldOptions as $i => $optValue)
                                    <div class="input-group mb-2 option-row">

                                        <div class="input-group-text">
                                            <input
                                                type="radio"
                                                name="correct_option"
                                                value="{{ $i }}"
                                                {{ old('correct_option', 0) == $i ? 'checked' : '' }}
                                            >
                                        </div>

                                        <input
                                            type="text"
                                            name="options[]"
                                            class="form-control option-input"
                                            placeholder="Option {{ $i + 1 }}"
                                            value="{{ $optValue }}"
                                            required
                                        >

                                        <button
                                            type="button"
                                            class="btn btn-outline-danger remove-option-btn"
                                        >
                                            <i class="bi bi-x-lg"></i>
                                        </button>

                                    </div>
                                @endforeach
                            </div>
                            @error('options')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Short Answer Block -->
                        <div id="shortAnswerBlock" class="mb-3" style="display: none;">
                            <label class="form-label fw-bold">Expected Answer / Keywords <span class="text-muted fw-normal">(Optional)</span></label>
                            <textarea name="correct_answer_text" class="form-control" rows="2" placeholder="Reference answer or grading keywords">{{ old('correct_answer_text') }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mt-2">
                            <i class="bi bi-save me-1"></i> Add Question
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Side: Question Bank -->
        <div class="col-lg-7">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center py-3">
                    <span class="fs-6"><i class="bi bi-list-task me-2"></i>Question Bank</span>
                    <span class="badge bg-primary rounded-pill px-3 py-2">{{ $exam->questions ? $exam->questions->count() : 0 }} Questions</span>
                </div>
                <div class="card-body">
                    @if(isset($exam->questions) && $exam->questions->count() > 0)
                        @foreach($exam->questions as $index => $question)
                            <div class="card border rounded-3 mb-3">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="fw-bold mb-0 text-dark">
                                            Q{{ $index + 1 }}. {{ $question->question_text }}
                                        </h6>
                                        <form action="{{ route('lecturer.questions.destroy', $question->question_id ?? $question->id) }}" method="POST" onsubmit="return confirm('Delete this question?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-link text-danger p-0 border-0 fs-5">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>

                                    <div class="mb-3">
                                        <span class="badge bg-secondary me-1">{{ strtoupper($question->question_type) }}</span>
                                        <span class="badge bg-info text-dark">{{ $question->points ?? 1 }} Point(s)</span>
                                    </div>

                                    {{-- Options Listing matching target image style --}}
                                    @if($question->options && $question->options->count() > 0)
                                        <div class="border rounded-3 overflow-hidden">
                                            @foreach($question->options as $option)
                                                <div class="d-flex justify-content-between align-items-center p-2.5 border-bottom last-border-0 {{ $option->is_correct ? 'bg-success-subtle text-success fw-bold' : 'bg-white text-dark' }}" style="{{ $loop->last ? 'border-bottom: 0 !important;' : '' }}">
                                                    <div class="d-flex align-items-center ms-2">
                                                        @if($option->is_correct)
                                                            <i class="bi bi-check-circle-fill text-success me-3 fs-5"></i>
                                                        @else
                                                            <i class="bi bi-circle text-muted me-3 fs-5"></i>
                                                        @endif
                                                        <span>{{ $option->option_text }}</span>
                                                    </div>
                                                    @if($option->is_correct)
                                                        <span class="badge bg-success me-2 px-2 py-1">Correct Answer</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="alert alert-warning py-2 px-3 mb-0 fs-7">
                                            <i class="bi bi-exclamation-triangle-fill me-1"></i> No answer options are available for this question.
                                        </div>
                                    @endif

                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-folder-x fs-1 d-block mb-2 text-secondary"></i>
                            No questions added yet. Use the form on the left to add questions.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const typeSelect = document.getElementById('questionTypeSelect');
        const mcqBlock = document.getElementById('mcqOptionsBlock');
        const shortAnswerBlock = document.getElementById('shortAnswerBlock');
        const optionsContainer = document.getElementById('optionsContainer');
        const addOptionBtn = document.getElementById('addOptionBtn');

        // Toggle question type blocks
        function toggleTypeBlocks() {
            if (typeSelect.value === 'mcq') {
                mcqBlock.style.display = 'block';
                shortAnswerBlock.style.display = 'none';
                setInputsRequired('.option-input', true);
            } else {
                mcqBlock.style.display = 'none';
                shortAnswerBlock.style.display = 'block';
                setInputsRequired('.option-input', false);
            }
        }

        function setInputsRequired(selector, isRequired) {
            document.querySelectorAll(selector).forEach(input => {
                if (isRequired) {
                    input.setAttribute('required', 'required');
                } else {
                    input.removeAttribute('required');
                }
            });
        }

        typeSelect.addEventListener('change', toggleTypeBlocks);
        toggleTypeBlocks(); // Run on initial page load

        // Add Option dynamically
        addOptionBtn.addEventListener('click', function() {
            const index = optionsContainer.children.length;
            const row = document.createElement('div');
            row.className = 'input-group mb-2 option-row';
            row.innerHTML = `
                <div class="input-group-text">
                    <input type="radio" name="correct_option" value="${index}" title="Mark as correct">
                </div>
                <input type="text" name="options[]" class="form-control option-input" placeholder="Option ${index + 1}" required>
                <button type="button" class="btn btn-outline-danger remove-option-btn"><i class="bi bi-x-lg"></i></button>
            `;
            optionsContainer.appendChild(row);
            updateRadioValues();
        });

        // Remove Option dynamically
        optionsContainer.addEventListener('click', function(e) {
            if (e.target.closest('.remove-option-btn')) {
                if (optionsContainer.children.length > 2) {
                    e.target.closest('.option-row').remove();
                    updateRadioValues();
                } else {
                    alert('MCQs require at least 2 options.');
                }
            }
        });

        // Re-index radio button values so indices match option inputs sequentially
        function updateRadioValues() {
            const rows = optionsContainer.querySelectorAll('.option-row');
            rows.forEach((row, idx) => {
                const radio = row.querySelector('input[type="radio"]');
                const textInput = row.querySelector('.option-input');
                radio.value = idx;
                textInput.placeholder = `Option ${idx + 1}`;
            });
        }
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>