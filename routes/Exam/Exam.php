<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExamsController;

// Create a new exam
Route::middleware(['permission:schoolAdmin.exam.create'])->post('/exams', [ExamsController::class, 'createExam'])
    ->name('exams.store');

// Get all exams
Route::middleware(['permission:schoolAdmin.exam.view'])->get('/exams', [ExamsController::class, 'getExams'])
    ->name('exams.index');

// Get details of a specific exam
Route::middleware(['permission:schoolAdmin.exam.show|student.exam.show|teacher.exam.show'])->get('/exams/{examId}', [ExamsController::class, 'getExamDetails'])
    ->name('exams.show');

// Update a specific exam
Route::middleware(['permission:schoolAdmin.exam.update'])->put('/exams/{examId}', [ExamsController::class, 'updateExam'])
    ->name('exams.update');

// Delete a specific exam
Route::middleware(['permission:schoolAdmin.exam.delete'])->delete('/exams/{examId}', [ExamsController::class, 'deleteExam'])
    ->name('exams.destroy');

// Get letter grades associated with an exam
Route::middleware(['permission:schoolAdmin.exam.view.letter.grades'])->get('/exams/{examId}/letter-grades', [ExamsController::class, 'associateWeightedMarkWithLetterGrades'])
    ->name('exams.letter-grades');

// Get exams accessed by a specific student
Route::middleware(['permission:schoolAdmin.exam.view.accessed.student'])->get('/students/{studentId}/accessed-exams', [ExamsController::class, 'getAccessedExams'])
    ->name('students.accessed-exams.index');

// Add grading configuration to an exam
Route::middleware(['permission:schoolAdmin.exam.add.grade.config'])->post('/exams/{examId}/grading-configs/{gradesConfigId}', [ExamsController::class, 'addExamGrading'])
    ->name('exams.grading-configs.store');

// Bulk delete exams
Route::middleware(['permission:schoolAdmin.exam.delete'])->post('/exams/bulk-delete', [ExamsController::class, 'bulkDeleteExam'])
    ->name('exams.bulk-delete');

// Bulk update exams
Route::middleware(['permission:schoolAdmin.exam.update'])->patch('/exams/bulk-update', [ExamsController::class, 'bulkUpdateExam'])
    ->name('exams.bulk-update');

// Bulk add grading configurations to exams
Route::middleware(['permission:schoolAdmin.exam.add.grade.config'])->post('/exams/bulk-add-grading-configs', [ExamsController::class, 'bulkAddExamGrading'])
    ->name('exams.grading-configs.bulk-store');
