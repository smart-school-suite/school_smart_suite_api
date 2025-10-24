<?php

use illuminate\Support\Facades\Route;
use App\Http\Controllers\SchoolBranchSetting\SchoolBranchSettingController;


Route::get('/', [SchoolBranchSettingController::class, 'getSchoolBranchSetting'])->name("get.school.branch.setting");
Route::get('/test', [SchoolBranchSettingController::class, 'testSettingController']);
