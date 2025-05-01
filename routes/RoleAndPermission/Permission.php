<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PermissionController;
use App\Http\Middleware\IdentifyTenant;

Route::middleware(['auth:sanctum'])->post('/create-permission', [PermissionController::class, 'createPermission']);
Route::middleware(['auth:sanctum'])->get("/get-permissions", [PermissionController::class, "getPermission"]);
Route::middleware(['auth:sanctum'])->delete("/delete-permission/{permissionId}", [PermissionController::class, 'deletePermission']);
Route::middleware(['auth:sanctum'])->put('/update-permission/{permissionId}', [PermissionController::class, "updatePermission"]);
Route::middleware(['auth:sanctum', IdentifyTenant::class])->get('/get-schooladmin/permissions/{schoolAdminId}', [PermissionController::class, "getSchoolAdminPermissions"]);
Route::middleware(['auth:sanctum', IdentifyTenant::class])->post('/grant-schoolAdmin-permissions/{schoolAdminId}', [PermissionController::class, 'givePermissionToSchoolAdmin']);
Route::middleware(['auth:sanctum', IdentifyTenant::class])->post("/revoke-schoolAdmin-permissions/{schoolAdminId}", [PermissionController::class, 'revokePermission']);
