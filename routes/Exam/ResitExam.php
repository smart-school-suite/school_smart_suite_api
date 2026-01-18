<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ResitExam\ResitExamController;


Route::get('/', [ResitExamController::class, 'getAllResitExams'])
    ->name('resit-exams.index');

Route::get('/{resitExamId}', [ResitExamController::class, 'getResitExamDetails'])
    ->name('resit-exams.details');

Route::put('/{resitExamId}', [ResitExamController::class, 'updateResitExam'])
    ->name('resit-exams.update');

Route::delete('/{resitExamId}', [ResitExamController::class, 'deleteResitExam'])
    ->name('resit-exams.destroy');

Route::post('/{resitExamId}/grading-configs/{gradesConfigId}', [ResitExamController::class, 'addResitExamGrading'])
    ->name('resit-exams.grading-configs.store');

Route::post('/bulk-add-grading-configs', [ResitExamController::class, 'bulkAddExamGrading'])
    ->name('resit-exams.grading-configs.bulk-store');

Route::put('/bulk-update', [ResitExamController::class, 'bulkUpdateResitExam'])
    ->name('resit-exams.bulk-update');

Route::post('/bulk-delete', [ResitExamController::class, 'bulkDeleteResitExam'])
    ->name('resit-exams.bulk-delete');
