<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\InstructorAvailability;
use App\Models\SchoolSemester;
use App\Models\Timetable;

class CreateSpecailtyTimeTableService
{
    // Implement your logic here


    public function createTimetableByAvailability(array $scheduleEntries, $currentSchool, $semesterId) {
        $conflicts = [];
        $entriesToInsert = [];
        $schedulesToCheck = [];

        foreach ($scheduleEntries as $entry) {
            $schedulesToCheck[] = [
                'teacher_id' => $entry['teacher_id'],
                'day_of_week' => $entry['day_of_week'],
                'starting_time' => $entry['starting_time'],
                'ending_time' => $entry['ending_time']
            ];
            $entriesToInsert[] = [
                'school_branch_id' => $currentSchool->id,
                'course_id' => $entry['course_id'],
                'teacher_id' => $entry['teacher_id'],
                'day_of_week' => $entry['day_of_week'],
                'specialty_id' => $entry['specialty_id'],
                'level_id' => $entry['level_id'],
                'semester_id' => $entry['semester_id'],
                'student_batch_id' => $entry['student_batch_id'],
                'start_time' => $entry['starting_time'],
                'end_time' => $entry['ending_time'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $existingSchedules = Timetable::where("school_branch_id", $currentSchool->id)
            ->whereIn('teacher_id', array_column($schedulesToCheck, 'teacher_id'))
            ->whereIn('day_of_week', array_column($schedulesToCheck, 'day_of_week'))
            ->get();

        foreach ($schedulesToCheck as $schedule) {

            foreach ($existingSchedules as $existing) {
                if ($existing->teacher_id == $schedule['teacher_id'] && $existing->day_of_week == $schedule['day_of_week']) {
                    if ($this->isOverlapping($existing, $schedule)) {
                        $conflicts[] = [
                            'teacher_id' => $schedule['teacher_id'],
                            'day' => $schedule['day_of_week'],
                            'conflict_between' => [
                                'new_appointment' => [
                                    'starting_time' => $schedule['starting_time'],
                                    'ending_time' => $schedule['ending_time'],
                                ],
                                'existing_appointment' => [
                                    'starting_time' => $existing->start_time,
                                    'ending_time' => $existing->end_time,
                                ]
                            ],
                            'conflict_type' => 'overlap'
                        ];
                    }
                }
            }


            $isUnavailable = InstructorAvailability::where('school_branch_id', $currentSchool->id)
                ->where('teacher_id', $schedule['teacher_id'])
                ->where('semester_id', $schedule['semester_id'])
                ->where('day_of_week', $schedule['day_of_week'])
                ->where(function ($query) use ($schedule) {
                    $query->whereBetween('start_time', [$schedule['starting_time'], $schedule['ending_time']])
                        ->orWhereBetween('end_time', [$schedule['starting_time'], $schedule['ending_time']])
                        ->orWhere(function ($query) use ($schedule) {
                            $query->where('start_time', '<=', $schedule['starting_time'])
                                ->where('end_time', '>=', $schedule['ending_time']);
                        });
                })
                ->doesntExist();

            if ($isUnavailable) {
                $conflicts[] = [
                    'teacher_id' => $schedule['teacher_id'],
                    'day' => $schedule['day_of_week'],
                    'conflict_between' => [
                        'new_appointment' => [
                            'starting_time' => $schedule['starting_time'],
                            'ending_time' => $schedule['ending_time'],
                        ]
                    ],
                    'conflict_type' => 'unavailability',
                    'message' => 'Instructor is unavailable during this time.'
                ];
            }
        }

        if (!empty($conflicts)) {
            return ['error' => true, 'conflicts' => $conflicts];
        }

        $semester = SchoolSemester::find($semesterId);
        DB::transaction(function () use ($entriesToInsert, $semester) {
            Timetable::insert($entriesToInsert);
            $semester->timetable_published = true;
            $semester->save();
        });

        return ['error' => false, 'data' => Timetable::where('school_branch_id', $currentSchool->id)
            ->whereIn('start_time', array_column($entriesToInsert, 'start_time'))
            ->whereIn('end_time', array_column($entriesToInsert, 'end_time'))
            ->get()];
    }

    public function createTimetable(array $scheduleEntries, $currentSchool, $semesterId)
    {
        $conflicts = [];
        $entriesToInsert = [];
        $schedulesToCheck = [];

        foreach ($scheduleEntries as $entry) {
            $schedulesToCheck[] = [
                'teacher_id' => $entry['teacher_id'],
                'day_of_week' => $entry['day_of_week'],
                'starting_time' => $entry['starting_time'],
                'ending_time' => $entry['ending_time']
            ];
            $entriesToInsert[] = [
                'school_branch_id' => $currentSchool->id,
                'course_id' => $entry['course_id'],
                'teacher_id' => $entry['teacher_id'],
                'day_of_week' => $entry['day_of_week'],
                'specialty_id' => $entry['specialty_id'],
                'level_id' => $entry['level_id'],
                'semester_id' => $entry['semester_id'],
                'student_batch_id' => $entry['student_batch_id'],
                'start_time' => $entry['starting_time'],
                'end_time' => $entry['ending_time'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }


        $existingSchedules = Timetable::where("school_branch_id", $currentSchool->id)
            ->whereIn('teacher_id', array_column($schedulesToCheck, 'teacher_id'))
            ->whereIn('day_of_week', array_column($schedulesToCheck, 'day_of_week'))
            ->get();

        foreach ($schedulesToCheck as $schedule) {
            foreach ($existingSchedules as $existing) {
                if ($existing->teacher_id == $schedule['teacher_id'] && $existing->day_of_week == $schedule['day_of_week']) {
                    if ($this->isOverlapping($existing, $schedule)) {
                        $conflicts[] = [
                            'teacher_id' => $schedule['teacher_id'],
                            'day' => $schedule['day_of_week'],
                            'conflict_between' => [
                                'new_appointment' => [
                                    'starting_time' => $schedule['starting_time'],
                                    'ending_time' => $schedule['ending_time'],
                                ],
                                'existing_appointment' => [
                                    'starting_time' => $existing->start_time,
                                    'ending_time' => $existing->end_time,
                                ]
                            ]
                        ];
                    }
                }
            }
        }


        if (!empty($conflicts)) {
            return ['error' => true, 'conflicts' => $conflicts];
        }

        $semester = SchoolSemester::find($semesterId);
        DB::transaction(function () use ($entriesToInsert, $semester) {
            Timetable::insert($entriesToInsert);
            $semester->timetable_published = true;
            $semester->save();
        });


        return ['error' => false, 'data' => Timetable::where('school_branch_id', $currentSchool->id)
            ->whereIn('start_time', array_column($entriesToInsert, 'start_time'))
            ->whereIn('end_time', array_column($entriesToInsert, 'end_time'))
            ->get()];
    }
    protected function isOverlapping($existing, $newSchedule)
    {
        return ($newSchedule['starting_time'] < $existing->end_time && $newSchedule['ending_time'] > $existing->start_time);
    }
}
