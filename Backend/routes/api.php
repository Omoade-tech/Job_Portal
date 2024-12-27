<?php

use App\Http\Controllers\JobPortalController;
use App\Http\Controllers\JobApplyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('job_portals', JobPortalController::class);

Route::apiResource('job_applies', JobApplyController::class);


//http://localhost:8000/api/job_portals
//http://localhost:8000/api/job_applies

