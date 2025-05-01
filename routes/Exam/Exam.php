<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExamsController;

Route::middleware(['auth:sanctum'])->post('/create-exam', [ExamsController::class, 'createExam']);
Route::middleware(['auth:sanctum'])->put('/update-exam/{exam_id}', [ExamsController::class, 'updateExam']);
Route::middleware(['auth:sanctum'])->get('/getexams', [ExamsController::class, 'getExams']);
Route::middleware(['auth:sanctum'])->get('/exam-details/{exam_id}', [ExamsController::class, 'getExamDetails']);
Route::middleware(['auth:sanctum'])->delete('/delete-exams/{exam_id}', [ExamsController::class, 'deleteExam']);
Route::middleware(['auth:sanctum'])->get('/letter-grades/{exam_id}', [ExamsController::class, 'associateWeightedMarkWithLetterGrades']);
Route::middleware(['auth:sanctum'])->get("/accessed_exams/{student_id}", [ExamsController::class, "getAccessedExams"]);
Route::middleware(['auth:sanctum'])->post('/addExamGrading/{examId}/{gradesConfigId}', [ExamsController::class, 'addExamGrading']);
Route::middleware(['auth:sanctum'])->get('/getAllResitExams', [ExamsController::class, 'getResitExams']);
Route::middleware(['auth:sanctum'])->delete('/bulkDeleteExam/{examIds}', [ExamsController::class, 'bulkDeleteExam']);
Route::middleware(['auth:sanctum'])->put('/bulkUpdateExam', [ExamsController::class, 'bulkUpdateExam']);
Route::middleware(['auth:sanctum'])->post('/bulkAddExamGrading', [ExamsController::class, 'bulkAddExamGrading']);
