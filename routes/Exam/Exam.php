<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Exam\ExamController;

// Create a new exam
Route::middleware(['permission:schoolAdmin.exam.create'])->post('/', [ExamController::class, 'createExam'])
    ->name('exams.store');

// Get all exams
Route::middleware(['permission:schoolAdmin.exam.view'])->get('/', [ExamController::class, 'getExams'])
    ->name('exams.index');

// Get details of a specific exam
Route::middleware(['permission:schoolAdmin.exam.show|student.exam.show|teacher.exam.show'])->get('/{examId}', [ExamController::class, 'getExamDetails'])
    ->name('exams.show');

// Update a specific exam
Route::middleware(['permission:schoolAdmin.exam.update'])->put('/{examId}', [ExamController::class, 'updateExam'])
    ->name('exams.update');

// Delete a specific exam
Route::middleware(['permission:schoolAdmin.exam.delete'])->delete('/{examId}', [ExamController::class, 'deleteExam'])
    ->name('exams.destroy');

// Get letter grades associated with an exam
Route::middleware(['permission:schoolAdmin.exam.view.letter.grades'])->get('/{examId}/letter-grades', [ExamController::class, 'associateWeightedMarkWithLetterGrades'])
    ->name('exams.letter-grades');

// Get exams accessed by a specific student
Route::middleware(['permission:schoolAdmin.exam.view.accessed.student'])->get('/students/{studentId}/accessed-exams', [ExamController::class, 'getAccessedExams'])
    ->name('students.accessed-exams.index');

// Add grading configuration to an exam
Route::middleware(['permission:schoolAdmin.exam.add.grade.config'])->post('/{examId}/grading-configs/{gradesConfigId}', [ExamController::class, 'addExamGrading'])
    ->name('exams.grading-configs.store');

// Bulk delete exams
Route::middleware(['permission:schoolAdmin.exam.delete'])->post('/bulk-delete', [ExamController::class, 'bulkDeleteExam'])
    ->name('exams.bulk-delete');

// Bulk update exams
Route::middleware(['permission:schoolAdmin.exam.update'])->patch('/bulk-update', [ExamController::class, 'bulkUpdateExam'])
    ->name('exams.bulk-update');

// Bulk add grading configurations to exams
Route::middleware(['permission:schoolAdmin.exam.add.grade.config'])->post('/bulk-add-grading-configs', [ExamController::class, 'bulkAddExamGrading'])
    ->name('exams.grading-configs.bulk-store');


Route::get('/student/{studentId}/exams-all', [ExamController::class, 'getAllExamsByStudentId'])->name("get.all.exams.by.studentId");
Route::get('/student/{studentId}/semester/{semesterId}', [ExamController::class, "getAllExamsByStudentIdSemesterId"])->name("get.exams.by.studentId.semesteId");
Route::get('/{examId}/grade-scale', [ExamController::class, 'getExamGradeScale'])->name('get.exams.grade-scale');
Route::get('/upcoming/student', [ExamController::class, 'getStudentUpcomingExams'])->name('get.student.upcoming.exams');
