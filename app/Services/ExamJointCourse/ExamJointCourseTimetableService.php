<?php

namespace App\Services\ExamJointCourse;

use App\Exceptions\AppException;
use App\Exceptions\ExamJointCourse\ExamJointCourseSlotConflict;
use App\Models\AccessedStudent;
use App\Models\Course\CourseSpecialty;
use App\Models\ExamJointCourse\ExamJointCourse;
use App\Models\ExamJointCourse\ExamJointCourseRef;
use App\Models\ExamJointCourse\ExamJointCourseSlot;
use App\Models\Exams;
use App\Models\ExamTimetable\ExamInvigilator;
use App\Models\ExamTimetable\ExamTimetableSlot;
use App\Models\ExamTimetable\ExamTimetableVersion;
use App\Models\Hall;
use App\Models\Schoolbranches;
use App\Models\Teacher;
use App\Models\TeacherCoursePreference;
use App\Schedular\ExamTimetable\Placement\Support\Time;
use App\Schedular\ExamTimetable\Solution\Support\Text;
use App\Services\ExamJointCourse\Support\JointExamSlotFinder;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Sets the one time of a joint exam: the exam of a course that students of
 * several specialties sit together. Works like the semester joint course
 * timetable: free times are generated automatically, the user picks one and
 * it is saved; every linked specialty's exam timetable then places the
 * course at that time.
 *
 * A time is offered only when, for every linked exam, it falls inside the
 * exam period, the students have no other exam then, enough exam hall seats
 * are free for all of them together and enough invigilators are free who do
 * not teach the course.
 */
class ExamJointCourseTimetableService
{
    private const GENERATED = [ExamTimetableVersion::STATUS_OPTIMAL, ExamTimetableVersion::STATUS_PARTIAL];
    private const DEFAULT_START = '08:00';
    private const DEFAULT_END = '17:00';
    private const EXAM_HALL_TYPE = 'exam.hall';
    private const PROPOSAL_TTL_MINUTES = 30;
    private const MAX_OPTIONS_PER_DATE = 8;

    public function __construct(private ExamJointCourseSync $sync) {}

    // ── Listing ──────────────────────────────────────────────────────────

    /** Academic year and exam type pairs that have joint exams, with how many have a time. */
    public function terms(object $currentSchool): array
    {
        return ExamJointCourse::where('school_branch_id', $currentSchool->id)
            ->with(['schoolYear', 'examType', 'semester'])
            ->withCount('examJointCourseSlot')
            ->get()
            ->groupBy(fn(ExamJointCourse $course) => "{$course->school_year_id}:{$course->exam_type_id}")
            ->map(function (Collection $courses) {
                $first = $courses->first();
                $configured = $courses->filter(fn(ExamJointCourse $course) => $course->exam_joint_course_slot_count > 0)->count();

                return [
                    ...$this->termSummary($first),
                    'joint_course_count' => $courses->count(),
                    'configured_count' => $configured,
                    'not_configured_count' => $courses->count() - $configured,
                ];
            })
            ->sortBy([['year_start', 'desc'], ['exam_type', 'asc']])
            ->values()
            ->all();
    }

    public function term(object $currentSchool, string $schoolYearId, string $examTypeId): array
    {
        $courses = ExamJointCourse::where('school_branch_id', $currentSchool->id)
            ->where('school_year_id', $schoolYearId)
            ->where('exam_type_id', $examTypeId)
            ->with($this->relations())
            ->get();

        $presented = $courses
            ->map(fn(ExamJointCourse $course) => $this->presentCourse($course))
            ->sortBy(fn(array $course) => [$course['status'] === 'configured' ? 1 : 0, $course['course']['title']])
            ->values();
        $missing = $presented->where('status', 'not_configured')->count();

        return [
            'term' => $courses->first() ? $this->termSummary($courses->first()) : ['school_year_id' => $schoolYearId, 'exam_type_id' => $examTypeId],
            'summary' => [
                'course_count' => $presented->count(),
                'configured_count' => $presented->count() - $missing,
                'not_configured_count' => $missing,
                'message' => match (true) {
                    $presented->isEmpty() => 'No joint exams for this exam. A course becomes a joint exam when two or more specialties take it.',
                    $missing === 0 => 'Every joint exam has its time set. The exam timetables of the linked specialties can be generated.',
                    default => "{$missing} joint exam(s) still need a time. The exam timetables of the linked specialties can't be generated until they have one.",
                },
            ],
            'courses' => $presented->all(),
        ];
    }

    public function show(object $currentSchool, string $examJointCourseId): array
    {
        return $this->presentCourse($this->find($currentSchool, $examJointCourseId));
    }

    /** Links an existing exam to its joint exams (new exams are linked when created). */
    public function syncExam(object $currentSchool, string $examId): array
    {
        $exam = Exams::where('school_branch_id', $currentSchool->id)->find($examId) ?? throw new AppException(
            "Exam {$examId} not found.",
            404,
            'Exam Not Found',
            "We couldn't find this exam. It may have been deleted.",
            null
        );

        $count = $this->sync->forExam($currentSchool->id, $exam->id);

        $courses = ExamJointCourse::where('school_branch_id', $currentSchool->id)
            ->whereHas('examJcRef', fn($query) => $query->where('exam_id', $exam->id))
            ->with($this->relations())
            ->get()
            ->map(fn(ExamJointCourse $course) => $this->presentCourse($course))
            ->values()
            ->all();

        return [
            'exam_id' => $exam->id,
            'joint_course_count' => $count,
            'courses' => $courses,
            'message' => $count === 0
                ? 'This exam has no course shared with other specialties.'
                : "This exam shares {$count} course(s) with other specialties.",
        ];
    }

    // ── Generating and choosing a time ───────────────────────────────────

    /**
     * Free times for the joint exam, grouped by date. Each time has an
     * option id; confirming one saves it as the joint exam's time.
     */
    public function generate(object $currentSchool, string $examJointCourseId, array $params): array
    {
        $jointCourse = $this->find($currentSchool, $examJointCourseId);
        $exams = $this->linkedExams($jointCourse);
        $duration = (int) $params['duration'];

        if ($exams->isEmpty()) {
            throw new AppException(
                "Joint course {$examJointCourseId} has no linked exam.",
                422,
                'No Exam Yet',
                'None of the specialties taking ' . $jointCourse->course?->course_title . ' has this exam yet. Create their exams first.',
                null
            );
        }

        $data = $this->gather($jointCourse, $exams, $params);
        $finder = new JointExamSlotFinder($data);
        $options = collect($finder->options($duration, (int) ($params['step_minutes'] ?? JointExamSlotFinder::STEP_MINUTES)));

        $proposalOptions = [];
        $dates = [];

        foreach (array_keys($data['dates']) as $date) {
            $times = $options->where('date', $date)
                ->take(self::MAX_OPTIONS_PER_DATE)
                ->sortBy('start_time')
                ->values()
                ->map(function (array $option) use (&$proposalOptions) {
                    $id = (string) Str::uuid();
                    $proposalOptions[$id] = Str::of($option['date'])->append('|', $option['start_time'], '|', $option['end_time'])->toString();

                    return [
                        'option_id' => $id,
                        ...$option,
                        'time' => Text::time($option['start_time'], $option['end_time']),
                    ];
                })
                ->all();

            $dates[] = [
                'date' => $date,
                'date_label' => Text::date($date),
                'other_exams_that_day' => count($data['student_busy'][$date] ?? []),
                'times' => $times,
                'message' => $times ? count($times) . ' free time(s)' : 'No free time on this date for every linked specialty.',
            ];
        }

        $best = $options->first();
        $recommendedId = $best
            ? array_search("{$best['date']}|{$best['start_time']}|{$best['end_time']}", $proposalOptions, true) ?: null
            : null;

        $proposalId = (string) Str::uuid();
        Cache::put($this->proposalKey($proposalId), [
            'school_branch_id' => $currentSchool->id,
            'exam_joint_course_id' => $jointCourse->id,
            'options' => $proposalOptions,
        ], now()->addMinutes(self::PROPOSAL_TTL_MINUTES));

        $title = $jointCourse->course?->course_title ?? 'this course';
        $freeDates = collect($dates)->filter(fn(array $date) => $date['times'])->count();

        return [
            'proposal_id' => $proposalId,
            'expires_at' => now()->addMinutes(self::PROPOSAL_TTL_MINUTES)->toIso8601String(),
            'course' => $this->courseSummary($jointCourse),
            'duration_minutes' => $duration,
            'candidates' => $data['candidate_summary'],
            'specialties_without_exam' => $this->specialtiesWithoutExam($jointCourse, $exams),
            'recommended_option_id' => $recommendedId,
            'dates' => $dates,
            'summary' => [
                'option_count' => count($proposalOptions),
                'free_date_count' => $freeDates,
                'message' => match (true) {
                    empty($data['dates']) => "The exam periods of the linked specialties have no date in common, so {$title} can't be examined together. Align the exam dates first.",
                    empty($proposalOptions) => "No time works for every linked specialty. Try a shorter exam, longer exam hours, or free some exam halls or invigilators.",
                    default => "Free times on {$freeDates} of " . count($dates) . " date(s) for {$title}. Pick one and confirm.",
                },
            ],
        ];
    }

    public function confirmProposal(object $currentSchool, string $examJointCourseId, string $proposalId, string $optionId): array
    {
        $proposal = Cache::get($this->proposalKey($proposalId));

        if (!$proposal || $proposal['school_branch_id'] !== $currentSchool->id || $proposal['exam_joint_course_id'] !== $examJointCourseId) {
            throw new AppException(
                "Proposal {$proposalId} not found.",
                404,
                'Proposal Expired',
                'These generated times have expired or were already saved. Generate them again.',
                null
            );
        }

        $option = $proposal['options'][$optionId] ?? throw new AppException(
            "Unknown option {$optionId}.",
            422,
            'Unknown Time',
            'The chosen time is not part of these generated times. Generate them again and pick from the new list.',
            null
        );

        [$date, $start, $end] = explode('|', $option);
        $result = $this->saveSlot($currentSchool, $examJointCourseId, ['date' => $date, 'start_time' => $start, 'end_time' => $end]);

        Cache::forget($this->proposalKey($proposalId));

        return $result;
    }

    /**
     * Saves the joint exam's time, replacing any earlier one. Checked again
     * under the school-wide lock that exam timetable activation takes, so no
     * activation can take the seats or invigilators in between.
     */
    public function saveSlot(object $currentSchool, string $examJointCourseId, array $slot): array
    {
        $jointCourse = $this->find($currentSchool, $examJointCourseId);
        $exams = $this->linkedExams($jointCourse);
        $start = Time::toMinutes($slot['start_time']);
        $end = Time::toMinutes($slot['end_time']);

        DB::transaction(function () use ($currentSchool, $jointCourse, $exams, $slot, $start, $end) {
            Schoolbranches::whereKey($currentSchool->id)->lockForUpdate()->first();

            $data = $this->gather($jointCourse, $exams, [
                'start_time' => min($slot['start_time'], self::DEFAULT_START),
                'end_time' => max($slot['end_time'], self::DEFAULT_END),
            ]);
            $check = (new JointExamSlotFinder($data))->check($slot['date'], $start, $end);

            if (!$check['ok']) {
                throw new ExamJointCourseSlotConflict(array_map(fn(array $problem) => [
                    ...$problem,
                    'date' => $slot['date'],
                    'date_label' => Text::date($slot['date']),
                    'time' => Text::time($slot['start_time'], $slot['end_time']),
                ], $check['problems']));
            }

            ExamJointCourseSlot::where('school_branch_id', $currentSchool->id)->where('exam_jc_id', $jointCourse->id)->delete();

            // Written without the model's casts, which would turn "08:00" into a datetime.
            ExamJointCourseSlot::query()->insert([
                'id' => (string) Str::uuid(),
                'school_branch_id' => $currentSchool->id,
                'exam_jc_id' => $jointCourse->id,
                'date' => $slot['date'],
                'start_time' => Time::normalize($slot['start_time']) . ':00',
                'end_time' => Time::normalize($slot['end_time']) . ':00',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        $course = $this->presentCourse($this->find($currentSchool, $jointCourse->id));
        $affected = collect($course['affected_timetables'])->pluck('label');

        return [
            'course' => $course,
            'message' => "{$course['course']['title']} is now examined on " . Text::date($slot['date']) . ' ' . Text::time($slot['start_time'], $slot['end_time']) . '.'
                . ($affected->isNotEmpty() ? ' Generate these exam timetables again so they use the new time: ' . $affected->implode(', ') . '.' : ''),
        ];
    }

    public function removeSlot(object $currentSchool, string $examJointCourseId): array
    {
        $jointCourse = $this->find($currentSchool, $examJointCourseId);
        ExamJointCourseSlot::where('school_branch_id', $currentSchool->id)->where('exam_jc_id', $jointCourse->id)->delete();

        $course = $this->presentCourse($this->find($currentSchool, $jointCourse->id));

        return [
            'course' => $course,
            'message' => "The time of {$course['course']['title']} was removed. The linked exam timetables can't be generated until it has one again.",
        ];
    }

    // ── What the finder needs ────────────────────────────────────────────

    private function gather(ExamJointCourse $jointCourse, Collection $exams, array $params): array
    {
        $branchId = $jointCourse->school_branch_id;
        $examIds = $exams->pluck('id')->all();
        $windowStart = Time::toMinutes($params['start_time'] ?? self::DEFAULT_START);
        $windowEnd = Time::toMinutes($params['end_time'] ?? self::DEFAULT_END);
        $dates = $this->commonDates($exams, $params['dates'] ?? null);

        $candidates = AccessedStudent::where('school_branch_id', $branchId)
            ->whereIn('exam_id', $examIds)
            ->selectRaw('exam_id, COUNT(*) as total')
            ->groupBy('exam_id')
            ->pluck('total', 'exam_id');

        $first = $dates ? min($dates) : null;
        $last = $dates ? max($dates) : null;
        $sessions = $first ? $this->activeSessions($branchId, $first, $last) : collect();

        // These exams' own sessions of this course will move with the joint exam.
        $isThisCourse = fn(array $session) => in_array($session['exam_id'], $examIds, true) && $session['course_id'] === $jointCourse->course_id;

        $studentBusy = [];
        foreach ($sessions as $session) {
            if (in_array($session['exam_id'], $examIds, true) && !$isThisCourse($session)) {
                $studentBusy[$session['date']][] = ['start' => $session['start'], 'end' => $session['end'], 'label' => $session['course_title'] ?? 'another exam'];
            }
        }
        foreach ($this->otherJointSlots($jointCourse, $examIds) as $slot) {
            $studentBusy[$slot['date']][] = $slot;
        }

        $halls = Hall::where('school_branch_id', $branchId)
            ->where('status', 'available')
            ->whereHas('types', fn($query) => $query->where('key', self::EXAM_HALL_TYPE))
            ->orderBy('name')
            ->get()
            ->map(fn(Hall $hall) => [
                'hall_id' => $hall->id,
                'hall_name' => $hall->name,
                'capacity' => (int) $hall->capacity,
                'busy' => $sessions->reject($isThisCourse)
                    ->flatMap(fn(array $session) => collect($session['halls'])
                        ->where('hall_id', $hall->id)
                        ->map(fn(array $row) => ['date' => $session['date'], 'start' => $session['start'], 'end' => $session['end'], 'seats' => $row['seats'], 'shareable' => true]))
                    ->groupBy('date')
                    ->map(fn(Collection $rows) => $rows->values()->all())
                    ->all(),
            ])
            ->values()
            ->all();

        return [
            'dates' => collect($dates)->mapWithKeys(fn(string $date) => [$date => [$windowStart, $windowEnd]])->all(),
            'student_busy' => $studentBusy,
            'halls' => $halls,
            'invigilators' => $this->invigilators($jointCourse, $examIds, $sessions->reject($isThisCourse)),
            'candidates' => (int) $candidates->sum(),
            'candidate_summary' => [
                'total' => (int) $candidates->sum(),
                'by_exam' => $exams->map(fn(Exams $exam) => [
                    'exam_id' => $exam->id,
                    'specialty' => $this->specialtyLabel($exam),
                    'candidate_count' => (int) ($candidates[$exam->id] ?? 0),
                ])->values()->all(),
            ],
        ];
    }

    /** Dates inside every linked exam's period, from today on. */
    private function commonDates(Collection $exams, ?array $only): array
    {
        $start = $exams->map(fn(Exams $exam) => $exam->start_date ? Carbon::parse($exam->start_date)->format('Y-m-d') : null);
        $end = $exams->map(fn(Exams $exam) => $exam->end_date ? Carbon::parse($exam->end_date)->format('Y-m-d') : null);

        if ($start->contains(null) || $end->contains(null)) {
            return [];
        }

        $from = max($start->max(), now()->format('Y-m-d'));
        $to = $end->min();

        if ($from > $to) {
            return [];
        }

        $dates = collect(CarbonPeriod::create($from, $to))->map(fn($date) => $date->format('Y-m-d'));

        return $dates->when($only, fn(Collection $dates) => $dates->intersect($only))->values()->all();
    }

    /** Sessions of every exam's active timetable between two dates. */
    private function activeSessions(string $branchId, string $from, string $to): Collection
    {
        return ExamTimetableSlot::where('school_branch_id', $branchId)
            ->whereDate('date', '>=', $from)
            ->whereDate('date', '<=', $to)
            // Only active versions have slots (activation replaces an exam's slots).
            ->whereHas('version')
            ->with(['course', 'examSessionHall', 'examSessionInvig.invigilator'])
            ->get()
            ->map(function (ExamTimetableSlot $slot) {
                $start = Time::normalize((string) $slot->getRawOriginal('start_time'));
                $end = Time::normalize((string) $slot->getRawOriginal('end_time'));

                return [
                    'exam_id' => $slot->exam_id,
                    'course_id' => $slot->course_id,
                    'course_title' => $slot->course?->course_title,
                    'date' => Carbon::parse($slot->date)->format('Y-m-d'),
                    'start' => Time::toMinutes($start),
                    'end' => Time::toMinutes($end),
                    'halls' => $slot->examSessionHall->map(fn($row) => ['hall_id' => $row->hall_id, 'seats' => (int) $row->candidate_count])->all(),
                    'people' => $slot->examSessionInvig
                        ->map(fn($row) => $row->invigilator ? $this->personKey($row->invigilator->actorable_type, $row->invigilator->actorable_id) : null)
                        ->filter()
                        ->values()
                        ->all(),
                ];
            });
    }

    /** Times of other joint exams the same students sit. */
    private function otherJointSlots(ExamJointCourse $jointCourse, array $examIds): array
    {
        return ExamJointCourseSlot::where('school_branch_id', $jointCourse->school_branch_id)
            ->where('exam_jc_id', '!=', $jointCourse->id)
            ->whereHas('examJointCourse.examJcRef', fn($query) => $query->whereIn('exam_id', $examIds))
            ->with('examJointCourse.course')
            ->get()
            ->map(fn(ExamJointCourseSlot $slot) => [
                'date' => Carbon::parse($slot->date)->format('Y-m-d'),
                'start' => Time::toMinutes(Time::normalize((string) $slot->getRawOriginal('start_time'))),
                'end' => Time::toMinutes(Time::normalize((string) $slot->getRawOriginal('end_time'))),
                'label' => 'the joint exam ' . ($slot->examJointCourse?->course?->course_title ?? 'of another course'),
            ])
            ->all();
    }

    /** One entry per person who invigilates any linked exam. */
    private function invigilators(ExamJointCourse $jointCourse, array $examIds, Collection $sessions): array
    {
        $people = ExamInvigilator::where('school_branch_id', $jointCourse->school_branch_id)
            ->whereIn('exam_id', $examIds)
            ->with('invigilatable')
            ->get()
            ->unique(fn(ExamInvigilator $invigilator) => $this->personKey($invigilator->actorable_type, $invigilator->actorable_id));

        $teachers = TeacherCoursePreference::where('school_branch_id', $jointCourse->school_branch_id)
            ->where('course_id', $jointCourse->course_id)
            ->pluck('teacher_id')
            ->flip();

        $busy = [];
        foreach ($sessions as $session) {
            foreach ($session['people'] as $person) {
                $busy[$person][$session['date']][] = ['start' => $session['start'], 'end' => $session['end']];
            }
        }

        return $people->map(function (ExamInvigilator $invigilator) use ($teachers, $busy) {
            $key = $this->personKey($invigilator->actorable_type, $invigilator->actorable_id);

            return [
                'key' => $key,
                'name' => $invigilator->invigilatable?->name,
                'teaches' => $invigilator->actorable_type === Teacher::class && isset($teachers[$invigilator->actorable_id]),
                'busy' => $busy[$key] ?? [],
            ];
        })->values()->all();
    }

    // ── Presenting ───────────────────────────────────────────────────────

    private function presentCourse(ExamJointCourse $jointCourse): array
    {
        $exams = $this->linkedExams($jointCourse);
        $slot = $jointCourse->examJointCourseSlot->sortByDesc('updated_at')->first();
        $start = $slot ? Time::normalize((string) $slot->getRawOriginal('start_time')) : null;
        $end = $slot ? Time::normalize((string) $slot->getRawOriginal('end_time')) : null;
        $date = $slot ? Carbon::parse($slot->date)->format('Y-m-d') : null;

        return [
            'id' => $jointCourse->id,
            'course' => $this->courseSummary($jointCourse),
            'exam_type' => ['id' => $jointCourse->exam_type_id, 'name' => $jointCourse->examType?->exam_name],
            'specialties' => $exams->map(fn(Exams $exam) => [
                'exam_id' => $exam->id,
                'specialty_id' => $exam->schoolYear?->specialty_id,
                'label' => $this->specialtyLabel($exam),
                'exam_period' => [
                    'start_date' => $exam->start_date ? Carbon::parse($exam->start_date)->format('Y-m-d') : null,
                    'end_date' => $exam->end_date ? Carbon::parse($exam->end_date)->format('Y-m-d') : null,
                ],
            ])->values()->all(),
            'specialties_without_exam' => $this->specialtiesWithoutExam($jointCourse, $exams),
            'slot' => $slot ? [
                'id' => $slot->id,
                'date' => $date,
                'date_label' => Text::date($date),
                'start_time' => $start,
                'end_time' => $end,
                'time' => Text::time($start, $end),
                'duration_minutes' => Time::duration($start, $end),
            ] : null,
            'status' => $slot ? 'configured' : 'not_configured',
            'status_label' => $slot ? 'Time set' : 'No time set',
            'affected_timetables' => $exams
                ->map(fn(Exams $exam) => $this->affectedTimetable($exam, $slot?->updated_at))
                ->filter()
                ->values()
                ->all(),
        ];
    }

    /** The latest generated version of a linked exam, flagged when it predates the joint exam's time. */
    private function affectedTimetable(Exams $exam, mixed $lastChange): ?array
    {
        $latest = $exam->examTimetableVersion
            ->whereIn('scheduler_status', self::GENERATED)
            ->sortByDesc('version_number')
            ->first();

        if (!$latest) {
            return null;
        }

        return [
            'exam_id' => $exam->id,
            'label' => $this->specialtyLabel($exam) . " ({$latest->label})",
            'latest_version' => $latest->only(['id', 'label', 'version_number', 'scheduler_status']),
            'needs_regeneration' => $lastChange !== null && Carbon::parse($latest->updated_at)->lte(Carbon::parse($lastChange)),
        ];
    }

    /** Specialties that take the course but have no exam of this type yet. */
    private function specialtiesWithoutExam(ExamJointCourse $jointCourse, Collection $exams): array
    {
        $covered = $exams->map(fn(Exams $exam) => $exam->schoolYear?->specialty_id)->filter()->all();

        return CourseSpecialty::where('school_branch_id', $jointCourse->school_branch_id)
            ->where('course_id', $jointCourse->course_id)
            ->whereNotIn('specialty_id', $covered)
            ->with('specialty.level')
            ->get()
            ->map(fn(CourseSpecialty $row) => [
                'specialty_id' => $row->specialty_id,
                'label' => trim(($row->specialty?->specialty_name ?? 'Specialty') . ($row->specialty?->level?->name ? " · {$row->specialty->level->name}" : '')),
            ])
            ->unique('specialty_id')
            ->values()
            ->all();
    }

    private function courseSummary(ExamJointCourse $jointCourse): array
    {
        return [
            'id' => $jointCourse->course_id,
            'code' => $jointCourse->course?->course_code,
            'title' => $jointCourse->course?->course_title,
            'credit' => $jointCourse->course?->credit,
        ];
    }

    private function termSummary(ExamJointCourse $course): array
    {
        return [
            'id' => "{$course->school_year_id}:{$course->exam_type_id}",
            'school_year_id' => $course->school_year_id,
            'exam_type_id' => $course->exam_type_id,
            'academic_year' => $course->schoolYear?->name,
            'year_start' => $course->schoolYear?->year_start,
            'exam_type' => $course->examType?->exam_name,
            'semester' => $course->semester?->name,
            'label' => trim(($course->examType?->exam_name ?? 'Exam') . ' · ' . ($course->schoolYear?->name ?? '')),
        ];
    }

    private function specialtyLabel(Exams $exam): string
    {
        $specialty = $exam->schoolYear?->specialty;
        return trim(($specialty?->specialty_name ?? 'Specialty') . ($specialty?->level?->name ? " · {$specialty->level->name}" : ''));
    }

    private function linkedExams(ExamJointCourse $jointCourse): Collection
    {
        return $jointCourse->examJcRef->map(fn(ExamJointCourseRef $ref) => $ref->exam)->filter()->unique('id')->values();
    }

    private function find(object $currentSchool, string $examJointCourseId): ExamJointCourse
    {
        return ExamJointCourse::where('school_branch_id', $currentSchool->id)
            ->with($this->relations())
            ->find($examJointCourseId)
            ?? throw new AppException(
                "Exam joint course {$examJointCourseId} not found.",
                404,
                'Joint Exam Not Found',
                "We couldn't find this joint exam. It may have been deleted.",
                null
            );
    }

    private function relations(): array
    {
        return [
            'course',
            'examType',
            'schoolYear',
            'semester',
            'examJointCourseSlot',
            'examJcRef.exam.schoolYear.specialty.level',
            'examJcRef.exam.examTimetableVersion',
        ];
    }

    private function personKey(?string $type, ?string $id): string
    {
        return "{$type}:{$id}";
    }

    private function proposalKey(string $proposalId): string
    {
        return "exam-joint-course-proposal:{$proposalId}";
    }
}
