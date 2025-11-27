<?php

use illuminate\Support\Facades\Route;
use App\Http\Controllers\Election\ElectionRoleController;

// Create a new election role
Route::middleware(['permission:schoolAdmin.electionRole.create'])->post('/', [ElectionRoleController::class, 'createElectionRole'])
    ->name('election-roles.store');

// Get all election roles (potentially admin-only)
Route::middleware(['permission:schoolAdmin.electionRole.view'])->get('/', [ElectionRoleController::class, 'getAllElectionRoles'])
    ->name('election-roles.index');

// Get election roles for a specific election
Route::middleware(['permission:student.electionRole.view.election|schoolAdmin.electionRole.view.election'])->get('/elections/{electionId}/roles', [ElectionRoleController::class, 'getElectionRolesByElectionId'])
    ->name('get.election-roles.by-election.id');

// Get active election roles for a specific election
Route::middleware(['permission:student.electionRole.view.active.election'])->get('/election/{electionId}/active-roles', [ElectionRoleController::class, 'getActiveRoles'])
    ->name('elections.roles.active');

// Update a specific election role
Route::middleware(['permission:schoolAdmin.electionRole.update'])->put('/{electionRoleId}', [ElectionRoleController::class, 'updateElectionRole'])
    ->name('election-roles.update');

// Delete a specific election role
Route::middleware(['permission:schoolAdmin.electionRole.delete'])->delete('/{electionRoleId}', [ElectionRoleController::class, 'deleteElectionRole'])
    ->name('election-roles.destroy');

// Bulk delete election roles
Route::middleware(['permission:schoolAdmin.electionRole.delete'])->post('/bulk-delete', [ElectionRoleController::class, 'bulkDeleteRole'])
    ->name('election-roles.bulk-delete');

// Bulk update election roles
Route::middleware(['permission:schoolAdmin.electionRole.update'])->patch('/bulk-update', [ElectionRoleController::class, 'bulkUpdateElectionRole'])
    ->name('election-roles.bulk-update');

// Activate a specific election role
Route::middleware(['permission:schoolAdmin.electionRole.activate'])->post('/{electionRoleId}/activate', [ElectionRoleController::class, 'activateRole'])
    ->name('election-roles.activate');

// Deactivate a specific election role
Route::middleware(['permission:schoolAdmin.electionRole.deactivate'])->post('/{electionRoleId}/deactivate', [ElectionRoleController::class, 'deactivateRole'])
    ->name('election-roles.deactivate');

// Bulk activate election roles
Route::middleware(['permission:schoolAdmin.electionRole.activate'])->post('/bulk-activate', [ElectionRoleController::class, 'bulkActivateRole'])
    ->name('election-roles.bulk-activate');

// Bulk deactivate election roles
Route::middleware(['permission:schoolAdmin.electionRole.deactivate'])->post('/bulk-deactivate', [ElectionRoleController::class, 'bulkDeactivateRole'])
    ->name('election-roles.bulk-deactivate');

Route::get('/{electionRoleId}', [ElectionRoleController::class, 'getElectionRoleDetails'])->name("get.election.role.details");

Route::get('/student/election/{electionId}', [ElectionRoleController::class, 'getStudentElectionRoles'])->name("get.election.roles");
