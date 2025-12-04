<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Announcement\AnnouncementLabelController;
use App\Http\Middleware\SchoolAdminAccountControl\CheckSchoolAdminAccountStatus;

Route::middleware([CheckSchoolAdminAccountStatus::class])->post('/', [AnnouncementLabelController::class, 'createAnnouncementLabel'])
->name('announcementlabel.create');
Route::get('/', [AnnouncementLabelController::class, 'getAnnouncementLabels'])
->name('announcementLabel.index');
Route::put('/{labelId}', [AnnouncementLabelController::class, 'updateAnnouncementLabel'])
->name('announcementLabel.update');
Route::delete('/{labelId}', [AnnouncementLabelController::class, 'deleteAnnouncementLabel'])
->name('announcementLabel.delete');
