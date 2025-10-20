<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SchoolEvent\SchoolEventCategoryController;

Route::post("/create", [SchoolEventCategoryController::class, 'createSchoolEventCategory'])->name("event-category.create");
Route::put("/update/{eventCategoryId}", [SchoolEventCategoryController::class, 'updateSchoolEventCagory'])->name("event-category.update");
Route::delete("/delete/{eventCategoryId}", [SchoolEventCategoryController::class, 'deleteSchoolEventCategory'])->name("event-category.delete");
Route::get("/", [SchoolEventCategoryController::class, 'getSchoolEventCategory'])->name("event-category.index");
Route::post("/deactivate/{eventCategoryId}", [SchoolEventCategoryController::class, 'deactivateSchoolEventCategory'])->name("event-category.deactivate");
Route::post("/activate/{eventCategoryId}", [SchoolEventCategoryController::class, 'activateSchoolEventCategory'])->name("event-category.activate");
Route::get("/active", [SchoolEventCategoryController::class, 'getActiveSchoolEventCategory'])->name("get.active.event.category");
Route::get("/details/{eventCategoryId}", [SchoolEventCategoryController::class, 'getSchoolEventCategoryDetails'])->name("get.school.event.category.details");
