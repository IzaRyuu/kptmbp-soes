<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Student;
use App\Models\Lecturer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    // Existing methods like dashboard() go here...

    public function registerUser(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role'     => 'required|in:student,lecturer,admin',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
        ]);

        if ($request->role === 'student') {
            Student::create([
                'user_id' => $user->id,
                'matric_number' => $request->matric_number ?? 'UNKNOWN',
            ]);
        } elseif ($request->role === 'lecturer') {
            Lecturer::create([
                'user_id' => $user->id,
                'staff_id' => $request->staff_id ?? 'UNKNOWN',
            ]);
        }

        return redirect()->back()->with('success', 'Account created successfully!');
    }
}