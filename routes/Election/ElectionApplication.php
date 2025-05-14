<?php

use App\Http\Controllers\ElectionApplicationController;
use illuminate\Support\Facades\Route;

// Create a new election application
Route::middleware(['permission:student.electionApplications.create'])->post('/election-applications', [ElectionApplicationController::class, 'createElectionApplication'])
    ->name('election-applications.store');

// Get applications for a specific election
Route::middleware(['permission:schoolAdmin.electionApplications.view.elections'])->get('/elections/{electionId}/applications', [ElectionApplicationController::class, 'getApplications'])
    ->name('elections.applications.index');

// Get all election applications (potentially admin-only)
Route::middleware(['permission:schoolAdmin.electionApplications.view'])->get('/election-applications', [ElectionApplicationController::class, 'getAllElectionApplication'])
    ->name('election-applications.index');

// Update a specific election application
Route::middleware(['permission:student.electionApplications.update'])->put('/election-applications/{applicationId}', [ElectionApplicationController::class, 'updateApplication'])
    ->name('election-applications.update');

// Delete a specific election application
Route::middleware(['permission:schoolAdmin.electionApplications.delete'])->delete('/election-applications/{applicationId}', [ElectionApplicationController::class, 'deleteApplication'])
    ->name('election-applications.destroy');

// Approve a specific election application
Route::middleware(['permission:schoolAdmin.electionApplications.approve'])->put('/election-applications/{applicationId}/approve', [ElectionApplicationController::class, 'approveApplication'])
    ->name('election-applications.approve');

// Bulk approve election applications
Route::middleware(['permission:schoolAdmin.electionApplications.approve'])->post('/election-applications/bulk-approve', [ElectionApplicationController::class, 'bulkApproveApplication'])
    ->name('election-applications.bulk-approve');

// Bulk delete election applications
Route::middleware(['permission:schoolAdmin.electionApplications.delete'])->delete('/election-applications/bulk-delete', [ElectionApplicationController::class, 'bulkDeleteApplication'])
    ->name('election-applications.bulk-delete');
