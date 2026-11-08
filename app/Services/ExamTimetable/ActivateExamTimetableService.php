<?php

namespace App\Services\ExamTimetable;

use App\Exceptions\AppException;
use App\Exceptions\ExamTimetable\ExamTimetableActivationConflict;
use App\Models\Exams;
use App\Models\ExamTimetable\ExamInvigilator;
use App\Models\ExamTimetable\ExamSessionHall;
use App\Models\ExamTimetable\ExamSessionInvigilator;
use App\Models\ExamTimetable\ExamTimetable;
use App\Models\ExamTimetable\ExamTimetableSlot;
use App\Models\ExamTimetable\ExamTimetableVersion;
use App\Models\Schoolbranches;
use App\Schedular\ExamTimetable\Placement\Support\Time;
use App\Schedular\ExamTimetable\Solution\Support\Text;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Makes a generated exam timetable version the one in use for its exam.
 *
 * The generated sessions (stored in Mongo) are written to the exam timetable
 * tables: one slot per exam, its halls with their candidates, and the
 * invigilators of each hall. The exam's previous active version is replaced:
 * only the active version of an exam has slots, so there is no separate
 * "active timetable" record (the same as semester timetables).
 * From then on its halls and invigilators count as busy when other exams
 * are generated.
 *
 * Activation is refused when a session clashes with another exam's active
 * timetable: a hall used at an overlapping time (sharing is only possible at
 * exactly the same times and within its capacity), or a person invigilating
 * two exams at once. Other exams may have been activated since this version
 * was generated, so this is checked again here.
 */
class ActivateExamTimetableService
{
    private const ACTIVATABLE = [ExamTimetableVersion::STATUS_OPTIMAL, ExamTimetableVersion::STATUS_PARTIAL];

    public function check(object $currentSchool, string $versionId): array
    {
        [$version, $exam, $sessions, $input] = $this->prepare($currentSchool, $versionId);
        $conflicts = $this->conflicts($currentSchool, $exam, $sessions, $input);

        return [
            'version' => $this->versionSummary($version),
            'can_activate' => empty($conflicts),
            'session_count' => $sessions->count(),
            'replaces' => $this->versionSummary($this->currentlyActive($currentSchool, $exam->id, exceptId: $version->id)),
            'conflicts' => $conflicts,
        ];
    }

    public function activate(object $currentSchool, string $versionId): array
    {
        return DB::transaction(function () use ($currentSchool, $versionId) {
            // One activation at a time per school, so two exams can't take the same hall at once.
            Schoolbranches::whereKey($currentSchool->id)->lockForUpdate()->first();

            [$version, $exam, $sessions, $input] = $this->prepare($currentSchool, $versionId);
            $conflicts = $this->conflicts($currentSchool, $exam, $sessions, $input);

            if (!empty($conflicts)) {
                throw new ExamTimetableActivationConflict($conflicts);
            }

            $previous = $this->currentlyActive($currentSchool, $exam->id, exceptId: $version->id);

            $this->clearExamSlots($currentSchool->id, $exam->id);
            $this->writeSessions($currentSchool->id, $exam->id, $version->id, $input['specialty_id'] ?? null, $sessions);

            return [
                'version' => $this->versionSummary($version),
                'exam_id' => $exam->id,
                'session_count' => $sessions->count(),
                'replaced' => $this->versionSummary($previous),
                'message' => $previous
                    ? "{$version->label} is now the active exam timetable and replaces {$previous->label}."
                    : "{$version->label} is now the active exam timetable.",
            ];
        });
    }

    public function activeFor(object $currentSchool, string $examId): array
    {
        $exam = Exams::where('school_branch_id', $currentSchool->id)->find($examId)
            ?? throw new AppException(
                "Exam {$examId} not found for school branch {$currentSchool->id}.",
                404,
                'Exam Not Found',
                "We couldn't find this exam. It may have been deleted.",
                null
            );

        $version = $this->currentlyActive($currentSchool, $exam->id);

        return [
            'exam_id' => $exam->id,
            'active_version' => $this->versionSummary($version),
            'session_count' => $version ? $version->examTimetableSlot()->count() : 0,
            'message' => $version
                ? "{$version->label} is the active exam timetable for this exam."
                : 'No exam timetable is active for this exam yet.',
        ];
    }

    // ── Loading ──────────────────────────────────────────────────────────

    private function prepare(object $currentSchool, string $versionId): array
    {
        $version = ExamTimetableVersion::where('school_branch_id', $currentSchool->id)->with('exam')->find($versionId);

        if (!$version || !$version->exam) {
            throw new AppException(
                "Exam timetable version {$versionId} not found for school branch {$currentSchool->id}.",
                404,
                'Timetable Version Not Found',
                "We couldn't find this exam timetable version. It may have been deleted.",
                null
            );
        }

        $exam = $version->exam;

        if ($exam->end_date && Carbon::parse($exam->end_date)->endOfDay()->isPast()) {
            throw new AppException(
                "Exam for version {$versionId} has ended.",
                422,
                'Exam Has Ended',
                'This exam ended on ' . Carbon::parse($exam->end_date)->format('j F Y') . ', so its timetable can no longer be made active.',
                null
            );
        }

        if (!in_array($version->scheduler_status, self::ACTIVATABLE, true)) {
            throw new AppException(
                "Exam timetable version {$versionId} has status {$version->scheduler_status} and cannot be activated.",
                422,
                'Timetable Not Ready',
                match ($version->scheduler_status) {
                    ExamTimetableVersion::STATUS_PENDING => 'This version has not been generated yet. Generate it first, then activate it.',
                    ExamTimetableVersion::STATUS_GENERATING => 'This version is still being generated. Wait for it to finish, then activate it.',
                    default => 'This version is not complete: some exams could not be placed. Fix its problems and generate it again.',
                },
                null
            );
        }

        $timetable = $this->generatedTimetable($currentSchool, $version);
        $days = $timetable['timetable_slots'];
        $sessions = collect($days)
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
                "Exam timetable version {$versionId} has no generated sessions.",
                422,
                'Nothing To Activate',
                'This version has no exams in its timetable. Generate it again before activating it.',
                null
            );
        }

        return [$version, $exam, $sessions, $timetable['scheduler_input']];
    }

    /** @return array{timetable_slots: array, scheduler_input: array} what generation stored for the version */
    protected function generatedTimetable(object $currentSchool, ExamTimetableVersion $version): array
    {
        $timetable = ExamTimetable::where('school_branch_id', $currentSchool->id)
            ->where('timetable_version_id', $version->id)
            ->orderBy('created_at', 'desc')
            ->first();

        return [
            'timetable_slots' => $this->asArray($timetable?->timetable_slots ?? []),
            'scheduler_input' => $this->asArray($timetable?->scheduler_input ?? []),
        ];
    }

    // ── Conflicts ────────────────────────────────────────────────────────

    private function conflicts(object $currentSchool, Exams $exam, Collection $sessions, array $input): array
    {
        $conflicts = [];
        $people = $this->people($currentSchool->id, $exam->id);
        $capacity = collect($input['halls'] ?? [])->pluck('capacity', 'hall_id');

        // Within this timetable: the same students never sit two exams at once.
        foreach ($sessions as $i => $session) {
            foreach ($sessions->slice($i + 1) as $other) {
                if ($this->overlaps($session, $other)) {
                    $conflicts[] = $this->conflict('students', 'this_timetable', $session, $other);
                }
            }
        }

        $others = $this->otherActiveSessions($currentSchool->id, $exam->id, $sessions->pluck('date')->unique()->values()->all());

        foreach ($sessions as $session) {
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
                    if ($person && in_array($person, $other['people'], true)) {
                        $conflicts[] = $this->conflict('invigilator', 'active_timetable', $session, $other, $invigilator['name'] ?? null);
                    }
                }
            }
        }

        return collect($conflicts)
            ->unique(fn(array $conflict) => implode('|', [$conflict['type'], $conflict['date'], $conflict['time'], $conflict['course']['id'], $conflict['conflicts_with']['course']['id'], $conflict['resource'] ?? '']))
            ->values()
            ->all();
    }

    /** @return array<string, string> this exam's invigilator id => person key */
    private function people(string $schoolBranchId, string $examId): array
    {
        return ExamInvigilator::where('school_branch_id', $schoolBranchId)
            ->where('exam_id', $examId)
            ->get()
            ->mapWithKeys(fn(ExamInvigilator $invigilator) => [$invigilator->id => "{$invigilator->actorable_type}:{$invigilator->actorable_id}"])
            ->all();
    }

    /** Sessions of other exams' active timetables on these dates, with the seats and people they use. */
    private function otherActiveSessions(string $schoolBranchId, string $examId, array $dates): Collection
    {
        return ExamTimetableSlot::where('school_branch_id', $schoolBranchId)
            ->where('exam_id', '!=', $examId)
            ->whereDate('date', '>=', min($dates))
            ->whereDate('date', '<=', max($dates))
            ->whereHas('version')
            ->with(['course', 'examSessionHall.hall', 'examSessionInvig.invigilator'])
            ->get()
            ->map(function (ExamTimetableSlot $slot) {
                $start = Time::normalize((string) $slot->getRawOriginal('start_time'));
                $end = Time::normalize((string) $slot->getRawOriginal('end_time'));

                return [
                    'date' => Carbon::parse($slot->date)->format('Y-m-d'),
                    'start_time' => $start,
                    'end_time' => $end,
                    'start' => Time::toMinutes($start),
                    'end' => Time::toMinutes($end),
                    'course_id' => $slot->course_id,
                    'course_title' => $slot->course?->course_title,
                    'halls' => $slot->examSessionHall
                        ->groupBy('hall_id')
                        ->map(fn(Collection $rows) => (int) $rows->sum('candidate_count'))
                        ->all(),
                    'people' => $slot->examSessionInvig
                        ->map(fn(ExamSessionInvigilator $row) => $row->invigilator ? "{$row->invigilator->actorable_type}:{$row->invigilator->actorable_id}" : null)
                        ->filter()
                        ->values()
                        ->all(),
                    'source' => 'another exam\'s active timetable',
                ];
            })
            ->filter(fn(array $session) => in_array($session['date'], $dates, true))
            ->values();
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
        $course = $session['course_title'] ?? 'This exam';
        $otherCourse = $other['course_title'] ?? 'another exam';

        $message = match ($type) {
            'hall' => ($resource ?? 'A hall') . " would host {$course} and {$otherCourse} ({$other['source']}) at overlapping times on {$when}.",
            'invigilator' => ($resource ?? 'An invigilator') . " would invigilate {$course} and {$otherCourse} ({$other['source']}) at the same time on {$when}.",
            default => "{$course} and {$otherCourse} overlap on {$when}, and the same students sit both.",
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
                'source' => $other['source'] ?? 'this timetable',
            ],
        ];
    }

    // ── Writing ──────────────────────────────────────────────────────────

    /** Removes the exam's slots of every version, with their halls and invigilators. */
    private function clearExamSlots(string $schoolBranchId, string $examId): void
    {
        $slotIds = ExamTimetableSlot::where('school_branch_id', $schoolBranchId)
            ->where('exam_id', $examId)
            ->pluck('id');

        if ($slotIds->isEmpty()) {
            return;
        }

        $slotIds->chunk(500)->each(function (Collection $chunk) {
            ExamSessionInvigilator::whereIn('exam_slot_id', $chunk)->delete();
            ExamSessionHall::whereIn('exam_slot_id', $chunk)->delete();
            ExamTimetableSlot::whereIn('id', $chunk)->delete();
        });
    }

    /**
     * Written without the models' casts: they would turn "08:00" into a full
     * datetime, while the columns are DATE and TIME.
     */
    private function writeSessions(string $schoolBranchId, string $examId, string $versionId, ?string $specialtyId, Collection $sessions): void
    {
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
                'course_id' => $session['course_id'],
                'version_id' => $versionId,
                'exam_id' => $examId,
                'school_branch_id' => $schoolBranchId,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            foreach ($session['halls'] as $hall) {
                $hallId = (string) Str::uuid();
                $halls[] = [
                    'id' => $hallId,
                    'candidate_count' => (int) $hall['candidate_count'],
                    'exam_slot_id' => $slotId,
                    'specialty_id' => $specialtyId,
                    'hall_id' => $hall['hall_id'],
                    'school_branch_id' => $schoolBranchId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                foreach ($hall['invigilators'] ?? [] as $invigilator) {
                    $invigilators[] = [
                        'id' => (string) Str::uuid(),
                        'exam_slot_id' => $slotId,
                        'exam_session_hall_id' => $hallId,
                        'invigilator_id' => $invigilator['invigilator_id'],
                        'school_branch_id' => $schoolBranchId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        foreach ([[ExamTimetableSlot::class, $slots], [ExamSessionHall::class, $halls], [ExamSessionInvigilator::class, $invigilators]] as [$model, $rows]) {
            foreach (array_chunk($rows, 500) as $chunk) {
                $model::query()->insert($chunk);
            }
        }
    }

    private function currentlyActive(object $currentSchool, string $examId, ?string $exceptId = null): ?ExamTimetableVersion
    {
        return ExamTimetableVersion::where('school_branch_id', $currentSchool->id)
            ->where('exam_id', $examId)
            ->when($exceptId, fn($query) => $query->where('id', '!=', $exceptId))
            ->active()
            ->latest('updated_at')
            ->first();
    }

    private function versionSummary(?ExamTimetableVersion $version): ?array
    {
        return $version?->only(['id', 'label', 'version_number', 'scheduler_status', 'exam_id']);
    }

    private function asArray(mixed $value): array
    {
        return json_decode(json_encode($value), true) ?? [];
    }
}
