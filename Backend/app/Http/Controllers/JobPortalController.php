<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\JobPortal;
use App\Models\Employer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class JobPortalController extends Controller
{
    /**
     * Display a listing of all job portals.
     */
    public function index()
    {
        try {
            $jobportals = JobPortal::latest()->get();
            return response()->json(['success' => true, 'data' => $jobportals], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch job portals.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display a specific job portal by ID.
     */
    public function show($id)
    {
        try {
            $jobportal = JobPortal::findOrFail($id);
            return response()->json(['success' => true, 'data' => $jobportal], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'JobPortal not found.'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occurred while fetching the job portal.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a new job portal.
     */
    public function store(Request $request)
    {
        try {
            // Validate the request data
            $validatedData = $request->validate([
                'companyLogo' => 'file|mimes:jpeg,png,jpg,gif|max:2048',
                'companyName' => 'required|string|max:255',
                'contract' => 'required|string|in:fulltime,remote,parttime',
                'post' => 'required|string|max:100',
                'salary' => ['required', 'regex:/^\$?\d{1,3}(,\d{3})*(\.\d{2})?$/'],
                'description' => 'required|string|min:10|max:1000',
                'location' => 'required|string|max:255',
                'responsibility' => 'required|string|min:10|max:1000',
                'employer_id' => 'sometimes|exists:employers,id'
            ]);

            // Get the authenticated user using the request
            $user = $request->user();

            // Log authentication details for debugging
            \Log::info('Authentication Debug', [
                'user_present' => (bool)$user,
                'user_details' => $user ? [
                    'id' => $user->id,
                    'email' => $user->email,
                    'role' => $user->role ?? 'No Role'
                ] : 'No User'
            ]);

            // Determine the employer_id
            if ($user) {
                // If user is an employer, find or create employer record
                if ($user->role === 'employer') {
                    $employer = Employer::firstOrCreate(
                        ['email' => $user->email],
                        [
                            'name' => $user->name ?? 'Employer',
                            'user_id' => $user->id
                        ]
                    );
                    $validatedData['employer_id'] = $employer->id;
                }
            }

            // If no employer_id is set, try to use the provided one or throw an error
            if (!isset($validatedData['employer_id'])) {
                if ($request->has('employer_id')) {
                    $validatedData['employer_id'] = $request->input('employer_id');
                } else {
                    throw new \Exception('No employer identified. Please provide an employer_id or log in as an employer.');
                }
            }

            // Handle file upload for company logo
            if ($request->hasFile('companyLogo')) {
                $validatedData['companyLogo'] = $request->file('companyLogo')->store('logos', 'public');
            }

            // Create the job portal entry
            $jobPortal = JobPortal::create($validatedData);

            return response()->json([
                'message' => 'Job portal created successfully',
                'data' => $jobPortal
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Log validation errors
            \Log::error('Validation Error', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'message' => 'Validation Error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // Log any other exceptions
            \Log::error('Job Portal Creation Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'message' => 'An error occurred while creating the job portal',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing job portal.
     */
    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'companyLogo' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048',
                'companyName' => 'required|string|max:255',
                'contract' => 'required|string|max:255',
                'post' => 'required|string|max:100',
                'salary' => ['required', 'regex:/^\$?\d{1,3}(,\d{3})*(\.\d{2})?$/'],
                'description' => 'required|string|min:10|max:1000',
                'location' => 'required|string|max:255',
                'responsibility' => 'required|string|min:10|max:1000',
            ]);

            $jobportal = JobPortal::findOrFail($id);

            if ($request->hasFile('companyLogo')) {
                if ($jobportal->companyLogo) {
                    Storage::disk('public')->delete($jobportal->companyLogo);
                }
                $validatedData['companyLogo'] = $request->file('companyLogo')->store('logos', 'public');
            }

            $jobportal->update($validatedData);

            return response()->json(['success' => true, 'message' => 'JobPortal updated successfully.', 'data' => $jobportal], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'JobPortal not found.'], 404);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update JobPortal.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete an existing job portal.
     */
    public function destroy($id)
    {
        try {
            $jobportal = JobPortal::findOrFail($id);

            if ($jobportal->companyLogo) {
                Storage::disk('public')->delete($jobportal->companyLogo);
            }

            $jobportal->delete();

            return response()->json(['success' => true, 'message' => 'JobPortal deleted successfully.'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'JobPortal not found.'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete JobPortal.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Search for job portals based on filters.
     */
    public function search(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'query' => 'nullable|string|max:255',
                'companyName' => 'nullable|string|max:255',
                'post' => 'nullable|string|max:100',
                'location' => 'nullable|string|max:255',
                'contract' => 'nullable|string|in:fulltime,remote,parttime',
            ]);

            $query = JobPortal::query();

            if (!empty($validatedData['query'])) {
                $query->where(function ($q) use ($validatedData) {
                    $q->where('companyName', 'LIKE', '%' . $validatedData['query'] . '%')
                        ->orWhere('post', 'LIKE', '%' . $validatedData['query'] . '%')
                        ->orWhere('location', 'LIKE', '%' . $validatedData['query'] . '%')
                        ->orWhere('contract', 'LIKE', '%' . $validatedData['query'] . '%')
                        ->orWhere('salary', 'LIKE', '%' . $validatedData['query'] . '%')
                        ->orWhere('description', 'LIKE', '%' . $validatedData['query'] . '%');
                });
            }

            if (!empty($validatedData['companyName'])) {
                $query->where('companyName', 'LIKE', '%' . $validatedData['companyName'] . '%');
            }

            if (!empty($validatedData['post'])) {
                $query->where('post', 'LIKE', '%' . $validatedData['post'] . '%');
            }

            if (!empty($validatedData['location'])) {
                $query->where('location', 'LIKE', '%' . $validatedData['location'] . '%');
            }

            if (!empty($validatedData['contract'])) {
                $query->where('contract', $validatedData['contract']);
            }

            $jobportals = $query->latest()->get();

            return response()->json(['success' => true, 'data' => $jobportals], 200);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to search job portals.', 'error' => $e->getMessage()], 500);
        }
    }
}