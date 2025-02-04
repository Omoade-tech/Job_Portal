<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\JobApply;
use App\Models\JobPortal;
use App\Models\Employer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class JobApplyController extends Controller
{
    public function index()
    {
        try {
            $jobsapply = JobApply::with(['jobPortal', 'jobSeeker'])->latest()->get(); 
            return response()->json(['success' => true, 'data' => $jobsapply], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $jobapply = JobApply::with(['jobPortal', 'jobSeeker'])->findOrFail($id); 
            return response()->json(['success' => true, 'data' => $jobapply], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Job application not found.'], 404);
        }
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'coverLetter' => 'required|string',
                'resume' => 'required|file|mimes:pdf,doc,docx',
                'job_portals_id' => 'required|exists:job_portals,id',
                'job_seekers_id' => 'required|exists:job_seekers,id',
            ]);

            // Get the job title from the job portal
            $jobPortal = JobPortal::findOrFail($validatedData['job_portals_id']);
            
            $path = $request->file('resume')->store('resumes', 'public');
            
            $jobApply = JobApply::create([
                'coverLetter' => $validatedData['coverLetter'],
                'resume' => $path,
                'job_portals_id' => $validatedData['job_portals_id'],
                'job_seekers_id' => $validatedData['job_seekers_id'],
                'job_title' => $jobPortal->post, // Store the job title
                'status' => 'pending'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Application submitted successfully',
                'data' => $jobApply
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit application: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'coverLetter' => 'required|string',
                'resume' => 'nullable|file|mimes:pdf,doc,docx',
                'job_portals_id' => 'required|exists:job_portals,id',
                'job_seekers_id' => 'required|exists:job_seekers,id',
                'status' => 'nullable|in:pending,accepted,rejected'
            ]);

            $jobapply = JobApply::findOrFail($id);

            // Handle file upload if provided
            if ($request->hasFile('resume')) {
                // Delete old resume if it exists
                if ($jobapply->resume) {
                    \Storage::disk(name: 'public')->delete($jobapply->resume);
                }
                $validatedData['resume'] = $request->file('resume')->store('resumes', 'public');
            } else {
                // Remove resume from validated data to prevent unintended removal
                unset($validatedData['resume']);
            }

            // Update job title if job portal changes
            if ($request->has('job_portals_id') && $jobapply->job_portals_id != $request->job_portals_id) {
                $jobPortal = JobPortal::findOrFail($request->job_portals_id);
                $validatedData['job_title'] = $jobPortal->post;
            }

            $jobapply->update(array_filter($validatedData));

            return response()->json(['success' => true, 'data' => $jobapply], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'status' => 'required|in:pending,accepted,rejected'
            ]);

            $jobApply = JobApply::findOrFail($id);
            $jobApply->status = $validatedData['status'];
            $jobApply->save();

            return response()->json([
                'success' => true,
                'message' => 'Application status updated successfully',
                'data' => $jobApply
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update application status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getApplicationsByEmployer($employerId)
    {
        try {
            // Validate employer ID
            if (!$employerId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid employer ID'
                ], 400);
            }

            // Find the employer
            $employer = Employer::findOrFail($employerId);

            // Find job portals for this employer using multiple matching strategies
            $employerJobPortals = JobPortal::where(function($query) use ($employer) {
                $query->where('companyName', $employer->name)
                      ->orWhere('employer_id', $employer->id);
            })->get();

            // Log job portals for debugging
            \Log::info('Employer Job Portals', [
                'employer_name' => $employer->name,
                'employer_id' => $employer->id,
                'portal_count' => $employerJobPortals->count(),
                'portal_ids' => $employerJobPortals->pluck('id')->toArray()
            ]);

            // If no job portals found, return empty list
            if ($employerJobPortals->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'No job portals found for this employer'
                ], 200);
            }

            // Fetch applications for job portals
            $applications = JobApply::with(['jobPortal'])
                ->whereIn('job_portals_id', $employerJobPortals->pluck('id'))
                ->latest()
                ->get()
                ->map(function ($application) {
                    return [
                        'id' => $application->id,
                        'job' => [
                            'id' => $application->jobPortal->id,
                            'title' => $application->job_title ?? $application->jobPortal->post,
                            'company' => $application->jobPortal->companyName,
                        ],
                        'status' => $application->status,
                        'created_at' => $application->created_at,
                    ];
                });

            // Log applications for debugging
            \Log::info('Employer Applications', [
                'total_applications' => $applications->count(),
                'application_ids' => $applications->pluck('id')->toArray()
            ]);

            return response()->json([
                'success' => true,
                'data' => $applications
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Log specific not found errors
            \Log::error('Employer Not Found', [
                'employer_id' => $employerId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Employer not found',
                'error' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            // Log any other unexpected errors
            \Log::error('Error Fetching Employer Applications', [
                'employer_id' => $employerId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch applications: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
