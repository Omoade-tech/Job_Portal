<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Employer;
use App\Models\JobSeeker;
use App\Models\Token;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            // Merge password confirmation field to standardize it for validation
            $request->merge(['password_confirmation' => $request->confirmPassword]);

            // Validate the request
            $validated = $request->validate([
                'role' => 'required|in:admin,employer,job_seeker',
                'name' => 'required|string|max:255',
                'email' => [
                    'required',
                    'email',
                    function ($attribute, $value, $fail) {
                        if (
                            Admin::where('email', $value)->exists() ||
                            Employer::where('email', $value)->exists() ||
                            JobSeeker::where('email', $value)->exists()
                        ) {
                            $fail('The email is already taken.');
                        }
                    },
                ],
                'password' => 'required|string|min:8|confirmed',
                'password_confirmation' => 'required|string|min:8',
                'phoneNumber' => 'required|string|max:15',
                'age' => 'required|integer|min:18|max:100',
                'sex' => 'required|string|in:male,female',
                'status' => 'required|string|in:single,married',
                'address' => 'required|string|max:255',
                'city' => 'required|string|max:255',
                'state' => 'required|string|max:255',
                'country' => 'required|string|max:255',
            ]);

            // Create the user based on the validated role
            $user = match ($validated['role']) {
                'admin' => Admin::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'phoneNumber' => $validated['phoneNumber'],
                    'age' => $validated['age'],
                    'sex' => $validated['sex'],
                    'status' => $validated['status'],
                    'address' => $validated['address'],
                    'city' => $validated['city'],
                    'state' => $validated['state'],
                    'country' => $validated['country'],
                ]),
                'employer' => Employer::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'phoneNumber' => $validated['phoneNumber'],
                    'age' => $validated['age'],
                    'sex' => $validated['sex'],
                    'status' => $validated['status'],
                    'address' => $validated['address'],
                    'city' => $validated['city'],
                    'state' => $validated['state'],
                    'country' => $validated['country'],
                ]),
                'job_seeker' => JobSeeker::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'phoneNumber' => $validated['phoneNumber'],
                    'age' => $validated['age'],
                    'sex' => $validated['sex'],
                    'status' => $validated['status'],
                    'address' => $validated['address'],
                    'city' => $validated['city'],
                    'state' => $validated['state'],
                    'country' => $validated['country'],
                ]),
                default => null,
            };

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create user. Please try again.'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => ucfirst($validated['role']) . ' registered successfully.',
                'data' => $user,
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Registration error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function login(Request $request)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            // Find user
            $user = Admin::where('email', $validated['email'])->first()
            ?? Employer::where('email', $validated['email'])->first()
            ?? JobSeeker::where('email', $validated['email'])->first();
            if (!$user) {
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }

            // Verify password
            if (!Hash::check($validated['password'], $user->password)) {
                throw ValidationException::withMessages([
                    'password' => ['The provided credentials are incorrect.'],
                ]);
            }

            // Create token
            $token = $user->tokens()->create([
                'name' => 'Default Token',
                'token' => Str::random(60),
                'abilities' => json_encode(['*']),
                'tokenable_id' => $user->id,
                'tokenable_type' => get_class($user),
            ]);
    

            return response()->json([
                'success' => true,
                'message' => 'Login successful.',
                'data' => $user,
                'token' => $token->token,
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Login failed. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function logout(Request $request)
    {
        try {
            // Revoke all tokens...
            $request->user()->tokens()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Successfully logged out.'
            ]);

        } catch (\Exception $e) {
            Log::error('Logout error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Logout failed. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function getUsersByRole(Request $request)
    {
        try {
            $validated = $request->validate([
                'role' => 'required|in:admin,employer,job_seeker',
            ]);

            $roleModels = [
                'admin' => Admin::class,
                'employer' => Employer::class,
                'job_seeker' => JobSeeker::class,
            ];

            $model = $roleModels[$validated['role']];
            $users = $model::all();

            if ($users->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => "No {$validated['role']}s found",
                    'data' => []
                ], 200);
            }

            return response()->json([
                'success' => true,
                'message' => ucfirst($validated['role']) . ' list retrieved successfully.',
                'data' => $users,
            ], 200);

        } catch (\Exception $e) {
            Log::error('GetUsersByRole error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve users. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}