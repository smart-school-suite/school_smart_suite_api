<?php

use App\Http\Middleware\IdentifyTenant;
use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;

// Create a new role
Route::post('/roles', [RoleController::class, 'createRole'])
->name('roles.store');

// Get all roles
Route::get('/roles', [RoleController::class, 'getRoles'])
->name('roles.index');

// Delete a specific role
Route::delete('/roles/{roleId}', [RoleController::class, 'deleteRole']) // Assuming 'updateRole' in delete route was a typo
->name('roles.destroy');

// Update a specific role
Route::put('/roles/{roleId}', [RoleController::class, 'updateRole'])
->name('roles.update');

// Assign a role to a school administrator
Route::middleware([ IdentifyTenant::class])->post('/school-admins/{schoolAdminId}/roles', [RoleController::class, 'assignRoleSchoolAdmin'])
->name('school-admins.roles.store'); // Using 'store' as it creates an association

// Remove a role from a school administrator
Route::post('/school-admins/{schoolAdminId}/roles', [RoleController::class, 'removeRoleSchoolAdmin'])
->name('school-admins.roles.destroy');

