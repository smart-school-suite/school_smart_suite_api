<?php

use App\Http\Middleware\IdentifyTenant;
use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;

// Create a new role
Route::middleware(['permission:appAdmin.role.create'])->post('/', [RoleController::class, 'createRole'])
->name('roles.store');

// Get all roles
Route::get('/', [RoleController::class, 'getRoles'])
->name('roles.index');

// Delete a specific role
Route::middleware(['permission:appAdmin.role.delete'])->delete('/{roleId}', [RoleController::class, 'deleteRole']) // Assuming 'updateRole' in delete route was a typo
->name('roles.destroy');

// Update a specific role
Route::middleware(['permission:appAdmin.role.update'])->put('/{roleId}', [RoleController::class, 'updateRole'])
->name('roles.update');

// Assign a role to a school administrator
Route::middleware([ IdentifyTenant::class, 'permission:schoolAdmin.role.assign'])->post('/school-admins/{schoolAdminId}', [RoleController::class, 'assignRoleSchoolAdmin'])
->name('school-admins.roles.store'); // Using 'store' as it creates an association

// Remove a role from a school administrator
Route::middleware(['permission:schoolAdmin.role.remove'])->post('/school-admins/{schoolAdminId}', [RoleController::class, 'removeRoleSchoolAdmin'])
->name('school-admins.roles.destroy');

