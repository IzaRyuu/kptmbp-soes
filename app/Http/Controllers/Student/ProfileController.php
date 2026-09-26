<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function edit()
    {
        /** @var User $user */
        $user = Auth::user();

        // Fetch student record matching the user_id
        $student = Student::where('user_id', $user->user_id ?? $user->id)->first();

        return view('student.profile', compact('user', 'student'));
    }

    public function update(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'matric_number' => 'required|string|max:50',
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|min:8|confirmed',
        ]);

        if ($request->filled('current_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Current password does not match.']);
            }
            $user->password = Hash::make($request->new_password);
        }

        $user->name = $request->name;
        $user->save();

        Student::updateOrCreate(
            ['user_id' => $user->user_id ?? $user->id],
            ['matric_number' => $request->matric_number]
        );

        return back()->with('success', 'Profile updated successfully!');
    }
}