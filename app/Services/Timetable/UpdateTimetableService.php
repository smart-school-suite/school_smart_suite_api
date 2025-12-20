<?php

namespace App\Services\Timetable;

use App\Exceptions\AppException;
use App\Models\Timetable;
use Exception;
use Illuminate\Support\Facades\DB;

class UpdateTimetableService
{
    public function updateTimetableEntriesByTeacherAvailability(array $scheduleUpdatesEntries, $currentSchool)
    {
        DB::beginTransaction();
        try {
            if (empty($scheduleUpdatesEntries)) {
                return 0;
            }

            $updates = collect($scheduleUpdatesEntries)->keyBy('entry_id');
            $existingEntries = Timetable::whereIn('id', $updates->keys())
                ->where('school_branch_id', $currentSchool->id)
                ->get()
                ->keyBy('id');

            if ($existingEntries->isEmpty()) {
                throw new AppException(
                    "None of the provided timetable entries were found.",
                    404,
                    "Timetable Entries Not Found",
                    "The system could not locate any of the timetable entries for the given IDs and school.",
                    null
                );
            }

            $updatedCount = 0;
            foreach ($updates as $entryId => $updateData) {
                if (!$existingEntries->has($entryId)) {
                    continue;
                }
                $entry = $existingEntries[$entryId];
                $entry->fill([
                    'teacher_id' => $updateData['teacher_id'],
                    'course_id' => $updateData['course_id'],
                    'day_of_week' => $updateData['day_of_week'],
                    'start_time' => $updateData['start_time'],
                    'end_time' => $updateData['end_time'],
                    'specialty_id' => $updateData['specialty_id'],
                    'level_id' => $updateData['level_id'],
                    'semester_id' => $updateData['semester_id'],
                    'student_batch_id' => $updateData['student_batch_id'],
                ]);
                $entry->save();
                $updatedCount++;
            }

            if ($updatedCount === 0) {
                throw new AppException(
                    "No valid entries were found to be updated.",
                    400,
                    "No Updates Performed",
                    "The request did not contain any valid entries that could be updated.",
                    null
                );
            }

            DB::commit();
            return $updatedCount;
        } catch (AppException $e) {
            DB::rollBack();
            throw $e;
        } catch (Exception $e) {
            DB::rollBack();
            throw new AppException(
                "An unexpected error occurred while updating the timetable.",
                500,
                "Timetable Update Error",
                "A server-side issue prevented the timetable entries from being updated.",
                null
            );
        }
    }
    public function updateTimetableEntries(array $scheduleUpdatesEntries, $currentSchool)
    {
        DB::beginTransaction();
        try {
            if (empty($scheduleUpdatesEntries)) {
                throw new AppException(
                    "No timetable entries were provided for the update.",
                    400,
                    "No Data Provided",
                    "The request body did not contain any valid entries to update.",
                    null
                );
            }

            $updates = collect($scheduleUpdatesEntries)->keyBy('entry_id');
            $existingEntries = Timetable::whereIn('id', $updates->keys())
                ->where('school_branch_id', $currentSchool->id)
                ->get()
                ->keyBy('id');

            if ($existingEntries->isEmpty()) {
                throw new AppException(
                    "None of the provided timetable entries were found for your school.",
                    404,
                    "Entries Not Found",
                    "The system could not locate any timetable entries with the given IDs within your school branch.",
                    null
                );
            }

            $updatedCount = 0;
            foreach ($updates as $entryId => $updateData) {
                if ($existingEntries->has($entryId)) {
                    $entry = $existingEntries[$entryId];

                    // Validate if the entry data is valid before filling
                    $fillableData = collect($updateData)->only([
                        'teacher_id',
                        'course_id',
                        'day_of_week',
                        'start_time',
                        'end_time',
                        'specialty_id',
                        'level_id',
                        'semester_id',
                        'student_batch_id'
                    ])->toArray();

                    if (empty($fillableData)) {
                        // Skip if no valid data to update, or throw an exception if required
                        continue;
                    }

                    $entry->fill($fillableData);
                    $entry->save();
                    $updatedCount++;
                }
            }

            if ($updatedCount === 0) {
                throw new AppException(
                    "No valid entries were updated.",
                    400,
                    "Update Failed",
                    "The provided data did not result in any changes to the timetable.",
                    null
                );
            }

            DB::commit();
            return $updatedCount;
        } catch (AppException $e) {
            DB::rollBack();
            throw $e;
        } catch (Exception $e) {
            DB::rollBack();
            throw new AppException(
                "An unexpected error occurred while updating the timetable entries.",
                500,
                "Timetable Update Error",
                "A server-side issue prevented the timetable from being updated successfully.",
                null
            );
        }
    }
}
