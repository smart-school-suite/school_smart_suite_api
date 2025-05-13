<?php

namespace App\Services;

use App\Models\InstructorAvailability;
use Illuminate\Support\Facades\DB;
use Exception;

class InstructorAvaliabilityService
{
    // Implement your logic here
    public function createInstructorAvailability(array $instructorAvailabilities, $currentSchool): array
    {
        DB::beginTransaction();
        try {
            $result = [];
            foreach ($instructorAvailabilities as $availability) {
                $clashExists = InstructorAvailability::where('school_branch_id', $currentSchool->id)
                    ->where('teacher_id', $availability['teacher_id'])
                    ->where('semester_id', $availability['semester_id'])
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
                $newAvailability->semester_id = $availability['semester_id'];

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

    public function createAvialabilityByOtherSlots($semesterId, $teacherId, $currentSchool){
        $availabilities = InstructorAvailability::where("school_branch_id", $currentSchool->id)
                                  ->where("teacher_id", $teacherId)
                                  ->where("semester_id", $semesterId)
                                  ->get();
        foreach($availabilities as $availability){
            InstructorAvailability::create([
               'teacher_id' => $availability['teacher_id'],
               'day_of_week' => $availability['day_of_week'],
               'school_branch_id' => $currentSchool->id,
               'start_time' => $availability['start_time'],
               'end_time' => $availability['end_time'],
               'semester_id' => $availability['semester_id']
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
        $instructorAvailability = InstructorAvailability::Where('school_branch_id', $currentSchool->id)->find($availabiltyId);
        if (!$instructorAvailability) {
            return ApiResponseService::error("This data was not found Check your Credentials And Try Again");
        }
        $instructorAvailability->delete();
        return $instructorAvailability;
    }

    public function updateInstructorAvailability(array $data, $currentSchool, $availabilityId)
    {
        $instructorAvailability = InstructorAvailability::where('school_branch_id', $currentSchool->id)->find($availabilityId);
        if (!$instructorAvailability) {
            return ApiResponseService::error("This data was not found. Check your credentials and try again.");
        }
        $clashExists = InstructorAvailability::where('school_branch_id', $currentSchool->id)
            ->where('semester', $data['semester'] ?? $instructorAvailability->semester)
            ->where('day_of_week', $data['day_of_week'] ?? $instructorAvailability->day_of_week)
            ->where(function ($query) use ($data, $instructorAvailability) {
                $query->whereBetween('start_time', [$data["start_time"] ?? $instructorAvailability->start_time, $data["end_time"] ?? $instructorAvailability->end_time])
                    ->orWhereBetween('end_time', [$data["start_time"] ?? $instructorAvailability->start_time, $data["end_time"] ?? $instructorAvailability->end_time])
                    ->orWhere(function ($query) use ($data, $instructorAvailability) {
                        $query->where('start_time', '<=', $data["start_time"] ?? $instructorAvailability->start_time)
                            ->where('end_time', '>=', $data["end_time"] ?? $instructorAvailability->end_time);
                    });
            })
            ->where('id', '!=', $availabilityId)
            ->exists();
        if ($clashExists) {
            return ApiResponseService::error("A clash was detected in the entry.", null, 400);
        }
        $filteredData = array_filter($data);
        $instructorAvailability->update($filteredData);
        return $instructorAvailability;
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
                    ->where('semester_id', $availability['semester_id'])
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
                $existingAvailability->semester_id = $availability['semester_id'];
                $existingAvailability->save();
            }
            DB::commit();
            return $instructorAvailabilities;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function checkRecordsAlreadyExists($availability, $currentSchool){
        $availability = InstructorAvailability::where('school_branch_id', $currentSchool->id)
                                             ->where("teacher_id", $availability['teacher_id'])
                                             ->where("semester_id", $availability['semester_id'])
                                             ->where("day_of_week", $availability["day_of_week"])
                                             ->where("start_time", $availability["start_time"])
                                             ->where("end_time", $availability['end_time'])
                                             ->exists();
        if($availability){
            throw new Exception("Record Already Exists");
        }
    }
}
