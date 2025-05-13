<?php
use App\Http\Controllers\ElectionsController;
use App\Http\Controllers\ElectionResultsController;
use illuminate\Support\Facades\Route;

 // Elections Resource
    Route::post('/elections', [ElectionsController::class, 'createElection'])
        ->name('elections.store');
    Route::get('/elections', [ElectionsController::class, 'getElections'])
        ->name('elections.index');
    Route::delete('/elections/{electionId}', [ElectionsController::class, 'deleteElection'])
        ->name('elections.destroy');
    Route::put('/elections/{electionId}', [ElectionsController::class, 'updateElection'])
        ->name('elections.update');
    Route::get('/elections/{electionId}/candidates', [ElectionsController::class, 'getElectionCandidates'])
        ->name('elections.candidates.index');
    Route::delete('/elections/bulk-delete/{electionIds}', [ElectionsController::class, 'bulkDeleteElection'])
        ->name('elections.bulk-delete');
    Route::put('/elections/bulk-update', [ElectionsController::class, 'bulkUpdateElection'])
        ->name('elections.bulk-update');
    Route::get('/elections/{electionId}/allowed-participants', [ElectionsController::class, 'getAllowedParticipants'])
        ->name('elections.allowed-participants.index');
    Route::post('/elections/{electionId}/allowed-participants', [ElectionsController::class, 'addAllowedParticipants'])
        ->name('elections.allowed-participants.store');
    Route::post('/elections/{electionId}/allowed-participants/from/{targetElectionId}', [ElectionsController::class, 'addAllowedParticipantsByOtherElection'])
        ->name('elections.allowed-participants.store-from-other');

    // Voting
    Route::post('/elections/{electionId}/cast-vote', [ElectionsController::class, 'vote'])
        ->name('elections.vote');

    // Election Results
    Route::get('/elections/{electionId}/results', [ElectionResultsController::class, 'getElectionResults'])
        ->name('elections.results.show');
    Route::get('/past-election-winners', [ElectionsController::class, 'getPastElectionWinners'])
        ->name('elections.winners.past');
    Route::get('/current-election-winners', [ElectionsController::class, 'getCurrentElectionWinners'])
        ->name('elections.winners.current');
    Route::get('/getElectionResults/{electionId}', [ElectionsController::class, '']) // Empty controller action, needs review
        ->name('elections.results.get');

    // Election Types Resource
    Route::post('/election-types', [ElectionsController::class, 'createElectionType'])
        ->name('election-types.store');
    Route::get('/election-types', [ElectionsController::class, 'getElectionTypes']) // Inconsistent action, should be getElectionTypes
        ->name('election-types.index');
    Route::put('/election-types/{electionTypeId}', [ElectionsController::class, 'updateElectionType'])
        ->name('election-types.update');
    Route::delete('/election-types/{electionTypeId}', [ElectionsController::class, 'deleteElectionType'])
        ->name('election-types.destroy');
    Route::post('/election-types/{electionTypeId}/activate', [ElectionsController::class, 'activateElectionType'])
        ->name('election-types.activate');
    Route::post('/election-types/{electionTypeId}/deactivate', [ElectionsController::class, 'deactivateElectionType'])
        ->name('election-types.deactivate');
    Route::get('/active-election-types', [ElectionsController::class, 'getActiveElectionTypes'])
        ->name('election-types.active');
