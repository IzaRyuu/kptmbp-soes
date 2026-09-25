<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Student;
use App\Models\Lecturer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

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

        DB::transaction(function () use ($request) {
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'role'     => strtolower($request->role),
            ]);

            $role = strtolower($request->role);

            if ($role === 'student') {
                Student::create([
                    'user_id'       => $user->user_id ?? $user->id,
                    'matrix_number' => $request->input('matrix_number'),
                ]);
            } elseif ($role === 'lecturer') {
                Lecturer::create([
                    'user_id'      => $user->user_id ?? $user->id,
                    'staff_number' => $request->input('staff_number'),
                    'department'   => $request->input('department', 'General'),
                ]);
            }
        });

        return redirect()->back()->with('success', 'Account registered successfully!');
    }

    public function dashboard()
    {
        $totalLecturers = User::where('role', 'lecturer')->count();
        $totalStudents  = User::where('role', 'student')->count();
        
        $totalUsers     = User::count();
        $users          = User::orderBy('created_at', 'desc')->get();

        return view('admin.dashboard', compact('totalLecturers', 'totalStudents', 'totalUsers', 'users'));
    }
}