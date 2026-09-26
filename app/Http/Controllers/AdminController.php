<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Student;
use App\Models\Lecturer;
use App\Models\ProfileAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Exception;

class AdminController extends Controller
{
    public function dashboard()
    {
        $totalLecturers = \App\Models\Lecturer::count();
        $totalStudents  = \App\Models\Student::count();
        $totalUsers     = \App\Models\User::count();

        // Fetch latest profile audit logs
        $auditLogs = ProfileAuditLog::orderBy('created_at', 'desc')->paginate(10);
        
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
            'students',
            'auditLogs'
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
                    $generatedMatric = $request->input('matrix_number') 
                        ?? $request->input('matric_number') 
                        ?? 'STU-' . time();

                    Student::create([
                        'user_id'       => $userId,
                        'matrix_number' => $generatedMatric, // Matches your DB column name
                        'program_code'   => $request->input('program_code', 'GENERAL'), // Provides default if empty
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

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $admin = Auth::user();

        // 1. Audit Logging BEFORE updating target user
        $actorName = $admin ? $admin->name : 'System Admin';
        $targetUserId = is_object($user) ? ($user->user_id ?? $user->id ?? 0) : 0;
        $targetUserName = is_object($user) ? ($user->name ?? 'Unknown') : 'Unknown';
        $userRole = ucfirst(is_object($user) ? ($user->role ?? 'User') : 'User');

        $fieldsToTrack = ['name', 'email', 'role'];

        foreach ($fieldsToTrack as $field) {
            if ($request->filled($field)) {
                $oldVal = is_object($user) ? ($user->$field ?? 'N/A') : 'N/A';
                $newVal = $request->input($field);

                if ((string)$oldVal !== (string)$newVal) {
                    ProfileAuditLog::create([
                        'user_id'         => $targetUserId,
                        'user_name'       => $targetUserName,
                        'user_role'       => $userRole,
                        'changed_field'   => strtoupper(str_replace('_', ' ', $field)),
                        'old_value'       => (string)$oldVal,
                        'new_value'       => (string)$newVal,
                        'changed_by_name' => $actorName,
                    ]);
                }
            }
        }

        // 2. Perform actual update
        $user->update($request->all());

        return redirect()->back()->with('success', 'User details updated and change logged.');
    }

    public function deleteUser($id)
    {
        try {
            // Query explicitly by 'user_id' instead of find() or findOrFail()
            $user = User::where('user_id', $id)->firstOrFail();

            // Delete associated records first if necessary (e.g. Student or Lecturer)
            if ($user->role === 'student') {
                Student::where('user_id', $user->user_id)->delete();
            } elseif ($user->role === 'lecturer') {
                Lecturer::where('user_id', $user->user_id)->delete();
            }

            $user->delete();

            return redirect()->back()->with('success', 'User deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Delete Failed: ' . $e->getMessage());
        }
    }
}