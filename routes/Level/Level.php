<?php

use App\Http\Controllers\EducationLevelsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->post('/create-level', [EducationLevelsController::class, ' createEducationLevel']);
Route::middleware(['auth:sanctum'])->put('/update-level/{level_id}', [EducationLevelsController::class, 'updateEducationLevel']);
Route::middleware(['auth:sanctum'])->delete('/delete-level/{level_id}', [EducationLevelsController::class, 'deleteEducationLevel']);
Route::middleware(['auth:sanctum'])->get('/education-levels', [EducationLevelsController::class, 'getEducationLevel']);
