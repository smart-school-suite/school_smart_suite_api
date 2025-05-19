<?php

namespace App\Services;

use App\Models\InstructorAvailability;
use App\Models\SchoolSemester;
use App\Models\TeacherSpecailtyPreference;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;

class InstructorAvaliabilityService
{
    // Implement your logic here
    public function createInstructorAvailability(array $instructorAvailabilities, $currentSchool): array
    {
        DB::beginTransaction();
        $schoolSemester = null;
        try {
            $result = [];
            foreach ($instructorAvailabilities as $availability) {
                if ($schoolSemester == null) {
                    $schoolSemester = SchoolSemester::where('id', $availability['school_semester_id'])
                        ->where('school_branch_id', $currentSchool->id)
                        ->with(['specialty'])
                        ->first();
                }
                $clashExists = InstructorAvailability::where('school_branch_id', $currentSchool->id)
                    ->where('teacher_id', $availability['teacher_id'])
                    ->where('school_semester_id', $availability['school_semester_id'])
                    ->where('day_of_week', $availability['day_of_week'])
                    ->where(function ($query) use ($availability) {
                        $query->whereBetween('start_time', [$availability['start_time'], $availability['end_time']])
                            ->orWhereBetween('end_time', [$availability['start_time'], $availability['end_time']])
                            ->orWhere(function ($query) use ($availability) {
                                $query->where('start_time', '<=', $availability['start_time'])
                                    ->where('end_time', '>=', $availability['end_time']);
                            });
                    })
                    ->exists();
                if ($clashExists) {
                    throw new Exception('Time slot clash with existing timetable entries for teacher ID: ' . $availability['teacher_id']);
                }

                $this->checkRecordsAlreadyExists($availability, $currentSchool);
                $newAvailability = new InstructorAvailability();
                $newAvailability->school_branch_id = $currentSchool->id;
                $newAvailability->teacher_id = $availability['teacher_id'];
                $newAvailability->day_of_week = $availability['day_of_week'];
                $newAvailability->start_time = $availability['start_time'];
                $newAvailability->end_time = $availability['end_time'];
                $newAvailability->level_id = $schoolSemester->specialty->level_id ?? null;
                $newAvailability->specialty_id = $schoolSemester->specialty_id ?? null;
                $newAvailability->school_semester_id = $availability['school_semester_id'];

                $newAvailability->save();

                $result[] = $newAvailability;
            }
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function createAvialabilityByOtherSlots($semesterId, $teacherId, $currentSchool)
    {
        $availabilities = InstructorAvailability::where("school_branch_id", $currentSchool->id)
            ->where("teacher_id", $teacherId)
            ->where("school_semester_id", $semesterId)
            ->get();
        foreach ($availabilities as $availability) {
            InstructorAvailability::create([
                'teacher_id' => $availability['teacher_id'],
                'day_of_week' => $availability['day_of_week'],
                'school_branch_id' => $currentSchool->id,
                'start_time' => $availability['start_time'],
                'specialty_id' => $availability['specialty_id'],
                'level_id' => $availability['level_id'],
                'end_time' => $availability['end_time'],
                'school_semester_id' => $availability['school_semester_id']
            ]);
        }
    }
    public function getAllInstructorAvailabilties($currentSchool)
    {
        $instructorAvailabilty = InstructorAvailability::Where('school_branch_id', $currentSchool->id)->get();
        return $instructorAvailabilty;
    }

    public function getInstructorAvailability($currentSchool, $teacherId)
    {
        $instructorAvailabilty = InstructorAvailability::Where('school_branch_id', $currentSchool->id)->Where('teacher_id', $teacherId)->get();
        return $instructorAvailabilty;
    }

    public function deleteInstructorAvailability($currentSchool, $availabiltyId)
    {
        $instructorAvailabilitySlot = InstructorAvailability::Where('school_branch_id', $currentSchool->id)->find($availabiltyId);
        if (!$instructorAvailabilitySlot) {
            return ApiResponseService::error("Slot Not Found", null, 404);
        }
        $instructorAvailabilitySlot->delete();
        return $instructorAvailabilitySlot;
    }

    public function deleteAvailabilityBySemester($schoolSemesterId, $currentSchool, $teacherId)
    {
        $result = [];
        $teacherAvailabilitySlots = InstructorAvailability::where('school_semester_id', $schoolSemesterId)
            ->where('school_branch_id', $currentSchool->id)
            ->where('teacher_id', $teacherId)
            ->get();
        foreach ($teacherAvailabilitySlots as $teacherAvailabilitySlot) {
            $teacherAvailabilitySlot->delete();
            $result[] = $teacherAvailabilitySlot;
        }
        return $result;
    }

    public function getSchoolSemestersByTeacherSpecialtyPreference($currentSchool, $teacherId)
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
    public function updateInstructorAvailability(array $data, $currentSchool, $availabilityId)
    {
        $instructorAvailabilitySlot = InstructorAvailability::where('school_branch_id', $currentSchool->id)->find($availabilityId);
        if (!$instructorAvailabilitySlot) {
            return ApiResponseService::error("This data was not found. Check your credentials and try again.");
        }
        $clashExists = InstructorAvailability::where('school_branch_id', $currentSchool->id)
            ->where('semester', $data['semester'] ?? $instructorAvailabilitySlot->semester)
            ->where('day_of_week', $data['day_of_week'] ?? $instructorAvailabilitySlot->day_of_week)
            ->where(function ($query) use ($data, $instructorAvailabilitySlot) {
                $query->whereBetween('start_time', [$data["start_time"] ?? $instructorAvailabilitySlot->start_time, $data["end_time"] ?? $instructorAvailabilitySlot->end_time])
                    ->orWhereBetween('end_time', [$data["start_time"] ?? $instructorAvailabilitySlot->start_time, $data["end_time"] ?? $instructorAvailabilitySlot->end_time])
                    ->orWhere(function ($query) use ($data, $instructorAvailabilitySlot) {
                        $query->where('start_time', '<=', $data["start_time"] ?? $instructorAvailabilitySlot->start_time)
                            ->where('end_time', '>=', $data["end_time"] ?? $instructorAvailabilitySlot->end_time);
                    });
            })
            ->where('id', '!=', $availabilityId)
            ->exists();
        if ($clashExists) {
            return ApiResponseService::error("A clash was detected in the entry.", null, 400);
        }
        $filteredData = array_filter($data);
        $instructorAvailabilitySlot->update($filteredData);
        return $instructorAvailabilitySlot;
    }

    public function bulkUpdateInstructorAvailability(array $instructorAvailabilities, $currentSchool): array
    {
        DB::beginTransaction();

        try {
            foreach ($instructorAvailabilities as $availability) {
                $existingAvailability = InstructorAvailability::find($availability['id']);

                if (!$existingAvailability) {
                    throw new Exception('Instructor availability record with ID ' . $availability['id'] . ' not found.');
                }
                $clashExists = InstructorAvailability::where('school_branch_id', $currentSchool->id)
                    ->where('teacher_id', $availability['teacher_id'])
                    ->where('school_semester_id', $availability['school_semester_id'])
                    ->where('day_of_week', $availability['day_of_week'])
                    ->where('id', '<>', $availability['id'])
                    ->where(function ($query) use ($availability) {
                        $query->whereBetween('start_time', [$availability['start_time'], $availability['end_time']])
                            ->orWhereBetween('end_time', [$availability['start_time'], $availability['end_time']])
                            ->orWhere(function ($query) use ($availability) {
                                $query->where('start_time', '<=', $availability['start_time'])
                                    ->where('end_time', '>=', $availability['end_time']);
                            });
                    })
                    ->exists();

                if ($clashExists) {
                    throw new Exception('Time slot clash with existing timetable entries for teacher ID: ' . $availability['teacher_id']);
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

    private function checkRecordsAlreadyExists($availability, $currentSchool)
    {
        $availability = InstructorAvailability::where('school_branch_id', $currentSchool->id)
            ->where("teacher_id", $availability['teacher_id'])
            ->where("school_semester_id", $availability['school_semester_id'])
            ->where("day_of_week", $availability["day_of_week"])
            ->where("start_time", $availability["start_time"])
            ->where("end_time", $availability['end_time'])
            ->exists();
        if ($availability) {
            throw new Exception("Record Already Exists");
        }
    }
}
