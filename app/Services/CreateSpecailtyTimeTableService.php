<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\SchoolSemester;
use App\Models\Timetable;
use Exception;
use Illuminate\Support\Str;

class CreateSpecailtyTimeTableService
{
    public function createTimetableByAvailability(array $scheduleEntries, $currentSchool)
    {
        $entriesToInsert = [];
        $schoolSemester = null;
        foreach ($scheduleEntries as $entry) {
            $uniqueId = Str::uuid();
            if($schoolSemester == null){
                $schoolSemester = SchoolSemester::findOrFail($entry['semester_id']);
            }
            $entriesToInsert[] = [
                'id' => $uniqueId,
                'school_branch_id' => $currentSchool->id,
                'course_id' => $entry['course_id'],
                'teacher_id' => $entry['teacher_id'],
                'day_of_week' => $entry['day_of_week'],
                'specialty_id' => $entry['specialty_id'],
                'level_id' => $entry['level_id'],
                'semester_id' => $entry['semester_id'],
                'student_batch_id' => $entry['student_batch_id'],
                'start_time' => $entry['start_time'],
                'end_time' => $entry['end_time'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (empty($entriesToInsert)) {
            return ['error' => true, 'message' => 'No valid schedule entries provided for insertion.'];
        }

        try {
            DB::transaction(function () use ($entriesToInsert, $schoolSemester) {
                Timetable::insert($entriesToInsert);
                SchoolSemester::where('id', $schoolSemester->id)->update(['timetable_published' => true]);
            });

            $insertedTimetable = Timetable::where('school_branch_id', $currentSchool->id)
                ->whereIn('start_time', array_column($entriesToInsert, 'start_time'))
                ->whereIn('end_time', array_column($entriesToInsert, 'end_time'))
                ->get();

            return $insertedTimetable;

        } catch (Exception $e) {
            throw $e;
        }
    }
    public function createTimetable(array $scheduleEntries, $currentSchool)
    {
        $entriesToInsert = [];
        $schoolSemester = null;
        foreach ($scheduleEntries as $entry) {
            $uniqueId = Str::uuid();
            if($schoolSemester == null){
                $schoolSemester = SchoolSemester::findOrFail($entry['semester_id']);
            }
            $entriesToInsert[] = [
                'id' => $uniqueId,
                'school_branch_id' => $currentSchool->id,
                'course_id' => $entry['course_id'],
                'teacher_id' => $entry['teacher_id'],
                'day_of_week' => $entry['day_of_week'],
                'specialty_id' => $entry['specialty_id'],
                'level_id' => $entry['level_id'],
                'semester_id' => $entry['semester_id'],
                'student_batch_id' => $entry['student_batch_id'],
                'start_time' => $entry['start_time'],
                'end_time' => $entry['end_time'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (empty($entriesToInsert)) {
            return ['error' => true, 'message' => 'No valid schedule entries provided for insertion.'];
        }

        try {
            DB::transaction(function () use ($entriesToInsert, $schoolSemester) {
                Timetable::insert($entriesToInsert);
                SchoolSemester::where('id', $schoolSemester->id)->update(['timetable_published' => true]);
            });

            $insertedTimetable = Timetable::where('school_branch_id', $currentSchool->id)
                ->whereIn('start_time', array_column($entriesToInsert, 'start_time'))
                ->whereIn('end_time', array_column($entriesToInsert, 'end_time'))
                ->get();

            return $insertedTimetable;

        } catch (Exception $e) {
            throw $e;
        }
    }
}
