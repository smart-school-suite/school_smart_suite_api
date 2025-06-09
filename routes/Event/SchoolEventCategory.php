<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventCategoryController;

Route::post("/create", [EventCategoryController::class, 'createCategory'])->name("event-category.create");
Route::put("/update/{categoryId}", [EventCategoryController::class, 'updateCategory'])->name("event-category.update");
Route::delete("/delete/{categoryId}", [EventCategoryController::class, 'deleteCategory'])->name("event-category.delete");
Route::get("/{status}", [EventCategoryController::class, 'getCategoryByStatus'])->name("event-category.status");
Route::get("/", [EventCategoryController::class, 'getAllCategories'])->name("event-category.index");
Route::get("/deactivate/{categoryId}", [EventCategoryController::class, 'deactivateCategory'])->name("event-category.deactivate");
Route::get("/activate/{categoryId}", [EventCategoryController::class, 'activateCategory'])->name("event-category.activate");

