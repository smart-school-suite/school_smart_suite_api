<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SchoolAdminController;
use App\Http\Middleware\IdentifyTenant;

// Get all school admins (requires 'view-admin' permission)
Route::middleware(['permission:schoolAdmin.schoolAdmin.view'])->get('/school-admins', [SchoolAdminController::class, 'getSchoolAdmin'])
    ->name('school-admins.index');

// Get details of a specific school admin
Route::middleware(['permission:schoolAdmin.schoolAdmin.show'])->get('/school-admins/{schoolAdminId}', [SchoolAdminController::class, 'getSchoolAdminDetails'])
    ->name('school-admins.show');

// Update a specific school admin
Route::middleware(['permission:schoolAdmin.schoolAdmin.update'])->put('/school-admins/{schoolAdminId}', [SchoolAdminController::class, 'updateSchoolAdmin'])
    ->name('school-admins.update');

// Delete a specific school admin
Route::middleware(['permission:schoolAdmin.schoolAdmin.delete'])->delete('/school-admins/{schoolAdminId}', [SchoolAdminController::class, 'deleteSchoolAdmin'])
    ->name('school-admins.destroy');

// Upload profile picture for the authenticated school admin
Route::middleware(['permission:schoolAdmin.schoolAdmin.upload.avatar'])->post('/school-admins/profile-picture', [SchoolAdminController::class, 'uploadProfilePicture'])
    ->name('school-admins.profile-picture.upload');

// Delete profile picture for the authenticated school admin
Route::middleware(['permission:schoolAdmin.schoolAdmin.delete.avatar'])->delete('/school-admins/profile-picture', [SchoolAdminController::class, 'deleteProfilePicture'])
    ->name('school-admins.profile-picture.destroy');

// Deactivate a specific school admin account
Route::middleware(['permission:schoolAdmin.schoolAdmin.deactivate'])->post('/school-admins/{schoolAdminId}/deactivate', [SchoolAdminController::class, 'deactivateAccount'])
    ->name('school-admins.deactivate');

// Activate a specific school admin account
Route::middleware(['permission:schoolAdmin.schoolAdmin.activate'])->post('/school-admins/{schoolAdminId}/activate', [SchoolAdminController::class, 'activateAccount'])
    ->name('school-admins.activate');

// Bulk delete school admins
Route::middleware(['permission:schoolAdmin.schoolAdmin.delete'])->post('/school-admins/bulk-delete', [SchoolAdminController::class, 'bulkDeleteSchoolAdmin'])
    ->name('school-admins.bulk-delete');

// Bulk update school admins
Route::middleware(['permission:schoolAdmin.schoolAdmin.update'])->patch('/school-admins/bulk-update', [SchoolAdminController::class, 'bulkUpdateSchoolAdmin'])
    ->name('school-admins.bulk-update');

// Bulk deactivate school admins
Route::middleware(['permission:schoolAdmin.schoolAdmin.deactivate'])->post('/school-admins/bulk-deactivate', [SchoolAdminController::class, 'bulkDeactivateSchoolAdmin'])
    ->name('school-admins.bulk-deactivate');

// Bulk activate school admins
Route::middleware(['permission:schoolAdmin.schoolAdmin.activate'])->post('/school-admins/bulk-activate', [SchoolAdminController::class, 'bulkActivateSchoolAdmin'])
    ->name('school-admins.bulk-activate');
