<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SchoolGradeConfigController;
use App\Http\Middleware\IdentifyTenant;

Route::middleware(['auth:sanctum', IdentifyTenant::class])->get('/getSchoolGradesConfig', [SchoolGradeConfigController::class, 'getSchoolGradesConfig']);
