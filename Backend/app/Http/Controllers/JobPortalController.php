<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\JobPortal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class JobPortalController extends Controller
{
    /**
     * Display a listing of all job portals.
     */
    public function index()
    {
        try {
            // Fetch job portals ordered by 'created_at' in descending order
            $jobportals = JobPortal::latest()->get();

            return response()->json(['success' => true, 'data' => $jobportals], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch job portals.'], 500);
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
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'JobPortal not found.'], 404);
        }
    }

    /**
     * Store a newly created job portal in the database.
     */
    public function store(Request $request)
    {
        try {
            // Validate the incoming data
            $validatedData = $request->validate([
                'companyLogo' => 'required|file|mimes:jpeg,png,jpg,gif|max:2048', // Limit file size to 2MB
                'companyName' => 'required|string|max:255',
                'contract' => 'required|string|in:fulltime,remote,parttime',
                'post' => 'required|string|max:100',
                'salary' => 'required|numeric|min:0',
                'description' => 'required|string|min:10|max:1000',
                'location' => 'required|string|max:255',
                'responsibility' => 'required|string|min:10|max:1000',
            ]);

            // Handle file upload for companyLogo
            if ($request->hasFile('companyLogo')) {
                $validatedData['companyLogo'] = $request->file('companyLogo')->store('logos', 'public');
            }

            // Create the job portal record
            $jobportal = JobPortal::create($validatedData);

            return response()->json(['success' => true, 'data' => $jobportal], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to create JobPortal.'], 500);
        }
    }

    /**
     * Update an existing job portal.
     */
    public function update(Request $request, $id)
    {
        try {
            // Validate the incoming data
            $validatedData = $request->validate([
                'companyLogo' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048',
                'companyName' => 'required|string|max:255',
                'contract' => 'required|string|max:255',
                'post' => 'required|string|max:100',
                'salary' => 'required|integer|min:0',
                'description' => 'required|string|min:10|max:1000',
                'location' => 'required|string|max:255',
                'responsibility' => 'required|string|min:10|max:1000',
            ]);

            $jobportal = JobPortal::findOrFail($id);

            // Handle file upload for companyLogo
            if ($request->hasFile('companyLogo')) {
                // Delete the old logo if it exists
                if ($jobportal->companyLogo) {
                    Storage::disk('public')->delete($jobportal->companyLogo);
                }
                $validatedData['companyLogo'] = $request->file('companyLogo')->store('logos', 'public');
            }

            // Update the job portal record
            $jobportal->update($validatedData);

            return response()->json(['success' => true, 'message' => 'JobPortal updated successfully.', 'data' => $jobportal], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update JobPortal.'], 500);
        }
    }

    /**
     * Delete an existing job portal.
     */
    public function destroy($id)
    {
        try {
            $jobportal = JobPortal::findOrFail($id);

            // Delete the associated logo file if it exists
            if ($jobportal->companyLogo) {
                Storage::disk('public')->delete($jobportal->companyLogo);
            }

            $jobportal->delete();

            return response()->json(['success' => true, 'message' => 'JobPortal deleted successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete JobPortal.'], 500);
        }
    }
}
