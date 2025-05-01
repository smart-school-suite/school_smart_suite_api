<?php
use App\Http\Controllers\ElectionsController;
use App\Http\Controllers\ElectionResultsController;
use illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->post('/create-election', [ElectionsController::class, 'createElection']);
Route::middleware(['auth:sanctum'])->get('/get-elections', [ElectionsController::class, 'getElections']);
Route::middleware(['auth:sanctum'])->delete('/delete-election/{election_id}', [ElectionsController::class, 'deleteElection']);
Route::middleware(['auth:sanctum'])->get('/update-election/{election_id}', [ElectionsController::class, 'updateElection']);
Route::middleware(['auth:sanctum'])->post('/cast-vote', [ElectionsController::class, 'vote']);
Route::middleware(['auth:sanctum'])->get('/election-results/{election_id}', [ElectionResultsController::class, 'getElectionResults']);
Route::middleware(['auth:sanctum'])->get('/election-candidates/{electionId}', [ElectionsController::class, 'getElectionCandidates']);
Route::middleware(['auth:sanctum'])->post('/createElectionType', [ElectionsController::class, 'createElectionType']);
Route::middleware(['auth:sanctum'])->put('/updateElectionType/{electionTypeId}', [ElectionsController::class, 'updateElectionType']);
Route::middleware(['auth:sanctum'])->delete("/deleteElectionType/{electionTypeId}", [ElectionsController::class, 'deleteElectionType']);
Route::middleware(['auth:sanctum'])->get('/electionType', [ElectionsController::class, 'deleteElectionType']);
Route::middleware(['auth:sanctum'])->post('/activateElectionType/{electionTypeId}', [ElectionsController::class, 'activateElectionType']);
Route::middleware(['auth:sanctum'])->post('/deactivateElectionType/{electionTypeId}', [ElectionsController::class, 'deactivateElectionType']);
Route::middleware(['auth:sanctum'])->get('/activeElectionTypes', [ElectionsController::class, 'getActiveElectionTypes']);
Route::middleware(['auth:sanctum'])->get('/getElectionResults/{electionId}', [ElectionsController::class, '']);
Route::middleware(['auth:sanctum'])->get('/getPastElectionWinners', [ElectionsController::class, 'getPastElectionWinners']);
Route::middleware(['auth:sanctum'])->get('/getCurrentElectionWinners', [ElectionsController::class, 'getCurrentElectionWinners']);
Route::middleware(['auth:sanctum'])->post('/addAllowedParticipantsByOtherElection/{targetElectionId}/{electionId}', [ElectionsController::class, 'addAllowedParticipantsByOtherElection']);
Route::middleware(['auth:sanctum'])->get('/getAllowedParticipants/{electionId}', [ElectionsController::class, 'getAllowedParticipants']);
Route::middleware(['auth:sanctum'])->post('/addAllowedParticipants', [ElectionsController::class, 'addAllowedParticipants']);
Route::middleware(['auth:sanctum'])->delete('/bulkDeleteElection/{electionIds}', [ElectionsController::class, 'bulkDeleteElection']);
Route::middleware(['auth:sanctum'])->put('/bulkUpdateElection', [ElectionsController::class, 'bulkUpdateElection']);
