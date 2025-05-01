<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SchoolsController;
Route::post('/register', [SchoolsController::class, 'createSchool']);
Route::middleware(['auth:sanctum'])->put('/update_school', [SchoolsController::class, 'updateSchool']);
Route::middleware(['auth:sanctum'])->delete('/delete-school/{school_id}', [SchoolsController::class, 'deleteSchool']);
Route::middleware(['auth:sanctum'])->get('/schoolDetails', [schoolsController::class, 'getSchoolDetails']);
