<?php
use App\Http\Controllers\ElectionApplicationController;
use illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->post('/apply', [ElectionApplicationController::class, 'createElectionApplication']);
Route::middleware(['auth:sanctum'])->get('/applications/{election_id}', [ElectionApplicationController::class, 'getApplications']);
Route::middleware(['auth:sanctum'])->put('/update-application/{application_id}', [ElectionApplicationController::class, 'updateApplication']);
Route::middleware(['auth:sanctum'])->delete('/delete/{application_id}', [ElectionApplicationController::class, 'deleteApplication']);
Route::middleware(['auth:sanctum'])->put('/approve-application/{application_id}', [ElectionApplicationController::class, 'approveApplication']);
Route::middleware(['auth:sanctum'])->get('/getAllApplications', [ElectionApplicationController::class, 'getAllElectionApplication']);
Route::middleware(['auth:sanctum'])->post('/bulkApproveApplications/{applicationIds}', [ElectionApplicationController::class, 'bulkApproveApplication']);
Route::middleware(['auth:sanctum'])->delete('/bulkDeleteApplication/{applicationIds}', [ElectionApplicationController::class, 'bulkDeleteApplication']);
