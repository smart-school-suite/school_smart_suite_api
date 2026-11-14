<?php

namespace App\Services\ResitTimetable;

use App\Exceptions\AppException;
use App\Exceptions\ExamTimetable\ExamTimetableActivationConflict;
use App\Models\ResitExam;
use App\Models\Resitexamtimetable;
use App\Models\ResitTimetable\ResitInvigilator;
use App\Models\ResitTimetable\ResitSessionHall;
use App\Models\ResitTimetable\ResitSessionInvigilator;
use App\Models\ResitTimetable\ResitTimetable;
use App\Models\ResitTimetable\ResitTimetableVersion;
use App\Models\Schoolbranches;
use App\Models\Specialty;
use App\Schedular\ExamTimetable\Placement\Support\Time;
use App\Schedular\ExamTimetable\Solution\Support\Text;
use App\Services\ResitTimetable\Support\ActiveExamSessions;
use App\Services\ResitTimetable\Support\ResitActiveCommitments;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Makes a generated resit timetable version the one in use for its resit
 * exam, like ActivateExamTimetableService: its sessions are written to
 * resit_exam_timetable_slots with their halls and each hall's invigilators,
 * replacing the resit exam's previous sessions.
 *
 * Activation is refused when, since generation, something now clashes:
 *  - two resits of this timetable overlap and share a candidate;
 *  - a hall is used by another active exam or resit timetable at an
 *    overlapping time (sharing is only possible at exactly the same times
 *    and within its capacity);
 *  - a person invigilates two exams at once;
 *  - a candidate sits another exam at an overlapping time.
 */
class ActivateResitTimetableService
{
    private const ACTIVATABLE = [ResitTimetableVersion::STATUS_OPTIMAL, ResitTimetableVersion::STATUS_PARTIAL];

    public function __construct(private ResitActiveCommitments $commitments) {}

    public function check(object $currentSchool, string $versionId): array
    {
        [$version, $resitExam, $sessions, $input] = $this->prepare($currentSchool, $versionId);
        $conflicts = $this->conflicts($currentSchool, $resitExam, $sessions, $input);

        return [
            'version' => $this->versionSummary($version),
            'can_activate' => empty($conflicts),
            'session_count' => $sessions->count(),
            'replaces' => $this->versionSummary($this->currentlyActive($currentSchool, $resitExam->id, exceptId: $version->id)),
            'conflicts' => $conflicts,
        ];
    }

    public function activate(object $currentSchool, string $versionId): array
    {
        return DB::transaction(function () use ($currentSchool, $versionId) {
            // One activation at a time per school, so two timetables can't take the same hall at once.
            Schoolbranches::whereKey($currentSchool->id)->lockForUpdate()->first();

            [$version, $resitExam, $sessions, $input] = $this->prepare($currentSchool, $versionId);
            $conflicts = $this->conflicts($currentSchool, $resitExam, $sessions, $input);

            if (!empty($conflicts)) {
                throw new ExamTimetableActivationConflict($conflicts);
            }

            $previous = $this->currentlyActive($currentSchool, $resitExam->id, exceptId: $version->id);

            $this->clearSlots($currentSchool->id, $resitExam->id);
            $this->writeSessions($currentSchool->id, $resitExam, $version->id, $sessions);

            return [
                'version' => $this->versionSummary($version),
                'resit_exam_id' => $resitExam->id,
                'session_count' => $sessions->count(),
                'replaced' => $this->versionSummary($previous),
                'message' => $previous
                    ? "{$version->label} is now the active resit timetable and replaces {$previous->label}."
                    : "{$version->label} is now the active resit timetable.",
            ];
        });
    }

    public function activeFor(object $currentSchool, string $resitExamId): array
    {
        $resitExam = ResitExam::where('school_branch_id', $currentSchool->id)->find($resitExamId)
            ?? throw new AppException(
                "Resit exam {$resitExamId} not found for school branch {$currentSchool->id}.",
                404,
                'Resit Exam Not Found',
                "We couldn't find this resit exam. It may have been deleted.",
                null
            );

        $version = $this->currentlyActive($currentSchool, $resitExam->id);

        return [
            'resit_exam_id' => $resitExam->id,
            'active_version' => $this->versionSummary($version),
            'session_count' => $version ? $version->slots()->count() : 0,
            'message' => $version
                ? "{$version->label} is the active resit timetable for this resit exam."
                : 'No resit timetable is active for this resit exam yet.',
        ];
    }

    // ── Loading ──────────────────────────────────────────────────────────

    private function prepare(object $currentSchool, string $versionId): array
    {
        $version = ResitTimetableVersion::where('school_branch_id', $currentSchool->id)
            ->with('resitExam.schoolYear.specialty')
            ->find($versionId);

        if (!$version || !$version->resitExam) {
            throw new AppException(
                "Resit timetable version {$versionId} not found for school branch {$currentSchool->id}.",
                404,
                'Timetable Version Not Found',
                "We couldn't find this resit timetable version. It may have been deleted.",
                null
            );
        }

        $resitExam = $version->resitExam;

        if ($resitExam->end_date && Carbon::parse($resitExam->end_date)->endOfDay()->isPast()) {
            throw new AppException(
                "Resit exam for version {$versionId} has ended.",
                422,
                'Resit Exam Has Ended',
                'This resit exam ended on ' . Carbon::parse($resitExam->end_date)->format('j F Y') . ', so its timetable can no longer be made active.',
                null
            );
        }

        if (!in_array($version->scheduler_status, self::ACTIVATABLE, true)) {
            throw new AppException(
                "Resit timetable version {$versionId} has status {$version->scheduler_status} and cannot be activated.",
                422,
                'Timetable Not Ready',
                match ($version->scheduler_status) {
                    ResitTimetableVersion::STATUS_PENDING => 'This version has not been generated yet. Generate it first, then activate it.',
                    ResitTimetableVersion::STATUS_GENERATING => 'This version is still being generated. Wait for it to finish, then activate it.',
                    default => 'This version is not complete: some resits could not be placed. Fix its problems and generate it again.',
                },
                null
            );
        }

        $timetable = $this->generatedTimetable($currentSchool, $version);
        $sessions = collect($timetable['timetable_slots'])
            ->flatMap(fn(array $day) => collect($day['slots'] ?? [])->map(fn(array $slot) => [
                ...$slot,
                'date' => $day['date'],
                'start' => Time::toMinutes($slot['start_time']),
                'end' => Time::toMinutes($slot['end_time']),
            ]))
            ->filter(fn(array $session) => !empty($session['course_id']) && !empty($session['halls']))
            ->values();

        if ($sessions->isEmpty()) {
            throw new AppException(
                "Resit timetable version {$versionId} has no generated sessions.",
                422,
                'Nothing To Activate',
                'This version has no resits in its timetable. Generate it again before activating it.',
                null
            );
        }

        return [$version, $resitExam, $sessions, $timetable['scheduler_input']];
    }

    /** @return array{timetable_slots: array, scheduler_input: array} what generation stored for the version */
    protected function generatedTimetable(object $currentSchool, ResitTimetableVersion $version): array
    {
        $timetable = ResitTimetable::where('school_branch_id', $currentSchool->id)
            ->where('timetable_version_id', $version->id)
            ->orderBy('created_at', 'desc')
            ->first();

        return [
            'timetable_slots' => $this->asArray($timetable?->timetable_slots ?? []),
            'scheduler_input' => $this->asArray($timetable?->scheduler_input ?? []),
        ];
    }

    /** Overridable in tests, which have no other timetables to read. */
    protected function otherSessions(object $currentSchool, ResitExam $resitExam, array $dates): Collection
    {
        return $this->commitments->otherSessions($currentSchool->id, $resitExam->id, min($dates), max($dates))
            ->filter(fn(array $session) => in_array($session['date'], $dates, true))
            ->values();
    }

    // ── Conflicts ────────────────────────────────────────────────────────

    private function conflicts(object $currentSchool, ResitExam $resitExam, Collection $sessions, array $input): array
    {
        $conflicts = [];
        $people = $this->people($currentSchool->id, $resitExam->id);
        $capacity = collect($input['halls'] ?? [])->pluck('capacity', 'hall_id');
        $students = collect($input['courses'] ?? [])
            ->mapWithKeys(fn(array $course) => [$course['course_id'] => array_flip(array_map('strval', $course['student_ids'] ?? []))])
            ->all();

        // Within this timetable: candidates never sit two resits at once, and halls and people are never double-booked.
        foreach ($sessions as $i => $session) {
            foreach ($sessions->slice($i + 1) as $other) {
                if (!$this->overlaps($session, $other)) {
                    continue;
                }
                if (array_intersect_key($students[$session['course_id']] ?? [], $students[$other['course_id']] ?? [])) {
                    $conflicts[] = $this->conflict('students', 'this_timetable', $session, $other);
                }
                foreach ($session['halls'] as $hall) {
                    if (collect($other['halls'])->contains('hall_id', $hall['hall_id'])) {
                        $conflicts[] = $this->conflict('hall', 'this_timetable', $session, $other, $hall['hall_name'] ?? null);
                    }
                }
                $theirPeople = collect($this->invigilatorsOf($other))->pluck('invigilator_id')->all();
                foreach ($this->invigilatorsOf($session) as $invigilator) {
                    if (in_array($invigilator['invigilator_id'], $theirPeople, true)) {
                        $conflicts[] = $this->conflict('invigilator', 'this_timetable', $session, $other, $invigilator['name'] ?? null);
                    }
                }
            }
        }

        $others = $this->otherSessions($currentSchool, $resitExam, $sessions->pluck('date')->unique()->values()->all());

        foreach ($sessions as $session) {
            $ourStudents = $students[$session['course_id']] ?? [];

            foreach ($others as $other) {
                if (!$this->overlaps($session, $other)) {
                    continue;
                }

                foreach ($session['halls'] as $hall) {
                    $theirs = $other['halls'][$hall['hall_id']] ?? null;
                    if ($theirs === null) {
                        continue;
                    }
                    $sameTimes = $session['start'] === $other['start'] && $session['end'] === $other['end'];
                    $fits = $hall['candidate_count'] + $theirs <= (int) ($capacity[$hall['hall_id']] ?? $hall['capacity'] ?? 0);
                    if (!$sameTimes || !$fits) {
                        $conflicts[] = $this->conflict('hall', 'active_timetable', $session, $other, $hall['hall_name'] ?? null);
                    }
                }

                foreach ($this->invigilatorsOf($session) as $invigilator) {
                    $person = $people[$invigilator['invigilator_id']] ?? null;
                    if ($person && in_array($person, $other['people'] ?? [], true)) {
                        $conflicts[] = $this->conflict('invigilator', 'active_timetable', $session, $other, $invigilator['name'] ?? null);
                    }
                }

                $shared = count(array_intersect_key($ourStudents, array_flip($other['student_ids'] ?? [])));
                if ($shared > 0) {
                    $conflicts[] = $this->conflict('students', 'active_timetable', $session, $other, $shared === 1 ? '1 candidate' : "{$shared} candidates");
                }
            }
        }

        return collect($conflicts)
            ->unique(fn(array $conflict) => implode('|', [$conflict['type'], $conflict['against'], $conflict['date'], $conflict['time'], $conflict['course']['id'], $conflict['conflicts_with']['course']['id'], $conflict['resource'] ?? '']))
            ->values()
            ->all();
    }

    /** @return array<string, string> this resit exam's invigilator id => person key */
    private function people(string $schoolBranchId, string $resitExamId): array
    {
        return ResitInvigilator::where('school_branch_id', $schoolBranchId)
            ->where('resit_exam_id', $resitExamId)
            ->get()
            ->mapWithKeys(fn(ResitInvigilator $invigilator) => [$invigilator->id => ActiveExamSessions::personKey($invigilator->actorable_type, $invigilator->actorable_id)])
            ->all();
    }

    private function invigilatorsOf(array $session): array
    {
        return collect($session['halls'])->flatMap(fn(array $hall) => $hall['invigilators'] ?? [])->all();
    }

    private function overlaps(array $a, array $b): bool
    {
        return $a['date'] === $b['date'] && Time::overlaps($a['start'], $a['end'], $b['start'], $b['end']);
    }

    private function conflict(string $type, string $against, array $session, array $other, ?string $resource = null): array
    {
        $time = Text::time($session['start_time'], $session['end_time']);
        $when = Text::date($session['date']) . ' ' . $time;
        $course = $session['course_title'] ?? 'This resit';
        $otherCourse = $other['course_title'] ?? 'another exam';
        $where = $against === 'this_timetable' ? 'this timetable' : ($other['source'] ?? 'another timetable');

        $message = match ($type) {
            'hall' => ($resource ?? 'A hall') . " would host {$course} and {$otherCourse} ({$where}) at overlapping times on {$when}.",
            'invigilator' => ($resource ?? 'An invigilator') . " would invigilate {$course} and {$otherCourse} ({$where}) at the same time on {$when}.",
            default => ($resource ? ucfirst($resource) : 'Some candidates') . " would sit {$course} and {$otherCourse} ({$where}) at the same time on {$when}.",
        };

        return [
            'type' => $type,
            'against' => $against,
            'date' => $session['date'],
            'date_label' => Text::date($session['date']),
            'time' => $time,
            'resource' => $resource,
            'message' => $message,
            'course' => ['id' => $session['course_id'], 'name' => $session['course_title'] ?? null],
            'conflicts_with' => [
                'course' => ['id' => $other['course_id'] ?? null, 'name' => $other['course_title'] ?? null],
                'date' => $other['date'],
                'time' => Text::time($other['start_time'], $other['end_time']),
                'source' => $where,
            ],
        ];
    }

    // ── Writing ──────────────────────────────────────────────────────────

    /** Removes every session of the resit exam (of any version), with their halls and invigilators. */
    private function clearSlots(string $schoolBranchId, string $resitExamId): void
    {
        $slotIds = Resitexamtimetable::where('school_branch_id', $schoolBranchId)
            ->where('resit_exam_id', $resitExamId)
            ->pluck('id');

        $slotIds->chunk(500)->each(function (Collection $chunk) {
            ResitSessionInvigilator::whereIn('resit_slot_id', $chunk)->delete();
            ResitSessionHall::whereIn('resit_slot_id', $chunk)->delete();
            Resitexamtimetable::whereIn('id', $chunk)->delete();
        });
    }

    /** Written without the models' casts: the columns are DATE and TIME. */
    private function writeSessions(string $schoolBranchId, ResitExam $resitExam, string $versionId, Collection $sessions): void
    {
        $specialtyId = $resitExam->schoolYear?->specialty_id;
        $levelId = $resitExam->schoolYear?->specialty?->level_id
            ?? ($specialtyId ? Specialty::find($specialtyId)?->level_id : null);

        $now = now();
        $slots = [];
        $halls = [];
        $invigilators = [];

        foreach ($sessions as $session) {
            $slotId = (string) Str::uuid();
            $slots[] = [
                'id' => $slotId,
                'date' => $session['date'],
                'start_time' => $session['start_time'] . ':00',
                'end_time' => $session['end_time'] . ':00',
                'duration' => ($session['end'] - $session['start']) . ' minutes',
                'course_id' => $session['course_id'],
                'resit_exam_id' => $resitExam->id,
                'specialty_id' => $specialtyId,
                'level_id' => $levelId,
                'version_id' => $versionId,
                'school_branch_id' => $schoolBranchId,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            foreach ($session['halls'] as $hall) {
                $hallId = (string) Str::uuid();
                $halls[] = [
                    'id' => $hallId,
                    'candidate_count' => (int) $hall['candidate_count'],
                    'resit_slot_id' => $slotId,
                    'hall_id' => $hall['hall_id'],
                    'school_branch_id' => $schoolBranchId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                foreach ($hall['invigilators'] ?? [] as $invigilator) {
                    $invigilators[] = [
                        'id' => (string) Str::uuid(),
                        'resit_slot_id' => $slotId,
                        'resit_session_hall_id' => $hallId,
                        'invigilator_id' => $invigilator['invigilator_id'],
                        'school_branch_id' => $schoolBranchId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        foreach ([[Resitexamtimetable::class, $slots], [ResitSessionHall::class, $halls], [ResitSessionInvigilator::class, $invigilators]] as [$model, $rows]) {
            foreach (array_chunk($rows, 500) as $chunk) {
                $model::query()->insert($chunk);
            }
        }
    }

    private function currentlyActive(object $currentSchool, string $resitExamId, ?string $exceptId = null): ?ResitTimetableVersion
    {
        return ResitTimetableVersion::where('school_branch_id', $currentSchool->id)
            ->where('resit_exam_id', $resitExamId)
            ->when($exceptId, fn($query) => $query->where('id', '!=', $exceptId))
            ->active()
            ->latest('updated_at')
            ->first();
    }

    private function versionSummary(?ResitTimetableVersion $version): ?array
    {
        return $version?->only(['id', 'label', 'version_number', 'scheduler_status', 'resit_exam_id']);
    }

    private function asArray(mixed $value): array
    {
        return json_decode(json_encode($value), true) ?? [];
    }
}
