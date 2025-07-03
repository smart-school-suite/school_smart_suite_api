<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SchoolEventSettingController;

Route::get('/', [SchoolEventSettingController::class, 'getSettings'])->name("school-event-setting.index");
Route::put('/{settingId}', [SchoolEventSettingController::class, 'updateSetting'])->name("school-event-setting.update");
