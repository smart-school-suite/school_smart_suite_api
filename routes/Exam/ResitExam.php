<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ResitExamController;

Route::middleware(['auth:sanctum'])->post('/bulkAddExamGrading', [ResitExamController::class, 'bulkAddExamGrading']);
Route::middleware(['auth:sanctum'])->put('/updateResitExam/{resitExamId}', [ResitExamController::class, 'updateResitExam']);
Route::middleware(['auth:sanctum'])->get('/getAllResitExam', [ResitExamController::class, 'getAllResitExams']);
Route::middleware(['auth:sanctum'])->delete('/deleteResitExam/{resitExamId}', [ResitExamController::class, 'deleteResitExam']);
Route::middleware(['auth:sanctum'])->post('/addResitExamGrading/{resitExamId}/{gradesConfigId}', [ResitExamController::class, 'addResitExamGrading']);
Route::middleware(['auth:sanctum'])->get('/getResitExamDetails', [ResitExamController::class, 'getResitExamDetails']);
Route::middleware(['auth:sanctum'])->delete('/bulkDeleteResitExam/{resitExamIds}', [ResitExamController::class, 'bulkDeleteResitExam']);
Route::middleware(['auth:sanctum'])->put('/bulkUpdateResitExam', [ResitExamController::class, 'bulkUpdateResitExam']);
