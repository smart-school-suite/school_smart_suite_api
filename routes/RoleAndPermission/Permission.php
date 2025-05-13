<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PermissionController;
use App\Http\Middleware\IdentifyTenant;

// Create a new permission
Route::post('/permissions', [PermissionController::class, 'createPermission'])
->name('permissions.store');

// Get all permissions
Route::get('/permissions', [PermissionController::class, 'getPermission'])
->name('permissions.index');

// Delete a specific permission
Route::delete('/permissions/{permissionId}', [PermissionController::class, 'deletePermission'])
->name('permissions.destroy');

// Update a specific permission
Route::put('/permissions/{permissionId}', [PermissionController::class, 'updatePermission'])
->name('permissions.update');
// Get permissions for a specific school administrator
Route::middleware([IdentifyTenant::class])->get('/school-admins/{schoolAdminId}/permissions', [PermissionController::class, 'getSchoolAdminPermissions'])
->name('school-admins.permissions.index');

// Grant permissions to a specific school administrator
Route::middleware([IdentifyTenant::class])->post('/school-admins/{schoolAdminId}/permissions', [PermissionController::class, 'givePermissionToSchoolAdmin'])
->name('school-admins.permissions.store'); // Using store as it's adding/creating a relationship

// Revoke permissions from a specific school administrator
Route::middleware([IdentifyTenant::class])->delete('/school-admins/{schoolAdminId}/permissions', [PermissionController::class, 'revokePermission'])
->name('school-admins.permissions.destroy');


