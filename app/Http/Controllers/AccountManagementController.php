<?php

// app/Http/Controllers/AccountManagementController.php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use App\Models\Lecturer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AccountManagementController extends Controller
{
    // Register a new user (Student or Lecturer)
    public function registerUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:student,lecturer',
            'matric_number' => 'nullable|string|required_if:role,student',
        ]);

        // 1. Create base User
        $user = User::create([
            'name' => $request->name,
            'email' => strtolower($request->email),
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        // 2. Attach profile based on role
        if ($request->role === 'student') {
            Student::create([
                'user_id' => $user->id ?? $user->user_id,
                'student_id' => $request->matric_number ?? strtoupper(strtok($request->email, '@')),
            ]);
        } elseif ($request->role === 'lecturer') {
            Lecturer::create([
                'user_id' => $user->id ?? $user->user_id,
            ]);
        }

        return redirect()->back()->with('success', ucfirst($request->role) . ' account created successfully!');
    }
}