<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Announcement\AnnouncementTagController;

Route::get('/', [AnnouncementTagController::class, 'getAnnouncementTags'])->name('announcement-tag.index');
Route::post('/', [AnnouncementTagController::class, 'createTag'])->name('announcement-tag.create');
Route::put('/{tagId}', [AnnouncementTagController::class, 'updateTag'])->name('announcement-tag.update');
Route::delete('/{tagId}', [AnnouncementTagController::class, 'deleteTag'])->name('announcement-tag.delete');
