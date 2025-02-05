<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\JobApply;
use App\Models\JobPortal;
use App\Models\Employer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

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
            Log::info('Job Apply Store Request Data:', $request->all());
            
            $validatedData = $request->validate([
                'coverLetter' => 'required|string',
                'resume' => 'required|file|mimes:pdf,doc,docx',
                'job_portals_id' => 'required|exists:job_portals,id',
                'job_seekers_id' => 'required|exists:job_seekers,id',
            ]);

            // Detailed logging of validation data
            Log::info('Validated Job Apply Data:', $validatedData);

            // Get the job title from the job portal
            $jobPortal = JobPortal::findOrFail($validatedData['job_portals_id']);
            
            // Detailed logging of file upload
            if (!$request->hasFile('resume')) {
                Log::error('No resume file uploaded');
                throw new \Exception('Resume file is required');
            }

            $path = $request->file('resume')->store('resumes', 'public');
            Log::info('Resume uploaded successfully', ['path' => $path]);
            
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
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Log validation errors
            Log::error('Validation Error in Job Apply', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // Log any other unexpected errors
            Log::error('Unexpected Error in Job Apply Store', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
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
                    Storage::disk(name: 'public')->delete($jobapply->resume);
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
        Log::info('getApplicationsByEmployer called', [
            'employerId' => $employerId,
            'request_path' => request()->path(),
            'request_method' => request()->method(),
            'input_type' => gettype($employerId)
        ]);

        try {
            // More flexible employer matching
            $employer = Employer::where('id', $employerId)
                                 ->orWhere('user_id', $employerId)
                                 ->first();

            Log::info('Employer Lookup', [
                'employerId' => $employerId,
                'employer_found' => $employer ? 'Yes' : 'No',
                'employer_details' => $employer ? [
                    'id' => $employer->id,
                    'name' => $employer->name,
                    'email' => $employer->email
                ] : null
            ]);

            if (!$employer) {
                Log::warning('No employer found', ['employerId' => $employerId]);
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'No employer found'
                ], 200);
            }

            // More flexible job portal matching
            $employerJobPortals = JobPortal::where(function($query) use ($employer) {
                $query->where('employer_id', $employer->id)
                      ->orWhere('companyName', $employer->name)
                      ->orWhere('user_id', $employer->user_id);
            })->get();

            Log::info('Employer Job Portals', [
                'employerId' => $employerId,
                'employerName' => $employer->name ?? 'N/A',
                'jobPortalsCount' => $employerJobPortals->count(),
                'jobPortalIds' => $employerJobPortals->pluck('id')
            ]);

            // If no job portals found, return empty list
            if ($employerJobPortals->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'No job portals found for this employer'
                ], 200);
            }

            // Fetch applications for job portals with detailed job portal information
            $applications = JobApply::with(['jobPortal', 'jobSeeker'])
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
                        'jobSeeker' => [
                            'id' => $application->jobSeeker->id,
                            'name' => $application->jobSeeker->name,
                            'email' => $application->jobSeeker->email,
                        ],
                        'status' => $application->status,
                        'coverLetter' => $application->coverLetter,
                        'resume' => $application->resume,
                        'created_at' => $application->created_at,
                    ];
                });

            Log::info('Employer Applications', [
                'employerId' => $employerId,
                'applicationsCount' => $applications->count()
            ]);

            return response()->json([
                'success' => true,
                'data' => $applications
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to fetch applications', [
                'employerId' => $employerId,
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
