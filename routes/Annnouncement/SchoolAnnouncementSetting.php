<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SchoolAnnouncementSettingController;

Route::get('/', [SchoolAnnouncementSettingController::class, 'getSchoolAnnouncementSettings'])
->name('school-announcement-setting.index');
Route::patch('/update/{settingId}', [SchoolAnnouncementSettingController::class, 'updateSchoolAnnouncement'])
->name('school-announcement-setting.update');
