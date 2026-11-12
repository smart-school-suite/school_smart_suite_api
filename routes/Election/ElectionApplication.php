<?php

use App\Http\Controllers\Election\ElectionApplicationController;
use Illuminate\Support\Facades\Route;

Route::post('/', [ElectionApplicationController::class, 'createElectionApplication'])->name('election-applications.store');
Route::get('/', [ElectionApplicationController::class, 'getAllElectionApplication'])->name('election-applications.index');
Route::get('/mine', [ElectionApplicationController::class, 'getMyApplications'])->name('election-applications.mine');
Route::get('/mine/election/{electionId}', [ElectionApplicationController::class, 'getMyApplications'])->name('election-applications.mine.election');
Route::get('/student/election/{electionId}', [ElectionApplicationController::class, 'getMyApplications'])->name('get.student.election.applications');
Route::get('/elections/{electionId}', [ElectionApplicationController::class, 'getApplications'])->name('elections.applications.index');

Route::post('/bulk-approve', [ElectionApplicationController::class, 'bulkApproveApplication'])->name('election-applications.bulk-approve');
Route::post('/bulk-reject', [ElectionApplicationController::class, 'bulkRejectApplication'])->name('election-applications.bulk-reject');
Route::post('/bulk-delete', [ElectionApplicationController::class, 'bulkDeleteApplication'])->name('election-applications.bulk-delete');

Route::put('/{applicationId}/approve', [ElectionApplicationController::class, 'approveApplication'])->name('election-applications.approve');
Route::put('/{applicationId}/reject', [ElectionApplicationController::class, 'rejectApplication'])->name('election-applications.reject');
Route::post('/{applicationId}/withdraw', [ElectionApplicationController::class, 'withdrawApplication'])->name('election-applications.withdraw');
Route::get('/{applicationId}', [ElectionApplicationController::class, 'getApplicationDetails'])->name('get.application.details');
Route::put('/{applicationId}', [ElectionApplicationController::class, 'updateApplication'])->name('election-applications.update');
Route::delete('/{applicationId}', [ElectionApplicationController::class, 'deleteApplication'])->name('election-applications.destroy');
