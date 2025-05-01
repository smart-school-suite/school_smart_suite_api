<?php
 use Illuminate\Support\Facades\Route;
 use App\Http\Controllers\AccessedStudentController;

 Route::get('/getAccessedStudent', [AccessedStudentController::class, 'getAccessedStudent']);
 Route::delete('/deleteAccessedStudent/{accessedStudentId}', [AccessedStudentController::class, 'deleteAccessedStudent']);
