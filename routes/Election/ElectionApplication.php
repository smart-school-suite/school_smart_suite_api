<?php

use App\Http\Controllers\ElectionApplicationController;
use illuminate\Support\Facades\Route;

// Create a new election application
Route::middleware(['permission:student.electionApplications.create'])->post('/', [ElectionApplicationController::class, 'createElectionApplication'])
    ->name('election-applications.store');

// Get applications for a specific election
Route::middleware(['permission:schoolAdmin.electionApplications.view.elections'])->get('/elections/{electionId}', [ElectionApplicationController::class, 'getApplications'])
    ->name('elections.applications.index');

// Get all election applications (potentially admin-only)
Route::middleware(['permission:schoolAdmin.electionApplications.view'])->get('/', [ElectionApplicationController::class, 'getAllElectionApplication'])
    ->name('election-applications.index');

// Update a specific election application
Route::middleware(['permission:student.electionApplications.update'])->put('/{applicationId}', [ElectionApplicationController::class, 'updateApplication'])
    ->name('election-applications.update');

// Delete a specific election application
Route::middleware(['permission:schoolAdmin.electionApplications.delete'])->delete('/{applicationId}', [ElectionApplicationController::class, 'deleteApplication'])
    ->name('election-applications.destroy');

// Approve a specific election application
Route::middleware(['permission:schoolAdmin.electionApplications.approve'])->put('/{applicationId}/approve', [ElectionApplicationController::class, 'approveApplication'])
    ->name('election-applications.approve');

// Bulk approve election applications
Route::middleware(['permission:schoolAdmin.electionApplications.approve'])->post('/bulk-approve', [ElectionApplicationController::class, 'bulkApproveApplication'])
    ->name('election-applications.bulk-approve');

// Bulk delete election applications
Route::middleware(['permission:schoolAdmin.electionApplications.delete'])->post('/bulk-delete', [ElectionApplicationController::class, 'bulkDeleteApplication'])
    ->name('election-applications.bulk-delete');
Route::get('election-application/student/{studentId}', [ElectionApplicationController::class, 'getMyApplications'])->name('get.student-election.application');
