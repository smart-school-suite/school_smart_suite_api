<?php

use App\Http\Controllers\Election\ElectionCandidateController;
use illuminate\Support\Facades\Route;

Route::get('/{electionId}', [ElectionCandidateController::class, "getCandidatesByElection"])->name("get.candidates.by.election");
Route::get('/', [ElectionCandidateController::class, "getElectionCadidates"])->name("get.candidates");
Route::get('/{candidateId}', [ElectionCandidateController::class, "getElectionCandidateDetails"])->name("get.candidate.details");
Route::post('/disqualify/{candidateId}', [ElectionCandidateController::class, "disqualifyCandidate"])->name("disqualify.candidate");
Route::post('/reinstate/{candidateId}', [ElectionCandidateController::class, "reinstateCandidate"])->name("reinstate.candidate");
Route::post('/bulk/reinstate', [ElectionCandidateController::class, "bulkReinstateCandidate"])->name("bulk.reinstate.candidate");
Route::get('/bulk/disqualify', [ElectionCandidateController::class, "bulkDisQualifyCandidate"])->name("bulk.disqualify.candidate");

