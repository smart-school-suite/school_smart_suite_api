<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\Stats\AnnouncementStatController;
Route::get('/stat/{year}', [AnnouncementStatController::class, 'getAnnouncementStatService'])->name('announcement.stat');
Route::post('/create',  [AnnouncementController::class, 'createAnnoucement'])->name('announcement.create');
Route::get('/{status}', [AnnouncementController::class, 'getAnnouncementByState'])->name('announcement.status.get');
Route::put('/update-content/{announcementId}', [AnnouncementController::class, 'updateAnnouncementContent'])->name('announcement.update.content');
Route::delete('/delete/{announcementId}', [AnnouncementController::class, 'deleteAnnouncement'])->name('announcement.delete');
