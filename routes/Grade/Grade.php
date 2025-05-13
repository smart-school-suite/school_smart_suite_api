<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GradesController;

    // Create new exam grades
    Route::post('/exam-grades', [GradesController::class, 'createExamGrades'])
        ->name('exam-grades.store');

    // Get all exam grades
    Route::get('/exam-grades', [GradesController::class, 'getAllGrades'])
        ->name('exam-grades.index');

    // Update a specific grade configuration
    Route::put('/exam-grades/{gradeId}', [GradesController::class, 'update_grades_scoped'])
        ->name('exam-grades.update');

    // Delete grades for a specific exam
    Route::delete('/exams/{examId}/grades', [GradesController::class, 'deleteGrades'])
        ->name('exams.grades.destroy');

    // Get related exams for a specific exam (what does "related" mean here?)
    Route::get('/exams/{examId}/related-exams', [GradesController::class, 'getRelatedExams'])
        ->name('exams.related-exams');

    // Get grade configuration for a specific exam
    Route::get('/exams/{examId}/grades-config', [GradesController::class, 'getGradesConfigByExam'])
        ->name('exams.grades-config');

    // Get exam configuration data for a specific exam (redundant with /exams/{examId}/grades-config?)
    Route::get('/exams/{examId}/config-data', [GradesController::class, 'getExamConfigData'])
        ->name('exams.config-data');

    // Bulk delete grades for multiple exams
    Route::delete('/exams/bulk-delete-grades/{examIds}', [GradesController::class, 'bulkDeleteGrades'])
        ->name('exams.grades.bulk-delete');

    // Create grades based on another grade configuration
    Route::post('/grade-configs/{configId}/target-configs/{targetConfigId}/grades', [GradesController::class, 'createGradesByOtherGrades'])
        ->name('grade-configs.target-configs.grades.store');
