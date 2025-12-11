<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ActivationCode\ActivationCodeController;

Route::post('/purchase', [ActivationCodeController::class, 'purchaseActivationCode'])->name("purchase.activationCode");
Route::get('/school', [ActivationCodeController::class, 'getSchoolBranchActivationCodes'])->name("get.schoolBranchActivationCode");
Route::post('/student/activate', [ActivationCodeController::class, 'activateStudentAccount'])->name("activate.studentAccount.activationCode");
Route::post('/teacher/activate', [ActivationCodeController::class, 'activateTeacherAccount'])->name("activate.teacherAccount.activationCode");
