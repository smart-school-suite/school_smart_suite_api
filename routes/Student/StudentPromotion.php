<?php

use App\Http\Middleware\IdentifyTenant;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Student\StudentPromotionController;
Route::middleware([IdentifyTenant::class, 'auth:sanctum', 'permission:schoolAdmin.student.promote'])->post('/promote-student', [StudentpromotionController::class, 'promoteStudent']);
