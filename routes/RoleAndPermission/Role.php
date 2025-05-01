<?php

use App\Http\Middleware\IdentifyTenant;
use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->post('/create-role', [RoleController::class, 'createRole']);
Route::middleware(['auth:sanctum'])->get('/get-roles', [RoleController::class, 'getRoles']);
Route::middleware(['auth:sanctum'])->delete('/delete-roles/{roleId}', [RoleController::class, 'updateRole']);
Route::middleware(['auth:sanctum'])->put('/update-role/{roleId}', [RoleController::class, 'updateRole']);
Route::middleware(['auth:sanctum', IdentifyTenant::class])->post('/assign-role/{schoolAdminId}', [RoleController::class, 'assignRoleSchoolAdmin']);
Route::middleware(['auth:sanctum'])->post('/remove-role/{schoolAdminId}', [RoleController::class, 'removeRoleSchoolAdmin']);
