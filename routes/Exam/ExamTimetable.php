<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExamEvaluation\ExamEvaluationController;
use App\Http\Controllers\ExamTimetable\ExamTimetableGenerationController;

// Registered before GET /{examId}, which would otherwise catch them.
Route::get('/constraints', [ExamTimetableGenerationController::class, 'getConstraints'])->name('examTimetable.constraints');
Route::get('/exam/{examId}/scheduling-data', [ExamTimetableGenerationController::class, 'getSchedulingData'])->name('examTimetable.schedulingData');

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

// Automatic generation, following the semester timetable flow.
Route::post('/generate', [ExamTimetableGenerationController::class, 'generateTimetable'])->name('examTimetable.generate');
Route::get('/version/{versionId}/status', [ExamTimetableGenerationController::class, 'getTimetableStatus'])->name('examTimetable.status');
Route::get('/version/{versionId}/errors', [ExamTimetableGenerationController::class, 'getErrors'])->name('examTimetable.errors');
Route::get('/version/{versionId}/slots', [ExamTimetableGenerationController::class, 'getTimetableSlots'])->name('examTimetable.slots');
Route::get('/version/{versionId}/payload', [ExamTimetableGenerationController::class, 'getTimetableRequestPayload'])->name('examTimetable.payload');
Route::get('/version/{versionId}/raw-diagnostics', [ExamTimetableGenerationController::class, 'getRawDiagnostics'])->name('examTimetable.rawDiagnostics');
Route::get('/version/{versionId}/solutions', [ExamTimetableGenerationController::class, 'getTimetableSolutions'])->name('examTimetable.solutions');
Route::post('/version/{versionId}/solutions/apply', [ExamTimetableGenerationController::class, 'applySolutions'])->name('examTimetable.solutions.apply');
Route::post('/version/{versionId}/activate', [ExamTimetableGenerationController::class, 'activateTimetableVersion'])->name('examTimetable.version.activate');
Route::get('/version/{versionId}/activation-check', [ExamTimetableGenerationController::class, 'checkTimetableActivation'])->name('examTimetable.version.activationCheck');
Route::get('/exam/{examId}/active-timetable', [ExamTimetableGenerationController::class, 'getActiveTimetable'])->name('examTimetable.active.get');
