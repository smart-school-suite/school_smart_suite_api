<?php

use illuminate\Support\Facades\Route;
use App\Http\Controllers\HosController;

Route::middleware(['auth:sanctum'])->post("/assign-hos", [HosController::class, 'assignHeadOfSpecialty']);
Route::middleware(['auth:sanctum'])->get('/get-assigned-hos', [HosController::class, 'getHeadOfSpecialty']);
Route::middleware(['auth:sanctum'])->delete("/remove-hos/{hosId}", [HosController::class, 'removeHeadOfSpecialty']);
Route::middleware(['auth:sanctum'])->delete("/bulkRemoveHos/{hosId}", [HosController::class, 'bulkRemoveHos']);
Route::middleware(['auth:sanctum'])->get("/getAllHos", [HosController::class, "getAllHos"]);
Route::middleware(['auth:sanctum'])->get("/getHosDetails/{hosId}", [HosController::class, "getHosDetails"]);
