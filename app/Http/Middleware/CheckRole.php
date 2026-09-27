<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // If user's role isn't allowed for this route, redirect to their proper home dashboard
        if (!in_array(strtolower($user->role), array_map('strtolower', $roles))) {
            return match (strtolower($user->role)) {
                'admin'    => redirect()->route('admin.dashboard')->with('error', 'Unauthorized access.'),
                'lecturer' => redirect()->route('lecturer.dashboard')->with('error', 'Unauthorized access.'),
                'student'  => redirect()->route('student.dashboard')->with('error', 'Unauthorized access.'),
                default    => redirect()->route('login')->with('error', 'Unauthorized access.'),
            };
        }

        return $next($request);
    }
}