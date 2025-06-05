<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AnnouncementSettingController;
Route::post('/create', [AnnouncementSettingController::class, 'createSetting'])
->name('announcement-setting.create');
Route::put('/update/{settingId}', [AnnouncementSettingController::class, 'updateSetting'])
->name('announcement-setting.update');
Route::delete('/delete/{settingId}', [AnnouncementSettingController::class, 'deleteSetting'])
->name('announcement-setting.delete');
Route::get('/', [AnnouncementSettingController::class, 'getSettings'])
->name('announcement-setting.index');
