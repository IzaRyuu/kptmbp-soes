<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Student;
use App\Models\Lecturer;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\Request;

class GoogleAuthController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            $email = strtolower($googleUser->getEmail());

            $studentDomain = env('ALLOWED_STUDENT_DOMAIN', 'student.kptm.edu.my');
            $lecturerDomain = env('ALLOWED_LECTURER_DOMAIN', 'student.uptm.edu.my');

            // 1. Identify Domain & Assign System Role
            $role = null;
            if (str_ends_with($email, '@' . $studentDomain)) {
                $role = 'student';
            } elseif (str_ends_with($email, '@' . $lecturerDomain)) {
                $role = 'lecturer';
            }

            // 2. Reject Unauthorized Domains
            if (!$role) {
                return redirect()->route('login')->withErrors([
                    'email' => "Access denied. Only @{$studentDomain} (Students) and @{$lecturerDomain} (Lecturers) emails are permitted."
                ]);
            }

            // 3. Find or Create Base User
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $googleUser->getName(),
                    'role' => $role,
                    'status' => 'active'
                ]
            );

            // 4. Check for Account Suspension
            if ($user->status === 'suspended') {
                return redirect()->route('login')->withErrors([
                    'email' => 'Your account is currently suspended. Please contact admin.'
                ]);
            }

            // 5. Automatically Sync Sub-Profile Records (Student/Lecturer)
            if ($role === 'student') {
                Student::firstOrCreate(
                    ['user_id' => $user->user_id],
                    [
                        'matrix_number' => strtoupper(explode('@', $email)[0]), // e.g. BPJ221010022
                        'program_code' => 'UNKNOWN' // Updated during profile setup
                    ]
                );
            } elseif ($role === 'lecturer') {
                Lecturer::firstOrCreate(
                    ['user_id' => $user->user_id],
                    [
                        'staff_number' => strtoupper(explode('@', $email)[0]),
                        'department' => 'FACULTY'
                    ]
                );
            }

            // 6. Log the User In
            Auth::login($user);

            // 7. Write to Activity Logs
            ActivityLog::create([
                'user_id' => $user->user_id,
                'event_type' => 'login',
                'description' => "Logged in successfully as [{$role}] via domain verification.",
                'ip_address' => $request->ip(),
            ]);

            // 8. Role-Based Dashboard Redirection
            return match ($user->role) {
                'admin' => redirect()->route('admin.dashboard'),
                'lecturer' => redirect()->route('lecturer.dashboard'),
                default => redirect()->route('student.dashboard'),
            };

        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors([
                'email' => 'Google Authentication encountered an issue: ' . $e->getMessage()
            ]);
        }
    }
}