<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExamEvaluation\ExamEvaluationController;

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

Route::post('/auto-gen-timetable', [ExamEvaluationController::class, 'autoGenExamTimetable'])->name('auto-gen.exam-timetable');
Route::get('/student/{studentId}/exam/{examId}/timetable', [ExamEvaluationController::class, 'getExamTimetableStudentIdExamId'])->name('get.exam-timetable.studentid.examid');
