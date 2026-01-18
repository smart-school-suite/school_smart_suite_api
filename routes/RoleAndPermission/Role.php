<?php

use App\Http\Middleware\IdentifyTenant;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RBAC\RoleController;

Route::middleware(['permission:appAdmin.role.create'])->post('/', [RoleController::class, 'createRole'])
->name('roles.store');

Route::get('/', [RoleController::class, 'getRoles'])
->name('roles.index');


Route::delete('/{roleId}', [RoleController::class, 'deleteRole'])
->name('roles.destroy');


Route::put('/{roleId}', [RoleController::class, 'updateRole'])
->name('roles.update');

Route::middleware([ IdentifyTenant::class])->post('/school-admins/{schoolAdminId}', [RoleController::class, 'assignRoleSchoolAdmin'])
->name('school-admins.roles.store');

Route::post('/school-admins/{schoolAdminId}', [RoleController::class, 'removeRoleSchoolAdmin'])
->name('school-admins.roles.destroy');

