<?php

use illuminate\Support\Facades\Route;
use App\Http\Controllers\SchoolBranchSetting\SchoolBranchSettingController;


Route::get('/', [SchoolBranchSettingController::class, 'getSchoolBranchSetting'])->name("get.school.branch.setting");
Route::patch('/resit-setting', [SchoolBranchSettingController::class, 'updateResitSetting'])->name("update.resit.setting");
Route::patch('/timetable-setting', [SchoolBranchSettingController::class, 'updateTimetableSetting'])->name("update.timetable.setting");
Route::patch('/grade-setting', [SchoolBranchSettingController::class, 'updateGradeSetting'])->name("update.grade.setting");
Route::patch('/promotion-setting', [SchoolBranchSettingController::class, 'updatePromotionSetting'])->name("update.promotion.setting");
Route::patch('/election-setting', [SchoolBranchSettingController::class, 'updateElectionSetting'])->name("update.election.setting");
Route::patch('/exam-setting', [SchoolBranchSettingController::class, 'updateExamSetting'])->name("update.exam.setting");
Route::get('/details/{schoolBranchSettingId}', [SchoolBranchSettingController::class, 'getSchoolBranchSettingDetails'])->name("get.school.branch.setting.details");
