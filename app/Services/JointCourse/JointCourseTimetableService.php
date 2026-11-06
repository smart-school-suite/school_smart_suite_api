<?php

namespace App\Services\JointCourse;

use App\Exceptions\AppException;
use App\Exceptions\JointCourse\JointCourseSlotConflict;
use App\Models\AcademicYear\SystemAcademicYear;
use App\Models\Course\JointCourseSlot;
use App\Models\Course\SemesterJoinCourseReference;
use App\Models\Course\SemesterJointCourse;
use App\Models\Hall;
use App\Models\InstructorAvailability;
use App\Models\PeriodDuration\PeriodDuration;
use App\Models\SchoolSemester;
use App\Models\Schoolbranches;
use App\Models\Semester;
use App\Models\SemesterTimetable\SemesterTimetableVersion;
use App\Schedular\SemesterTimetable\Solution\Support\Text;
use App\Schedular\SemesterTimetable\Solution\Support\Time;
use App\Services\SemesterTimetable\Support\ActiveCommitments;
use App\Services\SemesterTimetable\TimetableGenerationDispatcher;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class JointCourseTimetableService
{
    private const DAYS = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
    private const GENERATED = [SemesterTimetableVersion::STATUS_OPTIMAL, SemesterTimetableVersion::STATUS_PARTIAL];
    private const DEFAULT_START = '08:00';
    private const DEFAULT_END = '17:00';
    private const STEP_MINUTES = 30;
    private const MAX_SLOTS = 20; // matches SaveJointCourseSlotsRequest
    private const TEACHING_DURATION_TYPE = 'semester_timetable_duration';
    private const PROPOSAL_TTL_MINUTES = 30;

    public function __construct(
        private ActiveCommitments $commitments,
        private TimetableGenerationDispatcher $generation,
    ) {}

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

        // Check and write under the same school-wide lock that activating a
        // semester timetable takes, so no other joint course save or timetable
        // activation can claim the same teacher or hall between the check and
        // the write. A conflict throws inside the transaction and rolls it back.
        $warnings = DB::transaction(function () use ($currentSchool, $jointCourse, $candidates) {
            Schoolbranches::whereKey($currentSchool->id)->lockForUpdate()->first();

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

            return $warnings;
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
        $teachers = $this->teachersFor($jointCourse, $params['teacher_id'] ?? null);
        $hall = !empty($params['hall_id']) ? $this->hall($currentSchool, $params['hall_id']) : null;
        [$windowStart, $windowEnd] = $this->window($params, $minutes);

        $busy = $this->recommendationBusy($jointCourse, $teachers, array_filter([$hall?->id]), $day);
        [$recommended, $unavailable] = $this->scan($busy, $day, $minutes, $teachers, $hall, $windowStart, $windowEnd);

        return [
            'day' => $day,
            'day_label' => Text::day($day),
            'duration_minutes' => $minutes,
            'window' => ['start_time' => Time::toTime($windowStart), 'end_time' => Time::toTime($windowEnd)],
            'teachers' => $this->people($teachers),
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

    public function hallAvailability(object $currentSchool, string $semesterJointCourseId, array $params): array
    {
        $jointCourse = $this->findCourse($currentSchool, $semesterJointCourseId);
        $minutes = (int) $params['duration_minutes'];
        $days = $this->days($params);
        $teachers = $this->teachersFor($jointCourse, $params['teacher_id'] ?? null);
        [$windowStart, $windowEnd] = $this->window($params, $minutes);

        $halls = Hall::where('school_branch_id', $currentSchool->id)
            ->where('status', 'available')
            ->orderBy('name')
            ->get();

        $busy = $this->recommendationBusy($jointCourse, $teachers, $halls->pluck('id')->all());

        $rows = $halls->map(function (Hall $hall) use ($busy, $days, $minutes, $teachers, $windowStart, $windowEnd) {
            $perDay = collect($days)->map(function (string $day) use ($busy, $hall, $minutes, $teachers, $windowStart, $windowEnd) {
                [$recommended] = $this->scan($busy, $day, $minutes, $teachers, $hall, $windowStart, $windowEnd);

                return ['day' => $day, 'day_label' => Text::day($day), 'free_count' => count($recommended), 'first_free' => $recommended[0]['time'] ?? null];
            });
            $free = $perDay->sum('free_count');

            return [
                'id' => $hall->id,
                'name' => $hall->name,
                'capacity' => $hall->capacity,
                'location' => $hall->location,
                'free_count' => $free,
                'free_day_count' => $perDay->where('free_count', '>', 0)->count(),
                'days' => $perDay->values()->all(),
                'message' => $free > 0
                    ? "{$free} free time(s) on " . $perDay->where('free_count', '>', 0)->count() . ' of ' . count($days) . ' day(s)'
                    : 'Not free when the course can be taught on these days',
            ];
        })
            ->sortByDesc('free_count')
            ->values();

        return [
            'duration_minutes' => $minutes,
            'days' => $days,
            'teachers' => $this->people($teachers),
            'halls' => $rows->all(),
            'summary' => [
                'hall_count' => $rows->count(),
                'usable_count' => $rows->where('free_count', '>', 0)->count(),
                'message' => $rows->isEmpty()
                    ? 'No hall is available in your school. Add a hall or mark one as available first.'
                    : $rows->where('free_count', '>', 0)->count() . ' of ' . $rows->count() . " hall(s) have a free {$minutes}-minute time for {$jointCourse->course->course_title}.",
            ],
        ];
    }

    public function recommendWeek(object $currentSchool, string $semesterJointCourseId, array $params): array
    {
        $jointCourse = $this->findCourse($currentSchool, $semesterJointCourseId);
        $minutes = (int) $params['duration_minutes'];
        $days = $this->days($params);
        $teachers = $this->teachersFor($jointCourse, $params['teacher_id'] ?? null);
        $hall = $this->hall($currentSchool, $params['hall_id']);
        [$windowStart, $windowEnd] = $this->window($params, $minutes);

        $busy = $this->recommendationBusy($jointCourse, $teachers, [$hall->id]);

        $perDay = collect($days)->map(function (string $day) use ($busy, $minutes, $teachers, $hall, $windowStart, $windowEnd) {
            [$recommended, $unavailable] = $this->scan($busy, $day, $minutes, $teachers, $hall, $windowStart, $windowEnd);

            return [
                'day' => $day,
                'day_label' => Text::day($day),
                'recommended' => $recommended,
                'unavailable' => $unavailable,
                'message' => $recommended
                    ? count($recommended) . ' free time(s)'
                    : 'No free time: ' . (collect($unavailable)->flatMap(fn($slot) => $slot['reasons'])->countBy()->sortDesc()->keys()->first() ?? 'everything is taken'),
            ];
        });
        $free = $perDay->sum(fn(array $day) => count($day['recommended']));

        return [
            'duration_minutes' => $minutes,
            'window' => ['start_time' => Time::toTime($windowStart), 'end_time' => Time::toTime($windowEnd)],
            'teachers' => $this->people($teachers),
            'hall' => ['id' => $hall->id, 'name' => $hall->name, 'capacity' => $hall->capacity],
            'days' => $perDay->values()->all(),
            'summary' => [
                'free_count' => $free,
                'free_day_count' => $perDay->filter(fn(array $day) => $day['recommended'])->count(),
                'message' => $free > 0
                    ? "{$hall->name} and a teacher of {$jointCourse->course->course_title} are both free at {$free} time(s) on " . $perDay->filter(fn(array $day) => $day['recommended'])->count() . ' day(s).'
                    : "{$hall->name} has no free {$minutes}-minute time on these days. Pick another hall, more days or a shorter duration.",
            ],
        ];
    }

    /**
     * List every slot a joint course could take on the chosen days: each
     * option is one period of the day's duration in one hall, at a time when
     * the course's teacher and that hall are both free. When the school
     * respects teacher preferences, options must also fall inside the
     * teacher's availability for one of the course's linked semesters.
     * The options are stored as a proposal; the admin picks from them and
     * confirms (confirmProposal). Nothing is saved here.
     */
    public function generate(object $currentSchool, string $semesterJointCourseId, array $params): array
    {
        $jointCourse = $this->findCourse($currentSchool, $semesterJointCourseId);
        $teacher = $this->courseTeachers($jointCourse)->first() ?? throw new AppException(
            "No teacher for course {$jointCourse->course_id}.",
            422,
            'No Teacher For This Course',
            "No teacher is assigned to teach {$jointCourse->course->course_title} yet. Assign a teacher to the course first.",
            null
        );

        $requests = $this->dayRequests($params);
        [$windowStart, $windowEnd] = $this->window($params, $requests->max('minutes'));

        $respectPreference = !$this->generation->ignoresTeacherPreference($currentSchool);
        $availability = $respectPreference ? $this->teacherAvailability($jointCourse, $teacher->id) : null;

        if ($respectPreference && $availability->isEmpty()) {
            throw new AppException(
                "Teacher {$teacher->id} has no availability for the linked semesters.",
                422,
                'No Teaching Availability',
                "{$teacher->name} hasn't set when they can teach for the semesters that share this course. Add their availability, or switch the timetable setting to ignore teacher preferences.",
                null
            );
        }

        $halls = Hall::where('school_branch_id', $currentSchool->id)
            ->where('status', 'available')
            ->orderBy('name')
            ->get();

        if ($halls->isEmpty()) {
            throw new AppException(
                'No available hall.',
                422,
                'No Hall Available',
                'No hall is available in your school. Add a hall or mark one as available first.',
                null
            );
        }

        $teachers = collect([$teacher]);
        $busy = $this->recommendationBusy($jointCourse, $teachers, $halls->pluck('id')->all());

        $options = [];
        $days = [];

        foreach ($requests as $request) {
            // start minute => [start, end, halls free then]
            $times = [];

            foreach ($halls as $hall) {
                [$recommended] = $this->scan($busy, $request['day'], $request['minutes'], $teachers, $hall, $windowStart, $windowEnd);

                foreach ($recommended as $time) {
                    $start = Time::toMinutes($time['start_time']);
                    $end = Time::toMinutes($time['end_time']);

                    if ($availability && !$this->withinAvailability($availability, $request['day'], $start, $end)) {
                        continue;
                    }

                    $times[$start] ??= ['start' => $start, 'end' => $end, 'halls' => []];
                    $times[$start]['halls'][] = $hall;
                }
            }

            ksort($times);

            $dayTimes = collect($times)->values()->map(function (array $time) use ($request, &$options) {
                $startTime = Time::toTime($time['start']);
                $endTime = Time::toTime($time['end']);

                return [
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'time' => Text::time($startTime, $endTime),
                    'halls' => collect($time['halls'])->map(function (Hall $hall) use ($request, $startTime, $endTime, &$options) {
                        $id = (string) Str::uuid();
                        $options[$id] = [
                            'day' => $request['day'],
                            'start_time' => $startTime,
                            'end_time' => $endTime,
                            'hall_id' => $hall->id,
                        ];

                        return ['option_id' => $id, 'id' => $hall->id, 'name' => $hall->name, 'capacity' => $hall->capacity];
                    })->values()->all(),
                ];
            })->all();

            $days[] = [
                'day' => $request['day'],
                'day_label' => Text::day($request['day']),
                'duration_minutes' => $request['minutes'],
                'period_duration' => ['id' => $request['duration']->id, 'name' => $request['duration']->name],
                'times' => $dayTimes,
                'message' => $dayTimes
                    ? count($dayTimes) . ' free time(s)'
                    : $this->noTimeMessage($request, $teacher->name, $availability),
            ];
        }

        $proposal = [
            'id' => (string) Str::uuid(),
            'school_branch_id' => $currentSchool->id,
            'semester_joint_course_id' => $jointCourse->id,
            'teacher_id' => $teacher->id,
            'options' => $options,
        ];

        Cache::put($this->proposalKey($proposal['id']), $proposal, now()->addMinutes(self::PROPOSAL_TTL_MINUTES));

        $freeDays = collect($days)->filter(fn(array $day) => $day['times'])->count();

        return [
            'proposal_id' => $proposal['id'],
            'expires_at' => now()->addMinutes(self::PROPOSAL_TTL_MINUTES)->toIso8601String(),
            'teacher' => ['id' => $teacher->id, 'name' => $teacher->name],
            'teacher_preference' => $respectPreference ? 'respected' : 'ignored',
            'window' => ['start_time' => Time::toTime($windowStart), 'end_time' => Time::toTime($windowEnd)],
            'max_selections' => self::MAX_SLOTS,
            'days' => $days,
            'summary' => [
                'option_count' => count($options),
                'free_day_count' => $freeDays,
                'message' => $options
                    ? "Free times on {$freeDays} of " . count($days) . " day(s) for {$jointCourse->course->course_title}. Pick the slots you want and confirm."
                    : "No free time on the chosen days for {$jointCourse->course->course_title}. Try other days or another duration.",
            ],
        ];
    }

    /**
     * Save the options the admin picked from a proposal, exactly as generated.
     * They must come from that proposal and not overlap each other; saveSlots
     * then revalidates them under the school lock and writes them in one
     * transaction. A proposal can be confirmed once.
     */
    public function confirmProposal(object $currentSchool, string $semesterJointCourseId, string $proposalId, array $optionIds): array
    {
        $proposal = Cache::get($this->proposalKey($proposalId));

        if (
            !$proposal
            || $proposal['school_branch_id'] !== $currentSchool->id
            || $proposal['semester_joint_course_id'] !== $semesterJointCourseId
        ) {
            throw new AppException(
                "Proposal {$proposalId} not found.",
                404,
                'Proposal Expired',
                'These generated slots have expired or were already saved. Generate them again.',
                null
            );
        }

        $unknown = collect($optionIds)->reject(fn(string $id) => isset($proposal['options'][$id]));

        if ($unknown->isNotEmpty()) {
            throw new AppException(
                'Unknown option(s): ' . $unknown->implode(', '),
                422,
                'Unknown Slot',
                'One of the chosen slots is not part of these generated slots. Generate them again and pick from the new list.',
                null
            );
        }

        $chosen = collect($optionIds)->map(fn(string $id) => $proposal['options'][$id])->values();

        // The teacher can only be in one place: picks on the same day can't overlap.
        foreach ($chosen as $i => $slot) {
            foreach ($chosen->slice($i + 1) as $other) {
                if (
                    $slot['day'] === $other['day']
                    && Time::overlaps(Time::toMinutes($slot['start_time']), Time::toMinutes($slot['end_time']), Time::toMinutes($other['start_time']), Time::toMinutes($other['end_time']))
                ) {
                    throw new AppException(
                        'Chosen slots overlap.',
                        422,
                        'Slots Overlap',
                        Text::day($slot['day']) . ' ' . Text::time($slot['start_time'], $slot['end_time']) . ' overlaps '
                            . Text::time($other['start_time'], $other['end_time']) . '. Pick slots that do not overlap.',
                        null
                    );
                }
            }
        }

        $result = $this->saveSlots($currentSchool, $semesterJointCourseId, $chosen
            ->map(fn(array $slot) => [...$slot, 'teacher_id' => $proposal['teacher_id']])
            ->all());

        Cache::forget($this->proposalKey($proposalId));

        return $result;
    }

    /** Requested days with their period duration, in week order. */
    private function dayRequests(array $params): Collection
    {
        $ids = collect($params['days'])->pluck('period_duration_id')->push($params['period_duration_id'])->filter()->unique()->values();
        $durations = PeriodDuration::with('type')->whereIn('id', $ids)->get()->keyBy('id');

        foreach ($ids as $id) {
            $duration = $durations->get($id);

            if (!$duration || $duration->status !== 'active' || $duration->type?->key !== self::TEACHING_DURATION_TYPE) {
                throw new AppException(
                    "Period duration {$id} cannot be used for teaching.",
                    422,
                    'Invalid Period Duration',
                    'Choose one of the active teaching period durations.',
                    null
                );
            }
        }

        return collect($params['days'])
            ->map(function (array $day) use ($params, $durations) {
                $duration = $durations->get($day['period_duration_id'] ?? $params['period_duration_id']);

                return [
                    'day' => strtolower($day['day']),
                    'minutes' => (int) $duration->minutes,
                    'duration' => $duration,
                ];
            })
            ->sortBy(fn(array $day) => $this->dayOrder($day['day']))
            ->values();
    }

    /**
     * The teacher's availability windows by day, merged across every semester
     * linked to the joint course: a time counts if any of them allows it.
     */
    private function teacherAvailability(SemesterJointCourse $jointCourse, string $teacherId): Collection
    {
        return InstructorAvailability::where('school_branch_id', $jointCourse->school_branch_id)
            ->where('teacher_id', $teacherId)
            ->whereIn('school_semester_id', $jointCourse->semesterJointCourseRef->pluck('school_semester_id'))
            ->with('instructorAvailabilitySlot')
            ->get()
            ->flatMap(fn(InstructorAvailability $availability) => $availability->instructorAvailabilitySlot)
            ->map(fn($slot) => [
                'day' => strtolower($slot->day_of_week),
                'start' => Time::toMinutes($slot->start_time),
                'end' => Time::toMinutes($slot->end_time),
            ])
            ->groupBy('day');
    }

    private function withinAvailability(Collection $availability, string $day, int $start, int $end): bool
    {
        return collect($availability->get($day, []))
            ->contains(fn(array $window) => $start >= $window['start'] && $end <= $window['end']);
    }

    private function noTimeMessage(array $request, string $teacherName, ?Collection $availability): string
    {
        $day = Text::day($request['day']);

        if ($availability && !$availability->has($request['day'])) {
            return "{$teacherName} is not available to teach on {$day}.";
        }

        return "No free {$request['minutes']}-minute time on {$day}: the teacher and the halls are not free together"
            . ($availability ? " within {$teacherName}'s availability." : '.');
    }

    private function proposalKey(string $proposalId): string
    {
        return "joint-course-timetable:proposal:{$proposalId}";
    }

    private function scan(Collection $busy, string $day, int $minutes, Collection $teachers, ?Hall $hall, int $windowStart, int $windowEnd): array
    {
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
                    'teachers' => $this->people($freeTeachers),
                    'hall' => $hall ? ['id' => $hall->id, 'name' => $hall->name] : null,
                ];
            } else {
                $unavailable[] = [...$entry, 'reasons' => $reasons->unique()->values()->all()];
            }
        }

        return [$recommended, $unavailable];
    }

    private function recommendationBusy(SemesterJointCourse $jointCourse, Collection $teachers, array $hallIds, ?string $day = null): Collection
    {
        return $this->busy($jointCourse, $day, $teachers->pluck('id')->all(), $hallIds)
            ->reject(fn(array $other) => $other['kind'] === 'linked_timetable')
            ->values();
    }

    private function teachersFor(SemesterJointCourse $jointCourse, ?string $teacherId): Collection
    {
        $teachers = $this->courseTeachers($jointCourse);

        if ($teacherId) {
            $teachers = $teachers->where('id', $teacherId)->values();
        }

        if ($teachers->isEmpty()) {
            throw new AppException(
                "No usable teacher for course {$jointCourse->course_id}.",
                422,
                $teacherId ? 'Teacher Does Not Teach This Course' : 'No Teacher For This Course',
                $teacherId
                    ? "The chosen teacher is not assigned to {$jointCourse->course->course_title}. Choose one of its teachers."
                    : "No teacher is assigned to teach {$jointCourse->course->course_title} yet. Assign a teacher to the course first.",
                null
            );
        }

        return $teachers;
    }

    private function hall(object $currentSchool, string $hallId): Hall
    {
        return Hall::where('school_branch_id', $currentSchool->id)->find($hallId)
            ?? throw new AppException('Hall not found.', 404, 'Hall Not Found', "We couldn't find this hall in your school.", null);
    }

    private function window(array $params, int $minutes): array
    {
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

        return [$windowStart, $windowEnd];
    }

    private function days(array $params): array
    {
        $days = collect($params['days'] ?? array_slice(self::DAYS, 0, 5))
            ->map(fn($day) => strtolower($day))
            ->unique()
            ->sortBy(fn($day) => $this->dayOrder($day))
            ->values()
            ->all();

        return $days ?: array_slice(self::DAYS, 0, 5);
    }

    private function people(Collection $teachers): array
    {
        return $teachers->map(fn($teacher) => ['id' => $teacher->id, 'name' => $teacher->name])->values()->all();
    }

    private function busy(SemesterJointCourse $jointCourse, ?string $day = null, array $teacherIds = [], array $hallIds = []): Collection
    {
        $linkedSemesterIds = $jointCourse->semesterJointCourseRef->pluck('school_semester_id')->all();

        // Other joint courses block a time while they are still being taught: at
        // least one of their linked school semesters hasn't ended. Their term
        // doesn't matter, since semesters of different terms can overlap.
        $otherJointCourses = SemesterJointCourse::where('school_branch_id', $jointCourse->school_branch_id)
            ->where('id', '!=', $jointCourse->id)
            ->whereHas('semesterJointCourseRef.schoolSemester', fn($query) => $query->stillRunning())
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
