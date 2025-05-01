<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SchoolGradeConfigController;

Route::middleware(['auth:sanctum'])->get('/getSchoolGradesConfig', [SchoolGradeConfigController::class, 'getSchoolGradesConfig']);
