<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\LecturerController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\LecturerQuestionController;
// Optional: Uncomment if you created a separate LecturerQuestionController
// use App\Http\Controllers\LecturerQuestionController;

/*
|--------------------------------------------------------------------------
| Public & Authentication Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () { 
    return view('auth.login'); 
})->name('login');

Route::get('/auth/google', [GoogleAuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback']);

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->name('logout');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // Student Routes
    Route::get('/student/dashboard', [StudentController::class, 'index'])->name('student.dashboard');

    // Student Routes
    Route::get('/student/dashboard', [StudentController::class, 'index'])
        ->name('student.dashboard');
    
    Route::get('/student/assessment-history', [App\Http\Controllers\StudentController::class, 'history'])
        ->name('student.assessment.history');

    Route::post('/student/classes/enroll', [StudentController::class, 'enrollClass'])
        ->name('student.classes.enroll');

    Route::get('/student/exams/{examId}/start', [StudentController::class, 'startExam'])
        ->name('student.exam.start');

    Route::get('/student/exam/{examId}', [StudentController::class, 'takeExam'])
        ->name('student.exam');

    Route::post('/student/exam/{examId}/submit', [StudentController::class, 'submitExam'])
        ->name('student.exam.submit');

    Route::get('/lecturer/exam/{examId}/submissions', [LecturerController::class, 'viewSubmissions'])
        ->name('lecturer.exam.submissions');
        
    Route::get('/lecturer/attempt/{attemptId}/grade', [LecturerController::class, 'gradeAttempt'])
        ->name('lecturer.attempt.grade');
    Route::post('/lecturer/students/{id}/toggle-suspend', [LecturerController::class, 'toggleSuspend'])
        ->name('lecturer.students.toggleSuspend');
    Route::delete('/lecturer/classes/{classId}/students/{studentId}/kick', [LecturerController::class, 'kickStudent'])
        ->name('lecturer.students.kick');

    Route::put('/lecturer/classes/{id}', [App\Http\Controllers\LecturerController::class, 'updateClass'])
        ->name('lecturer.classes.update');
    
    Route::delete('/lecturer/attempt/{attempt_id}', [App\Http\Controllers\LecturerController::class, 'deleteAttempt'])
        ->name('lecturer.attempt.delete');
        
    Route::post('/lecturer/attempt/{attemptId}/grade', [LecturerController::class, 'saveGrade'])
        ->name('lecturer.attempt.saveGrade');

    Route::post('/student/exam/{id}/log-violation', [StudentController::class, 'logViolation'])
        ->name('student.exam.logViolation');

    Route::get('/student/exams/{id}/take', [StudentController::class, 'takeExam'])
        ->name('student.exams.take');
    
    Route::get('/lecturer/api/violations', [LecturerController::class, 'getViolationsApi'])
        ->name('lecturer.api.violations');
    Route::delete('/lecturer/violations/bulk-delete', [LecturerController::class, 'bulkDeleteViolations'])
        ->name('lecturer.violations.bulk-delete');
    Route::delete('/lecturer/questions/{id}', [LecturerController::class, 'destroyQuestion'])
        ->name('lecturer.questions.destroy');

Route::middleware(['auth'])->prefix('lecturer')->group(function () {
    Route::delete('/violations/bulk-delete', [LecturerController::class, 'bulkDeleteViolations'])->name('lecturer.violations.bulk-delete');
    Route::delete('/violations/{id}', [LecturerController::class, 'deleteViolation'])->name('lecturer.violations.delete');
    });

    // Lecturer Routes Group
    Route::prefix('lecturer')->name('lecturer.')->group(function () {
        
        // Dashboard
        Route::get('/dashboard', [LecturerController::class, 'index'])->name('dashboard');

        // Exam CRUD Routes
        Route::post('/exams', [LecturerController::class, 'storeExam'])->name('exams.store');
        Route::put('/exams/{id}', [LecturerController::class, 'updateExam'])->name('exams.update');
        Route::delete('/exams/{id}', [LecturerController::class, 'destroyExam'])->name('exams.destroy');

        // Question Management Routes
        Route::get('/exams/{examId}/questions', [LecturerController::class, 'manageQuestions'])->name('questions.index');
        Route::post('/exams/{examId}/questions', [LecturerController::class, 'storeQuestion'])->name('questions.store');
        Route::delete('/questions/{questionId}', [LecturerController::class, 'destroyQuestion'])->name('questions.destroy');

        // Student Management Route
        Route::patch('/students/{id}/toggle-status', [LecturerController::class, 'toggleStudentStatus'])
            ->name('students.toggleStatus');

        // Class Management Routes
        Route::post('/classes', [LecturerController::class, 'storeClass'])->name('classes.store');
        Route::post('/classes/{classId}/enroll', [LecturerController::class, 'enrollStudent'])->name('classes.enroll');
    });

    Route::prefix('lecturer')->name('lecturer.')->middleware(['auth'])->group(function () {
        // Existing routes...
        Route::post('/classes/store', [LecturerController::class, 'storeClass'])->name('classes.store');
        
        // NEW: Update and Delete routes
        Route::put('/classes/{id}', [LecturerController::class, 'updateClass'])->name('classes.update');
        Route::delete('/classes/{id}', [LecturerController::class, 'destroyClass'])->name('classes.destroy');
    });

Route::middleware(['auth'])->prefix('lecturer')->name('lecturer.')->group(function () {

        Route::get(
            '/exams/{examId}/questions',
            [LecturerQuestionController::class, 'index']
        )->name('questions.index');

        Route::post(
            '/classes', 
            [App\Http\Controllers\LecturerController::class, 'storeClass']
        )->name('classes.store');


        Route::post(
            '/exams/{examId}/questions',
            [LecturerQuestionController::class, 'store']
        )->name('questions.store');

        Route::delete(
            '/questions/{questionId}',
            [LecturerQuestionController::class, 'destroy']
        )->name('questions.destroy');

    });

    Route::middleware(['auth'])->prefix('student')->name('student.')->group(function () {
        // 1. Student Dashboard Route
        Route::get('/dashboard', [StudentController::class, 'index'])
            ->name('dashboard');

        // 2. Assessment History Route
        Route::get('/assessment-history', [StudentController::class, 'history'])
            ->name('assessment.history');

        Route::post('/classes/search', [StudentController::class, 'searchClass'])
            ->name('classes.search');
            
        Route::post('/classes/enroll', [StudentController::class, 'confirmEnrollment'])
            ->name('classes.confirm-enroll');
    });
});