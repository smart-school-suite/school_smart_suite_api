<?php

namespace Database\Seeders;

use App\Models\InstructorAvailability;
use App\Models\InstructorAvailabilitySlot;
use App\Models\Studentbatch;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SchoolSemester;
use App\Models\Semester;
use App\Models\Specialty;
use App\Models\TeacherSpecailtyPreference;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class SchoolSemesterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $chunkSize = 1000;

        $schoolSemesters = [];
        $allowedSemesters = ["SES", "FIS"];
        $semesters = Semester::whereIn("program_name", $allowedSemesters)->get();
        $specialties = Specialty::all();
        $studentBatch = Studentbatch::first();

        $allowedDays = ["monday", "tuesday", "wednesday", "thursday", "friday", "saturday"];

        // Calculate semester dates
        $firstStart = Carbon::createFromFormat('Y-m-d', '2025-12-12');
        $firstEnd = $firstStart->clone()->addMonths(4)->addDays(15);
        $secondStart = $firstEnd->clone()->addDay();
        $secondEnd = $secondStart->clone()->addMonths(4)->addDays(15);

        // Possible start times for 1-hour slots (assuming school hours 8 AM to 5 PM)
        $possibleStarts = [
            '08:00', '09:00', '10:00', '11:00', '12:00',
            '13:00', '14:00', '15:00', '16:00'
        ];

        foreach ($specialties as $specialty) {
            foreach ($semesters as $semester) {
                $startDate = ($semester->program_name === 'SES') ? $firstStart : $secondStart;
                $endDate = ($semester->program_name === 'SES') ? $firstEnd : $secondEnd;

                $schoolSemesters[] = [
                    'id' => Str::uuid(),
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'school_year' => "2025-2026",
                    'semester_id' => $semester->id,
                    'specialty_id' => $specialty->id,
                    'status' => "active",  // active // pending
                    'timetable_published' => false,
                    'school_branch_id' => $specialty->school_branch_id,
                    'student_batch_id' => $studentBatch->id
                ];
            }
        }

        foreach (array_chunk($schoolSemesters, $chunkSize) as $chunk) {
            SchoolSemester::insert($chunk);
        }

        $allPrefs = TeacherSpecailtyPreference::with(['specailty'])->get()->groupBy('specialty_id');

        $teacherAvailability = [];

        foreach ($schoolSemesters as $schoolSemesterData) {
            $teacherSpecialtyPrefs = $allPrefs->get($schoolSemesterData['specialty_id'], collect());
            foreach ($teacherSpecialtyPrefs as $specialtyPref) {
                $teacherAvailability[] = [
                    "id" => Str::uuid(),
                    'school_branch_id' => $specialtyPref->school_branch_id,
                    'teacher_id' => $specialtyPref->teacher_id,
                    'level_id' => $specialtyPref->specailty->level_id,
                    'school_semester_id' => $schoolSemesterData['id'],
                    'specialty_id' => $schoolSemesterData['specialty_id'],
                    'status' => "added"
                ];
            }
        }

        foreach (array_chunk($teacherAvailability, $chunkSize) as $chunk) {
            InstructorAvailability::insert($chunk);
        }

        $teacherScheduleSlots = [];

        foreach ($teacherAvailability as $teacherScheduleData) {
            $numSlots = rand(4, 6);
            for ($i = 0; $i < $numSlots; $i++) {
                $start = Arr::random($possibleStarts);
                $startCarbon = Carbon::createFromTimeString($start);
                $end = $startCarbon->addHour()->format('H:i');

                $teacherScheduleSlots[] = [
                    "id" => Str::uuid(),
                    'school_branch_id' => $teacherScheduleData['school_branch_id'],
                    'teacher_id' => $teacherScheduleData['teacher_id'],
                    'day_of_week' => Arr::random($allowedDays),
                    'start_time' => $start,
                    'end_time' => $end,
                    'level_id' => $teacherScheduleData['level_id'],
                    'school_semester_id' => $teacherScheduleData['school_semester_id'],
                    'specialty_id' => $teacherScheduleData['specialty_id'],
                    'teacher_availability_id' => $teacherScheduleData['id']
                ];
            }
        }

        foreach (array_chunk($teacherScheduleSlots, $chunkSize) as $chunk) {
            InstructorAvailabilitySlot::insert($chunk);
        }
    }
}
