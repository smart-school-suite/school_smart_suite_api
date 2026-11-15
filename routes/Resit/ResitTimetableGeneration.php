<?php

use App\Http\Controllers\ResitTimetable\ResitTimetableGenerationController as Resit;
use Illuminate\Support\Facades\Route;

// Automatic generation of resit timetables, following the exam timetable flow.
// Prefix: /api/v1/resit-timetable-generation

Route::get('/constraints', [Resit::class, 'getConstraints'])->name('resitTimetable.constraints');
Route::get('/resit-exam/{resitExamId}/scheduling-data', [Resit::class, 'getSchedulingData'])->name('resitTimetable.schedulingData');

Route::post('/generate', [Resit::class, 'generateTimetable'])->name('resitTimetable.generate');
Route::get('/version/{versionId}/status', [Resit::class, 'getTimetableStatus'])->name('resitTimetable.status');
Route::get('/version/{versionId}/errors', [Resit::class, 'getErrors'])->name('resitTimetable.errors');
Route::get('/version/{versionId}/slots', [Resit::class, 'getTimetableSlots'])->name('resitTimetable.slots');
Route::get('/version/{versionId}/payload', [Resit::class, 'getTimetableRequestPayload'])->name('resitTimetable.payload');
Route::get('/version/{versionId}/raw-diagnostics', [Resit::class, 'getRawDiagnostics'])->name('resitTimetable.rawDiagnostics');
Route::get('/version/{versionId}/solutions', [Resit::class, 'getTimetableSolutions'])->name('resitTimetable.solutions');
Route::post('/version/{versionId}/solutions/apply', [Resit::class, 'applySolutions'])->name('resitTimetable.solutions.apply');
Route::post('/version/{versionId}/activate', [Resit::class, 'activateTimetableVersion'])->name('resitTimetable.version.activate');
Route::get('/version/{versionId}/activation-check', [Resit::class, 'checkTimetableActivation'])->name('resitTimetable.version.activationCheck');
Route::delete('/version/{versionId}', [Resit::class, 'deleteVersion'])->name('resitTimetable.version.delete');
Route::post('/version', [Resit::class, 'createVersion'])->name('resitTimetable.version.create');
Route::get('/resit-exam/{resitExamId}/versions', [Resit::class, 'getVersions'])->name('resitTimetable.versions');
Route::get('/resit-exam/{resitExamId}/active-timetable', [Resit::class, 'getActiveTimetable'])->name('resitTimetable.active.get');

Route::post('/invigilators/assign', [Resit::class, 'assignInvigilators'])->name('resitTimetable.invigilators.assign');
Route::post('/invigilators/remove', [Resit::class, 'removeInvigilators'])->name('resitTimetable.invigilators.remove');
Route::get('/resit-exam/{resitExamId}/invigilators', [Resit::class, 'getInvigilators'])->name('resitTimetable.invigilators');
Route::get('/resit-exam/{resitExamId}/invigilators/potential', [Resit::class, 'getPotentialInvigilators'])->name('resitTimetable.invigilators.potential');
