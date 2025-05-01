<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentBatchcontroller;

Route::middleware(['auth:sanctum'])->post('/create-batch', [StudentBatchcontroller::class, 'createStudentBatch']);
Route::middleware(['auth:sanctum'])->get('/student-batches', [StudentBatchcontroller::class, 'getStudentBatch']);
Route::middleware(['auth:sanctum'])->delete('/delete-batch/{batch_id}', [StudentBatchcontroller::class, 'deleteStudentBatch']);
Route::middleware(['auth:sanctum'])->put('/update-batch/{batch_id}', [StudentBatchcontroller::class, 'updateStudentBatch']);
Route::middleware(['auth:sanctum'])->post('/activateStudentBatch/{batchId}', [StudentBatchController::class, 'activateStudentBatch']);
Route::middleware(['auth:sanctum'])->post('/deactivateStudentBatch/{batchId}', [StudentBatchController::class, 'deactivateStudentBatch']);
Route::middleware(['auth:sanctum'])->post('/assignGraduationDatesByBatch', [StudentBatchController::class, 'assignGradDatesBySpecialty']);
Route::middleware(['auth:sanctum'])->get('/getStudentGraduationDatesByBatch/{batchId}', [StudentBatchController::class, 'getGraduationDatesByBatch']);
Route::middleware(['auth:sanctum'])->delete("/bulkDeleteStudentBatch/{batchIds}", [StudentBatchController::class, 'bulkDeleteStudentBatch']);
Route::middleware(['auth:sanctum'])->post("/bulkActivateStudentBatch/{batchIds}", [StudentBatchController::class, 'bulkActivateStudentBatch']);
Route::middleware(['auth:sanctum'])->post("/bulkDeactivateStudentBatch/{batchIds}", [StudentBatchController::class, 'bulkDeactivateStudentBatch']);
Route::middleware(['auth:sanctum'])->put("/bulkUpdateStudentBatch", [StudentBatchController::class, 'bulkUpdateStudentBatch']);
Route::middleware(['auth:sanctum'])->post("/bulkAssignGradDatesBySpecialty", [StudentBatchController::class, 'bulkAssignGradDateBySpecialty']);
