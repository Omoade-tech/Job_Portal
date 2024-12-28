<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Employer;
use App\Models\JobSeeker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Register a new user (Admin, Employer, or JobSeeker)
    public function register(Request $request)
    {
        // Validate input data using Laravel's built-in validation
        $validated = $request->validate([
            'role' => 'required|in:admin,employer,job_seeker',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email|unique:employers,email|unique:job_seekers,email',
            // The password field should match the 'confirmPassword' field
            'password' => 'required|string|min:8|confirmed', 
            'phoneNumber' => 'required|string|max:15',
            'age' => 'required|integer|min:25',
            'sex' => 'required|string|in:male,female',
            'status' => 'required|stringin:single,married',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'country' => 'required|string|max:255',
        ]);

        // Create user based on role
        $user = null;
        switch ($validated['role']) {
            case 'admin':
                $user = Admin::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                ]);
                break;

            case 'employer':
                $user = Employer::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                ]);
                break;

            case 'job_seeker':
                $user = JobSeeker::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                ]);
                break;

            default:
                return response()->json(['success' => false, 'message' => 'Invalid role'], 400);
        }

        return response()->json([
            'success' => true,
            'message' => ucfirst($validated['role']) . ' registered successfully.',
            'data' => $user,
        ], 201);
    }

    // Login a user (Admin, Employer, or JobSeeker)
    public function login(Request $request)
    {
        // Validate input data
        $validated = $request->validate([
            'role' => 'required|in:admin,employer,job_seeker',
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ]);

        $role = $validated['role'];
        $user = null;

        // Find user by role
        switch ($role) {
            case 'admin':
                $user = Admin::where('email', $validated['email'])->first();
                break;

            case 'employer':
                $user = Employer::where('email', $validated['email'])->first();
                break;

            case 'job_seeker':
                $user = JobSeeker::where('email', $validated['email'])->first();
                break;

            default:
                return response()->json(['success' => false, 'message' => 'Invalid role'], 400);
        }

        // Check if user exists and password matches
        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json(['success' => false, 'message' => 'Invalid credentials'], 401);
        }

        return response()->json([
            'success' => true,
            'message' => ucfirst($role) . ' logged in successfully.',
            'data' => $user,
        ], 200);
    }

    // Logout the authenticated user
    public function logout()
    {
        return response()->json([
            'success' => true,
            'message' => 'User logged out successfully.',
        ], 200);
    }

    // Get all registered users by role
    public function getUsersByRole(Request $request)
    {
        // Validate role input
        $validated = $request->validate([
            'role' => 'required|in:admin,employer,job_seeker',
        ]);

        $role = $validated['role'];
        $users = null;

        // Fetch users based on role
        switch ($role) {
            case 'admin':
                $users = Admin::all();
                break;

            case 'employer':
                $users = Employer::all();
                break;

            case 'job_seeker':
                $users = JobSeeker::all();
                break;

            default:
                return response()->json(['success' => false, 'message' => 'Invalid role'], 400);
        }

        if ($users->isEmpty()) {
            return response()->json(['success' => true, 'message' => "No $role found"], 200);
        }

        return response()->json([
            'success' => true,
            'message' => ucfirst($role) . ' list retrieved successfully.',
            'data' => $users,
        ], 200);
    }
}
