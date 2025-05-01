<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExamTypecontroller;
use App\Http\Middleware\IdentifyTenant;

Route::middleware(['auth:sanctum'])->post('/create-exam-type', [ExamTypecontroller::class, 'createExamType']);
Route::middleware(['auth:sanctum', IdentifyTenant::class])->get('/exam_types', [ExamTypecontroller::class, 'getExamType']);
Route::middleware(['auth:sanctum'])->delete('/exam-type/{exam_id}', [ExamTypecontroller::class, 'deleteExamType']);
Route::middleware(['auth:sanctum'])->put('/update-exam-type/{exam_id}', [ExamTypecontroller::class, 'updateExamType']);
