<?php

use App\Http\Controllers\JobPortalController;
use App\Http\Controllers\JobApplyController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Job Portal Routes
Route::get('/job_portals/search', [JobPortalController::class, 'search']);
Route::apiResource('job_portals', JobPortalController::class);

// Route::middleware(['auth:sanctum'])->group(function () {
    // Job Application Routes
    Route::get('/job_applies/employer/{employerId}', [JobApplyController::class, 'getApplicationsByEmployer'])
        ->name('job_applies.employer');
    
    Route::apiResource('job_applies', JobApplyController::class);
    Route::put('/job_applies/{id}/status', [JobApplyController::class, 'updateStatus'])
        ->name('job_applies.status');
// });

// Authentication Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::get('/users', [AuthController::class, 'getUsersByRole']);



//GET /api/users?role=admin

//http://localhost:8000/api/job_portals
//http://localhost:8000/api/job_applies
http://localhost:8000/api/job_applies/employer/{employerId}
//http://localhost:8000/api/register
//http://localhost:8000/api/login
//http://localhost:8000/api/logout
// http://localhost:8000/api/users?role=job_seeker