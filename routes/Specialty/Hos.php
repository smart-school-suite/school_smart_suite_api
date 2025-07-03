<?php

use illuminate\Support\Facades\Route;
use App\Http\Controllers\HosController;

// Assign a Head of Specialty
Route::middleware(['permission:schoolAdmin.hos.create'])->post('/', [HosController::class, 'assignHeadOfSpecialty'])
    ->name('head-of-specialties.store');

// Get all assigned Heads of Specialty
Route::middleware(['permission:schoolAdmin.hos.view'])->get('/', [HosController::class, 'getHeadOfSpecialty'])
    ->name('head-of-specialties.index');

// Get details of a specific Head of Specialty assignment
Route::middleware(['permission:schoolAdmin.hos.show'])->get('/{hosId}', [HosController::class, 'getHosDetails'])
    ->name('head-of-specialties.show');

// Remove a specific Head of Specialty assignment
Route::middleware(['permission:schoolAdmin.hos.delete'])->delete('/{hosId}', [HosController::class, 'removeHeadOfSpecialty'])
    ->name('head-of-specialties.destroy');

// Bulk remove Head of Specialty assignments (consider using DELETE with a request body)
Route::middleware(['permission:schoolAdmin.hos.delete'])->delete('/bulk-remove/{hosIds}', [HosController::class, 'bulkRemoveHos'])
    ->name('head-of-specialties.bulk-remove');

// Get all Heads of Specialty (potentially all users who can be assigned)
Route::middleware(['permission:schoolAdmin.specialty.deactivate'])->get('/all-hos', [HosController::class, 'getAllHos'])
    ->name('all-hos.index');
