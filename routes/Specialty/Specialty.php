<?php

use illuminate\Support\Facades\Route;
use App\Http\Controllers\SpecialtyController;

// Create a new specialty
Route::post('/specialties', [SpecialtyController::class, 'createSpecialty'])
    ->name('specialties.store');

// Get all specialties for the authenticated user's school branch
Route::get('/specialties', [SpecialtyController::class, 'getSpecialtiesBySchoolBranch'])
    ->name('specialties.index');

// Get details of a specific specialty
Route::get('/specialties/{specialtyId}', [SpecialtyController::class, 'getSpecialtyDetails'])
    ->name('specialties.show');

// Update a specific specialty
Route::put('/specialties/{specialtyId}', [SpecialtyController::class, 'updateSpecialty'])
    ->name('specialties.update');

// Delete a specific specialty
Route::delete('/specialties/{specialtyId}', [SpecialtyController::class, 'deleteSpecialty'])
    ->name('specialties.destroy');

// Activate a specific specialty
Route::post('/specialties/{specialtyId}/activate', [SpecialtyController::class, 'activateSpecialty'])
    ->name('specialties.activate');

// Deactivate a specific specialty
Route::post('/specialties/{specialtyId}/deactivate', [SpecialtyController::class, 'deactivateSpecialty'])
    ->name('specialties.deactivate');

// Bulk delete specialties
Route::delete('/specialties/bulk-delete/{specialtyIds}', [SpecialtyController::class, 'bulkDeleteSpecialty'])
    ->name('specialties.bulk-delete');

// Bulk update specialties
Route::put('/specialties/bulk-update', [SpecialtyController::class, 'bulkUdateSpecialty'])
    ->name('specialties.bulk-update');

// Bulk activate specialties
Route::post('/specialties/bulk-activate/{specialtyIds}', [SpecialtyController::class, 'bulkActivateSpecialty'])
    ->name('specialties.bulk-activate');

// Bulk deactivate specialties
Route::post('/specialties/bulk-deactivate/{specialtyIds}', [SpecialtyController::class, 'bulkDeactivateSpecialty'])
    ->name('specialties.bulk-deactivate');
