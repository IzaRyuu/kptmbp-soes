<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\Student;
use App\Models\Lecturer;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function handleGoogleCallback()
    {
        $googleUser = Socialite::driver('google')->user();

        // 1. Find or create the base User
        $user = User::where('email', strtolower($googleUser->getEmail()))->first();

        if (!$user) {
            // Unregistered user attempt
            return redirect()->route('login')->with('error', 'Your email is not registered in the system. Please contact your lecturer or admin to register your account.');
        }

        // 2. Log the user in
        Auth::login($user);

        // 3. ROUTING LOGIC: Determine role via database models/role column
        
        // Admin Routing
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        // Lecturer Routing (Checks role column or lecturer relationship)
        $isLecturer = $user->role === 'lecturer' 
            || Lecturer::where('user_id', $user->id ?? $user->user_id)->exists();

        if ($isLecturer) {
            return redirect()->route('lecturer.dashboard');
        }

        // Student Routing (Checks role column or student relationship)
        $isStudent = $user->role === 'student' 
            || Student::where('user_id', $user->id ?? $user->user_id)->exists();

        if ($isStudent) {
            return redirect()->route('student.dashboard');
        }

        // Default Fallback
        return redirect()->route('student.dashboard');
    }
}