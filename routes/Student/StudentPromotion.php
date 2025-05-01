<?php

use App\Http\Middleware\IdentifyTenant;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentPromotionController;

Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->post('/promote-student', [StudentpromotionController::class, 'promoteStudent']);
