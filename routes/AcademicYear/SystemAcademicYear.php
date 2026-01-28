<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AcademicYear\SystemAcademicYearController;

Route::get('/all', [SystemAcademicYearController::class, 'getAllSystemAcademicYears'])->name('systemAcademicYear.getAll');
Route::get('/current-year', [SystemAcademicYearController::class, 'getSystemAcademicYearByCurrentYear'])->name('systemAcademicYear.getByCurrentYear');