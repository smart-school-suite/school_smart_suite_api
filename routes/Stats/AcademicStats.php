<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Stats\AcademicStatController;

Route::get("/exam/{examId}/standings", [AcademicStatController::class, 'getStudentExamStandings'])->name('stats.student.exam.standings');
Route::get("/exam/{examId}", [AcademicStatController::class, 'getExamStats'])->name('stats.exam.stats');
Route::get("/ca-exam/{examId}", [AcademicStatController::class, 'getCaExamStats'])->name('stats.ca.exam.stats');
Route::get("/student/{studentId}/ca-exam/{examId}", [AcademicStatController::class, 'getStudentCaExamStats'])->name('stats.student.ca.exam');
Route::get("/student/{studentId}/exam/{examId}", [AcademicStatController::class, 'getStudentExamStats'])->name('stats.student.exam.stats');
Route::get("/{year}", [AcademicStatController::class, 'getSchoolAcademicStats'])->name('stats.academic.stats');
