<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Exam\ExamController;

Route::post('/', [ExamController::class, 'createExam'])
    ->name('exams.store');

Route::get('/', [ExamController::class, 'getExams'])
    ->name('exams.index');

// Before /{examId}. {examId} only matches a UUID, so an unknown path is a
// 404 for the route, never "Exam Not Found".
Route::get('/creation-options', [ExamController::class, 'getCreationOptions'])
    ->name('exams.creation-options');

Route::get('/{examId}', [ExamController::class, 'getExamDetails'])
    ->name('exams.show')
    ->whereUuid('examId');

Route::get('/related-ca/school-year/{schoolYearId}/exam-type/{examTypeId}', [ExamController::class, 'getRelatedCaExam'])
    ->name('get.relatedExams');

Route::get('/{examId}/update-rules', [ExamController::class, 'getUpdateRules'])
    ->name('exams.update-rules')
    ->whereUuid('examId');

Route::put('/{examId}', [ExamController::class, 'updateExam'])
    ->name('exams.update')
    ->whereUuid('examId');

Route::delete('/{examId}', [ExamController::class, 'deleteExam'])
    ->name('exams.destroy')
    ->whereUuid('examId');

Route::get('/{examId}/letter-grades', [ExamController::class, 'associateWeightedMarkWithLetterGrades'])
    ->name('exams.letter-grades')
    ->whereUuid('examId');

Route::get('/students/{studentId}/accessed-exams', [ExamController::class, 'getAccessedExams'])
    ->name('students.accessed-exams.index');

Route::post('/{examId}/grade-scale/{gradeScaleCategoryId}', [ExamController::class, 'addExamGradeScale'])
    ->name('exams.grading-configs.store')
    ->whereUuid('examId');

Route::post('/bulk-delete', [ExamController::class, 'bulkDeleteExam'])
    ->name('exams.bulk-delete');

Route::patch('/bulk-update', [ExamController::class, 'bulkUpdateExam'])
    ->name('exams.bulk-update');

Route::post('/bulk-add-grading-configs', [ExamController::class, 'bulkAddExamGrading'])
    ->name('exams.grading-configs.bulk-store');


Route::get('/student/{studentId}/exams-all', [ExamController::class, 'getAllExamsByStudentId'])->name("get.all.exams.by.studentId");
Route::get('/student/{studentId}/semester/{semesterId}', [ExamController::class, "getAllExamsByStudentIdSemesterId"])->name("get.exams.by.studentId.semesteId");
Route::get('/{examId}/grade-scale', [ExamController::class, 'getExamGradeScale'])->name('get.exams.grade-scale')
    ->whereUuid('examId');
Route::get('/upcoming/student', [ExamController::class, 'getStudentUpcomingExams'])->name('get.student.upcoming.exams');
