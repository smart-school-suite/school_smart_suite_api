<?php

namespace App\Services\ExamTimetable;

use App\Exceptions\AppException;
use App\Exceptions\ExamTimetable\ExamTimetableActivationConflict;
use App\Models\Exams;
use App\Models\ExamTimetable\ActiveExamTimetable;
use App\Models\ExamTimetable\ExamInvigilator;
use App\Models\ExamTimetable\ExamSessionHall;
use App\Models\ExamTimetable\ExamSessionInvigilator;
use App\Models\ExamTimetable\ExamTimetableRun;
use App\Models\ExamTimetable\ExamTimetableSlot;
use App\Models\ExamTimetable\ExamTimetableVersion;
use App\Models\Hall;
use App\Models\Schoolbranches;
use App\Schedular\ExamTimetable\Solution\Support\Text;
use App\Schedular\ExamTimetable\Support\Time;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Makes one generated exam timetable version the active one for its exam.
 *
 * Bookings of other exams may have changed since the version was generated, so activation checks every
 * session again against the other active exam timetables: a hall used at an overlapping (but different) time,
 * a shared hall that would be over capacity, or an invigilator in two exams at once blocks activation.
 * Activating writes the sessions to exam_timetable_slots, exam_session_halls and exam_session_invigs, removes
 * the slots of the exam's other versions and points active_exam_timetables at the version.
 */
class ActivateExamTimetableService
{
    private const ACTIVATABLE = [ExamTimetableVersion::STATUS_OPTIMAL, ExamTimetableVersion::STATUS_PARTIAL];

    public function check(object $currentSchool, string $versionId): array
    {
        [$version, $exam, $sessions] = $this->prepare($currentSchool, $versionId);
        $conflicts = $this->conflicts($currentSchool, $exam, $sessions);

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
            Schoolbranches::whereKey($currentSchool->id)->lockForUpdate()->first();

            [$version, $exam, $sessions] = $this->prepare($currentSchool, $versionId);
            $conflicts = $this->conflicts($currentSchool, $exam, $sessions);

            if (!empty($conflicts)) {
                throw new ExamTimetableActivationConflict($conflicts);
            }

            $previous = $this->currentlyActive($currentSchool, $exam->id, exceptId: $version->id);
            $this->clearExamSlots($currentSchool->id, $exam->id);

            $ownInvigilators = ExamInvigilator::where('school_branch_id', $currentSchool->id)
                ->where('exam_id', $exam->id)
                ->pluck('id')
                ->all();
            $specialtyId = $exam->schoolYear?->specialty_id;
            $now = now();

            foreach ($sessions as $session) {
                $slot = ExamTimetableSlot::create([
                    'school_branch_id' => $currentSchool->id,
                    'exam_id' => $exam->id,
                    'version_id' => $version->id,
                    'course_id' => $session['course_id'],
                    'type' => $session['type'] ?? 'regular',
                    'date' => $session['date'],
                    'start_time' => $session['start_time'],
                    'end_time' => $session['end_time'],
                ]);

                foreach ($session['halls'] as $hall) {
                    $sessionHall = ExamSessionHall::create([
                        'school_branch_id' => $currentSchool->id,
                        'exam_slot_id' => $slot->id,
                        'specialty_id' => $specialtyId,
                        'hall_id' => $hall['hall_id'],
                        'candidate_count' => (int) $hall['candidate_count'],
                    ]);

                    // Invigilators of a hall shared with another exam already belong to that exam.
                    $invigilators = collect($hall['invigilators'] ?? [])
                        ->pluck('invigilator_id')
                        ->filter(fn($id) => in_array($id, $ownInvigilators, true))
                        ->unique();

                    ExamSessionInvigilator::insert($invigilators->map(fn($id) => [
                        'id' => (string) Str::uuid(),
                        'school_branch_id' => $currentSchool->id,
                        'exam_slot_id' => $slot->id,
                        'exam_session_hall_id' => $sessionHall->id,
                        'invigilator_id' => $id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])->values()->all());
                }
            }

            ActiveExamTimetable::updateOrCreate(
                ['school_branch_id' => $currentSchool->id, 'exam_id' => $exam->id],
                ['version_id' => $version->id]
            );

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
        $exam = Exams::where('school_branch_id', $currentSchool->id)->find($examId);

        if (!$exam) {
            throw new AppException(
                "Exam {$examId} not found for school branch {$currentSchool->id}.",
                404,
                'Exam Not Found',
                "We couldn't find this exam. It may have been deleted.",
                null
            );
        }

        $version = $this->currentlyActive($currentSchool, $exam->id);

        return [
            'exam_id' => $exam->id,
            'exam_finished' => $exam->end_date ? Carbon::parse($exam->end_date)->endOfDay()->isPast() : false,
            'active_version' => $this->versionSummary($version),
            'session_count' => $version ? $version->examTimetableSlot()->count() : 0,
            'message' => $version
                ? "{$version->label} is the active exam timetable for this exam."
                : 'No exam timetable is active for this exam yet.',
        ];
    }

    private function prepare(object $currentSchool, string $versionId): array
    {
        $version = ExamTimetableVersion::where('school_branch_id', $currentSchool->id)->find($versionId);

        if (!$version) {
            throw new AppException(
                "Exam timetable version {$versionId} not found for school branch {$currentSchool->id}.",
                404,
                'Exam Timetable Version Not Found',
                "We couldn't find this exam timetable version. It may have been deleted.",
                null
            );
        }

        $exam = Exams::where('school_branch_id', $currentSchool->id)->with('schoolYear')->find($version->exam_id);

        if (!$exam || ($exam->end_date && Carbon::parse($exam->end_date)->endOfDay()->isPast())) {
            throw new AppException(
                "Exam for version {$versionId} has ended.",
                422,
                'Exam Has Ended',
                'This exam ended on ' . ($exam?->end_date ? Carbon::parse($exam->end_date)->toDateString() : 'an earlier date') . ', so its timetable can no longer be made active.',
                null
            );
        }

        if (!in_array($version->scheduler_status, self::ACTIVATABLE, true)) {
            throw new AppException(
                "Exam timetable version {$versionId} has status {$version->scheduler_status} and cannot be activated.",
                422,
                'Exam Timetable Not Ready',
                match ($version->scheduler_status) {
                    ExamTimetableVersion::STATUS_PENDING => 'This version has not been generated yet. Generate it first, then activate it.',
                    ExamTimetableVersion::STATUS_GENERATING => 'This version is still being generated. Wait for it to finish, then activate it.',
                    default => 'This version is incomplete, so it cannot be made active. Fix its problems and generate it again.',
                },
                null
            );
        }

        $run = ExamTimetableRun::where('school_branch_id', $currentSchool->id)
            ->where('timetable_version_id', $version->id)
            ->orderBy('created_at', 'desc')
            ->first();

        $dates = json_decode(json_encode($run?->timetable_slots ?? []), true) ?? [];
        $sessions = collect($dates)->flatMap(fn(array $date) => $date['sessions'] ?? [])->values();

        if ($sessions->isEmpty()) {
            throw new AppException(
                "Exam timetable version {$versionId} has no sessions.",
                422,
                'Nothing To Activate',
                'This version has no exams in its timetable. Generate it again before activating it.',
                null
            );
        }

        return [$version, $exam, $sessions];
    }

    private function conflicts(object $currentSchool, Exams $exam, Collection $sessions): array
    {
        $others = ExamTimetableSlot::where('school_branch_id', $currentSchool->id)
            ->where('exam_id', '!=', $exam->id)
            ->whereIn('date', $sessions->pluck('date')->unique()->values()->all())
            ->whereHas('version.activeTimetable')
            ->with(['course', 'examSessionHall.invigilators.invigilator', 'version'])
            ->get()
            ->map(fn(ExamTimetableSlot $slot) => [
                'date' => Time::date($slot->date),
                'start' => Time::toMinutes($slot->start_time),
                'end' => Time::toMinutes($slot->end_time),
                'course_name' => $slot->course?->course_title,
                'exam_id' => $slot->exam_id,
                'halls' => $slot->examSessionHall->map(fn($hall) => [
                    'hall_id' => $hall->hall_id,
                    'candidate_count' => (int) $hall->candidate_count,
                    'people' => $hall->invigilators->map(fn($row) => $row->invigilator ? $row->invigilator->actorable_type . '|' . $row->invigilator->actorable_id : null)->filter()->values()->all(),
                ])->all(),
            ]);

        $people = ExamInvigilator::where('school_branch_id', $currentSchool->id)
            ->where('exam_id', $exam->id)
            ->get()
            ->mapWithKeys(fn($invigilator) => [$invigilator->id => $invigilator->actorable_type . '|' . $invigilator->actorable_id]);
        $capacities = Hall::whereIn('id', $sessions->flatMap(fn($session) => array_column($session['halls'], 'hall_id'))->unique()->values()->all())
            ->pluck('capacity', 'id');

        $conflicts = [];

        foreach ($sessions as $session) {
            $start = Time::toMinutes($session['start_time']);
            $end = Time::toMinutes($session['end_time']);

            foreach ($others->where('date', $session['date']) as $other) {
                if (!Time::overlaps($start, $end, $other['start'], $other['end'])) {
                    continue;
                }

                $sameWindow = $start === $other['start'] && $end === $other['end'];

                foreach ($session['halls'] as $hall) {
                    $otherHall = collect($other['halls'])->firstWhere('hall_id', $hall['hall_id']);

                    if ($otherHall && !$sameWindow) {
                        $conflicts[] = $this->conflict('hall', $session, $other, ($hall['hall_name'] ?? 'The hall') . ' is used by ' . ($other['course_name'] ?? 'another exam') . ' at an overlapping time.');
                    } elseif ($otherHall && $sameWindow && $hall['candidate_count'] + $otherHall['candidate_count'] > (int) ($capacities[$hall['hall_id']] ?? PHP_INT_MAX)) {
                        $conflicts[] = $this->conflict('hall_capacity', $session, $other, ($hall['hall_name'] ?? 'The hall') . ' would hold more candidates than it has seats.');
                    }

                    foreach ($hall['invigilators'] ?? [] as $invigilator) {
                        $person = $people[$invigilator['invigilator_id']] ?? null;
                        $sharedHall = $otherHall && $sameWindow;
                        $busy = $person && collect($other['halls'])->contains(fn($row) => in_array($person, $row['people'], true));

                        if ($busy && !$sharedHall) {
                            $conflicts[] = $this->conflict('invigilator', $session, $other, ($invigilator['name'] ?? 'An invigilator') . ' would invigilate ' . ($other['course_name'] ?? 'another exam') . ' at the same time.');
                        }
                    }
                }
            }
        }

        return collect($conflicts)->unique(fn($conflict) => $conflict['type'] . '|' . $conflict['message'] . '|' . $conflict['date'] . '|' . $conflict['time'])->values()->all();
    }

    private function conflict(string $type, array $session, array $other, string $message): array
    {
        $time = Text::time($session['start_time'], $session['end_time']);

        return [
            'type' => $type,
            'date' => $session['date'],
            'date_label' => Text::date($session['date']),
            'time' => $time,
            'message' => ($session['course_name'] ?? 'This exam') . ' on ' . Text::date($session['date']) . " {$time}: {$message}",
            'session' => ['course_id' => $session['course_id'], 'course_name' => $session['course_name'] ?? null],
            'conflicts_with' => [
                'exam_id' => $other['exam_id'],
                'course_name' => $other['course_name'],
                'time' => Text::time(Time::toTime($other['start']), Time::toTime($other['end'])),
            ],
        ];
    }

    private function clearExamSlots(string $branchId, string $examId): void
    {
        $slotIds = ExamTimetableSlot::where('school_branch_id', $branchId)->where('exam_id', $examId)->pluck('id');

        if ($slotIds->isEmpty()) {
            return;
        }

        ExamSessionInvigilator::whereIn('exam_slot_id', $slotIds)->delete();
        ExamSessionHall::whereIn('exam_slot_id', $slotIds)->delete();
        ExamTimetableSlot::whereIn('id', $slotIds)->delete();
    }

    private function currentlyActive(object $currentSchool, string $examId, ?string $exceptId = null): ?ExamTimetableVersion
    {
        $active = ActiveExamTimetable::where('school_branch_id', $currentSchool->id)->where('exam_id', $examId)->first();

        if (!$active || $active->version_id === $exceptId) {
            return null;
        }

        return ExamTimetableVersion::find($active->version_id);
    }

    private function versionSummary(?ExamTimetableVersion $version): ?array
    {
        return $version?->only(['id', 'label', 'version_number', 'scheduler_status']);
    }
}
