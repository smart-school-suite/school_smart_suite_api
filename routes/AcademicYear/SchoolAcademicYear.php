<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AcademicYear\SchoolAcademicYearController;

Route::post('/create', [SchoolAcademicYearController::class, 'createSchoolAcademicYear'])->name('schoolAcademicYear.create');
Route::put('/{schoolAcademicYearId}/update', [SchoolAcademicYearController::class, 'updateSchoolAcademicYear'])->name('schoolAcademicYear.update');
Route::get('/', [SchoolAcademicYearController::class, 'getSchoolAcademicYears'])->name('schoolAcademicYear.getAll');
Route::get('/{schoolAcademicYearId}', [SchoolAcademicYearController::class, 'getSchoolAcademicYearById'])->name('schoolAcademicYear.getById');
Route::delete('/{schoolAcademicYearId}/delete', [SchoolAcademicYearController::class, 'deleteSchoolAcademicYear'])->name('schoolAcademicYear.delete');