<?php

namespace App\Services\Teacher;

use App\Models\InstructorAvailability;
use App\Models\InstructorAvailabilitySlot;
use App\Models\SchoolSemester;
use App\Models\Teacher;
use App\Models\TeacherSpecailtyPreference;
use App\Notifications\AvailabilitySubmitted;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Events\Actions\AdminActionEvent;
class TeacherPreferedTeachingTimeService
{
    public function createInstructorAvailability(array $instructorAvailabilities, $currentSchool): array
    {
        DB::beginTransaction();
        $schoolSemester = null;
        $teacher = null;
        $instructorAvailability = null;
        try {
            $result = [];
            foreach ($instructorAvailabilities as $availability) {
                if ($schoolSemester == null) {
                    $schoolSemester = SchoolSemester::where('id', $availability['school_semester_id'])
                        ->where('school_branch_id', $currentSchool->id)
                        ->with(['specialty.level', 'semester'])
                        ->first();
                }
                if ($teacher == null) {
                    $teacher = Teacher::where("school_branch_id", $currentSchool->id)->find($availability['teacher_id']);
                }
                if ($instructorAvailability == null) {
                    $instructorAvailability =  InstructorAvailability::where("school_branch_id", $currentSchool)
                        ->where("school_semester_id", $availability['school_semester_id'])
                        ->findOrFail($availability['teacher_availability_id']);
                }
                if ($instructorAvailability == 'added') {
                    throw new Exception("Your Preferred Teaching Time for this semester is already Added", 400);
                }
                $availability = new InstructorAvailabilitySlot();
                $availability->school_branch_id = $currentSchool->id;
                $availability->teacher_id = $availability['teacher_id'];
                $availability->day_of_week = $availability['day_of_week'];
                $availability->start_time = $availability['start_time'];
                $availability->end_time = $availability['end_time'];
                $availability->level_id = $schoolSemester->specialty->level_id ?? null;
                $availability->specialty_id = $schoolSemester->specialty_id ?? null;
                $availability->school_semester_id = $availability['school_semester_id'];
                $availability->teacher_availability_id = $availability['teacher_availability_id'];
                $availability->save();
                $result[] = $availability;
            }
            $instructorAvailability->status = 'added';
            $instructorAvailability->save();
            DB::commit();
            $availabilityData = [
                'schoolYear' => $schoolSemester->year,
                'specialty' => $schoolSemester->specialty->specialty_name,
                'level' => $schoolSemester->specialty->level->name,
                'semester' => $schoolSemester->semester->name
            ];
            $teacher->notify(new AvailabilitySubmitted($availabilityData));
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function createAvialabilityByOtherSlots(string $targetAvailabilityId, string $availabilityId, $currentSchool)
    {
        //targetAvailability refers to the availability we are going to use or will be using to populate the desired avaialability slots
        try {
            DB::beginTransaction();
            $availability = InstructorAvailability::where("school_branch_id", $currentSchool->id)
                ->find($availabilityId);
            if ($availability->status == 'added') {
                throw new Exception("Your Preferred Teaching Time for this semester is already Added", 400);
            }
            $availabilitySlots = InstructorAvailabilitySlot::where("school_branch_id", $currentSchool->id)
                ->where("teacher_availability_id", $targetAvailabilityId)
                ->get();

            foreach ($availabilitySlots as $availabilitySlot) {
                InstructorAvailabilitySlot::create([
                    'teacher_id' => $availabilitySlot['teacher_id'],
                    'day_of_week' => $availabilitySlot['day_of_week'],
                    'school_branch_id' => $currentSchool->id,
                    'start_time' => $availabilitySlot['start_time'],
                    'specialty_id' => $availability->specialty_id,
                    'level_id' => $availability->level_id,
                    'end_time' => $availabilitySlot['end_time'],
                    'school_semester_id' => $availability->school_semester_id
                ]);
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function deleteAvailabilitySlots(string $availabilityId, $currentSchool, string $teacherId)
    {
        $result = [];
        $teacherAvailabilitySlots = InstructorAvailabilitySlot::where('teacher_availability_id', $availabilityId)
            ->where('school_branch_id', $currentSchool->id)
            ->where('teacher_id', $teacherId)
            ->get();
        foreach ($teacherAvailabilitySlots as $teacherAvailabilitySlot) {
            $teacherAvailabilitySlot->delete();
            $result[] = $teacherAvailabilitySlot;
        }
        return $result;
    }
    public function getSchoolSemestersByTeacherSpecialtyPreference($currentSchool, string $teacherId)
    {
        $specialtyIds = TeacherSpecailtyPreference::where('school_branch_id', $currentSchool->id)
            ->where('teacher_id', $teacherId)
            ->distinct()
            ->pluck('specialty_id');

        $schoolSemesters = SchoolSemester::with('specialty.level', 'semester')
            ->where('school_branch_id', $currentSchool->id)
            ->where('status', 'active')
            ->whereIn('specialty_id', $specialtyIds)
            ->get();

        if ($schoolSemesters->isEmpty()) {
            return collect();
        }

        return $schoolSemesters;
    }
    public function bulkUpdateInstructorAvailability(array $instructorAvailabilities, $currentSchool): array
    {
        DB::beginTransaction();

        try {
            foreach ($instructorAvailabilities as $availability) {
                $existingAvailability = InstructorAvailabilitySlot::find($availability['slot_id']);

                if (!$existingAvailability) {
                    throw new Exception('Instructor availability record with ID ' . $availability['slot_id'] . ' not found.');
                }
                $existingAvailability->day_of_week = $availability['day_of_week'];
                $existingAvailability->start_time = $availability['start_time'];
                $existingAvailability->end_time = $availability['end_time'];
                $existingAvailability->save();
            }
            DB::commit();
            return $instructorAvailabilities;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function getInstructorAvailabilities($currentSchool)
    {
        $instructorAvailabilities = InstructorAvailability::where("school_branch_id", $currentSchool->id)
            ->with(['teacher', 'level', 'schoolSemester', 'specialty'])
            ->get();
        return $instructorAvailabilities;
    }
    public function getInstructorAvailabilitesByTeacher($currentSchool, $teacherId)
    {
        $instructorAvailabilities = InstructorAvailability::where("school_branch_id", $currentSchool->id)
            ->where('teacher_id', $teacherId)
            ->with(['teacher', 'level', 'schoolSemester', 'specialty'])
            ->get();
        return $instructorAvailabilities;
    }
    public function getInstructorAvailabilityDetails($currentSchool, string $availabilityId)
    {
        $instructorAvailabilities = InstructorAvailability::where("school_branch_id", $currentSchool->id)
            ->with(['teacher', 'level', 'schoolSemester', 'specialty'])
            ->find($availabilityId);
        return $instructorAvailabilities;
    }
    public function getAvailabilitySlotsByTeacher($currentSchool, string $availabilityId)
    {
        $slots = InstructorAvailabilitySlot::where("school_branch_id", $currentSchool->id)
            ->where("teacher_availability_id", $availabilityId)
            ->get();
        return $slots;
    }
}
