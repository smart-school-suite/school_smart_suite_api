<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Qualification\QualificationController;

Route::get('/all', [QualificationController::class, 'getAllQualifications'])->name('get.allQualification');
Route::get('/active', [QualificationController::class, 'getActiveQualifications'])->name('get.activeQualification');
Route::post('{qualificationId}/deactivate', [QualificationController::class, 'deactivateQualification'])->name('deactivate.qualification');
Route::post('{qualificationId}/activate', [QualificationController::class, 'activateQualification'])->name('activate.qualification');
Route::delete('{qualificationId}/delete', [QualificationController::class, 'deleteQualification'])->name('delete.qualification');
Route::post('/create', [QualificationController::class, 'createQualification'])->name('create.qualification');
Route::put('{qualificationId}/update', [QualificationController::class, 'updateQualification'])->name('create.qualification');
Route::get('/{qualificationId}/details', [QualificationController::class, 'getQualificationDetails'])->name('details.qualification');
