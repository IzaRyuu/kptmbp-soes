<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Student;
use App\Models\StudentAnswer;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamViolation;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class StudentController extends Controller
{
    /**
     * Student Dashboard
     */
    public function index()
    {
        $user = Auth::user();

        $student = Student::where('user_id', $user->user_id)->first();

        $now = Carbon::now();

        $enrolledClasses = $student
            ? $student->classes()->with('lecturer')->get()
            : collect();

        $studentClassIds = $enrolledClasses->pluck('class_id');

        // Fetch classes enrolled by the student
        $studentClasses = $student ? $student->classes()->with('lecturer.user')->get() : collect();

        /*
        |--------------------------------------------------------------------------
        | Available Exams
        |--------------------------------------------------------------------------
        */
        $availableExams = Exam::with([
                'class',
                'questions.options'
            ])
            ->whereIn('class_id', $studentClassIds)
            ->whereNotNull('start_time')
            ->whereNotNull('end_time')
            ->where('start_time', '<=', now())
            ->where('end_time', '>=', now())
            ->get();
        
        // Get IDs of classes the student is enrolled in
        $classIds = $student->classes()->pluck('classes.class_id');

        // Fetch exams for enrolled classes
        $exams = Exam::whereIn('class_id', $classIds)
            ->where('end_time', '>', $now) // Filter out ended exams
            ->with('class')
            ->orderBy('start_time', 'asc')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Assessment History
        |--------------------------------------------------------------------------
        */
        $pastAttempts = $student
            ? ExamAttempt::with('exam')
                ->where('student_id', $student->student_id)
                ->where('status', 'submitted')
                ->latest('submitted_at')
                ->get()
            : collect();

        return view('student.dashboard', compact(
            'user',
            'student',
            'enrolledClasses',
            'availableExams',
            'pastAttempts',
            'exams',
            'studentClasses'
        ));
    }

    /**
     * Enroll in a Class
     */
    public function enrollClass(Request $request)
    {
        $request->validate([
            'class_code' => 'required|string',
        ]);

        $user = Auth::user();
        $student = Student::where('user_id', $user->user_id ?? $user->id)->first();

        if (!$student) {
            return redirect()->back()->with('error', 'Student profile not found.');
        }

        // Get the most recently created class matching this code
        $class = \App\Models\Classes::where('class_code', $request->class_code)
            ->orWhere('code', $request->class_code)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$class) {
            return redirect()->back()->with('error', 'Class code not found.');
        }

        $classId = $class->class_id ?? $class->id;

        if ($student->classes()->where('classes.class_id', $classId)->orWhere('classes.id', $classId)->exists()) {
            return redirect()->back()->with('error', 'You are already enrolled in this class.');
        }

        $student->classes()->attach($classId);

        return redirect()->back()->with('success', 'You have successfully enrolled in ' . ($class->class_name ?? $class->name) . '!');
    }

    // 1. Search classes by code
    public function searchClass(Request $request)
    {
        $request->validate([
            'class_code' => 'required|string',
        ]);

        $searchCode = trim($request->class_code);

        // Build query checking available column name safely
        $query = \App\Models\Classes::query();

        if (Schema::hasColumn('classes', 'class_code')) {
            $query->where('class_code', $searchCode);
        } else {
            $query->where('code', $searchCode);
        }

        $searchResults = $query->with(['lecturer.user'])->get();

        return redirect()->back()->with('searchResults', $searchResults)->withInput();
    }

    // 2. Confirm and enroll into chosen class_id
    public function confirmEnrollment(Request $request)
    {
        $request->validate([
            'class_id' => 'required|integer|exists:classes,class_id',
        ]);

        $user = Auth::user();
        $userId = $user->user_id ?? $user->id;

        // Resolve student record safely without querying non-existent 'id' column
        $student = \App\Models\Student::where('user_id', $userId)
            ->orWhere('student_id', $userId)
            ->first();

        if (!$student) {
            return redirect()->back()->with('error', 'Student profile not found.');
        }

        $classId = $request->class_id;

        // Check if student is already enrolled using class_id only
        $alreadyEnrolled = $student->classes()
            ->where('classes.class_id', $classId)
            ->exists();

        if ($alreadyEnrolled) {
            return redirect()->back()->with('error', 'You are already enrolled in this class.');
        }

        // Attach student to class
        $student->classes()->attach($classId);

        return redirect()->back()->with('success', 'You have successfully enrolled in the class!');
    }

    /**
     * Display examination page (Testing mode enabled)
     */
    public function takeExam($examId)
    {
        $user = Auth::user();

        $student = Student::where('user_id', $user->user_id)->firstOrFail();

        // 1. Single efficient query for Exam with relations
        $exam = Exam::with([
                'class',
                'questions.options'
            ])
            ->where('exam_id', $examId)
            ->firstOrFail();

        // 2. Check class enrollment
        $isEnrolled = $student->classes()
            ->where('classes.class_id', $exam->class_id)
            ->exists();

        if (!$isEnrolled) {
            return redirect()
                ->route('student.dashboard')
                ->with('error', 'You are not enrolled in the class for this examination.');
        }

        if ($student->is_suspended) {
        return redirect()->back()->with('error', 'You are currently suspended and banned from taking exams.');
        }

        // 3. Convert duration column value to integer minutes (Default to 60 if <= 0)
        $durationInMinutes = (int) $exam->duration;
        if ($durationInMinutes <= 0) {
            $durationInMinutes = 60; 
        }

        // 4. Check exam timing
        if ($exam->start_time && now()->lt($exam->start_time)) {
            return redirect()
                ->route('student.dashboard')
                ->with('error', 'This examination has not started yet.');
        }

        if ($exam->end_time && now()->gt($exam->end_time)) {
            return redirect()
                ->route('student.dashboard')
                ->with('error', 'This examination has already ended.');
        }

        // 5. Fetch existing attempt or create new one
        $attempt = ExamAttempt::firstOrCreate(
            [
                'exam_id' => $exam->exam_id,
                'student_id' => $student->student_id,
            ],
            [
                'started_at' => now(),
                'status' => 'in_progress',
                'total_score' => 0,
            ]
        );

        if ($attempt->status === 'submitted') {
            return redirect()->route('student.dashboard')->with('error', 'You have already submitted this exam.');
        }

        // --- RANDOMIZATION LOGIC START ---
        // Seed generator: combines exam_id and student_id so each student gets a unique, deterministic order
        $seed = (int) ($exam->exam_id . $student->student_id);

        // Shuffle Questions dynamically using seed
        $shuffledQuestions = $exam->questions->shuffle($seed);

        // Shuffle Multiple Choice Options dynamically per question
        $shuffledQuestions->each(function ($question) use ($student) {
            if ($question->options && $question->options->count() > 0) {
                $optionSeed = (int) ($question->question_id . $student->student_id);
                $question->setRelation('options', $question->options->shuffle($optionSeed));
            }
        });

        // Replace loaded questions relation with the shuffled collection
        $exam->setRelation('questions', $shuffledQuestions);
        // --- RANDOMIZATION LOGIC END ---

        // 2. DYNAMIC DURATION SYNC
        // Check if exam has a duration_minutes attribute OR calculate from start_time and end_time
        if (!empty($exam->duration_minutes)) {
            $totalDurationSeconds = (int) $exam->duration_minutes * 60;
        } elseif (!empty($exam->duration)) {
            $totalDurationSeconds = (int) $exam->duration * 60;
        } else {
            // Fallback: Calculate duration using Start Time and End Time window
            $startTime = \Carbon\Carbon::parse($exam->start_time);
            $endTime = \Carbon\Carbon::parse($exam->end_time);
            $totalDurationSeconds = $startTime->diffInSeconds($endTime);
        }

        // 3. Calculate remaining seconds based on attempt start time
        $startedAt = \Carbon\Carbon::parse($attempt->started_at);
        $elapsedSeconds = now()->diffInSeconds($startedAt);

        // If attempt is old/stale from testing, reset start time to NOW
        if ($elapsedSeconds >= $totalDurationSeconds) {
            $startedAt = now();
            $attempt->update(['started_at' => $startedAt]);
            $elapsedSeconds = 0;
        }

        $remainingSeconds = max(0, $totalDurationSeconds - $elapsedSeconds);

        return view('student.exam', compact('exam', 'student', 'attempt', 'remainingSeconds'));
    }

    /**
     * Submit examination (Testing mode enabled - overwrites result)
     */
    public function submitExam(Request $request, $examId)
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->user_id)->firstOrFail();

        $exam = Exam::with(['questions.options'])
            ->where('exam_id', $examId)
            ->firstOrFail();

        // 1. Verify enrollment
        $isEnrolled = $student->classes()
            ->where('classes.class_id', $exam->class_id)
            ->exists();

        if (!$isEnrolled) {
            return redirect()->route('student.dashboard')->with('error', 'You are not enrolled in this class.');
        }

        // 2. Fetch or create attempt
        $attempt = ExamAttempt::firstOrCreate(
            [
                'exam_id' => $exam->exam_id,
                'student_id' => $student->student_id,
            ],
            [
                'started_at' => now(),
                'status' => 'in_progress',
                'total_score' => 0,
            ]
        );

        // 3. Extract inputs & process answers
        $answers = $request->input('answers', []);
        $totalScore = 0;

        foreach ($exam->questions as $question) {
            $qId = $question->question_id ?? $question->id;
            
            // Grab value regardless of array key type
            $studentAnswer = $answers[$qId] ?? $answers[(string)$qId] ?? null;
            $type = strtoupper($question->question_type ?? $question->type);

            if (in_array($type, ['MCQ', 'MULTIPLE_CHOICE'])) {
                $correctOption = $question->options->where('is_correct', true)->first();
                $correctId = $correctOption->option_id ?? $correctOption->id ?? null;

                if ($correctId && (string)$studentAnswer === (string)$correctId) {
                    $totalScore += $question->points;
                }

                \Illuminate\Support\Facades\DB::table('student_answers')->updateOrInsert(
                    [
                        'attempt_id'  => $attempt->attempt_id ?? $attempt->id,
                        'question_id' => $qId,
                    ],
                    [
                        'selected_option_id' => $studentAnswer,
                        'answer_text'        => null,
                        'updated_at'         => now(),
                        'created_at'         => now(),
                    ]
                );
            } else {
                // SHORT_ANSWER / ESSAY / TEXT
                \Illuminate\Support\Facades\DB::table('student_answers')->updateOrInsert(
                    [
                        'attempt_id'  => $attempt->attempt_id ?? $attempt->id,
                        'question_id' => $qId,
                    ],
                    [
                        'selected_option_id' => null,
                        'answer_text'        => $studentAnswer,
                        'updated_at'         => now(),
                        'created_at'         => now(),
                    ]
                );
            }
        }

        // 4. Finalize submission
        $attempt->update([
            'submitted_at' => now(),
            'status' => 'submitted',
            'total_score' => $totalScore,
        ]);

        return redirect()->route('student.dashboard')
            ->with('success', "Exam submitted successfully! Score: {$totalScore}");
    }

    /**
     * Alias for takeExam to maintain route compatibility
     */
    public function startExam(Request $request, $examId)
    {
        // 1. Verify reCAPTCHA token exists
        $recaptchaToken = $request->input('g-recaptcha-response');

        if (!$recaptchaToken) {
            return redirect()->back()->with('error', 'Please complete the reCAPTCHA verification before starting the exam.');
        }

        // 2. Validate token with Google API
        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret'   => config('services.recaptcha.secret_key') ?? env('RECAPTCHA_SECRET_KEY'),
            'response' => $recaptchaToken,
            'remoteip' => $request->ip(),
        ]);

        if (!$response->json('success')) {
            return redirect()->back()->with('error', 'reCAPTCHA verification failed. Please try again.');
        }

        // 3. Proceed to load exam session
        return view('student.take-exam', compact('examId'));
    }

    public function logViolation(Request $request, $examId)
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->user_id)->firstOrFail();

        ExamViolation::create([
            'exam_id' => $examId,
            'student_id' => $student->student_id,
            'violation_type' => $request->input('violation_type', 'tab_switch'),
            'occurred_at' => now(),
        ]);

        return response()->json(['status' => 'logged']);
    }

    public function history()
    {
        // Ensure student is authenticated
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        // Get student ID safely from user relationship or user object
        $studentId = $user->student->id ?? $user->student->student_id ?? $user->id;

        // Fetch history records
        $history = ExamAttempt::where('student_id', $studentId)
            ->with('exam')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('student.assessment-history', compact('history'));
    }

}