<?php

use illuminate\Support\Facades\Route;
use App\Http\Controllers\Election\ElectionRoleController;

Route::post('/', [ElectionRoleController::class, 'createElectionRole'])
    ->name('election-roles.store');

Route::get('/', [ElectionRoleController::class, 'getAllElectionRoles'])
    ->name('election-roles.index');

Route::get('/elections/{electionId}/roles', [ElectionRoleController::class, 'getElectionRolesByElectionId'])
    ->name('get.election-roles.by-election.id');

Route::get('/election/{electionId}/active-roles', [ElectionRoleController::class, 'getActiveRoles'])
    ->name('elections.roles.active');

Route::put('/{electionRoleId}', [ElectionRoleController::class, 'updateElectionRole'])
    ->name('election-roles.update');

Route::delete('/{electionRoleId}', [ElectionRoleController::class, 'deleteElectionRole'])
    ->name('election-roles.destroy');

Route::post('/bulk-delete', [ElectionRoleController::class, 'bulkDeleteRole'])
    ->name('election-roles.bulk-delete');

Route::patch('/bulk-update', [ElectionRoleController::class, 'bulkUpdateElectionRole'])
    ->name('election-roles.bulk-update');

Route::post('/{electionRoleId}/activate', [ElectionRoleController::class, 'activateRole'])
    ->name('election-roles.activate');

Route::post('/{electionRoleId}/deactivate', [ElectionRoleController::class, 'deactivateRole'])
    ->name('election-roles.deactivate');

Route::post('/bulk-activate', [ElectionRoleController::class, 'bulkActivateRole'])
    ->name('election-roles.bulk-activate');

Route::post('/bulk-deactivate', [ElectionRoleController::class, 'bulkDeactivateRole'])
    ->name('election-roles.bulk-deactivate');

Route::get('/{electionRoleId}', [ElectionRoleController::class, 'getElectionRoleDetails'])->name("get.election.role.details");

Route::get('/student/election/{electionId}', [ElectionRoleController::class, 'getStudentElectionRoles'])->name("get.election.roles");
