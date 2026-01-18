<?php
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\IdentifyTenant;
use App\Http\Controllers\RBAC\PermissionController;


Route::post('/', [PermissionController::class, 'createPermission'])
->name('permissions.store');

Route::get('/', [PermissionController::class, 'getPermission'])
->name('permissions.index');

Route::get('/assignable-permissions/{schoolAdminId}', [PermissionController::class, "getAssignablePermissions"])
->name('assignable-permission.index');

Route::delete('/{permissionId}', [PermissionController::class, 'deletePermission'])
->name('permissions.destroy');

Route::put('/{permissionId}', [PermissionController::class, 'updatePermission'])
->name('permissions.update');


Route::get('/school-admin/{schoolAdminId}', [PermissionController::class, 'getSchoolAdminPermissions'])
->name('school-admins.permissions.index');

Route::post('/school-admin/{schoolAdminId}', [PermissionController::class, 'givePermissionToSchoolAdmin'])
->name('school-admins.permissions.store');

Route::middleware([IdentifyTenant::class])->delete('/school-admin/{schoolAdminId}', [PermissionController::class, 'revokePermission'])
->name('school-admins.permissions.destroy');


