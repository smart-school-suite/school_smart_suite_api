<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SemesterTimetable\SemesterTimetableVersionController;

Route::post('/{schoolSemesterId}/version/create', [SemesterTimetableVersionController::class, 'createSemesterTimetableVersion'])->name('semesterTimetable.version.create');
Route::delete('/{schoolSemesterId}/version/{versionId}/delete', [SemesterTimetableVersionController::class, 'deleteTimetableVersion'])->name('semesterTimetable.version.delete');
Route::delete('slot/{slotId}/delete', [SemesterTimetableVersionController::class, 'deleteTimetableVersionSlot'])->name('semesterTimetable.version.slot.delete');
Route::get('/slot/{slotId}/detail', [SemesterTimetableVersionController::class, 'getTimetableVersionSlotDetail'])->name('semesterTimetable.version.slot.detail');
Route::get('/{schoolSemesterId}/version/{versionId}/slots', [SemesterTimetableVersionController::class, 'getTimetableSlotsVersionId'])->name('semesterTimetable.version.slots');
