<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Announcement\AnnouncementCategoryController;
Route::get('/', [AnnouncementCategoryController::class, 'getCategories'])->name('annnouncement-category.index');
Route::post('/', [AnnouncementCategoryController::class, 'createCategory'])->name('announcement-category.create');
Route::put('/{categoryId}', [AnnouncementCategoryController::class, 'updateCategory'])->name('announcement-category.update');
Route::delete('/{categoryId}', [AnnouncementCategoryController::class, 'deleteCategory'])->name('announcement-category.delete');
