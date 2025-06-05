<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AnnouncementCategoryController;

Route::get('/', [AnnouncementCategoryController::class, 'getCategories'])->name('annnouncement-category.index');
Route::post('/create', [AnnouncementCategoryController::class, 'createCategory'])->name('announcement-category.create');
Route::put('/update/{categoryId}', [AnnouncementCategoryController::class, 'updateCategory'])->name('announcement-category.update');
Route::delete('/delete/{categoryId}', [AnnouncementCategoryController::class, 'deleteCategory'])->name('announcement-category.delete');
