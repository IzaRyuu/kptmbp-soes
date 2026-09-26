<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Student;
use App\Models\Lecturer;
use App\Models\ProfileAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Exception;

class AdminController extends Controller
{
    public function dashboard()
    {
        // Fetch users with related lecturer and student profiles
        $users = User::with(['lecturer', 'student'])->orderBy('created_at', 'desc')->get();

        // Filter collections for detail modals
        $lecturers = $users->where('role', 'lecturer');
        $students  = $users->where('role', 'student');

        // Dynamically calculate counts based on actual filtered user records
        $totalLecturers = $lecturers->count();
        $totalStudents  = $students->count();
        $totalUsers     = \App\Models\User::count();

        // Fetch latest profile audit logs
        $auditLogs = ProfileAuditLog::orderBy('created_at', 'desc')->paginate(10);

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

    // Reset User Password
    public function resetPassword(Request $request, $id)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::findOrFail($id);
        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->back()->with('success', 'Password reset successfully for ' . $user->name);
    }

    public function updateUser(Request $request, $id)
    {
        if (Auth::user()->role !== 'admin' && Auth::user()->role !== 'lecturer') {
            abort(403, 'Unauthorized action.');
        }

        $user = User::where('user_id', $id)->firstOrFail();

        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->user_id . ',user_id',
            'role'  => 'nullable|string|in:admin,lecturer,student',
        ]);

        $auditEntries = [];

        // Check Name
        if ($user->name !== $request->name) {
            $auditEntries[] = [
                'changed_field' => 'name',
                'old_value'     => $user->name,
                'new_value'     => $request->name,
            ];
            $user->name = $request->name;
        }

        // Check Email
        if ($user->email !== $request->email) {
            $auditEntries[] = [
                'changed_field' => 'email',
                'old_value'     => $user->email,
                'new_value'     => $request->email,
            ];
            $user->email = $request->email;
        }

        // Check Role
        if ($request->filled('role') && $user->role !== $request->role) {
            $auditEntries[] = [
                'changed_field' => 'role',
                'old_value'     => $user->role,
                'new_value'     => $request->role,
            ];
            $user->role = $request->role;
        }

        $user->save();

        // Student Specific Field Check
        if ($user->role === 'student' && $user->student) {
            $student = $user->student;
            $oldMatric = $student->matric_number ?? $student->matrix_number ?? '';

            if ($request->has('matric_number') && $oldMatric !== $request->matric_number) {
                $auditEntries[] = [
                    'changed_field' => 'matric_number',
                    'old_value'     => $oldMatric,
                    'new_value'     => $request->matric_number,
                ];

                if (isset($student->matric_number) || array_key_exists('matric_number', $student->getAttributes())) {
                    $student->matric_number = $request->matric_number;
                } else {
                    $student->matrix_number = $request->matric_number;
                }
                $student->save();
            }
        }
        // Lecturer Specific Field Check
        elseif ($user->role === 'lecturer' && $user->lecturer) {
            $lecturer = $user->lecturer;
            $oldStaff = $lecturer->staff_number ?? '';

            if ($request->has('staff_number') && $oldStaff !== $request->staff_number) {
                $auditEntries[] = [
                    'changed_field' => 'staff_number',
                    'old_value'     => $oldStaff,
                    'new_value'     => $request->staff_number,
                ];
                $lecturer->staff_number = $request->staff_number;
                $lecturer->save();
            }
        }

        // Save individual audit records for each changed field
        if (!empty($auditEntries)) {
            $existingColumns = Schema::getColumnListing('profile_audit_logs');

            foreach ($auditEntries as $entry) {
                $candidateData = [
                    'user_id'       => $user->user_id,
                    'user_name'     => $user->name,
                    'user_role'     => $user->role,
                    'changed_field' => $entry['changed_field'],
                    'old_value'     => (string) $entry['old_value'],
                    'new_value'     => (string) $entry['new_value'],
                    'updated_by'    => Auth::user()->name ?? 'System Administrator',
                    'action'        => 'Updated ' . $entry['changed_field'],
                ];

                // Safely filter against existing database columns
                $logData = array_intersect_key($candidateData, array_flip($existingColumns));

                ProfileAuditLog::create($logData);
            }
        }

        return redirect()->back()->with('success', 'User details updated successfully for ' . $user->name);
    }

    // Delete User
    public function deleteUser($id)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $user = User::findOrFail($id);
        $userName = $user->name;

        if ($user->student) $user->student->delete();
        if ($user->lecturer) $user->lecturer->delete();

        $user->delete();

        return redirect()->back()->with('success', 'User "' . $userName . '" has been deleted.');
    }
}