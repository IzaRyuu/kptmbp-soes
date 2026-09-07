<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Question;
use App\Models\Lecturer;
use App\Models\Classes;
use App\Models\Course;
use App\Models\Student;
use App\Models\ActivityLog;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use App\Models\QuestionOption;
use App\Models\ExamViolation;
use App\Models\ExamAttempt;
use Illuminate\Support\Facades\DB;

class LecturerController extends Controller
{
    // 1. Dashboard View with Exam List
    public function index()
    {
        $user = Auth::user();
        $userId = $user->user_id ?? $user->id;

        // Get or Create Lecturer profile safely
        $lecturer = Lecturer::firstOrCreate(
            ['user_id' => $userId],
            [
                'staff_number' => 'LEC-' . sprintf('%04d', $userId),
                'department'   => 'Computer Science'
            ]
        );

        // Fetch classes using lecturer_id to match database constraints
        $assignedClasses = Classes::where('lecturer_id', $lecturer->lecturer_id)
            ->with('students.user')
            ->get();

        // 1. Fetch tab-switch violations for this lecturer's exams
        $violations = ExamViolation::with(['student.user', 'exam'])
            ->whereHas('exam.class', function ($query) use ($lecturer) {
                $query->where('lecturer_id', $lecturer->lecturer_id);
            })
            ->latest('occurred_at')
            ->get();

        // Fetch classes belonging to this lecturer
        $myClasses = Classes::where('lecturer_id', $lecturer->lecturer_id)->get();
        
        $courses = Course::all();

        // Retrieve Exams created by this lecturer
        $exams = Exam::with(['class', 'course', 'questions'])
            ->where('lecturer_id', $lecturer->lecturer_id)
            ->latest()
            ->get();

        // Counts setup
        $totalClasses  = $myClasses->count();
        $totalExams    = $exams->count();
        
        // Count unique registered students enrolled in this lecturer's classes
        $totalStudents = Student::whereHas('classes', function ($q) use ($lecturer) {
            $q->where('classes.lecturer_id', $lecturer->lecturer_id);
        })->count();

        $students = Student::with('user')->get();
        $activityLogs = ActivityLog::with('user')->latest()->take(10)->get();

        return view('lecturer.dashboard', compact(
            'user', 'lecturer', 'violations', 'myClasses', 'courses', 'exams', 
            'students', 'activityLogs', 'totalClasses', 'totalExams', 'totalStudents', 'assignedClasses'
        ))->with('classes', $myClasses);
    }

    public function manageStudents()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $userPrimaryId = $user->user_id ?? $user->id;
        $lecturerModel = \App\Models\Lecturer::where('user_id', $userPrimaryId)->first();

        $lecturerIds = array_filter([
            $userPrimaryId,
            $user->lecturer_id ?? null,
            $lecturerModel->lecturer_id ?? null,
            $lecturerModel->id ?? null,
        ]);

        // 1. Fetch all classes owned by this lecturer
        $assignedClasses = Classes::whereIn('lecturer_id', $lecturerIds)
            ->with(['students.user'])
            ->get();

        // 2. Count total classes (This counts all 2 classes regardless of student enrollment)
        $assignedClassesCount = $assignedClasses->count();

        // 3. Count unique students across all classes
        $registeredStudentsCount = $assignedClasses
            ->pluck('students')
            ->flatten()
            ->unique('student_id')
            ->count();

        return view('lecturer.manage-students', [
            'assignedClasses'         => $assignedClasses,
            'classes'                 => $assignedClasses,
            'assignedClassesCount'    => $assignedClassesCount,
            'registeredStudentsCount' => $registeredStudentsCount,
            'totalExams'              => \App\Models\Exam::whereIn('lecturer_id', $lecturerIds)->count(),
        ]);
    }

    // 2. Store New Exam
    public function storeExam(Request $request)
    {
        // 1. Validate incoming form inputs
        $request->validate([
            'title'            => 'required|string|max:255',
            'duration_minutes' => 'required|integer|min:1',
            'class_id'         => 'nullable|exists:classes,class_id',
            'course_id'        => 'nullable|exists:courses,course_id',
        ]);

        // 2. Fetch the logged-in lecturer's record
        // (Assumes your 'lecturers' table has a 'user_id' linking it to the logged-in user)
        $lecturer = Lecturer::where('user_id', Auth::id())->first();

        // Fallback: If you don't have a separate 'lecturers' table and 'lecturer_id' is just 'Auth::id()'
        $lecturerId = $lecturer ? $lecturer->lecturer_id : Auth::id();

        // 3. Create the exam safely inside a try-catch block to reveal errors
        try {
            Exam::create([
                'title'            => $request->title,
                'lecturer_id'      => $lecturerId,
                'course_id'        => $request->course_id ?? null,
                'class_id'         => $request->class_id ?? null,
                'duration_minutes' => $request->duration_minutes ?? 60,
                'start_time'       => $request->start_time ?? null,
                'end_time'         => $request->end_time ?? null,
                'is_randomized'    => $request->has('is_randomized'),
            ]);

            return redirect()->route('lecturer.dashboard')->with('success', 'Exam created successfully!');

        } catch (\Exception $e) {
            // This will dump the exact error on your screen if saving fails
            dd('Database Error: ' . $e->getMessage());
        }
    }

    // Update an existing exam
    public function updateExam(Request $request, $id)
    {
        // Validate the submitted data
        $request->validate([
            'title' => 'required|string|max:255',
            'duration_minutes' => 'required|integer|min:1',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
        ]);

        // Get the currently logged-in lecturer
        $lecturer = Lecturer::where('user_id', Auth::id())->first();

        if (!$lecturer) {
            return redirect()
                ->back()
                ->with('error', 'Lecturer profile not found.');
        }

        // Find the exam
        $exam = Exam::where('exam_id', $id)
            ->where('lecturer_id', $lecturer->lecturer_id)
            ->firstOrFail();

        // Update the exam
        $exam->update([
            'title' => $request->title,
            'duration_minutes' => $request->duration_minutes,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'is_randomized' => $request->has('is_randomized'),
        ]);

        return redirect()
            ->route('lecturer.dashboard')
            ->with('success', 'Exam updated successfully!');
    }

    /**
     * Store a new class in the database.
     */
    public function storeClass(Request $request)
    {
        $user = Auth::user();
        $userPrimaryId = $user->user_id ?? $user->id;
        
        $lecturer = \App\Models\Lecturer::where('user_id', $userPrimaryId)->first();
        $lecturerId = $lecturer->lecturer_id ?? $lecturer->id ?? $userPrimaryId;

        $codeColumn = Schema::hasColumn('classes', 'class_code') ? 'class_code' : 'code';

        // Validate and block duplicates BEFORE attempting database insertion
        $request->validate([
            'name' => 'required|string|max:255',
            'class_code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('classes', $codeColumn)->where(function ($query) use ($lecturerId) {
                    return $query->where('lecturer_id', $lecturerId);
                }),
            ],
        ], [
            'class_code.unique' => 'You have already created a class with this class code.',
        ]);

        $class = new Classes();
        $nameColumn = Schema::hasColumn('classes', 'class_name') ? 'class_name' : 'name';

        $class->{$nameColumn} = $request->input('name');
        $class->{$codeColumn} = $request->input('class_code');
        $class->lecturer_id   = $lecturerId;
        $class->save();

        return redirect()->back()->with('success', 'New target class created successfully!');
    }
    // 3. Question Management View
    // public function manageQuestions($examId)
    //{
        //$exam = Exam::with([
            //'class',
            //'questions.options'
        //])->findOrFail($examId);

        //return view('lecturer.questions', compact('exam'));
    //}

    // 4. Store Question
    /* public function storeQuestion(Request $request, $examId)
    {
        $request->validate([
            'question_text' => 'required|string',
            'question_type' => 'required|in:mcq,short_answer',
            'points'        => 'required|integer|min:1',
            'options'       => 'nullable|array',
            'correct_option' => 'nullable|integer',
        ]);

        try {
            // Build the data array dynamically based on available columns
            $questionData = [
                'exam_id'       => $examId,
                'question_text' => $request->question_text,
                'question_type' => $request->question_type,
                'points'        => $request->points,
            ];

            // Safely pass correct_answer_text only if provided in request
            if ($request->filled('correct_answer_text')) {
                $questionData['correct_answer_text'] = $request->correct_answer_text;
            }

            // 1. Create Question without 'correct_answer_text'
            $question = Question::create([
                'exam_id'       => $examId,
                'question_text' => $request->question_text,
                'question_type' => $request->question_type, // 'short_answer' or 'mcq'
                'points'        => $request->points ?? 1,
            ]);

            // 2. Save the expected answer/keyword into options table if short answer
            if ($request->question_type === 'short_answer' && $request->filled('expected_answer')) {
                QuestionOption::create([
                    'question_id' => $question->question_id ?? $question->id,
                    'option_text' => $request->expected_answer,
                    'is_correct'  => true,
                ]);
            }

            return redirect()->back()->with('success', 'Question added successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to add question: ' . $e->getMessage());
        }
    } */

    public function updateClass(Request $request, $id)
    {
        // 1. Validate inputs
        $request->validate([
            'name'       => 'required|string|max:255',
            'class_code' => 'required|string|max:50',
        ]);

        // 2. Find the class by primary key (class_id)
        $class = \App\Models\Classes::where('class_id', $id)->first();

        if (!$class) {
            $class = \App\Models\Classes::findOrFail($id);
        }

        // 3. Explicitly assign database columns and save
        $class->class_name = $request->input('name');
        $class->class_code = $request->input('class_code');
        $class->save();

        return redirect()->back()->with('success', 'Class updated successfully!');
    }

    public function destroyClass($id)
    {
        try {
            $class = Classes::findOrFail($id);
            $class->delete();

            return redirect()->back()->with('success', 'Class deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete class: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified examination from storage.
     */
    public function destroyExam($id)
    {
        // Find the exam or fail with 404
        $exam = Exam::findOrFail($id);

        // Delete the examination (and cascade delete related questions/attempts if configured)
        $exam->delete();

        return redirect()
            ->back()
            ->with('success', 'Examination deleted successfully.');
    }

    public function systemMonitoring()
    {
        $lecturer = Auth::user()->lecturer;

        // Fetch tab-switch violations for exams belonging to this lecturer
        $violations = ExamViolation::with(['student.user', 'exam'])
            ->whereHas('exam.class', function ($query) use ($lecturer) {
                $query->where('lecturer_id', $lecturer->lecturer_id);
            })
            ->latest('occurred_at')
            ->get();

        // Your existing log fetching logic here...
        // $logs = ActivityLog::latest()->get();

        return view('lecturer.monitoring', compact('violations'/*, 'logs' */));
    }

    public function getViolationsApi()
    {
        $violations = DB::table('exam_violations')
            ->join('students', 'exam_violations.student_id', '=', 'students.student_id')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->join('exams', 'exam_violations.exam_id', '=', 'exams.exam_id')
            ->select(
                'exam_violations.violation_id as violation_id', // Make sure primary key is mapped!
                'exam_violations.created_at',
                'users.name as student_name',
                'exams.title as exam_title',
                'exam_violations.violation_type'
            )
            ->latest('exam_violations.created_at')
            ->get()
            ->map(function ($v) {
                return [
                    'violation_id' => $v->violation_id,
                    'timestamp'    => \Carbon\Carbon::parse($v->created_at)->format('d M Y, h:i A'),
                    'student_name' => $v->student_name,
                    'exam_title'   => $v->exam_title,
                    'violation_type' => str_replace('_', ' ', strtoupper($v->violation_type)),
                ];
            });

        return response()->json([
            'count' => $violations->count(),
            'violations' => $violations
        ]);
    }

    public function destroyQuestion($id)
    {
        $question = Question::findOrFail($id);

        // Delete associated options first (if cascading delete is not set in database)
        if (method_exists($question, 'options')) {
            $question->options()->delete();
        }

        $question->delete();

        return redirect()->back()->with('success', 'Question deleted successfully!');
    }

    public function submissions($exam_id)
    {
        $exam = Exam::findOrFail($exam_id);

        // Fetch attempts and eager-load the student/user relationship
        // Note: change 'attempts' to whatever your relationship method is named in Exam model
        $attempts = $exam->attempts()->with('user')->get(); 
        $attempts = ExamAttempt::where('exam_id', $exam_id)->get();

        // Alternatively, if you don't have relationships set up yet:
        // $attempts = DB::table('exam_attempts')->where('exam_id', $exam_id)->get();

        return view('lecturer.submissions', compact('exam', 'attempts'));
    }

    /**
     * List all submitted attempts for a specific exam
     */
    public function viewSubmissions($examId)
    {
        $exam = Exam::with(['class', 'attempts.student.user'])->findOrFail($examId);

        return view('lecturer.submissions', compact('exam'));
    }

    /**
     * View single student attempt and grade short answers / override total score
     */
    public function gradeAttempt($attempt_id)
    {
        // Search directly using attempt_id without checking for 'id'
        $attempt = ExamAttempt::with([
            'student.user',
            'exam.questions.options',
            'answers' // Ensure student answers relationship is loaded
        ])->where('attempt_id', $attempt_id)
        ->firstOrFail();

        return view('lecturer.grade', compact('attempt'));
    }

    /**
     * Save manual marks and update student result
     */
    public function saveGrade(Request $request, $attemptId)
    {
        $request->validate([
            'manual_score' => 'required|numeric|min:0',
        ]);

        $attempt = ExamAttempt::findOrFail($attemptId);

        // Overwrite total score directly or add manual short answer scores
        $attempt->update([
            'total_score' => $request->input('manual_score'),
            'status' => 'submitted', // Ensures status remains final
        ]);

        return redirect()
            ->route('lecturer.exam.submissions', $attempt->exam_id)
            ->with('success', 'Student marks updated successfully!');
    }

    public function deleteAttempt($attempt_id)
    {
        // Search only by attempt_id
        $attempt = \App\Models\ExamAttempt::where('attempt_id', $attempt_id)->firstOrFail();

        // Delete associated student answers first
        \Illuminate\Support\Facades\DB::table('student_answers')->where('attempt_id', $attempt->attempt_id)->delete();

        // Delete the attempt record
        $attempt->delete();

        return redirect()->back()->with('success', 'Student attempt deleted successfully.');
    }

    // Delete single violation
    public function deleteViolation($id)
    {
        \Illuminate\Support\Facades\DB::table('violations')->where('violation_id', $id)->orWhere('id', $id)->delete();

        return redirect()->back()->with('success', 'Violation record deleted.');
    }

    public function showGradePage($attemptId)
    {
        // 1. Fetch attempt/submission with student details
        $attempt = ExamAttempt::with(['student.user', 'exam'])->findOrFail($attemptId);

        // 2. Fetch questions in ORIGINAL fixed order for the lecturer
        $questions = Question::where('exam_id', $attempt->exam_id)
            ->with(['options'])
            ->orderBy('question_id', 'asc') // Keeps consistent Q1, Q2, Q3 order
            ->get();

        return view('lecturer.grading.show', compact('attempt', 'questions'));
    }

    // Bulk Delete Method
    public function bulkDeleteViolations(Request $request)
    {
        // Retrieve IDs whether sent via JSON body or standard form request
        $rawIds = $request->json('violation_ids') ?? $request->input('violation_ids', []);

        // Ensure array and keep only numeric values
        $ids = array_filter((array) $rawIds, function($id) {
            return is_numeric($id);
        });

        if (!empty($ids)) {
            // Delete directly from database
            DB::table('exam_violations')
                ->whereIn('violation_id', $ids)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => count($ids) . ' violation(s) deleted successfully.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No items selected'
        ], 400);
    }

    // Single Delete Method
    public function StudentViolation(Request $request, $id)
    {
        if (is_numeric($id)) {
            DB::table('exam_violations')
                ->where('violation_id', $id)
                ->delete();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Item deleted successfully']);
            }

            return redirect()->back()->with('success', 'Violation record deleted.');
        }

        return response()->json(['success' => false, 'message' => 'Invalid ID'], 400);
    }

    public function getApiViolations()
    {
        // Update model name to your actual Eloquent Model (e.g. ExamViolation or Violation)
        $violations = \App\Models\ExamViolation::with(['student.user', 'exam'])
            ->latest()
            ->get();

        $formatted = $violations->map(function ($v) {
            return [
                'id'             => $v->id ?? $v->violation_id, // IMPORTANT: Output primary key ID here
                'timestamp'      => \Carbon\Carbon::parse($v->occurred_at ?? $v->created_at)->format('d M Y, h:i A'),
                'student_name'   => $v->student->user->name ?? 'Unknown Student',
                'exam_title'     => $v->exam->title ?? 'N/A',
                'violation_type' => str_replace('_', ' ', strtoupper($v->violation_type)),
            ];
        });

        return response()->json([
            'count'      => $formatted->count(),
            'violations' => $formatted,
        ]);
    }

    public function toggleSuspend($id)
    {
        $student = Student::findOrFail($id);
        $student->is_suspended = !$student->is_suspended;
        $student->save();

        $statusMsg = $student->is_suspended ? 'suspended and banned from exams.' : 'unsuspended.';

        return back()->with('success', 'Student has been successfully ' . $statusMsg);
    }

    public function kickStudent($classId, $studentId)
    {
        $class = Classes::findOrFail($classId);

        // Detach the student from the class relationship
        $class->students()->detach($studentId);

        return back()->with('success', 'Student has been removed from this class.');
    }
}