<?php

namespace App\Services;

use App\Models\Timetable;
use Exception;
use illuminate\Support\Facades\DB;
use illuminate\Support\Facades\Log;

class UpdateTimeTableService
{
    // Implement your logic here

    public function updateTimetableEntriesByTeacherAvailability(array $scheduleUpdatesEntries, $currentSchool) {
        $updates = collect($scheduleUpdatesEntries)->keyBy('entry_id')->toArray();

        $existingEntries = Timetable::whereIn('id', array_keys($updates))
            ->where('school_branch_id', $currentSchool->id)
            ->get()
            ->keyBy('id');

        DB::beginTransaction();

        try {
            $updatedCount = 0;
            foreach ($updates as $entryId => $updateData) {
                if ($existingEntries->has($entryId)) {
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
            }

            DB::commit();
            return $updatedCount;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateTimetableEntries(array $scheduleUpdatesEntries, $currentSchool)
    {
        $updates = collect($scheduleUpdatesEntries)->keyBy('entry_id')->toArray();

        $existingEntries = Timetable::whereIn('id', array_keys($updates))
            ->where('school_branch_id', $currentSchool->id)
            ->get()
            ->keyBy('id');

        DB::beginTransaction();

        try {
            $updatedCount = 0;
            foreach ($updates as $entryId => $updateData) {
                if ($existingEntries->has($entryId)) {
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
            }

            DB::commit();
            return $updatedCount;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
