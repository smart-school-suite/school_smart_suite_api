<?php
use App\Http\Controllers\ElectionsController;
use App\Http\Controllers\ElectionResultsController;
use illuminate\Support\Facades\Route;

 // Elections Resource
    Route::middleware(['permission:schoolAdmin.election.create'])->post('/elections', [ElectionsController::class, 'createElection'])
        ->name('elections.store');
    Route::middleware(['permission:schoolAdmin.election.view'])->get('/elections', [ElectionsController::class, 'getElections'])
        ->name('elections.index');
    Route::middleware(['permission:schoolAdmin.election.delete'])->delete('/elections/{electionId}', [ElectionsController::class, 'deleteElection'])
        ->name('elections.destroy');
    Route::middleware(['permission:schoolAdmin.election.update'])->put('/elections/{electionId}', [ElectionsController::class, 'updateElection'])
        ->name('elections.update');
    Route::middleware(['permission:schoolAdmin.election.view.candidates'])->get('/elections/{electionId}/candidates', [ElectionsController::class, 'getElectionCandidates'])
        ->name('elections.candidates.index');
    Route::middleware(['permission:schoolAdmin.election.delete'])->delete('/elections/bulk-delete/{electionIds}', [ElectionsController::class, 'bulkDeleteElection'])
        ->name('elections.bulk-delete');
    Route::middleware(['permission:schoolAdmin.election.update'])->put('/elections/bulk-update', [ElectionsController::class, 'bulkUpdateElection'])
        ->name('elections.bulk-update');
    Route::middleware(['permission:schoolAdmin.election.view.participants'])->get('/elections/{electionId}/allowed-participants', [ElectionsController::class, 'getAllowedParticipants'])
        ->name('elections.allowed-participants.index');
    Route::middleware(['permission:schoolAdmin.election.add.participants'])->post('/elections/{electionId}/allowed-participants', [ElectionsController::class, 'addAllowedParticipants'])
        ->name('elections.allowed-participants.store');
    Route::middleware(['permission:schoolAdmin.election.add.participants'])->post('/elections/{electionId}/allowed-participants/from/{targetElectionId}', [ElectionsController::class, 'addAllowedParticipantsByOtherElection'])
        ->name('elections.allowed-participants.store-from-other');

    // Voting
    Route::middleware(['permission:schoolAdmin.election.vote'])->post('/elections/{electionId}/cast-vote', [ElectionsController::class, 'vote'])
        ->name('elections.vote');

    // Election Results
    Route::middleware(['permission:schoolAdmin.election.view.results'])->get('/elections/{electionId}/results', [ElectionResultsController::class, 'getElectionResults'])
        ->name('elections.results.show');
    Route::middleware(['permission:schoolAdmin.election.view.past.winners'])->get('/past-election-winners', [ElectionsController::class, 'getPastElectionWinners'])
        ->name('elections.winners.past');
    Route::middleware(['permission:schoolAdmin.election.view.winners.current'])->get('/current-election-winners', [ElectionsController::class, 'getCurrentElectionWinners'])
        ->name('elections.winners.current');

    // Election Types Resource
    Route::middleware(['permission:schoolAdmin.electionType.create'])->post('/election-types', [ElectionsController::class, 'createElectionType'])
        ->name('election-types.store');
    Route::middleware(['permission:schoolAdmin.electionType.view'])->get('/election-types', [ElectionsController::class, 'getElectionTypes']) // Inconsistent action, should be getElectionTypes
        ->name('election-types.index');
    Route::middleware(['permission:schoolAdmin.electionType.update'])->put('/election-types/{electionTypeId}', [ElectionsController::class, 'updateElectionType'])
        ->name('election-types.update');
    Route::middleware(['permission:schoolAdmin.electionType.delete'])->delete('/election-types/{electionTypeId}', [ElectionsController::class, 'deleteElectionType'])
        ->name('election-types.destroy');
    Route::middleware(['permission:schoolAdmin.electionType.activate'])->post('/election-types/{electionTypeId}/activate', [ElectionsController::class, 'activateElectionType'])
        ->name('election-types.activate');
    Route::middleware(['permission:schoolAdmin.electionType.deactivate'])->post('/election-types/{electionTypeId}/deactivate', [ElectionsController::class, 'deactivateElectionType'])
        ->name('election-types.deactivate');
    Route::middleware(['permission:schoolAdmin.electionType.view.active'])->get('/active-election-types', [ElectionsController::class, 'getActiveElectionTypes'])
        ->name('election-types.active');
