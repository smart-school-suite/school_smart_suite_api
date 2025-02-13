<?php

namespace App\Services;
use Illuminate\Support\Facades\DB;
use App\Models\InstructorAvailability;
use App\Models\Timetable;
use Exception;
class CreateSpecailtyTimeTableService
{
    // Implement your logic here

    public function createTimeTable(array $SpecailtyTimeTable, $currentSchool){
        $result = [];
        DB::beginTransaction();
        try {
            foreach ($SpecailtyTimeTable as $timetable) {
              $createTimeTable =   $this->createTimeSlot($currentSchool, $timetable);
              $result[] = $createTimeTable;
            }
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    private function createTimeSlot($currentSchool, $timetable)
    {
        if (!$this->isTeacherAvailable($currentSchool, $timetable)) {
            throw new Exception("Teacher is not available at this time", 409);
        }

        if ($this->isTimeSlotAlreadyAssigned($currentSchool, $timetable)) {
            throw new Exception("Teacher is already assigned to this time slot", 409);
        }

        $timeTableData = new Timetable();
        $timeTableData->fill([
            'school_branch_id' => $currentSchool->id,
            'course_id' => $timetable['course_id'],
            'teacher_id' => $timetable['teacher_id'],
            'day_of_week' => $timetable['day_of_week'],
            'specialty_id' => $timetable['specialty_id'],
            'level_id' => $timetable['level_id'],
            'semester_id' => $timetable['semester_id'],
            'student_batch_id' => $timetable['student_batch_id'],
            'start_time' => $timetable['start_time'],
            'end_time' => $timetable['end_time'],
        ]);

        $timeTableData->save();

        return $timeTableData;
    }

    private function isTeacherAvailable($currentSchool, $timetable)
    {
        return InstructorAvailability::where('school_branch_id', $currentSchool->id)
            ->where('teacher_id', $timetable['teacher_id'])
            ->where('semester_id', $timetable['semester_id'])
            ->where('day_of_week', $timetable['day_of_week'])
            ->where(function ($query) use ($timetable) {
                $query->whereBetween('start_time', [$timetable['start_time'], $timetable['end_time']])
                    ->orWhereBetween('end_time', [$timetable['start_time'], $timetable['end_time']])
                    ->orWhere(function ($query) use ($timetable) {
                        $query->where('start_time', '<=', $timetable['start_time'])
                            ->where('end_time', '>=', $timetable['end_time']);
                    });
            })
            ->doesntExist();
    }

    private function isTimeSlotAlreadyAssigned($currentSchool, $timetable)
    {
        return Timetable::where('school_branch_id', $currentSchool->id)
            ->where('teacher_id', $timetable['teacher_id'])
            ->where('semester_id', $timetable['semester_id'])
            ->where('day_of_week', $timetable['day_of_week'])
            ->where(function ($query) use ($timetable) {
                $query->whereBetween('start_time', [$timetable['start_time'], $timetable['end_time']])
                    ->orWhereBetween('end_time', [$timetable['start_time'], $timetable['end_time']])
                    ->orWhere(function ($query) use ($timetable) {
                        $query->where('start_time', '<=', $timetable['start_time'])
                            ->where('end_time', '>=', $timetable['end_time']);
                    });
            })
            ->exists();
    }
}
