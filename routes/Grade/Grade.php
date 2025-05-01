<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GradesController;

Route::middleware(['auth:sanctum'])->post('/create-grade', [GradesController::class, 'createExamGrades']);
Route::middleware(['auth:sanctum'])->get('/grades-for-exams', [GradesController::class, 'getAllGrades']);
Route::middleware(['auth:sanctum'])->put('/update-grade/{grade_id}', [GradesController::class, 'update_grades_scoped']);
Route::middleware(['auth:sanctum'])->delete('/delete-grade/{examId}', [GradesController::class, 'deleteGrades']);
Route::middleware(['auth:sanctum'])->get('/getRelatedExams/{examId}', [GradesController::class, 'getRelatedExams']);
Route::middleware(['auth:sanctum'])->get('/getGradesByExam/{examId}', [GradesController::class, 'getGradesConfigByExam']);
Route::middleware(['auth:sanctum'])->get('/getExamConfigData/{examId}', [GradesController::class, 'getExamConfigData']);
Route::middleware(['auth:sanctum'])->delete('/bulkDeleteGrades/{examIds}', [GradesController::class, 'bulkDeleteGrades']);
Route::middleware(['auth:sanctum'])->post('/createGradeByOtherConfig/{configId}/{targetConfigId}', [GradesController::class, 'createGradesByOtherGrades']);
