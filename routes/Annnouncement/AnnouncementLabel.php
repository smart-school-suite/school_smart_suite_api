<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AnnouncementLabelController;

Route::post('/', [AnnouncementLabelController::class, 'createAnnouncementLabel'])
->name('announcementlabel.create');
Route::get('/', [AnnouncementLabelController::class, 'getAnnouncementLabels'])
->name('announcementLabel.index');
Route::put('/{labelId}', [AnnouncementLabelController::class, 'updateAnnouncementLabel'])
->name('announcementLabel.update');
Route::delete('/{labelId}', [AnnouncementLabelController::class, 'deleteAnnouncementLabel'])
->name('announcementLabel.delete');
