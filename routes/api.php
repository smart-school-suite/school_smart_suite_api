<?php

use App\Http\Controllers\TargetAudienceController;
use Illuminate\Http\Request;
use App\Http\Middleware\IdentifyTenant;
use Illuminate\Support\Facades\Route;

Route::middleware([IdentifyTenant::class, 'auth:sanctum'])
->get('/v1/target-audience', [TargetAudienceController::class, 'getTargetAudience'])
->name('get-target-audience');


