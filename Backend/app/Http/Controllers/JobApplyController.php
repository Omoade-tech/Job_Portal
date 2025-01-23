<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\JobApply;
use Illuminate\Http\Request;

class JobApplyController extends Controller
{
    public function index()
    {
        try {
            $jobsapply = JobApply::with('jobPortal', 'jobSeeker')->latest()->get(); 
            return response()->json(['success' => true, 'data' => $jobsapply], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            
            $jobapply = JobApply::with('jobPortal', 'jobSeeker')->findOrFail($id); 
            return response()->json(['success' => true, 'data' => $jobapply], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Job application not found.'], 404);
        }
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'coverLetter' => 'required|string|min:20',
                'resume' => 'required|file|mimes:pdf|max:2048',
                'job_portals_id' => 'required|exists:job_portals,id',
                'job_seekers_id' => 'required|exists:job_seekers,id', 
            ]);

            // Handle file upload
            if ($request->hasFile('resume')) {
                $validatedData['resume'] = $request->file('resume')->store('resumes', 'public');
            }

            // Add job_seekers_id to the validated data
            $validatedData['job_seekers_id'] = $request->job_seekers_id;

            $jobapply = JobApply::create($validatedData);

            return response()->json(['success' => true, 'data' => $jobapply], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'coverLetter' => 'required|string|min:20',
                'resume' => 'required|file|mimes:pdf|max:2048',
                'job_portals_id' => 'required|exists:job_portals,id',
                'job_seekers_id' => 'required|exists:job_seekers,id', 
            ]);

            $jobapply = JobApply::findOrFail($id);

            // Handle file upload if provided
            if ($request->hasFile('resume')) {
                $validatedData['resume'] = $request->file('resume')->store('resumes', 'public');
            }

            // Add job_seekers_id to the validated data
            $validatedData['job_seekers_id'] = $request->job_seekers_id;

            $jobapply->update($validatedData);

            return response()->json(['success' => true, 'data' => $jobapply], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
