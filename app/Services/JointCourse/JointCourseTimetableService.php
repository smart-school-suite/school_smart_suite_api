<?php

namespace App\Services\JointCourse;

use App\Exceptions\AppException;
use App\Exceptions\JointCourse\JointCourseSlotConflict;
use App\Models\AcademicYear\SystemAcademicYear;
use App\Models\Course\JointCourseSlot;
use App\Models\Course\SemesterJoinCourseReference;
use App\Models\Course\SemesterJointCourse;
use App\Models\Hall;
use App\Models\SchoolSemester;
use App\Models\Semester;
use App\Models\SemesterTimetable\SemesterTimetableVersion;
use App\Schedular\SemesterTimetable\Solution\Support\Text;
use App\Schedular\SemesterTimetable\Solution\Support\Time;
use App\Services\SemesterTimetable\Support\ActiveCommitments;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class JointCourseTimetableService
{
    private const DAYS = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
    private const GENERATED = [SemesterTimetableVersion::STATUS_OPTIMAL, SemesterTimetableVersion::STATUS_PARTIAL];
    private const DEFAULT_START = '08:00';
    private const DEFAULT_END = '17:00';
    private const STEP_MINUTES = 30;

    public function __construct(private ActiveCommitments $commitments) {}

    public function terms(object $currentSchool): array
    {
        $terms = [];

        $semesters = SchoolSemester::where('school_branch_id', $currentSchool->id)
            ->with(['schoolYear.systemAcademicYear', 'semester'])
            ->get();

        foreach ($semesters as $schoolSemester) {
            $year = $schoolSemester->schoolYear?->systemAcademicYear;

            if (!$year || !$schoolSemester->semester) {
                continue;
            }

            $key = "{$year->id}:{$schoolSemester->semester->id}";
            $terms[$key] ??= $this->termSummary($year, $schoolSemester->semester);
            $terms[$key]['is_running'] = $terms[$key]['is_running'] || $schoolSemester->isStillRunning();
        }

        $jointCourses = SemesterJointCourse::where('school_branch_id', $currentSchool->id)
            ->with(['schoolYear', 'semester'])
            ->withCount('jointCourseSlots')
            ->get();

        foreach ($jointCourses as $jointCourse) {
            if (!$jointCourse->schoolYear || !$jointCourse->semester) {
                continue;
            }

            $key = "{$jointCourse->school_year_id}:{$jointCourse->semester_id}";
            $terms[$key] ??= $this->termSummary($jointCourse->schoolYear, $jointCourse->semester);
            $terms[$key]['joint_course_count']++;
            $terms[$key]['configured_count'] += $jointCourse->joint_course_slots_count > 0 ? 1 : 0;
        }

        return collect($terms)
            ->sortBy([['year_start', 'desc'], ['semester_order', 'asc']])
            ->values()
            ->all();
    }

    public function term(object $currentSchool, string $schoolYearId, string $semesterId): array
    {
        $year = SystemAcademicYear::find($schoolYearId);
        $type = Semester::find($semesterId);

        if (!$year || !$type) {
            throw new AppException(
                "Term {$schoolYearId}:{$semesterId} not found.",
                404,
                'Term Not Found',
                "We couldn't find this academic year and semester. Pick another one from the list.",
                null
            );
        }

        $courses = SemesterJointCourse::where('school_branch_id', $currentSchool->id)
            ->where('school_year_id', $year->id)
            ->where('semester_id', $type->id)
            ->with($this->courseRelations())
            ->get()
            ->map(fn(SemesterJointCourse $jointCourse) => $this->presentCourse($jointCourse))
            ->sortBy(fn(array $course) => [$course['status'] === 'configured' ? 1 : 0, $course['course']['title']])
            ->values();

        $configured = $courses->where('status', 'configured')->count();
        $missing = $courses->count() - $configured;

        return [
            'term' => [...$this->termSummary($year, $type), 'joint_course_count' => $courses->count(), 'configured_count' => $configured],
            'summary' => [
                'course_count' => $courses->count(),
                'configured_count' => $configured,
                'not_configured_count' => $missing,
                'message' => match (true) {
                    $courses->isEmpty() => 'No joint courses in this term. A course becomes a joint course when it is shared by two or more specialties.',
                    $missing === 0 => 'Every joint course has its time set. Timetables of the linked specialties can be generated.',
                    default => "{$missing} joint course(s) still need a time. Timetables of the linked specialties can't be generated until they have one.",
                },
            ],
            'courses' => $courses->all(),
        ];
    }

    public function saveSlots(object $currentSchool, string $semesterJointCourseId, array $slots): array
    {
        $jointCourse = $this->findCourse($currentSchool, $semesterJointCourseId);
        $teachers = $this->courseTeachers($jointCourse);
        $halls = Hall::where('school_branch_id', $currentSchool->id)
            ->whereIn('id', collect($slots)->pluck('hall_id')->unique()->all())
            ->get()
            ->keyBy('id');

        $candidates = collect($slots)->values()->map(function (array $slot, int $index) use ($jointCourse, $teachers, $halls) {
            $teacherId = $slot['teacher_id'] ?? $teachers->first()?->id;
            $teacher = $teachers->firstWhere('id', $teacherId);

            if (!$teacher) {
                throw new AppException(
                    "Teacher {$teacherId} does not teach course {$jointCourse->course_id}.",
                    422,
                    $teachers->isEmpty() ? 'No Teacher For This Course' : 'Teacher Does Not Teach This Course',
                    $teachers->isEmpty()
                        ? "No teacher is assigned to teach {$jointCourse->course->course_title} yet. Assign a teacher to the course first."
                        : "The chosen teacher is not assigned to {$jointCourse->course->course_title}. Choose one of its teachers.",
                    null
                );
            }

            $hall = $halls->get($slot['hall_id']) ?? throw new AppException(
                "Hall {$slot['hall_id']} not found.",
                404,
                'Hall Not Found',
                "We couldn't find one of the chosen halls in your school.",
                null
            );

            return ActiveCommitments::normalize([
                'index' => $index,
                'day' => strtolower($slot['day']),
                'start_time' => $slot['start_time'],
                'end_time' => $slot['end_time'],
                'teacher_id' => $teacher->id,
                'teacher_name' => $teacher->name,
                'hall_id' => $hall->id,
                'hall_name' => $hall->name,
                'course_id' => $jointCourse->course_id,
                'course_name' => $jointCourse->course->course_title,
            ], 'joint');
        });

        $busy = $this->busy(
            $jointCourse,
            teacherIds: $candidates->pluck('teacher_id')->unique()->all(),
            hallIds: $candidates->pluck('hall_id')->unique()->all(),
        );

        $conflicts = [];
        $warnings = [];

        foreach ($candidates as $i => $slot) {
            foreach ($candidates->slice($i + 1) as $other) {
                if ($slot['day'] === $other['day'] && Time::overlaps($slot['start'], $slot['end'], $other['start'], $other['end'])) {
                    $conflicts[] = $this->conflict('overlap', $slot, [...$other, 'source' => 'these times']);
                }
            }

            foreach ($busy as $other) {
                $clash = $this->clash($slot, $other);

                if ($clash === 'linked_timetable') {
                    $warnings[] = $this->conflict($clash, $slot, $other);
                } elseif ($clash) {
                    $conflicts[] = $this->conflict($clash, $slot, $other);
                }
            }
        }

        if ($conflicts) {
            throw new JointCourseSlotConflict($conflicts);
        }

        DB::transaction(function () use ($currentSchool, $jointCourse, $candidates) {
            JointCourseSlot::where('school_branch_id', $currentSchool->id)
                ->where('semester_joint_course_id', $jointCourse->id)
                ->delete();

            foreach ($candidates as $slot) {
                JointCourseSlot::create([
                    'school_branch_id' => $currentSchool->id,
                    'semester_joint_course_id' => $jointCourse->id,
                    'teacher_id' => $slot['teacher_id'],
                    'hall_id' => $slot['hall_id'],
                    'day' => $slot['day'],
                    'start_time' => $slot['start_time'],
                    'end_time' => $slot['end_time'],
                ]);
            }
        });

        $course = $this->presentCourse($this->findCourse($currentSchool, $jointCourse->id));
        $affected = collect($course['affected_timetables'])->pluck('label')->unique()->values();

        return [
            'course' => $course,
            'warnings' => array_values(collect($warnings)->unique('message')->all()),
            'message' => $candidates->isEmpty()
                ? "The times of {$course['course']['title']} were removed."
                : "The times of {$course['course']['title']} were saved." . ($affected->isNotEmpty()
                    ? ' Generate these timetables again so they use the new times: ' . $affected->implode(', ') . '.'
                    : ''),
        ];
    }

    public function recommend(object $currentSchool, string $semesterJointCourseId, array $params): array
    {
        $jointCourse = $this->findCourse($currentSchool, $semesterJointCourseId);
        $day = strtolower($params['day']);
        $minutes = (int) $params['duration_minutes'];
        $teachers = $this->courseTeachers($jointCourse);

        if (!empty($params['teacher_id'])) {
            $teachers = $teachers->where('id', $params['teacher_id'])->values();
        }

        if ($teachers->isEmpty()) {
            throw new AppException(
                "No usable teacher for course {$jointCourse->course_id}.",
                422,
                empty($params['teacher_id']) ? 'No Teacher For This Course' : 'Teacher Does Not Teach This Course',
                empty($params['teacher_id'])
                    ? "No teacher is assigned to teach {$jointCourse->course->course_title} yet. Assign a teacher to the course first."
                    : "The chosen teacher is not assigned to {$jointCourse->course->course_title}. Choose one of its teachers.",
                null
            );
        }

        $hall = null;
        if (!empty($params['hall_id'])) {
            $hall = Hall::where('school_branch_id', $currentSchool->id)->find($params['hall_id'])
                ?? throw new AppException('Hall not found.', 404, 'Hall Not Found', "We couldn't find this hall in your school.", null);
        }

        $windowStart = Time::toMinutes($params['start_time'] ?? self::DEFAULT_START);
        $windowEnd = Time::toMinutes($params['end_time'] ?? self::DEFAULT_END);

        if ($windowEnd - $windowStart < $minutes) {
            throw new AppException(
                'Time window is shorter than the duration.',
                422,
                'Time Window Too Short',
                'The time window ' . Text::time(Time::toTime($windowStart), Time::toTime($windowEnd)) . " is shorter than {$minutes} minutes. Choose a longer window or a shorter duration.",
                null
            );
        }

        $busy = $this->busy($jointCourse, $day, $teachers->pluck('id')->all(), array_filter([$hall?->id]))
            ->reject(fn(array $other) => $other['kind'] === 'linked_timetable');

        $recommended = [];
        $unavailable = [];

        for ($start = $windowStart; $start + $minutes <= $windowEnd; $start += self::STEP_MINUTES) {
            $slot = ['day' => $day, 'start' => $start, 'end' => $start + $minutes, 'teacher_id' => null, 'hall_id' => $hall?->id];
            $reasons = $busy->map(fn(array $other) => ($clash = $this->clash($slot, $other)) ? $this->reason($clash, $other) : null)->filter();

            $teacherReasons = collect();
            $freeTeachers = $teachers->filter(function ($teacher) use ($busy, $slot, $teacherReasons) {
                $taken = $busy->first(fn(array $other) => $this->clash([...$slot, 'teacher_id' => $teacher->id, 'hall_id' => null], $other) === 'teacher');

                if ($taken) {
                    $teacherReasons->push($this->reason('teacher', $taken));
                }

                return !$taken;
            })->values();

            if ($freeTeachers->isEmpty()) {
                $reasons = $reasons->concat($teacherReasons);
            }

            $entry = [
                'start_time' => Time::toTime($slot['start']),
                'end_time' => Time::toTime($slot['end']),
                'time' => Text::time(Time::toTime($slot['start']), Time::toTime($slot['end'])),
            ];

            if ($freeTeachers->isNotEmpty() && $reasons->isEmpty()) {
                $recommended[] = [
                    ...$entry,
                    'teachers' => $freeTeachers->map(fn($teacher) => ['id' => $teacher->id, 'name' => $teacher->name])->all(),
                    'hall' => $hall ? ['id' => $hall->id, 'name' => $hall->name] : null,
                ];
            } else {
                $unavailable[] = [...$entry, 'reasons' => $reasons->unique()->values()->all()];
            }
        }

        return [
            'day' => $day,
            'day_label' => Text::day($day),
            'duration_minutes' => $minutes,
            'window' => ['start_time' => Time::toTime($windowStart), 'end_time' => Time::toTime($windowEnd)],
            'teachers' => $teachers->map(fn($teacher) => ['id' => $teacher->id, 'name' => $teacher->name])->values()->all(),
            'hall' => $hall ? ['id' => $hall->id, 'name' => $hall->name] : null,
            'recommended' => $recommended,
            'unavailable' => $unavailable,
            'summary' => [
                'recommended_count' => count($recommended),
                'unavailable_count' => count($unavailable),
                'message' => count($recommended) > 0
                    ? count($recommended) . " free {$minutes}-minute time(s) on " . Text::day($day) . " for {$jointCourse->course->course_title}."
                    : "No free {$minutes}-minute time on " . Text::day($day) . ' for ' . $jointCourse->course->course_title . '. Try another day' . ($hall ? ', hall' : '') . ' or a shorter duration.',
            ],
        ];
    }

    private function busy(SemesterJointCourse $jointCourse, ?string $day = null, array $teacherIds = [], array $hallIds = []): Collection
    {
        $linkedSemesterIds = $jointCourse->semesterJointCourseRef->pluck('school_semester_id')->all();

        $otherJointCourses = SemesterJointCourse::where('school_branch_id', $jointCourse->school_branch_id)
            ->where('school_year_id', $jointCourse->school_year_id)
            ->where('semester_id', $jointCourse->semester_id)
            ->where('id', '!=', $jointCourse->id)
            ->with(['course', 'jointCourseSlots.teacher', 'jointCourseSlots.hall', 'semesterJointCourseRef'])
            ->get()
            ->flatMap(fn(SemesterJointCourse $other) => $other->jointCourseSlots
                ->filter(fn(JointCourseSlot $slot) => !$day || strtolower($slot->day) === $day)
                ->map(fn(JointCourseSlot $slot) => ActiveCommitments::normalize([
                    'kind' => 'joint',
                    'day' => strtolower($slot->day),
                    'start_time' => $slot->start_time,
                    'end_time' => $slot->end_time,
                    'teacher_id' => $slot->teacher_id,
                    'teacher_name' => $slot->teacher?->name,
                    'hall_id' => $slot->hall_id,
                    'hall_name' => $slot->hall?->name,
                    'course_id' => $other->course_id,
                    'course_name' => $other->course?->course_title,
                    'source' => 'joint course',
                    'shares_students' => $other->semesterJointCourseRef->pluck('school_semester_id')->intersect($linkedSemesterIds)->isNotEmpty(),
                ], 'joint')))
            ->filter();

        $otherTimetables = ($teacherIds || $hallIds)
            ? $this->commitments->timetableLessons(
                $jointCourse->school_branch_id,
                day: $day,
                teacherIds: $teacherIds,
                hallIds: $hallIds,
                exceptSemesterIds: $linkedSemesterIds,
            )->map(fn(array $lesson) => [...$lesson, 'kind' => 'timetable'])
            : collect();

        $linkedTimetables = collect($linkedSemesterIds)
            ->flatMap(fn(string $schoolSemesterId) => $this->commitments->timetableLessons($jointCourse->school_branch_id, onlySemesterId: $schoolSemesterId, day: $day))
            ->reject(fn(array $lesson) => $lesson['course_id'] === $jointCourse->course_id)
            ->map(fn(array $lesson) => [...$lesson, 'kind' => 'linked_timetable']);

        return $otherJointCourses->concat($otherTimetables)->concat($linkedTimetables)->values();
    }

    private function clash(array $slot, array $other): ?string
    {
        if ($slot['day'] !== $other['day'] || !Time::overlaps($slot['start'], $slot['end'], $other['start'], $other['end'])) {
            return null;
        }

        return match (true) {
            $other['kind'] === 'linked_timetable' => 'linked_timetable',
            $slot['teacher_id'] && $slot['teacher_id'] === $other['teacher_id'] => 'teacher',
            $slot['hall_id'] && $slot['hall_id'] === $other['hall_id'] => 'hall',
            $other['kind'] === 'joint' && $other['shares_students'] => 'students',
            default => null,
        };
    }

    private function reason(string $clash, array $other): string
    {
        $course = $other['course_name'] ?? 'another lesson';
        $when = Text::day($other['day']) . ' ' . Text::time($other['start_time'], $other['end_time']);
        $source = $other['source'] ?? 'another timetable';

        return match ($clash) {
            'teacher' => ($other['teacher_name'] ?? 'The teacher') . " teaches {$course} on {$when} ({$source})",
            'hall' => ($other['hall_name'] ?? 'The hall') . " is used for {$course} on {$when} ({$source})",
            'students' => "Some of the same students attend the joint course {$course} on {$when}",
            'overlap' => "It overlaps another time of this course on {$when}",
            default => "{$source} has {$course} on {$when}. Generate that timetable again so it moves around the joint course",
        };
    }

    private function conflict(string $clash, array $slot, array $other): array
    {
        return [
            'type' => $clash,
            'slot_index' => $slot['index'],
            'day' => $slot['day'],
            'day_label' => Text::day($slot['day']),
            'time' => Text::time($slot['start_time'], $slot['end_time']),
            'message' => Text::day($slot['day']) . ' ' . Text::time($slot['start_time'], $slot['end_time']) . ': ' . $this->reason($clash, $other) . '.',
            'conflicts_with' => [
                'course' => $other['course_name'] ?? null,
                'teacher' => $other['teacher_name'] ?? null,
                'hall' => $other['hall_name'] ?? null,
                'time' => Text::day($other['day']) . ' ' . Text::time($other['start_time'], $other['end_time']),
                'source' => $other['source'] ?? null,
            ],
        ];
    }

    private function presentCourse(SemesterJointCourse $jointCourse): array
    {
        $refs = $jointCourse->semesterJointCourseRef->filter(fn(SemesterJoinCourseReference $ref) => $ref->schoolSemester);
        $slots = $jointCourse->jointCourseSlots
            ->sortBy(fn(JointCourseSlot $slot) => [$this->dayOrder($slot->day), Time::toMinutes($slot->start_time)])
            ->values();
        $lastChange = $slots->max('updated_at');

        return [
            'id' => $jointCourse->id,
            'course' => [
                'id' => $jointCourse->course_id,
                'code' => $jointCourse->course?->course_code,
                'title' => $jointCourse->course?->course_title,
                'credit' => $jointCourse->course?->credit,
            ],
            'teachers' => $this->courseTeachers($jointCourse)->map(fn($teacher) => ['id' => $teacher->id, 'name' => $teacher->name])->values()->all(),
            'specialties' => $refs->map(fn(SemesterJoinCourseReference $ref) => $this->presentSpecialty($ref->schoolSemester))->values()->all(),
            'slots' => $slots->map(fn(JointCourseSlot $slot) => [
                'id' => $slot->id,
                'day' => strtolower($slot->day),
                'day_label' => Text::day($slot->day),
                'start_time' => Time::toTime(Time::toMinutes($slot->start_time)),
                'end_time' => Time::toTime(Time::toMinutes($slot->end_time)),
                'time' => Text::time($slot->start_time, $slot->end_time),
                'duration_minutes' => Time::toMinutes($slot->end_time) - Time::toMinutes($slot->start_time),
                'teacher' => ['id' => $slot->teacher_id, 'name' => $slot->teacher?->name],
                'hall' => ['id' => $slot->hall_id, 'name' => $slot->hall?->name],
            ])->all(),
            'slot_count' => $slots->count(),
            'weekly_minutes' => $slots->sum(fn(JointCourseSlot $slot) => Time::toMinutes($slot->end_time) - Time::toMinutes($slot->start_time)),
            'status' => $slots->isEmpty() ? 'not_configured' : 'configured',
            'status_label' => $slots->isEmpty() ? 'No time set' : $slots->count() . ' time(s) set',
            'affected_timetables' => $refs
                ->map(fn(SemesterJoinCourseReference $ref) => $this->affectedTimetable($ref->schoolSemester, $lastChange))
                ->filter()
                ->values()
                ->all(),
        ];
    }

    private function presentSpecialty(SchoolSemester $schoolSemester): array
    {
        $specialty = $schoolSemester->schoolYear?->specialty;
        $today = now()->startOfDay();

        return [
            'school_semester_id' => $schoolSemester->id,
            'specialty_id' => $specialty?->id,
            'specialty_name' => $specialty?->specialty_name,
            'level_name' => $specialty?->level?->name,
            'label' => trim(($specialty?->specialty_name ?? 'Specialty') . ($specialty?->level?->name ? " · {$specialty->level->name}" : '')),
            'semester_status' => match (true) {
                !$schoolSemester->isStillRunning() => 'ended',
                Carbon::parse($schoolSemester->start_date)->startOfDay()->gt($today) => 'upcoming',
                default => 'running',
            },
        ];
    }

    private function affectedTimetable(SchoolSemester $schoolSemester, mixed $lastChange): ?array
    {
        $generated = $schoolSemester->semesterTimetableVersion
            ->whereIn('scheduler_status', self::GENERATED)
            ->sortByDesc('version_number')
            ->values();

        if ($generated->isEmpty()) {
            return null;
        }

        $latest = $generated->first();
        $active = $generated->first(fn(SemesterTimetableVersion $version) => $version->has_slots);
        $specialty = $this->presentSpecialty($schoolSemester);

        return [
            'school_semester_id' => $schoolSemester->id,
            'label' => "{$specialty['label']} ({$latest->label})",
            'specialty' => $specialty['label'],
            'latest_version' => $latest->only(['id', 'label', 'version_number', 'scheduler_status']),
            'active_version' => $active?->only(['id', 'label', 'version_number', 'scheduler_status']),
            'needs_regeneration' => $lastChange !== null && Carbon::parse($latest->updated_at)->lte(Carbon::parse($lastChange)),
        ];
    }

    private function courseTeachers(SemesterJointCourse $jointCourse): Collection
    {
        return ($jointCourse->course?->teacherCoursePreference ?? collect())
            ->pluck('teacher')
            ->filter()
            ->unique('id')
            ->values();
    }

    private function findCourse(object $currentSchool, string $semesterJointCourseId): SemesterJointCourse
    {
        return SemesterJointCourse::where('school_branch_id', $currentSchool->id)
            ->with($this->courseRelations())
            ->find($semesterJointCourseId)
            ?? throw new AppException(
                "Semester joint course {$semesterJointCourseId} not found.",
                404,
                'Joint Course Not Found',
                "We couldn't find this joint course. It may have been deleted.",
                null
            );
    }

    private function courseRelations(): array
    {
        return [
            'course.teacherCoursePreference.teacher',
            'jointCourseSlots.teacher',
            'jointCourseSlots.hall',
            'semesterJointCourseRef.schoolSemester.schoolYear.specialty.level',
            'semesterJointCourseRef.schoolSemester.semesterTimetableVersion' => fn($query) => $query->withExists('timeTableSlot as has_slots'),
        ];
    }

    private function termSummary(SystemAcademicYear $year, Semester $type): array
    {
        return [
            'id' => "{$year->id}:{$type->id}",
            'school_year_id' => $year->id,
            'semester_id' => $type->id,
            'academic_year' => $year->name,
            'semester' => $type->name,
            'label' => "{$type->name} · {$year->name}",
            'year_start' => $year->year_start,
            'semester_order' => (int) $type->count,
            'joint_course_count' => 0,
            'configured_count' => 0,
            'is_running' => false,
        ];
    }

    private function dayOrder(?string $day): int
    {
        $index = array_search(strtolower((string) $day), self::DAYS, true);

        return $index === false ? 9 : $index;
    }
}
