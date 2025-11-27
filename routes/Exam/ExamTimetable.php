<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExamEvaluation\ExamEvaluationController;

// Create a new exam timetable for a specific exam
Route::middleware(['permission:schoolAdmin.exam.timetable.create'])->post('/exam/{examId}/timetable', [ExamEvaluationController::class, 'createTimetable'])
    ->name('exams.timetable.store');

// Update the exam timetable (requires request body to identify which entry to update)
Route::middleware(['permission:schoolAdmin.exam.timetable.update'])->patch('/exam-timetable', [ExamEvaluationController::class, 'updateTimetable'])
    ->name('exam-timetable.update');

// Get exam timetable by level and specialty
Route::middleware(['permission:schoolAdmin.exam.timetable.view'])->get('/{examId}', [ExamEvaluationController::class, 'generateExamTimetable'])
    ->name('generate-exam-timetable.index');

// Get course data for preparing an exam timetable
Route::middleware(['permission:schoolAdmin.exam.timetable.course.data'])->get('/exams/{examId}/timetable/data', [ExamEvaluationController::class, 'prepareExamTimeTableData'])
    ->name('exams.timetable.data');

// Delete a specific entry from the exam timetable
Route::middleware(['permission:schoolAdmin.exam.timetable.delete'])->delete('/entry/{entryId}', [ExamEvaluationController::class, 'deleteTimetableEntry'])
    ->name('exam-timetable.entries.destroy');

// Delete the entire exam timetable for a specific exam
Route::middleware(['permission:schoolAdmin.exam.timetable.delete'])->delete('/exam/{examId}/timetable', [ExamEvaluationController::class, 'deleteTimetable'])
    ->name('exams.timetable.destroy');

Route::post('/auto-gen-timetable', [ExamEvaluationController::class, 'autoGenExamTimetable'])->name('auto-gen.exam-timetable');
Route::get('/student/{studentId}/exam/{examId}/timetable', [ExamEvaluationController::class, 'getExamTimetableStudentIdExamId'])->name('get.exam-timetable.studentid.examid');
