<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentResultController;

Route::middleware(['auth:sanctum'])->get('/getAllStudentResults', [StudentResultController::class, 'getAllStudentResults']);
Route::middleware(['auth:sanctum'])->get('getResultByStudent/{examId}/{studentId}', [StudentResultController::class, 'getMyResults']);
Route::middleware(['auth:sanctum'])->get("/getStandingsByExam/{examId}", [StudentResultController::class, 'getStandingsByExam']);
Route::middleware(['auth:sanctum'])->get("/generateStandingsByExamPdf/{examId}", [StudentResultController::class, 'generateStudentResultStandingPdfByExam']);
Route::middleware(['auth:sanctum'])->get('/generateResultByStudentPdf/{examId}/{studentId}', [StudentResultController::class, 'generateStudentResultPdf']);
