<?php
use App\Http\Controllers\Election\ElectionController;
use illuminate\Support\Facades\Route;
use App\Http\Controllers\Stats\ElectionStatController;
use App\Http\Controllers\Election\ElectionVoteController;
use App\Http\Controllers\Election\ElectionResultController;

    Route::get("/stats/{year}", [ElectionStatController::class, 'getElectionStats'])->name('election.stats');
    Route::middleware(['permission:schoolAdmin.election.create'])->post('/', [ElectionController::class, 'createElection'])
        ->name('elections.store');
    Route::middleware(['permission:schoolAdmin.election.view'])->get('/', [ElectionController::class, 'getElections'])
        ->name('elections.index');
    Route::middleware(['permission:schoolAdmin.election.delete'])->delete('/{electionId}', [ElectionController::class, 'deleteElection'])
        ->name('elections.destroy');
    Route::middleware(['permission:schoolAdmin.election.update'])->put('/{electionId}', [ElectionController::class, 'updateElection'])
        ->name('elections.update');
    Route::middleware(['permission:schoolAdmin.election.delete'])->post('/bulk-delete', [ElectionController::class, 'bulkDeleteElection'])
        ->name('elections.bulk-delete');
    Route::middleware(['permission:schoolAdmin.election.update'])->patch('/bulk-update', [ElectionController::class, 'bulkUpdateElection'])
        ->name('elections.bulk-update');
    Route::middleware(['permission:schoolAdmin.election.view.participants'])->get('/{electionId}/allowed-participants', [ElectionController::class, 'getAllowedParticipants'])
        ->name('elections.allowed-participants.index');
    Route::middleware(['permission:schoolAdmin.election.add.participants'])->post('/{electionId}/allowed-participants', [ElectionController::class, 'addAllowedParticipants'])
        ->name('elections.allowed-participants.store');
    Route::middleware(['permission:schoolAdmin.election.add.participants'])->post('/{electionId}/allowed-participants/from/{targetElectionId}', [ElectionController::class, 'addAllowedParticipantsByOtherElection'])
        ->name('elections.allowed-participants.store-from-other');

    Route::post('/cast-vote', [ElectionVoteController::class, 'castVote'])->name('elections.vote');

    Route::middleware(['permission:schoolAdmin.election.view.results'])->get('/{electionId}/results', [ElectionResultController::class, 'getElectionResults'])
        ->name('elections.results.show');
    Route::get('/{electionId}/live-results', [ElectionResultController::class, 'getLiveElectionResults'])->name("get.live.election.results");

    Route::get('/{electionId}/past-results', [ElectionResultController::class, 'getPastElectionResult'])->name("get.past.election.results");
    Route::get('/{electionId}/current-result', [ElectionResultController::class, 'getCurrentElectionResult'])->name("get.current.election-results");

    Route::get('/student/{studentId}/upcoming-elections', [ElectionController::class, 'getStudentElections'])
        ->name('upcoming-elections.student.get');
    Route::get('/election/{electionId}', [ElectionController::class, 'getElectionDetails'])->name('get.election-details');

    Route::get('/past', [ElectionController::class, 'getPastElections'])->name("get.past.elections");
        Route::middleware(['permission:schoolAdmin.election.view.past.winners'])->get('/past-election-winners', [ElectionController::class, 'getPastElectionWinners'])
        ->name('elections.winners.past');
    Route::middleware(['permission:schoolAdmin.election.view.winners.current'])->get('/current-election-winners', [ElectionController::class, 'getCurrentElectionWinners'])
        ->name('elections.winners.current');



