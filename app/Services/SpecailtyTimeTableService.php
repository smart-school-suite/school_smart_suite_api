<?php

namespace App\Services;

use App\Models\Timetable;
use App\Models\Specialty;
use App\Models\Schoolbranches;
use App\Models\InstructorAvailabilitySlot;
use App\Models\SchoolSemester;
use App\Models\TeacherSpecailtyPreference;
use Carbon\Carbon;
use illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SpecailtyTimeTableService
{
    // Implement your logic here
    /**
     * Deletes a single timetable entry.
     *
     * @param SchoolBranches $currentSchool The current school.
     * @param string $timeTableId The ID of the timetable entry to delete.
     * @return Timetable
     * @throws Exception
     */
    public function deleteTimeTableEntry(Schoolbranches $currentSchool, string $entryId): Timetable
    {
        try {
            $timeTableEntry = Timetable::where('school_branch_id', $currentSchool->id)->findOrFail($entryId);
            $timeTableEntry->delete();
            $timeTableEntries = Timetable::where('school_branch_id', $currentSchool->id)
                ->where('specialty_id', $timeTableEntry->specialty_id)
                ->where('level_id', $timeTableEntry->level_id)
                ->where('student_batch_id', $timeTableEntry->student_batch_id)
                ->where('semester_id', $timeTableEntry->semester_id)
                ->count();
            if ($timeTableEntries == 0) {
                $schoolSemester = SchoolSemester::findOrFail($timeTableEntry->semester_id);
                $schoolSemester->timetable_published = false;
                $schoolSemester->save();
            }
            return $timeTableEntry;
        } catch (ModelNotFoundException $e) {
            throw $e;
        } catch (Exception $e) {
            Log::error('Error deleting timetable entry: ' . $e->getMessage(), [
                'entryid' => $entryId,
                'currentSchoolId' => $currentSchool->id,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Deletes timetable entries based on the provided parameters.
     *
     * @param SchoolBranches $currentSchool The current school.
     * @param array $timtableData The parameters to filter timetable entries.
     * @return array An array of deleted timetable entries.
     * @throws Exception
     */
    public function deleteTimetable(SchoolBranches $currentSchool, array $timtableData): array
    {
        try {
            $timetableEntries = Timetable::where("school_branch_id", $currentSchool->id)
                ->where("specialty_id", $timtableData['specialty_id'])
                ->where("level_id", $timtableData['level_id'])
                ->where("student_batch_id", $timtableData['student_batch_id'])
                ->where("semester_id", $timtableData['semester_id'])
                ->get();

            if ($timetableEntries->isEmpty()) {
                throw new Exception("No timetable entries found to delete");
            }

            $deletedEntries = [];
            DB::transaction(function () use ($timetableEntries, &$deletedEntries) {
                foreach ($timetableEntries as $entry) {
                    $deletedEntries[] = $entry->toArray();
                    $entry->delete();
                }
            });

            $schoolSemester =  SchoolSemester::findOrFail($timtableData['semester_id']);
            $schoolSemester->timetable_published = false;
            $schoolSemester->save();

            return $deletedEntries;
        } catch (Exception $e) {
            Log::error('Error deleting timetable entries: ' . $e->getMessage(), [
                'routeParams' => $timtableData,
                'currentSchoolId' => $currentSchool->id,
                'trace' => $e->getTraceAsString(),
            ]);
            throw new Exception('Failed to delete timetable entries');
        }
    }
    /**
     * Generates the timetable data based on route parameters.
     *
     * @param array $timtableData An array containing route parameters: 'specialty_id', 'level_id',
     * 'semester_id', and 'student_batch_id'.
     * @param SchoolBranches $currentSchool The current school.
     * @return array
     * @throws Exception
     */
    public function generateTimeTable(array $timtableData, SchoolBranches $currentSchool): array
    {
        try {
            $timetables = Timetable::where('school_branch_id', $currentSchool->id)
                ->where('specialty_id', $timtableData['specialty_id'])
                ->where('level_id', $timtableData['level_id'])
                ->where('semester_id', $timtableData['semester_id'])
                ->where('student_batch_id', $timtableData['student_batch_id'])
                ->with(['course:id,course_title,course_code,credit', 'teacher:id,name'])
                ->get();

            if ($timetables->isEmpty()) {
                throw new Exception("No timetable records found");
            }

            $timeTable = [
                'monday' => [],
                'tuesday' => [],
                'wednesday' => [],
                'thursday' => [],
                'friday' => [],
                'saturday' => [],
                'sunday' => []
            ];

            foreach ($timetables as $entry) {
                $day = strtolower($entry->day_of_week);

                if (array_key_exists($day, $timeTable)) {
                    try {
                        $startTime = Carbon::parse($entry->start_time);
                        $endTime = Carbon::parse($entry->end_time);

                        // Log parsed times for debugging
                        Log::debug('Parsed times', [
                            'start_time' => $entry->start_time,
                            'parsed_start_time' => $startTime->toDateTimeString(),
                            'end_time' => $entry->end_time,
                            'parsed_end_time' => $endTime->toDateTimeString(),
                        ]);

                        // Calculate the duration
                        $durationInMinutes = $endTime->diffInMinutes($startTime);
                        Log::debug('Duration calculation', [
                            'duration_in_minutes' => $durationInMinutes,
                        ]);

                        // Format the duration string
                        $duration = '';
                        if ($durationInMinutes <= 0) {
                            $duration = '0 min';
                        } else {
                            $hours = floor($durationInMinutes / 60);
                            $minutes = $durationInMinutes % 60;
                            if ($hours > 0) {
                                $duration .= "{$hours}h ";
                            }
                            if ($minutes > 0) {
                                $duration .= "{$minutes}min";
                            }
                            $duration = trim($duration);
                        }

                        $timeTable[$day][] = [
                            'id' => $entry->id,
                            'course' => $entry->course->course_title,
                            'course_code' => $entry->course->course_code,
                            'course_credit' => $entry->course->credit,
                            'start_time' => $startTime->format('g:i A'),
                            'end_time' => $endTime->format('g:i A'),
                            'duration' => $this->formatDurationFromTimes($startTime, $endTime),
                            'teacher' => $entry->teacher->name,
                        ];
                    } catch (\Exception $e) {
                        Log::error('Failed to process timetable entry', [
                            'entry_id' => $entry->id,
                            'start_time' => $entry->start_time,
                            'end_time' => $entry->end_time,
                            'error' => $e->getMessage(),
                        ]);
                        continue; // Skip invalid entries
                    }
                }
            }

            // Filter out empty days
            $filteredTimeTable = array_filter($timeTable, function ($daySchedule) {
                return !empty($daySchedule);
            });

            return $filteredTimeTable;
        } catch (Exception $e) {
            Log::error('Error generating timetable: ' . $e->getMessage(), [
                'routeParams' => $timtableData,
                'currentSchoolId' => $currentSchool->id,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    public function formatDurationFromTimes(string $startTime, string $endTime): string
    {
        $start = Carbon::parse($startTime);
        $end = Carbon::parse($endTime);

        $diffInMinutes = $start->diffInMinutes($end);
        $hours = floor($diffInMinutes / 60);
        $minutes = $diffInMinutes % 60;

        $duration = '';
        if ($hours > 0) {
            $duration = "$hours h";
        }
        if ($minutes > 0 || $duration === '') {
            $duration = "$minutes min";
        }

        return trim($duration);
    }
    /**
     * Retrieves instructor availability data, considering existing timetable entries.
     *
     * @param string $specialtyId The ID of the specialty.
     * @param string $semesterId The ID of the semester.
     * @param SchoolBranches $currentSchool The current school.
     * @return array
     * @throws Exception
     */
    public function getInstructorAvailability(string $specialtyId, string $semesterId, SchoolBranches $currentSchool): array
    {
        try {
            $specialty = Specialty::with(['level:id,name'])->findOrFail($specialtyId);
            $levelId = $specialty->level->id;

            $teacherIds = TeacherSpecailtyPreference::where("specialty_id", $specialtyId)->pluck("teacher_id");
            $instructorAvailabilityData = InstructorAvailabilitySlot::whereIn("teacher_id", $teacherIds)
                ->where("school_branch_id", $currentSchool->id)
                ->where("school_semester_id", $semesterId)
                ->with(['teacher:id,name'])
                ->get();

            $timetables = Timetable::whereIn('teacher_id', $teacherIds)
                ->where('semester_id', $semesterId)
                ->get();

            $timetableData = $timetables->groupBy('teacher_id');

            $results = [];

            foreach ($instructorAvailabilityData as $availability) {
                $teacherId = $availability->teacher_id;
                $day = $availability->day_of_week;
                $startTime = Carbon::parse($availability->start_time);
                $endTime = Carbon::parse($availability->end_time);

                $availableSlots = [[$startTime, $endTime]];

                if (isset($timetableData[$teacherId])) {
                    $timetableEntries = $timetableData[$teacherId]->where('day_of_week', $day)->sortBy('start_time');

                    foreach ($timetableEntries as $timetable) {
                        $timetableStartTime = Carbon::parse($timetable->start_time);
                        $timetableEndTime = Carbon::parse($timetable->end_time);

                        $newSlots = [];
                        foreach ($availableSlots as [$slotStart, $slotEnd]) {
                            if ($timetableEndTime <= $slotStart || $timetableStartTime >= $slotEnd) {
                                $newSlots[] = [$slotStart, $slotEnd];
                                continue;
                            }

                            if ($timetableStartTime <= $slotStart && $timetableEndTime >= $slotEnd) {
                                continue;
                            }

                            if ($timetableStartTime <= $slotStart && $timetableEndTime < $slotEnd) {
                                $newSlots[] = [$timetableEndTime, $slotEnd];
                                continue;
                            }

                            if ($timetableStartTime > $slotStart && $timetableEndTime >= $slotEnd) {
                                $newSlots[] = [$slotStart, $timetableStartTime];
                                continue;
                            }

                            $newSlots[] = [$slotStart, $timetableStartTime];
                            $newSlots[] = [$timetableEndTime, $slotEnd];
                        }
                        $availableSlots = $newSlots;
                    }
                }

                foreach ($availableSlots as [$availableStartTime, $availableEndTime]) {
                    $results[] = [
                        'teacher_id' => $teacherId,
                        'semester_id' => $semesterId,
                        'day' => $day,
                        'available_start_time' => $availableStartTime->format('g:i A'),
                        'available_end_time' => $availableEndTime->format('g:i A'),
                        'teacher_name' => $availability->teacher->name,
                        'level_id' => $levelId,
                    ];
                }
            }

            return $results;
        } catch (ModelNotFoundException $e) {
            throw $e;
        } catch (Exception $e) {
            Log::error('Error retrieving instructor availability: ' . $e->getMessage(), [
                'specialtyId' => $specialtyId,
                'semesterId' => $semesterId,
                'currentSchoolId' => $currentSchool->id,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Retrieves the details of a specific timetable entry.
     *
     * @param string $entryId The ID of the timetable entry.
     * @param SchoolBranches $currentSchool The current school.
     * @return Timetable
     * @throws Exception
     */
    public function getTimeTableDetails(string $entryId, SchoolBranches $currentSchool): Timetable
    {
        try {
            return Timetable::where("school_branch_id", $currentSchool->id)
                ->where("id", $entryId)
                ->with(['course', 'teacher'])
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new Exception("Timetable entry Not Found");
        } catch (Exception $e) {
            Log::error('Error retrieving timetable details: ' . $e->getMessage(), [
                'entryId' => $entryId,
                'currentSchoolId' => $currentSchool->id,
                'trace' => $e->getTraceAsString(),
            ]);
            throw new Exception('Failed to retrieve timetable details');
        }
    }
}
