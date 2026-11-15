<?php

namespace App\Services\ResitJointCourse;

use App\Exceptions\AppException;
use App\Exceptions\ExamJointCourse\ExamJointCourseSlotConflict;
use App\Models\AcademicYear\SystemAcademicYear;
use App\Models\Examtype;
use App\Models\Hall;
use App\Models\ResitExam;
use App\Models\ResitJointCourse\ResitJointCourse;
use App\Models\ResitJointCourse\ResitJointCourseRef;
use App\Models\ResitJointCourse\ResitJointCourseSlot;
use App\Models\ResitTimetable\ResitInvigilator;
use App\Models\ResitTimetable\ResitTimetableVersion;
use App\Models\Schoolbranches;
use App\Models\Teacher;
use App\Models\TeacherCoursePreference;
use App\Schedular\ExamTimetable\Placement\Support\Time;
use App\Schedular\ExamTimetable\Solution\Support\Text;
use App\Services\ExamJointCourse\Support\JointExamSlotFinder;
use App\Services\ResitTimetable\Support\ActiveExamSessions;
use App\Services\ResitTimetable\Support\ResitCandidates;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Sets the one time of a joint resit: a course students of several
 * specialties resit in the same academic year and resit exam type. Works like
 * the exam joint course timetable (same finder, same response shapes): free
 * times are generated, the user picks one and it is saved; every linked
 * resit timetable then places the course at that time.
 *
 * A time is offered only when it falls inside every linked resit exam's
 * period, none of the course's candidates sits another exam or resit then
 * (active exam and resit timetables, other joint resits), enough exam hall
 * seats are free for all of them together and enough of the linked resit
 * exams' invigilators are free who do not teach the course.
 */
class ResitJointCourseTimetableService
{
    private const GENERATED = [ResitTimetableVersion::STATUS_OPTIMAL, ResitTimetableVersion::STATUS_PARTIAL];
    private const DEFAULT_START = '08:00';
    private const DEFAULT_END = '17:00';
    private const EXAM_HALL_TYPE = 'exam.hall';
    private const PROPOSAL_TTL_MINUTES = 30;
    private const MAX_OPTIONS_PER_DATE = 8;

    /** @var array<string, array<string, string[]>> resit exam id => course id => student ids */
    private array $candidateCache = [];

    public function __construct(
        private ResitJointCourseSync $sync,
        private ResitCandidates $candidates,
        private ActiveExamSessions $sessions,
    ) {}

    // ── Listing ──────────────────────────────────────────────────────────

    /** Every academic year and resit exam type that has a resit exam, with its joint resits. */
    public function terms(object $currentSchool): array
    {
        $terms = [];
        $blank = ['exam_count' => 0, 'joint_course_count' => 0, 'configured_count' => 0, 'not_configured_count' => 0];

        $resitExams = ResitExam::where('school_branch_id', $currentSchool->id)
            ->with(['schoolYear.systemAcademicYear', 'examType.semesters'])
            ->get();

        foreach ($resitExams as $resitExam) {
            $year = $resitExam->schoolYear?->systemAcademicYear;
            $type = $resitExam->examType;
            if (!$year || !$type) {
                continue;
            }
            $key = "{$year->id}:{$type->id}";
            $terms[$key] ??= [...$this->termSummary($year, $type), ...$blank];
            $terms[$key]['exam_count']++;
        }

        $jointCourses = ResitJointCourse::where('school_branch_id', $currentSchool->id)
            ->withCount('slots')
            ->get();

        foreach ($jointCourses as $course) {
            $key = "{$course->school_year_id}:{$course->exam_type_id}";
            if (!isset($terms[$key])) {
                continue;
            }
            $terms[$key]['joint_course_count']++;
            $terms[$key]['configured_count'] += $course->slots_count > 0 ? 1 : 0;
        }

        return collect($terms)
            ->map(fn(array $term) => [...$term, 'not_configured_count' => $term['joint_course_count'] - $term['configured_count']])
            ->sortBy([['year_start', 'desc'], ['exam_type', 'asc']])
            ->values()
            ->all();
    }

    /** The joint resits of a term, synced first since who resits what changes as marks come in. */
    public function term(object $currentSchool, string $schoolYearId, string $examTypeId): array
    {
        $this->sync->forTerm($currentSchool->id, $schoolYearId, $examTypeId);

        $courses = ResitJointCourse::where('school_branch_id', $currentSchool->id)
            ->where('school_year_id', $schoolYearId)
            ->where('exam_type_id', $examTypeId)
            ->with($this->relations())
            ->get();

        $presented = $courses
            ->map(fn(ResitJointCourse $course) => $this->presentCourse($course))
            ->sortBy(fn(array $course) => [$course['status'] === 'configured' ? 1 : 0, $course['course']['title']])
            ->values();
        $missing = $presented->where('status', 'not_configured')->count();

        return [
            'term' => $this->termSummary(SystemAcademicYear::find($schoolYearId), Examtype::find($examTypeId)),
            'summary' => [
                'course_count' => $presented->count(),
                'configured_count' => $presented->count() - $missing,
                'not_configured_count' => $missing,
                'message' => match (true) {
                    $presented->isEmpty() => 'No joint resits for this resit exam. A course becomes a joint resit when students of two or more specialties resit it.',
                    $missing === 0 => 'Every joint resit has its time set. The resit timetables of the linked specialties can be generated.',
                    default => "{$missing} joint resit(s) still need a time. The resit timetables of the linked specialties can't be generated until they have one.",
                },
            ],
            'courses' => $presented->all(),
        ];
    }

    public function show(object $currentSchool, string $jointCourseId): array
    {
        return $this->presentCourse($this->find($currentSchool, $jointCourseId));
    }

    /** Links a resit exam to its joint resits (they are also synced whenever a term is read). */
    public function syncResitExam(object $currentSchool, string $resitExamId): array
    {
        $resitExam = ResitExam::where('school_branch_id', $currentSchool->id)->find($resitExamId) ?? throw new AppException(
            "Resit exam {$resitExamId} not found.",
            404,
            'Resit Exam Not Found',
            "We couldn't find this resit exam. It may have been deleted.",
            null
        );

        $count = $this->sync->forResitExam($currentSchool->id, $resitExam->id);

        return [
            'resit_exam_id' => $resitExam->id,
            'joint_course_count' => $count,
            'courses' => ResitJointCourse::where('school_branch_id', $currentSchool->id)
                ->whereHas('refs', fn($query) => $query->where('resit_exam_id', $resitExam->id))
                ->with($this->relations())
                ->get()
                ->map(fn(ResitJointCourse $course) => $this->presentCourse($course))
                ->values()
                ->all(),
            'message' => $count === 0
                ? 'No course of this resit exam is resat by other specialties.'
                : "This resit exam shares {$count} course(s) with other specialties.",
        ];
    }

    // ── Generating and choosing a time ───────────────────────────────────

    public function generate(object $currentSchool, string $jointCourseId, array $params): array
    {
        $jointCourse = $this->find($currentSchool, $jointCourseId);
        $resitExams = $this->linkedResitExams($jointCourse);
        $duration = (int) $params['duration'];

        if ($resitExams->count() < 2) {
            throw new AppException(
                "Joint resit {$jointCourseId} is linked to fewer than two resit exams.",
                422,
                'Not A Joint Resit Any More',
                'Only one specialty resits ' . ($jointCourse->course?->course_title ?? 'this course') . ' now. Reload the page.',
                null
            );
        }

        $data = $this->gather($jointCourse, $resitExams, $params);
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
                    $proposalOptions[$id] = "{$option['date']}|{$option['start_time']}|{$option['end_time']}";

                    return ['option_id' => $id, ...$option, 'time' => Text::time($option['start_time'], $option['end_time'])];
                })
                ->all();

            $dates[] = [
                'date' => $date,
                'date_label' => Text::date($date),
                'other_exams_that_day' => count($data['student_busy'][$date] ?? []),
                'times' => $times,
                'message' => $times
                    ? count($times) . ' free time(s)'
                    : 'No free time on this date. ' . ($finder->mainRefusal($date) ?? 'The resit does not fit in the exam hours.'),
            ];
        }

        $best = $options->first();
        $recommendedId = $best
            ? array_search("{$best['date']}|{$best['start_time']}|{$best['end_time']}", $proposalOptions, true) ?: null
            : null;

        $proposalId = (string) Str::uuid();
        Cache::put($this->proposalKey($proposalId), [
            'school_branch_id' => $currentSchool->id,
            'resit_joint_course_id' => $jointCourse->id,
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
            'specialties_without_exam' => [],
            'recommended_option_id' => $recommendedId,
            'checks' => [
                'invigilators' => $data['check_invigilators'],
                'invigilators_message' => $data['check_invigilators']
                    ? null
                    : 'No invigilator is assigned to the linked resit exams yet, so invigilators were not checked. The resit timetable assigns them when it is generated.',
            ],
            'dates' => $dates,
            'summary' => [
                'option_count' => count($proposalOptions),
                'free_date_count' => $freeDates,
                'message' => match (true) {
                    empty($data['dates']) => "The resit periods of the linked specialties have no date in common (from today), so {$title} can't be resat together. Align the resit exam dates first.",
                    empty($proposalOptions) => "No time works for {$title}. Most often: " . ($finder->mainRefusal() ?? 'the resit does not fit in the exam hours.') . ' Try a shorter resit or longer exam hours, or free some exam halls or invigilators.',
                    default => "Free times on {$freeDates} of " . count($dates) . " date(s) for {$title}. Pick one and confirm.",
                },
            ],
        ];
    }

    public function confirmProposal(object $currentSchool, string $jointCourseId, string $proposalId, string $optionId): array
    {
        $proposal = Cache::get($this->proposalKey($proposalId));

        if (!$proposal || $proposal['school_branch_id'] !== $currentSchool->id || $proposal['resit_joint_course_id'] !== $jointCourseId) {
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
        $result = $this->saveSlot($currentSchool, $jointCourseId, ['date' => $date, 'start_time' => $start, 'end_time' => $end]);

        Cache::forget($this->proposalKey($proposalId));

        return $result;
    }

    /**
     * Saves the joint resit's time, replacing any earlier one. Checked again
     * under the school-wide lock that timetable activation takes.
     */
    public function saveSlot(object $currentSchool, string $jointCourseId, array $slot): array
    {
        $jointCourse = $this->find($currentSchool, $jointCourseId);
        $resitExams = $this->linkedResitExams($jointCourse);
        $start = Time::toMinutes($slot['start_time']);
        $end = Time::toMinutes($slot['end_time']);

        DB::transaction(function () use ($currentSchool, $jointCourse, $resitExams, $slot, $start, $end) {
            Schoolbranches::whereKey($currentSchool->id)->lockForUpdate()->first();

            $data = $this->gather($jointCourse, $resitExams, [
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

            ResitJointCourseSlot::where('school_branch_id', $currentSchool->id)->where('resit_jc_id', $jointCourse->id)->delete();

            ResitJointCourseSlot::query()->insert([
                'id' => (string) Str::uuid(),
                'school_branch_id' => $currentSchool->id,
                'resit_jc_id' => $jointCourse->id,
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
            'message' => "{$course['course']['title']} is now resat on " . Text::date($slot['date']) . ' ' . Text::time($slot['start_time'], $slot['end_time']) . '.'
                . ($affected->isNotEmpty() ? ' Generate these resit timetables again so they use the new time: ' . $affected->implode(', ') . '.' : ''),
        ];
    }

    public function removeSlot(object $currentSchool, string $jointCourseId): array
    {
        $jointCourse = $this->find($currentSchool, $jointCourseId);
        ResitJointCourseSlot::where('school_branch_id', $currentSchool->id)->where('resit_jc_id', $jointCourse->id)->delete();

        $course = $this->presentCourse($this->find($currentSchool, $jointCourse->id));

        return [
            'course' => $course,
            'message' => "The time of {$course['course']['title']} was removed. The linked resit timetables can't be generated until it has one again.",
        ];
    }

    // ── What the finder needs ────────────────────────────────────────────

    private function gather(ResitJointCourse $jointCourse, Collection $resitExams, array $params): array
    {
        $branchId = $jointCourse->school_branch_id;
        $resitExamIds = $resitExams->pluck('id')->all();
        $windowStart = Time::toMinutes($params['start_time'] ?? self::DEFAULT_START);
        $windowEnd = Time::toMinutes($params['end_time'] ?? self::DEFAULT_END);
        $dates = $this->commonDates($resitExams, $params['dates'] ?? null);

        // Who resits the course, per linked resit exam.
        $byResitExam = $resitExams->mapWithKeys(fn(ResitExam $resitExam) => [
            $resitExam->id => $this->candidatesOf($resitExam)[$jointCourse->course_id] ?? [],
        ]);
        $students = array_flip($byResitExam->flatten()->unique()->values()->all());

        $first = $dates ? min($dates) : null;
        $last = $dates ? max($dates) : null;
        $sessions = $first ? $this->sessions->all($branchId, $first, $last, withStudents: true) : collect();

        // The linked resit timetables' own sessions of this course move with the joint resit.
        $isThisCourse = fn(array $session) => $session['kind'] === ActiveExamSessions::KIND_RESIT
            && in_array($session['owner_id'], $resitExamIds, true)
            && $session['course_id'] === $jointCourse->course_id;
        $others = $sessions->reject($isThisCourse)->values();

        $studentBusy = [];
        foreach ($others as $session) {
            $shared = count(array_intersect_key(array_flip($session['student_ids'] ?? []), $students));
            if ($shared > 0) {
                $studentBusy[$session['date']][] = [
                    'start' => $session['start'],
                    'end' => $session['end'],
                    'label' => $session['course_title'] ?? 'another exam',
                ];
            }
        }
        foreach ($this->otherJointSlots($jointCourse, $students) as $slot) {
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
                'busy' => $others
                    ->filter(fn(array $session) => isset($session['halls'][$hall->id]))
                    ->map(fn(array $session) => ['date' => $session['date'], 'start' => $session['start'], 'end' => $session['end'], 'seats' => $session['halls'][$hall->id], 'shareable' => true])
                    ->groupBy('date')
                    ->map(fn(Collection $rows) => $rows->values()->all())
                    ->all(),
            ])
            ->values()
            ->all();

        $invigilators = $this->invigilators($jointCourse, $resitExamIds, $others);
        $total = count($students);

        return [
            'dates' => collect($dates)->mapWithKeys(fn(string $date) => [$date => [$windowStart, $windowEnd]])->all(),
            'student_busy' => $studentBusy,
            'halls' => $halls,
            'invigilators' => $invigilators,
            'check_invigilators' => !empty($invigilators),
            'candidates' => $total,
            'candidate_summary' => [
                'total' => $total,
                'by_exam' => $resitExams->map(fn(ResitExam $resitExam) => [
                    'exam_id' => $resitExam->id,
                    'resit_exam_id' => $resitExam->id,
                    'specialty' => $this->specialtyLabel($resitExam),
                    'candidate_count' => count($byResitExam[$resitExam->id] ?? []),
                ])->values()->all(),
            ],
        ];
    }

    /** Dates inside every linked resit exam's period, from today on. */
    private function commonDates(Collection $resitExams, ?array $only): array
    {
        $start = $resitExams->map(fn(ResitExam $exam) => $exam->start_date ? Carbon::parse($exam->start_date)->format('Y-m-d') : null);
        $end = $resitExams->map(fn(ResitExam $exam) => $exam->end_date ? Carbon::parse($exam->end_date)->format('Y-m-d') : null);

        if ($start->contains(null) || $end->contains(null)) {
            return [];
        }

        $from = max($start->max(), now()->format('Y-m-d'));
        $to = $end->min();

        if ($from > $to) {
            return [];
        }

        return collect(CarbonPeriod::create($from, $to))
            ->map(fn($date) => $date->format('Y-m-d'))
            ->when($only, fn(Collection $dates) => $dates->intersect($only))
            ->values()
            ->all();
    }

    /** Times of other joint resits some of these candidates sit. */
    private function otherJointSlots(ResitJointCourse $jointCourse, array $students): array
    {
        $slots = ResitJointCourseSlot::where('school_branch_id', $jointCourse->school_branch_id)
            ->where('resit_jc_id', '!=', $jointCourse->id)
            ->with(['jointCourse.course', 'jointCourse.refs.resitExam'])
            ->get();

        $busy = [];
        foreach ($slots as $slot) {
            $other = $slot->jointCourse;
            if (!$other) {
                continue;
            }
            $theirs = [];
            foreach ($other->refs as $ref) {
                if ($ref->resitExam) {
                    foreach ($this->candidatesOf($ref->resitExam)[$other->course_id] ?? [] as $student) {
                        $theirs[$student] = true;
                    }
                }
            }
            if (!array_intersect_key($theirs, $students)) {
                continue;
            }
            $busy[] = [
                'date' => Carbon::parse($slot->getRawOriginal('date'))->format('Y-m-d'),
                'start' => Time::toMinutes(Time::normalize((string) $slot->getRawOriginal('start_time'))),
                'end' => Time::toMinutes(Time::normalize((string) $slot->getRawOriginal('end_time'))),
                'label' => 'the joint resit ' . ($other->course?->course_title ?? 'of another course'),
            ];
        }

        return $busy;
    }

    /** One entry per person who invigilates any linked resit exam. */
    private function invigilators(ResitJointCourse $jointCourse, array $resitExamIds, Collection $sessions): array
    {
        $people = ResitInvigilator::where('school_branch_id', $jointCourse->school_branch_id)
            ->whereIn('resit_exam_id', $resitExamIds)
            ->with('invigilatable')
            ->get()
            ->unique(fn(ResitInvigilator $invigilator) => ActiveExamSessions::personKey($invigilator->actorable_type, $invigilator->actorable_id));

        $teachers = TeacherCoursePreference::where('school_branch_id', $jointCourse->school_branch_id)
            ->where('course_id', $jointCourse->course_id)
            ->pluck('teacher_id')
            ->flip();

        $busy = [];
        foreach ($sessions as $session) {
            foreach ($session['people'] ?? [] as $person) {
                $busy[$person][$session['date']][] = ['start' => $session['start'], 'end' => $session['end']];
            }
        }

        return $people->map(function (ResitInvigilator $invigilator) use ($teachers, $busy) {
            $key = ActiveExamSessions::personKey($invigilator->actorable_type, $invigilator->actorable_id);

            return [
                'key' => $key,
                'name' => $invigilator->invigilatable?->name,
                'teaches' => $invigilator->actorable_type === Teacher::class && isset($teachers[$invigilator->actorable_id]),
                'busy' => $busy[$key] ?? [],
            ];
        })->values()->all();
    }

    private function candidatesOf(ResitExam $resitExam): array
    {
        return $this->candidateCache[$resitExam->id] ??= $this->candidates->byCourse($resitExam->school_branch_id, $resitExam);
    }

    // ── Presenting (same shape as the exam joint course timetable) ───────

    private function presentCourse(ResitJointCourse $jointCourse): array
    {
        $resitExams = $this->linkedResitExams($jointCourse);
        $slot = $jointCourse->slots->sortByDesc('updated_at')->first();
        $start = $slot ? Time::normalize((string) $slot->getRawOriginal('start_time')) : null;
        $end = $slot ? Time::normalize((string) $slot->getRawOriginal('end_time')) : null;
        $date = $slot ? Carbon::parse($slot->getRawOriginal('date'))->format('Y-m-d') : null;

        return [
            'id' => $jointCourse->id,
            'course' => $this->courseSummary($jointCourse),
            'exam_type' => ['id' => $jointCourse->exam_type_id, 'name' => $jointCourse->examType?->exam_name],
            'specialties' => $resitExams->map(fn(ResitExam $resitExam) => [
                'exam_id' => $resitExam->id,
                'resit_exam_id' => $resitExam->id,
                'specialty_id' => $resitExam->schoolYear?->specialty_id,
                'label' => $this->specialtyLabel($resitExam),
                'candidate_count' => count($this->candidatesOf($resitExam)[$jointCourse->course_id] ?? []),
                'exam_period' => [
                    'start_date' => $resitExam->start_date ? Carbon::parse($resitExam->start_date)->format('Y-m-d') : null,
                    'end_date' => $resitExam->end_date ? Carbon::parse($resitExam->end_date)->format('Y-m-d') : null,
                ],
            ])->values()->all(),
            // Only specialties with candidates are linked, so nobody is missing an exam.
            'specialties_without_exam' => [],
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
            'affected_timetables' => $resitExams
                ->map(fn(ResitExam $resitExam) => $this->affectedTimetable($resitExam, $slot?->updated_at))
                ->filter()
                ->values()
                ->all(),
        ];
    }

    private function affectedTimetable(ResitExam $resitExam, mixed $lastChange): ?array
    {
        $latest = $resitExam->timetableVersions
            ->whereIn('scheduler_status', self::GENERATED)
            ->sortByDesc('version_number')
            ->first();

        if (!$latest) {
            return null;
        }

        return [
            'exam_id' => $resitExam->id,
            'resit_exam_id' => $resitExam->id,
            'label' => $this->specialtyLabel($resitExam) . " ({$latest->label})",
            'latest_version' => $latest->only(['id', 'label', 'version_number', 'scheduler_status']),
            'needs_regeneration' => $lastChange !== null && Carbon::parse($latest->updated_at)->lte(Carbon::parse($lastChange)),
        ];
    }

    private function courseSummary(ResitJointCourse $jointCourse): array
    {
        return [
            'id' => $jointCourse->course_id,
            'code' => $jointCourse->course?->course_code,
            'title' => $jointCourse->course?->course_title,
            'credit' => $jointCourse->course?->credit,
        ];
    }

    private function termSummary(?SystemAcademicYear $year, ?Examtype $type): array
    {
        return [
            'id' => "{$year?->id}:{$type?->id}",
            'school_year_id' => $year?->id,
            'exam_type_id' => $type?->id,
            'academic_year' => $year?->name,
            'year_start' => $year?->year_start,
            'exam_type' => $type?->exam_name,
            'semester' => $type?->semesters?->name,
            'label' => trim(($type?->exam_name ?? 'Resit') . ' · ' . ($year?->name ?? '')),
        ];
    }

    private function specialtyLabel(ResitExam $resitExam): string
    {
        $specialty = $resitExam->schoolYear?->specialty;
        return trim(($specialty?->specialty_name ?? 'Specialty') . ($specialty?->level?->name ? " · {$specialty->level->name}" : ''));
    }

    private function linkedResitExams(ResitJointCourse $jointCourse): Collection
    {
        return $jointCourse->refs->map(fn(ResitJointCourseRef $ref) => $ref->resitExam)->filter()->unique('id')->values();
    }

    private function find(object $currentSchool, string $jointCourseId): ResitJointCourse
    {
        return ResitJointCourse::where('school_branch_id', $currentSchool->id)
            ->with($this->relations())
            ->find($jointCourseId)
            ?? throw new AppException(
                "Resit joint course {$jointCourseId} not found.",
                404,
                'Joint Resit Not Found',
                "We couldn't find this joint resit. It may no longer be shared by several specialties.",
                null
            );
    }

    private function relations(): array
    {
        return [
            'course',
            'examType',
            'slots',
            'refs.resitExam.schoolYear.specialty.level',
            'refs.resitExam.timetableVersions',
        ];
    }

    private function proposalKey(string $proposalId): string
    {
        return "resit-joint-course-proposal:{$proposalId}";
    }
}
