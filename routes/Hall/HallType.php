<?php

use App\Http\Controllers\Hall\HallTypeController;
use Illuminate\Support\Facades\Route;

Route::post("/create", [HallTypeController::class, "createHallType"])->name("create.hallType");
Route::put("/update/{hallTypeId}", [HallTypeController::class, "updateHallType"])->name("update.hallType");
Route::delete("/delete/{hallTypeId}", [HallTypeController::class, "deleteHallType"])->name("delete.hallType");
Route::get("/active", [HallTypeController::class, "getActiveHallTypes"])->name("active.hallTypes");
Route::get("/", [HallTypeController::class, "getAllHallTypes"])->name("hallTypes");
Route::post("/deactivate/{hallTypeId}", [HallTypeController::class, "deactivateHallType"])->name("deactivate.hallType");
Route::post("/activate/{hallTypeId}", [HallTypeController::class, "activateHallType"])->name("activate.hallType");
