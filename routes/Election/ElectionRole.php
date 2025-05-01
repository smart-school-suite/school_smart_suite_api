<?php
use illuminate\Support\Facades\Route;
use App\Http\Controllers\ElectionRolesController;

Route::middleware(['auth:sanctum'])->post('/create-role', [ElectionRolesController::class, 'createElectionRole']);
Route::middleware(['auth:sanctum'])->put('/update-election/{election_role_id}', [ElectionRolesController::class, 'updateElectionRole']);
Route::middleware(['auth:sanctum'])->delete('/delete-role/{election_role_id}', [ElectionRolesController::class, 'deleteElectionRole']);
Route::middleware(['auth:sanctum'])->get('/election-roles/{election_id}', [ElectionRolesController::class, 'getElectionRoles']);
Route::middleware(['auth:sanctum'])->get("/getAllRoles", [ElectionRolesController::class, 'getAllElectionRoles']);
Route::middleware(['auth:sanctum'])->delete('/bulkDeleteRoles/{electionRoleIds}', [ElectionRolesController::class, 'bulkDeleteRole']);
Route::middleware(['auth:sanctum'])->put('/bulkUpdateRoles', [ElectionRolesController::class, 'bulkUpdateElectionRole']);
Route::middleware(['auth:sanctum'])->post('/activateRole/{electionRoleId}', [ElectionRolesController::class, 'activateRole']);
Route::middleware(['auth:sanctum'])->post('/deactivateRole/{electionRoleId}', [ElectionRolesController::class, 'deactivateRole']);
Route::middleware(['auth:sanctum'])->post('/bulkActivateRole/{electionRoleIds}', [ElectionRolesController::class, 'bulkActivateRole']);
Route::middleware(['auth:sanctum'])->post('/bulkDeactivateRole/{electionRoleIds}', [ElectionRolesController::class, 'bulkDeactivateRole']);
Route::middleware(['auth:sanctum'])->get('/getActiveRoles/{electionId}', [ElectionRolesController::class, 'getActiveRoles']);
