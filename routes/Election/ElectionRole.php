<?php

use illuminate\Support\Facades\Route;
use App\Http\Controllers\ElectionRolesController;

// Create a new election role
Route::middleware(['permission:schoolAdmin.electionRole.create'])->post('/', [ElectionRolesController::class, 'createElectionRole'])
    ->name('election-roles.store');

// Get all election roles (potentially admin-only)
Route::middleware(['permission:schoolAdmin.electionRole.view'])->get('/', [ElectionRolesController::class, 'getAllElectionRoles'])
    ->name('election-roles.index');

// Get election roles for a specific election
Route::middleware(['permission:student.electionRole.view.election|schoolAdmin.electionRole.view.election'])->get('/elections/{electionId}/roles', [ElectionRolesController::class, 'getElectionRoles'])
    ->name('elections.roles.index');

// Get active election roles for a specific election
Route::middleware(['permission:student.electionRole.view.active.election'])->get('/election/{electionId}/active-roles', [ElectionRolesController::class, 'getActiveRoles'])
    ->name('elections.roles.active');

// Update a specific election role
Route::middleware(['permission:schoolAdmin.electionRole.update'])->put('/{electionRoleId}', [ElectionRolesController::class, 'updateElectionRole'])
    ->name('election-roles.update');

// Delete a specific election role
Route::middleware(['permission:schoolAdmin.electionRole.delete'])->delete('/{electionRoleId}', [ElectionRolesController::class, 'deleteElectionRole'])
    ->name('election-roles.destroy');

// Bulk delete election roles
Route::middleware(['permission:schoolAdmin.electionRole.delete'])->post('/bulk-delete', [ElectionRolesController::class, 'bulkDeleteRole'])
    ->name('election-roles.bulk-delete');

// Bulk update election roles
Route::middleware(['permission:schoolAdmin.electionRole.update'])->patch('/bulk-update', [ElectionRolesController::class, 'bulkUpdateElectionRole'])
    ->name('election-roles.bulk-update');

// Activate a specific election role
Route::middleware(['permission:schoolAdmin.electionRole.activate'])->post('/{electionRoleId}/activate', [ElectionRolesController::class, 'activateRole'])
    ->name('election-roles.activate');

// Deactivate a specific election role
Route::middleware(['permission:schoolAdmin.electionRole.deactivate'])->post('/{electionRoleId}/deactivate', [ElectionRolesController::class, 'deactivateRole'])
    ->name('election-roles.deactivate');

// Bulk activate election roles
Route::middleware(['permission:schoolAdmin.electionRole.activate'])->post('/bulk-activate', [ElectionRolesController::class, 'bulkActivateRole'])
    ->name('election-roles.bulk-activate');

// Bulk deactivate election roles
Route::middleware(['permission:schoolAdmin.electionRole.deactivate'])->post('/bulk-deactivate', [ElectionRolesController::class, 'bulkDeactivateRole'])
    ->name('election-roles.bulk-deactivate');
