<?php

namespace Database\Seeders;

use App\Models\Course\CourseSpecialty;
use App\Models\Course\JointCourseSlot;
use App\Models\Course\SemesterJointCourse;
use App\Models\Hall;
use App\Models\SchoolSemester;
use App\Models\SemesterTimetable\SemesterTimetableSlot;
use App\Models\SpecialtyHall;
use App\Models\TeacherCoursePreference;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class TimetableSlotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $this->generateTimetableSlots();
        $this->generateJointCourseSlots();
    }

    public function generateTimetableSlots()
    {
        $branchId = '50207b5e-65fb-46ca-b507-963931071777';

        $schoolSemesters = SchoolSemester::where('school_branch_id', $branchId)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->with(['specialty'])
            ->take(100)
            ->get();

        $possibleStarts = [];
        for ($h = 7; $h <= 18; $h++) {
            foreach ([0, 30] as $m) {
                $possibleStarts[] = sprintf('%02d:%02d', $h, $m);
            }
        }

         $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        $teacherUsage = [];

        foreach ($schoolSemesters as $semester) {
            $specialtyId = $semester->specialty_id;

            $courseSpecialties = CourseSpecialty::where('specialty_id', $specialtyId)
                ->with(['course' => function ($q) use ($semester) {
                    $q->where('semester_id', $semester->semester_id);
                }])
                ->get();

            $courses = $courseSpecialties->pluck('course')->filter();

            if ($courses->isEmpty()) {
                continue;
            }

            $halls = SpecialtyHall::where('specialty_id', $specialtyId)
                ->with('hall')
                ->get()
                ->pluck('hall')
                ->filter();

            if ($halls->isEmpty()) {
                continue;
            }



            $teacherPreferences = TeacherCoursePreference::whereIn('course_id', $courses->pluck('id'))
                ->get()
                ->groupBy('course_id');

            foreach ($courses as $course) {
                $coursePreferences = $teacherPreferences->get($course->id, collect());

                if ($coursePreferences->isEmpty()) {
                    continue;
                }

                $possibleTeachers = $coursePreferences->pluck('teacher_id')->unique();

                $weeklyOccurrences = match (true) {
                    $course->credit >= 4 => rand(2, 3),
                    $course->credit == 3 => rand(1, 3),
                    default              => rand(1, 2),
                };

                $totalSlotsForCourse = $weeklyOccurrences * rand(10, 14);

                $totalSlotsForCourse = min($totalSlotsForCourse, 40);

                for ($s = 0; $s < $totalSlotsForCourse; $s++) {
                    $day   = $days[array_rand($days)];
                    $start = $possibleStarts[array_rand($possibleStarts)];

                    $startTime = \Carbon\Carbon::createFromTimeString($start);
                    $endTime   = $startTime->copy()->addHour();

                    $teacherId = null;

                    $shuffledTeachers = $possibleTeachers->shuffle();

                    foreach ($shuffledTeachers as $tid) {
                        $currentCount = $teacherUsage[$tid] ?? 0;

                        if ($currentCount >= 4) {
                            continue;
                        }

                        $conflict = SemesterTimetableSlot::where('teacher_id', $tid)
                            ->where('day', $day)
                            ->where('start_time', $start)
                            ->where('school_semester_id', $semester->id)
                            ->exists();

                        if (!$conflict) {
                            $teacherId = $tid;
                            break;
                        }
                    }

                    if (!$teacherId) {
                        continue;
                    }

                    $hall = $halls->random();

                    SemesterTimetableSlot::create([
                        'school_branch_id'    => $branchId,
                        'specialty_id'        => $specialtyId,
                        'level_id'            => $semester->specialty->level_id ?? null,
                        'course_id'           => $course->id,
                        'teacher_id'          => $teacherId,
                        'day'                 => $day,
                        'start_time'          => $start,
                        'end_time'            => $endTime->format('H:i'),
                        'school_semester_id'  => $semester->id,
                        'hall_id'             => $hall->id,
                        'student_batch_id'    => $semester->student_batch_id,
                    ]);

                    // Update global usage counter
                    $teacherUsage[$teacherId] = ($teacherUsage[$teacherId] ?? 0) + 1;
                }
            }
        }
    }
    public function generateJointCourseSlots()
    {
        $branchId = '50207b5e-65fb-46ca-b507-963931071777';

        $halls = Hall::where('school_branch_id', $branchId)->get();

        if ($halls->isEmpty()) {
            return;
        }

        $semesterJointCourses = SemesterJointCourse::where("school_branch_id", $branchId)
            ->with(['course.teacherCoursePreference', 'semesterJointCourseRef' => function ($query) {
                $query->with(['schoolSemester' => function ($query) {
                    $query->where("start_date", "<=", now())
                        ->where("end_date", ">=", now());
                }]);
            }])
            ->get();

        $possibleStarts = [];
        for ($h = 7; $h <= 17; $h++) {
            foreach ([0, 15, 30, 45] as $m) {
                $possibleStarts[] = sprintf('%02d:%02d', $h, $m);
            }
        }

        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        foreach ($semesterJointCourses as $jointCourse) {
            $course = $jointCourse->course;

            if (!$course) {
                continue;
            }

            $preferences = $course->teacherCoursePreference ?? collect();

            if ($preferences->isEmpty()) {
                continue;
            }

            $teacherIds = $preferences->pluck('teacher_id')->unique()->all();

            if (empty($teacherIds)) {
                continue;
            }

            $numberOfSlots = rand(1, 3);

            $used = [];

            for ($i = 0; $i < $numberOfSlots; $i++) {
                $day   = $days[array_rand($days)];
                $start = $possibleStarts[array_rand($possibleStarts)];

                $startParts = explode(':', $start);
                $endHour = (int)$startParts[0] + 2;
                $end     = sprintf('%02d:%s', $endHour, $startParts[1]);

                $key = "{$day}-{$start}";
                if (in_array($key, $used)) {
                    $i--;
                    continue;
                }
                $used[] = $key;

                $hall      = $halls->random();
                $teacherId = $teacherIds[array_rand($teacherIds)];

                JointCourseSlot::create([
                    'start_time'               => $start,
                    'end_time'                 => $end,
                    'day'                      => $day,
                    'school_branch_id'         => $branchId,
                    'course_id'                => $course->id,
                    'hall_id'                  => $hall->id,
                    'teacher_id'               => $teacherId,
                    'semester_joint_course_id' => $jointCourse->id,
                ]);
            }
        }
    }
}
