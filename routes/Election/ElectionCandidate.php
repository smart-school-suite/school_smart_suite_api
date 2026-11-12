<?php

use App\Http\Controllers\Election\ElectionCandidateController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ElectionCandidateController::class, 'getElectionCandidates'])->name('get.candidates');
Route::get('/election/{electionId}', [ElectionCandidateController::class, 'getCandidatesByElection'])->name('get.candidates.by.election');
Route::get('/election/{electionId}/ballot', [ElectionCandidateController::class, 'getBallot'])->name('get.election.ballot');
Route::post('/bulk/reinstate', [ElectionCandidateController::class, 'bulkReinstateCandidate'])->name('bulk.reinstate.candidate');
Route::post('/bulk/disqualify', [ElectionCandidateController::class, 'bulkDisqualifyCandidate'])->name('bulk.disqualify.candidate');
Route::post('/disqualify/{candidateId}', [ElectionCandidateController::class, 'disqualifyCandidate'])->name('disqualify.candidate');
Route::post('/reinstate/{candidateId}', [ElectionCandidateController::class, 'reinstateCandidate'])->name('reinstate.candidate');
Route::get('/{candidateId}/details', [ElectionCandidateController::class, 'getElectionCandidateDetails'])->name('get.candidate.details');
