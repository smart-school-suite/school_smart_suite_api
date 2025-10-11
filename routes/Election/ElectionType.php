<?php


use App\Http\Controllers\Election\ElectionTypeController;
use illuminate\Support\Facades\Route;

Route::post('/', [ElectionTypeController::class, 'createElectionType'])->name('create.election.types');
Route::put('/{electionTypeId}', [ElectionTypeController::class, 'updateElectionType'])->name('update.election.type');
Route::delete('/{electionTypeId}', [ElectionTypeController::class, 'deleteElectionType'])->name('delete.election.type');
Route::get('/', [ElectionTypeController::class, 'getElectionType'])->name('get.electiontypes');
Route::post('/activate/{electionTypeId}', [ElectionTypeController::class, 'activateElectionType'])->name('activate.election.types');
Route::post('/deactivate/{electionTypeId}', [ElectionTypeController::class, 'deactivateElectionType'])->name('deactivate.election.type');
Route::get('/active', [ElectionTypeController::class, 'getActiveElectionType'])->name('get.active.election.types');
Route::post('/bulk/activate', [ElectionTypeController::class, 'bulkActivateElectionType'])->name('bulk.activate.election.types');
Route::post('/bulk/deactivate', [ElectionTypeController::class, 'bulkDeactivateElectionType'])->name('bulk.deactivate.election.types');
Route::get('/{electionTypeId}', [ElectionTypeController::class, 'getElectionTypeDetails'])->name('get.election.type.details');
