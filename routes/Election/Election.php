<?php

use App\Http\Controllers\Election\ElectionController;
use App\Http\Controllers\Election\ElectionOfficeController;
use App\Http\Controllers\Election\ElectionResultController;
use App\Http\Controllers\Election\ElectionVoteController;
use App\Http\Controllers\Stats\ElectionStatController;
use Illuminate\Support\Facades\Route;

Route::get('/stats/{year}', [ElectionStatController::class, 'getElectionStats'])->name('election.stats');

// Elections
Route::post('/', [ElectionController::class, 'createElection'])->name('elections.store');
Route::get('/', [ElectionController::class, 'getElections'])->name('elections.index');
Route::get('/past', [ElectionController::class, 'getPastElections'])->name('get.past.elections');
Route::get('/history', [ElectionResultController::class, 'getElectionHistory'])->name('elections.history');
// Winners now come from office holders (previous endpoint names)
Route::get('/past-election-winners', [ElectionResultController::class, 'getElectionHistory'])->name('elections.winners.past');
Route::get('/current-election-winners', [ElectionOfficeController::class, 'getCurrentOfficeHolders'])->name('elections.winners.current');
Route::get('/my-elections', [ElectionController::class, 'getMyElections'])->name('elections.mine');
Route::get('/student/upcoming-elections', [ElectionController::class, 'getMyElections'])->name('upcoming-elections.student.get');
Route::post('/bulk-delete', [ElectionController::class, 'bulkDeleteElection'])->name('elections.bulk-delete');
Route::patch('/bulk-update', [ElectionController::class, 'bulkUpdateElection'])->name('elections.bulk-update');

// Voting
Route::post('/cast-vote', [ElectionVoteController::class, 'castVote'])->name('elections.vote');
Route::get('/{electionId}/my-ballot', [ElectionVoteController::class, 'getMyBallotStatus'])->name('elections.ballot.mine');

// Lifecycle
Route::post('/{electionId}/publish', [ElectionController::class, 'publishElection'])->name('elections.publish');
Route::post('/{electionId}/cancel', [ElectionController::class, 'cancelElection'])->name('elections.cancel');
Route::post('/{electionId}/close-voting', [ElectionController::class, 'closeVoting'])->name('elections.close-voting');

// Participants & eligibility
Route::get('/{electionId}/participants', [ElectionController::class, 'getParticipants'])->name('elections.participants.index');
Route::put('/{electionId}/participants', [ElectionController::class, 'configureParticipants'])->name('elections.participants.configure');
Route::post('/{electionId}/participants/refresh', [ElectionController::class, 'refreshParticipants'])->name('elections.participants.refresh');
Route::post('/{electionId}/participants/from/{targetElectionId}', [ElectionController::class, 'copyParticipants'])->name('elections.participants.copy');
// Previous endpoint names
Route::get('/{electionId}/allowed-participants', [ElectionController::class, 'getParticipants'])->name('elections.allowed-participants.index');
Route::post('/{electionId}/allowed-participants', [ElectionController::class, 'configureParticipants'])->name('elections.allowed-participants.store');
Route::post('/{electionId}/allowed-participants/from/{targetElectionId}', [ElectionController::class, 'copyParticipants'])->name('elections.allowed-participants.store-from-other');

// Results (derived from votes)
Route::get('/{electionId}/results', [ElectionResultController::class, 'getElectionResults'])->name('elections.results.show');
Route::get('/{electionId}/live-results', [ElectionResultController::class, 'getElectionResults'])->name('get.live.election.results');
Route::get('/{electionId}/past-results', [ElectionResultController::class, 'getElectionResults'])->name('get.past.election.results');
Route::get('/{electionId}/current-result', [ElectionResultController::class, 'getElectionResults'])->name('get.current.election-results');
Route::post('/{electionId}/results/publish', [ElectionResultController::class, 'publishResults'])->name('elections.results.publish');

Route::get('/election/{electionId}', [ElectionController::class, 'getElectionDetails'])->name('get.election-details');
Route::get('/{electionId}', [ElectionController::class, 'getElectionDetails'])->name('elections.show');
Route::put('/{electionId}', [ElectionController::class, 'updateElection'])->name('elections.update');
Route::delete('/{electionId}', [ElectionController::class, 'deleteElection'])->name('elections.destroy');
