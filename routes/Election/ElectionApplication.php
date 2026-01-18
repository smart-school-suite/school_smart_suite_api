<?php

use illuminate\Support\Facades\Route;
use App\Http\Controllers\Election\ElectionApplicationController;


Route::post('/', [ElectionApplicationController::class, 'createElectionApplication'])
    ->name('election-applications.store');

Route::get('/elections/{electionId}', [ElectionApplicationController::class, 'getApplications'])
    ->name('elections.applications.index');

Route::get('/', [ElectionApplicationController::class, 'getAllElectionApplication'])
    ->name('election-applications.index');


Route::put('/{applicationId}', [ElectionApplicationController::class, 'updateApplication'])
    ->name('election-applications.update');

Route::delete('/{applicationId}', [ElectionApplicationController::class, 'deleteApplication'])
    ->name('election-applications.destroy');

Route::put('/{applicationId}/approve', [ElectionApplicationController::class, 'approveApplication'])
    ->name('election-applications.approve');

Route::post('/bulk-approve', [ElectionApplicationController::class, 'bulkApproveApplication'])
    ->name('election-applications.bulk-approve');

Route::post('/bulk-delete', [ElectionApplicationController::class, 'bulkDeleteApplication'])
    ->name('election-applications.bulk-delete');
Route::get('election-application/student/{studentId}', [ElectionApplicationController::class, 'getMyApplications'])->name('get.student-election.application');

Route::get('/{applicationId}', [ElectionApplicationController::class, 'getApplicationDetails'])->name("get.application.details");

Route::get('/student/election/{electionId}', [ElectionApplicationController::class, 'getStudentElectionApplication'])->name("get.student.election.applications");
