<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Invigilator\InvigilatorController;

Route::post("/create", [InvigilatorController::class, "createInvigilators"])->name("create.invigilator");
Route::post("/remove", [InvigilatorController::class, "removeInvigilators"])->name("remove.invigilator");
Route::get("/", [InvigilatorController::class, "getInvigilators"])->name("get.invigilators");
Route::get("/potential", [InvigilatorController::class, "getPotInvigilators"])->name("get.potentialInvigilators");
