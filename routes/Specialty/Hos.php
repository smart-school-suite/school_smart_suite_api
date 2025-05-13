<?php

use illuminate\Support\Facades\Route;
use App\Http\Controllers\HosController;

// Assign a Head of Specialty
Route::post('/head-of-specialties', [HosController::class, 'assignHeadOfSpecialty'])
    ->name('head-of-specialties.store');

// Get all assigned Heads of Specialty
Route::get('/head-of-specialties', [HosController::class, 'getHeadOfSpecialty'])
    ->name('head-of-specialties.index');

// Get details of a specific Head of Specialty assignment
Route::get('/head-of-specialties/{hosId}', [HosController::class, 'getHosDetails'])
    ->name('head-of-specialties.show');

// Remove a specific Head of Specialty assignment
Route::delete('/head-of-specialties/{hosId}', [HosController::class, 'removeHeadOfSpecialty'])
    ->name('head-of-specialties.destroy');

// Bulk remove Head of Specialty assignments (consider using DELETE with a request body)
Route::delete('/head-of-specialties/bulk-remove/{hosIds}', [HosController::class, 'bulkRemoveHos'])
    ->name('head-of-specialties.bulk-remove');

// Get all Heads of Specialty (potentially all users who can be assigned)
Route::get('/all-hos', [HosController::class, 'getAllHos'])
    ->name('all-hos.index');
