<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\JobApply;
use App\Models\reference;
use Illuminate\Http\Request;

class JobApplyController extends Controller
{
    public function index()
    {
        try {
            $jobsapply = JobApply::with('reference')->latest()->get();
            return response()->json(['success' => true, 'data' => $jobsapply], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $jobapply = JobApply::with('reference')->findOrFail($id);
            return response()->json(['success' => true, 'data' => $jobapply], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Job application not found.'], 404);
        }
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:job_applies,email',
                'phoneNumber' => 'required|string|max:20',
                'address' => 'required|string|min:20|max:100',
                'coverLetter' => 'required|string|min:20',
                'resume' => 'required|file|mimes:pdf|max:2048',
                'reference_id' => 'required|exists:references,id',
            ]);

            // Handle file upload
            if ($request->hasFile('resume')) {
                $validatedData['resume'] = $request->file('resume')->store('resumes', 'public');
            }

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
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:job_applies,email,' . $id,
                'phoneNumber' => 'required|string|max:20',
                'address' => 'required|string|min:20|max:100',
                'coverLetter' => 'required|string|min:20',
                'resume' => 'required|file|mimes:pdf|max:2048', 
                'reference_id' => 'required|exists:references,id',
            ]);

            $jobapply = JobApply::findOrFail($id);

            // Handle file upload if provided
            if ($request->hasFile('resume')) {
                $validatedData['resume'] = $request->file('resume')->store('resumes', 'public');
            }

            $jobapply->update($validatedData);

            return response()->json(['success' => true, 'data' => $jobapply], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
