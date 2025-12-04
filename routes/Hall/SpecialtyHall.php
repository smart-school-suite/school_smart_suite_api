<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Hall\SpecialtyHallController;


Route::post("/create", [SpecialtyHallController::class, "assignHallToSpecialty"])->name("assign.specialty.hall");
Route::get("/hall-unassigned/{specialtyId}", [SpecialtyHallController::class, "getAssignableHalls"])->name("get.avialable.assignable.hall");
Route::get("/hall-assigned/{specialtyId}", [SpecialtyHallController::class, "getAssignedHalls"])->name("get.assigned.halls");
Route::delete("/delete/{specialtyHallId}", [SpecialtyHallController::class, "removeAssignedHalls"])->name("delete.assigned.specialty.halls");
