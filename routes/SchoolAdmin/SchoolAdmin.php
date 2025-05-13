<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SchoolAdminController;
use App\Http\Middleware\IdentifyTenant;

// Get all school admins (requires 'view-admin' permission)
Route::get('/school-admins', [SchoolAdminController::class, 'getSchoolAdmin'])
    ->middleware('permission:view-admin')
    ->name('school-admins.index');

// Get details of a specific school admin
Route::get('/school-admins/{schoolAdminId}', [SchoolAdminController::class, 'getSchoolAdminDetails'])
    ->name('school-admins.show');

// Update a specific school admin
Route::put('/school-admins/{schoolAdminId}', [SchoolAdminController::class, 'updateSchoolAdmin'])
    ->name('school-admins.update');

// Delete a specific school admin
Route::delete('/school-admins/{schoolAdminId}', [SchoolAdminController::class, 'deleteSchoolAdmin'])
    ->name('school-admins.destroy');

// Upload profile picture for the authenticated school admin
Route::post('/school-admins/profile-picture', [SchoolAdminController::class, 'uploadProfilePicture'])
    ->name('school-admins.profile-picture.upload');

// Delete profile picture for the authenticated school admin
Route::delete('/school-admins/profile-picture', [SchoolAdminController::class, 'deleteProfilePicture'])
    ->name('school-admins.profile-picture.destroy');

// Deactivate a specific school admin account
Route::post('/school-admins/{schoolAdminId}/deactivate', [SchoolAdminController::class, 'deactivateAccount'])
    ->name('school-admins.deactivate');

// Activate a specific school admin account
Route::post('/school-admins/{schoolAdminId}/activate', [SchoolAdminController::class, 'activateAccount'])
    ->name('school-admins.activate');

// Bulk delete school admins
Route::delete('/school-admins/bulk-delete/{schoolAdminIds}', [SchoolAdminController::class, 'bulkDeleteSchoolAdmin'])
    ->name('school-admins.bulk-delete');

// Bulk update school admins
Route::put('/school-admins/bulk-update', [SchoolAdminController::class, 'bulkUpdateSchoolAdmin'])
    ->name('school-admins.bulk-update');

// Bulk deactivate school admins
Route::post('/school-admins/bulk-deactivate/{schoolAdminIds}', [SchoolAdminController::class, 'bulkDeactivateSchoolAdmin'])
    ->name('school-admins.bulk-deactivate');

// Bulk activate school admins
Route::post('/school-admins/bulk-activate/{schoolAdminIds}', [SchoolAdminController::class, 'bulkActivateSchoolAdmin'])
    ->name('school-admins.bulk-activate');
