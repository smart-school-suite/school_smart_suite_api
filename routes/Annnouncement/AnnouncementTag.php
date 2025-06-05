<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AnnouncementTagController;

Route::get('/', [AnnouncementTagController::class, 'getAnnouncementTags'])->name('announcement-tag.index');
Route::post('/create', [AnnouncementTagController::class, 'createTag'])->name('announcement-tag.create');
Route::put('/update/{tagId}', [AnnouncementTagController::class, 'updateTag'])->name('announcement-tag.update');
Route::delete('/delete/{tagId}', [AnnouncementTagController::class, 'deleteTag'])->name('announcement-tag.delete');
