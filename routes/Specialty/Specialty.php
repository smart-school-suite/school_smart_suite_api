<?php

use illuminate\Support\Facades\Route;
use App\Http\Controllers\Specialty\SpecialtyController;

// Create a new specialty
Route::middleware(['permission:schoolAdmin.specialty.create'])->post('/', [SpecialtyController::class, 'createSpecialty'])
    ->name('specialties.store');

// Get all specialties for the authenticated user's school branch
Route::middleware(['permission:schoolAdmin.specialty.view'])->get('/', [SpecialtyController::class, 'getSpecialtiesBySchoolBranch'])
    ->name('specialties.index');

// Get details of a specific specialty
Route::middleware(['permission:schoolAdmin.specialty.show.details'])->get('/{specialtyId}', [SpecialtyController::class, 'getSpecialtyDetails'])
    ->name('specialties.show');

// Update a specific specialty
Route::middleware(['permission:schoolAdmin.specialty.update'])->put('/{specialtyId}', [SpecialtyController::class, 'updateSpecialty'])
    ->name('specialties.update');

// Delete a specific specialty
Route::middleware(['permission:schoolAdmin.specialty.delete'])->delete('/{specialtyId}', [SpecialtyController::class, 'deleteSpecialty'])
    ->name('specialties.destroy');

// Activate a specific specialty
Route::middleware(['permission:schoolAdmin.specialty.activate'])->post('/{specialtyId}/activate', [SpecialtyController::class, 'activateSpecialty'])
    ->name('specialties.activate');

// Deactivate a specific specialty
Route::middleware(['permission:schoolAdmin.specialty.deactivate'])->post('/{specialtyId}/deactivate', [SpecialtyController::class, 'deactivateSpecialty'])
    ->name('specialties.deactivate');

// Bulk delete specialties
Route::middleware(['permission:schoolAdmin.specialty.delete'])->post('/bulk-delete', [SpecialtyController::class, 'bulkDeleteSpecialty'])
    ->name('specialties.bulk-delete');

// Bulk update specialties
Route::middleware(['permission:schoolAdmin.specialty.update'])->patch('/bulk-update', [SpecialtyController::class, 'bulkUdateSpecialty'])
    ->name('specialties.bulk-update');

// Bulk activate specialties
Route::middleware(['permission:schoolAdmin.specialty.activate'])->post('/bulk-activate', [SpecialtyController::class, 'bulkActivateSpecialty'])
    ->name('specialties.bulk-activate');

// Bulk deactivate specialties
Route::middleware(['permission:schoolAdmin.specialty.deactivate'])->post('/bulk-deactivate', [SpecialtyController::class, 'bulkDeactivateSpecialty'])
    ->name('specialties.bulk-deactivate');
