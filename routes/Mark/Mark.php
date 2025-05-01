<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MarksController;

Route::middleware(['auth:sanctum'])->post('/add-ca-scores', [MarksController::class, 'createCaMark']);
Route::middleware(['auth:sanctum'])->post('/add-exam-scores', [MarksController::class, 'createExamMark']);
Route::middleware(['auth:sanctum'])->put("/update-ca-score", [MarksController::class, 'updateCaMark']);
Route::middleware(['auth:sanctum'])->put("/update-exam-score", [MarksController::class, 'updateExamMark']);
Route::middleware(['auth:sanctum'])->delete('/delete-student-mark/{mark_id}', [MarksController::class, 'deleteMark']);
Route::middleware(['auth:sanctum'])->get('/scores-exam/{student_id}/{exam_id}', [MarksController::class, 'getMarksByExamStudent']);
Route::middleware(['auth:sanctum'])->get("/scores-exam/student", [MarksController::class, 'getMarkDetails']);
Route::middleware(['auth:sanctum'])->get("/score-details/{mark_id}", [MarksController::class, 'getMarkDetails']);
Route::middleware(['auth:sanctum'])->get("/accessed-courses/{examId}", [MarksController::class, "getAccessedCoursesWithLettergrades"]);
Route::middleware(['auth:sanctum'])->get("/prepareCaResultsByExam/{examId}/{studentId}", [MarksController::class, 'prepareCaResultsByExam']);
Route::middleware(['auth:sanctum'])->get("/prepareCaData/{examId}/{studentId}", [MarksController::class, 'prepareCaData']);
Route::middleware(['auth:sanctum'])->get("/prepareExamData/{examId}/{studentId}", [MarksController::class, 'prepareExamData']);
