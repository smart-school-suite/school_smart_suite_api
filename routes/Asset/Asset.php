<?php

use App\Http\Controllers\Asset\SchoolAdminAvatarController;
use App\Http\Controllers\Asset\SchoolLogoController;
use App\Http\Controllers\Asset\StudentAvatarController;
use App\Http\Controllers\Asset\TeacherAvatarController;
use Illuminate\Support\Facades\Route;

Route::post('/school-admin/upload', [SchoolAdminAvatarController::class, 'uploadAvatar'])->name('schoolAdmin.avatar.upload');
Route::delete('/school-admin/remove', [SchoolAdminAvatarController::class, 'removeAvatar'])->name('schoolAdmin.avatar.remove');

Route::post('/school-logo/upload', [SchoolLogoController::class, 'uploadLogo'])->name('school.logo.upload');
Route::delete('/school-logo/remove', [SchoolLogoController::class, 'removeLogo'])->name('school.logo.remove');

Route::post('/student-avatar/upload', [StudentAvatarController::class, 'uploadAvatar'])->name('student.avatar.upload');
Route::delete('/student-avatar/remove', [StudentAvatarController::class, 'removeAvatar'])->name('student.avatar.remove');

Route::post('/teacher-avatar/upload', [TeacherAvatarController::class, 'uploadAvatar'])->name('teacher.avatar.upload');
Route::delete('/teacher-avatar/remove', [TeacherAvatarController::class, 'removeAvatar'])->name('teacher.avatar.remove');
