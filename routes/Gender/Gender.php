<?php
use App\Http\Controllers\Gender\GenderController;
use Illuminate\Support\Facades\Route;

Route::post("/create", [GenderController::class, "createGender"])->name("create.gender");
Route::delete("/{genderId}/delete", [GenderController::class, "deleteGender"])->name("delete.gender");
Route::put("/{genderId}/update", [GenderController::class, "updateGender"])->name("update.gender");
Route::post("/{genderId}/activate", [GenderController::class, "activateGender"])->name("active.gender");
Route::post("/", [GenderController::class, "getAllGender"])->name("get.gender");
Route::post("/active", [GenderController::class, "getActiveGender"])->name("get.active.gender");
Route::post("/{genderId}/deactivate", [GenderController::class, "deactivateGender"])->name("deactivate.gender");
