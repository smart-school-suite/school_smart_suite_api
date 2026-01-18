<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Hall\SpecialtyHallController;


Route::post("/assign", [SpecialtyHallController::class, "assignHallToSpecialty"])->name("assign.Hall");
Route::get("/hall-unassigned/{specialtyId}", [SpecialtyHallController::class, "getAssignableHalls"])->name("assignableHalls");
Route::get("/hall-assigned/{specialtyId}", [SpecialtyHallController::class, "getAssignedHalls"])->name("assignedHalls");
Route::delete("/delete/{specialtyId}", [SpecialtyHallController::class, "removeAllAssignedHalls"])->name("removeAll.AssignedHalls");
Route::post("/remove/assigned", [SpecialtyHallController::class, 'removeAssignedHalls'])->name("remove.assignedHall");
