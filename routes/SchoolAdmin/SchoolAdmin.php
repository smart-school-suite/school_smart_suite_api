<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SchoolAdminController;
use App\Http\Middleware\IdentifyTenant;

Route::middleware([IdentifyTenant::class, 'auth:sanctum',])->put('/update-school-admin/{school_admin_id}', [SchoolAdminController::class, 'updateSchoolAdmin']);
Route::middleware([IdentifyTenant::class, 'auth:sanctum',])->delete('/delete-school-admin/{school_admin_id}', [SchoolAdminController::class, 'deleteSchoolAdmin']);
Route::middleware([IdentifyTenant::class, 'auth:sanctum', 'permission:view-admin'])->get('/get-all-school-admins', [SchoolAdminController::class, 'getSchoolAdmin']);
Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->get('/school-admin/details/{school_admin_id}', [SchoolAdminController::class, 'getSchoolAdminDetails']);
Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->post("/uploadProfilePic", [SchoolAdminController::class, 'uploadProfilePicture']);
Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->delete("/deleteProfilePic", [SchoolAdminController::class, 'deleteProfilePicture']);
Route::middleware('auth:sanctum')->post('/deactivateAccount/{schoolAdminId}', [SchoolAdminController::class, 'deactivateAccount']);
Route::middleware('auth:sanctum')->post("/activateAccount/{schoolAdminId}", [SchoolAdminController::class, 'activateAccount']);
Route::middleware(['auth:sanctum', IdentifyTenant::class])->delete('/bulkDeleteSchoolAdmin/{schoolAdminIds}',  [SchoolAdminController::class, 'bulkDeleteSchoolAdmin']);
Route::middleware(['auth:sanctum', IdentifyTenant::class])->put('/bulkUpdateSchoolAdmin', [SchoolAdminController::class, 'bulkUpdateSchoolAdmin']);
Route::middleware(['auth:sanctum', IdentifyTenant::class])->post('/bulkDeactivateSchoolAdmin/{schoolAdminIds}', [SchoolAdminController::class, 'bulkDeactivateSchoolAdmin']);
Route::middleware(['auth:sanctum', IdentifyTenant::class])->post('/bulkActivateSchoolAdmin/{schoolAdminIds}', [SchoolAdminController::class, 'bulkActivateSchoolAdmin']);
