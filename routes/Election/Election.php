<?php
use App\Http\Controllers\ElectionsController;
use App\Http\Controllers\ElectionResultsController;
use illuminate\Support\Facades\Route;
use App\Http\Controllers\Stats\ElectionStatController;
 // Elections Resource
    Route::get("/stats/{year}", [ElectionStatController::class, 'getElectionStats'])->name('election.stats');
    Route::middleware(['permission:schoolAdmin.election.create'])->post('/', [ElectionsController::class, 'createElection'])
        ->name('elections.store');
    Route::middleware(['permission:schoolAdmin.election.view'])->get('/', [ElectionsController::class, 'getElections'])
        ->name('elections.index');
    Route::middleware(['permission:schoolAdmin.election.delete'])->delete('/{electionId}', [ElectionsController::class, 'deleteElection'])
        ->name('elections.destroy');
    Route::middleware(['permission:schoolAdmin.election.update'])->put('/{electionId}', [ElectionsController::class, 'updateElection'])
        ->name('elections.update');
    Route::middleware(['permission:schoolAdmin.election.view.candidates'])->get('/{electionId}/candidates', [ElectionsController::class, 'getElectionCandidates'])
        ->name('elections.candidates.index');
    Route::middleware(['permission:schoolAdmin.election.delete'])->post('/bulk-delete', [ElectionsController::class, 'bulkDeleteElection'])
        ->name('elections.bulk-delete');
    Route::middleware(['permission:schoolAdmin.election.update'])->patch('/bulk-update', [ElectionsController::class, 'bulkUpdateElection'])
        ->name('elections.bulk-update');
    Route::middleware(['permission:schoolAdmin.election.view.participants'])->get('/{electionId}/allowed-participants', [ElectionsController::class, 'getAllowedParticipants'])
        ->name('elections.allowed-participants.index');
    Route::middleware(['permission:schoolAdmin.election.add.participants'])->post('/{electionId}/allowed-participants', [ElectionsController::class, 'addAllowedParticipants'])
        ->name('elections.allowed-participants.store');
    Route::middleware(['permission:schoolAdmin.election.add.participants'])->post('/{electionId}/allowed-participants/from/{targetElectionId}', [ElectionsController::class, 'addAllowedParticipantsByOtherElection'])
        ->name('elections.allowed-participants.store-from-other');

    // Voting
    Route::post('/{electionId}/cast-vote', [ElectionsController::class, 'vote'])
        ->name('elections.vote');

    // Election Results
    Route::middleware(['permission:schoolAdmin.election.view.results'])->get('/election/{electionId}/results', [ElectionResultsController::class, 'getElectionResults'])
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

    Route::get('/student/{studentId}/upcoming-elections', [ElectionsController::class, 'getStudentElections'])
        ->name('upcoming-elections.student.get');
    Route::get('/election/{electionId}', [ElectionsController::class, 'getElectionDetails'])->name('get.election-details');



