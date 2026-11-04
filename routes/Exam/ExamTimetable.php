<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExamEvaluation\ExamEvaluationController;
use App\Http\Controllers\ExamTimetable\ExamTimetableController;

// Scheduler (same flow and endpoints as /semester-timetable)
Route::post('/generate', [ExamTimetableController::class, 'generate'])->name('examTimetable.generate');
Route::get('/version/{versionId}/status', [ExamTimetableController::class, 'getTimetableStatus'])->name('examTimetable.status');
Route::get('/version/{versionId}/errors', [ExamTimetableController::class, 'getErrors'])->name('examTimetable.errors');
Route::get('/version/{versionId}/slots', [ExamTimetableController::class, 'getTimetableSlots'])->name('examTimetable.slots');
Route::get('/version/{versionId}/payload', [ExamTimetableController::class, 'getTimetableRequestPayload'])->name('examTimetable.payload');
Route::get('/version/{versionId}/parsed-diagnostics', [ExamTimetableController::class, 'getParsedDiagnostics'])->name('examTimetable.parsedDiagnostics');
Route::get('/version/{versionId}/raw-diagnostics', [ExamTimetableController::class, 'getRawDiagnostics'])->name('examTimetable.rawDiagnostics');
Route::get('/version/{versionId}/solutions', [ExamTimetableController::class, 'getTimetableSolutions'])->name('examTimetable.solutions');
Route::post('/version/{versionId}/solutions/apply', [ExamTimetableController::class, 'applySolutions'])->name('examTimetable.solutions.apply');
Route::post('/create/active', [ExamTimetableController::class, 'createActiveExamTimetable'])->name('examTimetable.active.create');
Route::post('/version/{versionId}/activate', [ExamTimetableController::class, 'activateTimetableVersion'])->name('examTimetable.version.activate');
Route::get('/version/{versionId}/activation-check', [ExamTimetableController::class, 'checkTimetableActivation'])->name('examTimetable.version.activationCheck');
Route::get('/exam/{examId}/active-timetable', [ExamTimetableController::class, 'getActiveTimetable'])->name('examTimetable.active.get');

// Manual exam timetable

Route::post('/exam/{examId}/timetable', [ExamEvaluationController::class, 'createTimetable'])
    ->name('exams.timetable.store');

Route::patch('/exam-timetable', [ExamEvaluationController::class, 'updateTimetable'])
    ->name('exam-timetable.update');

Route::middleware(['permission:schoolAdmin.exam.timetable.view'])->get('/{examId}', [ExamEvaluationController::class, 'generateExamTimetable'])
    ->name('generate-exam-timetable.index');

Route::middleware(['permission:schoolAdmin.exam.timetable.course.data'])->get('/exams/{examId}/timetable/data', [ExamEvaluationController::class, 'prepareExamTimeTableData'])
    ->name('exams.timetable.data');

Route::middleware(['permission:schoolAdmin.exam.timetable.delete'])->delete('/entry/{entryId}', [ExamEvaluationController::class, 'deleteTimetableEntry'])
    ->name('exam-timetable.entries.destroy');

Route::middleware(['permission:schoolAdmin.exam.timetable.delete'])->delete('/exam/{examId}/timetable', [ExamEvaluationController::class, 'deleteTimetable'])
    ->name('exams.timetable.destroy');

Route::get('/student/{studentId}/exam/{examId}/timetable', [ExamEvaluationController::class, 'getExamTimetableStudentIdExamId'])->name('get.exam-timetable.studentid.examid');
