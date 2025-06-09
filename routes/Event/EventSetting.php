<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventSettingController;

Route::get('/', [EventSettingController::class, 'getSetting'])->name('event-setting.index');
Route::post('/create', [EventSettingController::class, 'createSetting'])->name('event-setting.create');
Route::delete('/delete/{settingId}', [EventSettingController::class, 'deleteSetting'])->name('event-setting.delete');
Route::put('/update/{settingId}', [EventSettingController::class, 'updateSetting'])->name('event-setting.update');
