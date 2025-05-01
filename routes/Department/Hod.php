<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HodController;
Route::middleware(['auth:sanctum'])->get("/getHodDetails/{hodId}", [HodController::class, "getHodDetails"]);
Route::middleware(['auth:sanctum'])->delete('/bulkRemoveHods/{hodIds}', [HodController::class, 'bulkRemoveHod']);
Route::middleware(['auth:sanctum'])->get('/get-hods', [HodController::class, 'getHods']);
Route::middleware(['auth:sanctum'])->delete("/delete-hod/{hodId}", [HodController::class, 'removeHod']);
Route::middleware(['auth:sanctum'])->post('/assign-hod', [HodController::class, 'assignHeadOfDepartment']);
Route::middleware(['auth:sanctum'])->get("/getAllHods", [HodController::class, "getAllHods"]);
