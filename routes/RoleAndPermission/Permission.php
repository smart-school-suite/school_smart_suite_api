<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PermissionController;
use App\Http\Middleware\IdentifyTenant;

// Create a new permission
Route::middleware(['permission:appAdmin.permission.create'])->post('/', [PermissionController::class, 'createPermission'])
->name('permissions.store');

// Get all permissions
Route::get('/', [PermissionController::class, 'getPermission'])
->name('permissions.index');

Route::get('/assignable-permissions/{schoolAdminId}', [PermissionController::class, "getAssignablePermissions"])
->name('assignable-permission.index');

// Delete a specific permission
Route::middleware(['permission:appAdmin.permission.delete'])->delete('/{permissionId}', [PermissionController::class, 'deletePermission'])
->name('permissions.destroy');

// Update a specific permission
Route::middleware(['permission:appAdmin.permission.update'])->put('/{permissionId}', [PermissionController::class, 'updatePermission'])
->name('permissions.update');
// Get permissions for a specific school administrator
Route::middleware([IdentifyTenant::class, 'permission:schoolAdmin.permission.view.schoolAdmin'])->get('/school-admin/{schoolAdminId}', [PermissionController::class, 'getSchoolAdminPermissions'])
->name('school-admins.permissions.index');

// Grant permissions to a specific school administrator
Route::middleware([IdentifyTenant::class, 'permission:schoolAdmin.permission.assign'])->post('/school-admin/{schoolAdminId}', [PermissionController::class, 'givePermissionToSchoolAdmin'])
->name('school-admins.permissions.store'); // Using store as it's adding/creating a relationship

// Revoke permissions from a specific school administrator
Route::middleware([IdentifyTenant::class, 'permission:schoolAdmin.permission.remove'])->delete('/school-admin/{schoolAdminId}', [PermissionController::class, 'revokePermission'])
->name('school-admins.permissions.destroy');


