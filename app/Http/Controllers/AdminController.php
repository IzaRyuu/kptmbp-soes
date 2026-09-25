<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Student;
use App\Models\Lecturer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Exception;

class AdminController extends Controller
{
    public function dashboard()
    {
        $totalLecturers = User::where('role', 'lecturer')->count();
        $totalStudents  = User::where('role', 'student')->count();
        $totalUsers     = User::count();
        
        // Fetch users with related lecturer and student profiles
        $users = User::with(['lecturer', 'student'])->orderBy('created_at', 'desc')->get();

        // Filter collections for detail modals
        $lecturers = $users->where('role', 'lecturer');
        $students  = $users->where('role', 'student');

        return view('admin.dashboard', compact(
            'totalLecturers', 
            'totalStudents', 
            'totalUsers', 
            'users', 
            'lecturers', 
            'students'
        ));
    }

    public function registerUser(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role'     => 'required|in:student,lecturer,admin',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $user = User::create([
                    'name'     => $request->name,
                    'email'    => $request->email,
                    'password' => Hash::make($request->password),
                    'role'     => strtolower($request->role),
                ]);

                $userId = $user->user_id ?? $user->id;
                $role   = strtolower($request->role);

                if ($role === 'student') {
                    $generatedMatric = $request->input('matric_number') 
                        ?? $request->input('matrix_number') 
                        ?? 'STU-' . time();

                    Student::create([
                        'user_id'       => $userId,
                        'matrix_number' => $generatedMatric, // Matches your DB column name
                        'matric_number' => $generatedMatric, // Keeps fallback compatibility
                    ]);
                } elseif ($role === 'lecturer') {
                    Lecturer::create([
                        'user_id'      => $userId,
                        'staff_number' => $request->input('staff_number') ?? 'STF-' . time(),
                        'department'   => $request->input('department', 'General'),
                    ]);
                }
            });

            return redirect()->back()->with('success', 'Account registered successfully!');
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to create user: ' . $e->getMessage()])->withInput();
        }
    }

    public function deleteUser($id)
    {
        try {
            DB::transaction(function () use ($id) {
                // Find target user by user_id or id
                $user = User::where('user_id', $id)->orWhere('id', $id)->firstOrFail();

                // Get current logged-in user's primary key ID safely
                $currentUserId = Auth::id(); 
                $targetUserId  = $user->getKey(); // getKey() automatically gets user_id or id

                // Prevent self-deletion
                if ((string)$currentUserId === (string)$targetUserId) {
                    throw new Exception("You cannot delete your own active administrator account.");
                }

                // Delete associated student or lecturer records
                Student::where('user_id', $targetUserId)->delete();
                Lecturer::where('user_id', $targetUserId)->delete();

                // Delete main user account
                $user->delete();
            });

            return redirect()->back()->with('success', 'User profile deleted successfully!');
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Delete Failed: ' . $e->getMessage()]);
        }
    }
}