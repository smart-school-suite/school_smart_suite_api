<?php

use illuminate\Support\Facades\Route;
use App\Http\Controllers\SchoolBranchSetting\SchoolBranchSettingController;


Route::get('/', [SchoolBranchSettingController::class, 'getSchoolBranchSetting'])->name("get.school.branch.setting");
Route::patch('/update', [SchoolBranchSettingController::class, 'updateSetting'])->name("update.updateSetting");
Route::get('/details/{settingId}', [SchoolBranchSettingController::class, 'getSchoolBranchSettingDetails'])->name("get.school.branch.setting.details");
