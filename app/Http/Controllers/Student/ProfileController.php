<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
class ProfileController extends Controller
{
    public function edit()
    {
        $user = auth()->user();
        $student = $user->student; // assumes User HasOne Student relation

        return view('student.profile', compact('user', 'student'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'matric_number' => 'required|string|max:50',
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|min:8|confirmed',
        ]);

        // Check current password if attempting to change password
        if ($request->filled('current_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Current password does not match.']);
            }
            $user->password = Hash::make($request->new_password);
        }

        $user->name = $request->name;
        $user->save();

        if ($user->student) {
            $user->student->update([
                'matric_number' => $request->matric_number,
            ]);
        }

        return back()->with('success', 'Profile updated successfully!');
    }
}