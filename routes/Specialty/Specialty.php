<?php

use illuminate\Support\Facades\Route;
use App\Http\Controllers\SpecialtyController;

Route::middleware(['auth:sanctum'])->post('/create-specialty', [SpecialtyController::class, 'createSpecialty']);
Route::middleware(['auth:sanctum'])->delete('/delete-specialty/{specialty_id}', [SpecialtyController::class, 'deleteSpecialty']);
Route::middleware(['auth:sanctum'])->put('/update-specialty/{specialty_id}', [SpecialtyController::class, 'updateSpecialty']);
Route::middleware(['auth:sanctum'])->get('/my-specialties', [SpecialtyController::class, 'getSpecialtiesBySchoolBranch']);
Route::middleware(['auth:sanctum'])->get('/specialty-details/{specialty_id}', [SpecialtyController::class, 'getSpecialtyDetails']);
Route::middleware(['auth:sanctum'])->post("/deactivateSpecialty/{specialtyId}", [SpecialtyController::class, "deactivateSpecialty"]);
Route::middleware(['auth:sanctum'])->post("/activateSpecialty/{specialtyId}", [SpecialtyController::class, "activateSpecialty"]);
Route::middleware(['auth:sanctum'])->delete('/bulkDeleteSpecialty/{specialtyIds}', [SpecialtyController::class, 'bulkDeleteSpecialty']);
Route::middleware(['auth:sanctum'])->put('/bulkUpdateSpecialty/{specialtyIds}', [SpecialtyController::class, 'bulkUdateSpecialty']);
Route::middleware(['auth:sanctum'])->post('/bulkDeactivateSpecialty/{specialtyIds}', [SpecialtyController::class, 'bulkDeactivateSpecialty']);
Route::middleware(['auth:sanctum'])->post('/bulkActivateSpecialty/{specialtyIds}', [SpecialtyController::class, 'bulkActivateSpecialty']);
