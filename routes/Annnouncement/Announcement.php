<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\Stats\AnnouncementStatController;
Route::get('/stat/{year}', [AnnouncementStatController::class, 'getAnnouncementStatService'])->name('announcement.stat');
Route::post('/create',  [AnnouncementController::class, 'createAnnoucement'])->name('announcement.create');
Route::get('/{status}', [AnnouncementController::class, 'getAnnouncementByState'])->name('announcement.status.get');
Route::put('/update-content/{announcementId}', [AnnouncementController::class, 'updateAnnouncementContent'])->name('announcement.update.content');
Route::delete('/delete/{announcementId}', [AnnouncementController::class, 'deleteAnnouncement'])->name('announcement.delete');
Route::get('/details/{announcementId}', [AnnouncementController::class, 'getAnnouncementDetails'])->name('announcement.get.details');
Route::get('engagement-stats/{announcementId}', [AnnouncementController::class, 'getAnnouncementEngagementOverview'])->name('get.anouncement.engagement.stats');
Route::get('read-uread/list/{announcementId}', [AnnouncementController::class, 'getAnnouncementReadUnreadList'])->name('get.announcement.read.unread.list');
Route::put('/daft/update', [AnnouncementController::class, 'updateAnnouncementDraft'])->name("draft.announcement.update");
