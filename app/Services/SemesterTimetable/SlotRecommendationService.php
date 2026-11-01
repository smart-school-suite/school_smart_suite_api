<?php

namespace App\Services\SemesterTimetable;

use App\Exceptions\AppException;
use App\Models\Courses;
use App\Models\Hall;
use App\Models\PeriodDuration\PeriodDuration;
use App\Models\SchoolSemester;
use App\Models\Teacher;
use App\Models\TeacherCoursePreference;
use App\Schedular\SemesterTimetable\Solution\Support\Text;
use App\Schedular\SemesterTimetable\Solution\Support\Time;
use App\Services\SemesterTimetable\Support\ActiveCommitments;
use Illuminate\Support\Collection;

class SlotRecommendationService
{
    private const DEFAULT_START = '08:00';
    private const DEFAULT_END = '17:00';

    public function __construct(private ActiveCommitments $commitments) {}

    public function recommend(object $currentSchool, array $params): array
    {
        $branchId = $currentSchool->id;
        $day = strtolower($params['day']);
        $duration = $this->duration($params['period_duration_id']);
        $course = !empty($params['course_id']) ? $this->course($branchId, $params['course_id']) : null;
        $teachers = $this->teachers($branchId, $params['teacher_id'] ?? null, $course);
        $hall = !empty($params['hall_id']) ? $this->hall($branchId, $params['hall_id']) : null;
        $semester = !empty($params['school_semester_id']) ? $this->semester($branchId, $params['school_semester_id']) : null;
        [$windowStart, $windowEnd] = $this->window($params, (int) $duration->minutes);

        $teacherIds = $teachers->pluck('id')->all();
        $resourceBusy = $this->commitments->timetableLessons($branchId, day: $day, teacherIds: $teacherIds, hallIds: array_filter([$hall?->id]))
            ->concat($this->commitments->jointCourseSlots($branchId, day: $day, teacherIds: $teacherIds, hallIds: array_filter([$hall?->id])));
        $classBusy = $semester
            ? $this->commitments->timetableLessons($branchId, onlySemesterId: $semester->id, day: $day)
                ->concat($this->commitments->jointCourseSlots($branchId, sharedWithSemesterId: $semester->id, day: $day, onlySharedWithSemester: true))
            : collect();

        $teacherLoad = $teachers->mapWithKeys(fn(Teacher $teacher) => [
            $teacher->id => $resourceBusy->where('teacher_id', $teacher->id)->sum(fn($slot) => $slot['end'] - $slot['start']),
        ]);

        $recommended = [];
        $unavailable = [];

        for ($start = $windowStart; $start + $duration->minutes <= $windowEnd; $start += $duration->minutes) {
            $end = $start + $duration->minutes;
            $reasons = [];

            $freeTeachers = $teachers
                ->filter(function (Teacher $teacher) use ($resourceBusy, $start, $end, &$reasons) {
                    $clash = $resourceBusy->first(fn($slot) => $slot['teacher_id'] === $teacher->id && Time::overlaps($start, $end, $slot['start'], $slot['end']));

                    if ($clash) {
                        $reasons[] = "{$teacher->name} teaches " . ($clash['course_name'] ?? 'another lesson') . ' at ' . Text::time($clash['start_time'], $clash['end_time']) . " ({$clash['source']})";
                    }

                    return !$clash;
                })
                ->sortBy(fn(Teacher $teacher) => $teacherLoad[$teacher->id] ?? 0)
                ->values();

            $hallClash = $hall
                ? $resourceBusy->first(fn($slot) => $slot['hall_id'] === $hall->id && Time::overlaps($start, $end, $slot['start'], $slot['end']))
                : null;
            if ($hallClash) {
                $reasons[] = "{$hall->name} is used for " . ($hallClash['course_name'] ?? 'another lesson') . ' at ' . Text::time($hallClash['start_time'], $hallClash['end_time']) . " ({$hallClash['source']})";
            }

            $classClash = $classBusy->first(fn($slot) => Time::overlaps($start, $end, $slot['start'], $slot['end']));
            if ($classClash) {
                $reasons[] = 'This class already has ' . ($classClash['course_name'] ?? 'a lesson') . ' at ' . Text::time($classClash['start_time'], $classClash['end_time'])
                    . ($classClash['type'] === 'joint' ? ' (joint course)' : '');
            }

            $slot = [
                'start_time' => Time::toTime($start),
                'end_time' => Time::toTime($end),
                'time' => Text::time(Time::toTime($start), Time::toTime($end)),
            ];

            if ($freeTeachers->isNotEmpty() && !$hallClash && !$classClash) {
                $recommended[] = [
                    ...$slot,
                    'teachers' => $freeTeachers->map(fn(Teacher $teacher) => ['id' => $teacher->id, 'name' => $teacher->name])->all(),
                    'hall' => $hall ? ['id' => $hall->id, 'name' => $hall->name] : null,
                ];
            } else {
                $unavailable[] = [...$slot, 'reasons' => array_values(array_unique($reasons))];
            }
        }

        return [
            'day' => $day,
            'day_label' => Text::day($day),
            'duration' => ['id' => $duration->id, 'name' => $duration->name, 'minutes' => (int) $duration->minutes],
            'window' => ['start_time' => Time::toTime($windowStart), 'end_time' => Time::toTime($windowEnd)],
            'course' => $course ? ['id' => $course->id, 'name' => $course->course_title] : null,
            'teachers' => $teachers->map(fn(Teacher $teacher) => ['id' => $teacher->id, 'name' => $teacher->name])->values()->all(),
            'hall' => $hall ? ['id' => $hall->id, 'name' => $hall->name] : null,
            'recommended' => $recommended,
            'unavailable' => $unavailable,
            'summary' => [
                'recommended_count' => count($recommended),
                'unavailable_count' => count($unavailable),
                'message' => $this->message(count($recommended), $day, (int) $duration->minutes, $teachers, $hall, $course),
            ],
        ];
    }

    private function message(int $count, string $day, int $minutes, Collection $teachers, ?Hall $hall, ?Courses $course): string
    {
        $who = $course && $teachers->count() > 1
            ? "a teacher of {$course->course_title}"
            : $teachers->pluck('name')->implode(', ');
        $where = $hall ? " in {$hall->name}" : '';

        return $count > 0
            ? "{$count} free {$minutes}-minute slot(s) on " . Text::day($day) . " for {$who}{$where}."
            : "No free {$minutes}-minute slot on " . Text::day($day) . " for {$who}{$where}. Try another day or a different " . ($hall ? 'hall or teacher.' : 'teacher.');
    }

    private function teachers(string $branchId, ?string $teacherId, ?Courses $course): Collection
    {
        if ($teacherId) {
            $teacher = Teacher::where('school_branch_id', $branchId)->find($teacherId);

            if (!$teacher) {
                $this->notFound('Teacher', "We couldn't find this teacher in your school.");
            }

            if ($course && !TeacherCoursePreference::where('school_branch_id', $branchId)->where('course_id', $course->id)->where('teacher_id', $teacher->id)->exists()) {
                throw new AppException(
                    "Teacher {$teacherId} does not teach course {$course->id}.",
                    422,
                    'Teacher Does Not Teach This Course',
                    "{$teacher->name} is not assigned to teach {$course->course_title}. Choose one of the course's teachers, or leave the teacher out to see all of them.",
                    null
                );
            }

            return collect([$teacher]);
        }

        $teachers = TeacherCoursePreference::where('school_branch_id', $branchId)
            ->where('course_id', $course->id)
            ->with('teacher')
            ->get()
            ->pluck('teacher')
            ->filter()
            ->unique('id')
            ->values();

        if ($teachers->isEmpty()) {
            throw new AppException(
                "No teacher is assigned to course {$course->id}.",
                422,
                'No Teacher For This Course',
                "No teacher is assigned to teach {$course->course_title} yet. Assign a teacher to the course first.",
                null
            );
        }

        return $teachers;
    }

    private function window(array $params, int $minutes): array
    {
        $start = Time::toMinutes($params['start_time'] ?? self::DEFAULT_START);
        $end = Time::toMinutes($params['end_time'] ?? self::DEFAULT_END);

        if ($end - $start < $minutes) {
            throw new AppException(
                'Time window is shorter than the period duration.',
                422,
                'Time Window Too Short',
                'The time window ' . Text::time(Time::toTime($start), Time::toTime($end)) . " is shorter than one {$minutes}-minute period. Choose a longer window or a shorter duration.",
                null
            );
        }

        return [$start, $end];
    }

    private function duration(string $id): PeriodDuration
    {
        $duration = PeriodDuration::find($id);

        if (!$duration || $duration->status === 'inactive' || (int) $duration->minutes <= 0) {
            $this->notFound('Period Duration', "We couldn't find this period duration, or it is no longer active.");
        }

        return $duration;
    }

    private function course(string $branchId, string $id): Courses
    {
        return Courses::where('school_branch_id', $branchId)->find($id)
            ?? $this->notFound('Course', "We couldn't find this course in your school.");
    }

    private function hall(string $branchId, string $id): Hall
    {
        return Hall::where('school_branch_id', $branchId)->find($id)
            ?? $this->notFound('Hall', "We couldn't find this hall in your school.");
    }

    private function semester(string $branchId, string $id): SchoolSemester
    {
        return SchoolSemester::where('school_branch_id', $branchId)->find($id)
            ?? $this->notFound('School Semester', "We couldn't find this semester in your school.");
    }

    private function notFound(string $what, string $description): never
    {
        throw new AppException("{$what} not found.", 404, "{$what} Not Found", $description, null);
    }
}
