<?php

namespace App\Services\SemesterTimetable;

use App\Exceptions\AppException;
use App\Exceptions\SemesterTimetable\TimetableActivationConflict;
use App\Models\SchoolSemester;
use App\Models\Schoolbranches;
use App\Models\SemesterTimetable\SemesterTimetable;
use App\Models\SemesterTimetable\SemesterTimetableSlot;
use App\Models\SemesterTimetable\SemesterTimetableVersion;
use App\Schedular\SemesterTimetable\Solution\Support\Text;
use App\Schedular\SemesterTimetable\Solution\Support\Time;
use App\Services\SemesterTimetable\Support\ActiveCommitments;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ActivateSemesterTimetableService
{
    private const ACTIVATABLE = [SemesterTimetableVersion::STATUS_OPTIMAL, SemesterTimetableVersion::STATUS_PARTIAL];

    public function __construct(private ActiveCommitments $commitments) {}

    public function check(object $currentSchool, string $versionId): array
    {
        [$version, $semester, $slots] = $this->prepare($currentSchool, $versionId);
        $conflicts = $this->conflicts($currentSchool, $version, $semester, $slots);

        return [
            'version' => $this->versionSummary($version),
            'can_activate' => empty($conflicts),
            'slot_count' => $slots->count(),
            'replaces' => $this->versionSummary($this->currentlyActive($currentSchool, $semester->id, exceptId: $version->id)),
            'conflicts' => $conflicts,
        ];
    }

    public function activate(object $currentSchool, string $versionId): array
    {
        return DB::transaction(function () use ($currentSchool, $versionId) {
            Schoolbranches::whereKey($currentSchool->id)->lockForUpdate()->first();

            [$version, $semester, $slots] = $this->prepare($currentSchool, $versionId);
            $conflicts = $this->conflicts($currentSchool, $version, $semester, $slots);

            if (!empty($conflicts)) {
                throw new TimetableActivationConflict($conflicts);
            }

            $previous = $this->currentlyActive($currentSchool, $semester->id, exceptId: $version->id);
            $semesterVersionIds = SemesterTimetableVersion::where('school_branch_id', $currentSchool->id)
                ->where('school_semester_id', $semester->id)
                ->pluck('id');

            SemesterTimetableSlot::where('school_branch_id', $currentSchool->id)
                ->whereIn('version_id', $semesterVersionIds)
                ->delete();

            $now = now();
            $slots->chunk(500)->each(fn(Collection $chunk) => SemesterTimetableSlot::insert(
                $chunk->map(fn(array $slot) => [
                    'id' => (string) Str::uuid(),
                    'school_branch_id' => $currentSchool->id,
                    'version_id' => $version->id,
                    'day_of_week' => $slot['day'],
                    'start_time' => $slot['start_time'],
                    'end_time' => $slot['end_time'],
                    'break' => $slot['type'] === 'break',
                    'course_id' => $slot['course_id'],
                    'teacher_id' => $slot['teacher_id'],
                    'hall_id' => $slot['hall_id'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->values()->all()
            ));

            $semester->update(['timetable_published' => true]);

            return [
                'version' => $this->versionSummary($version),
                'school_semester_id' => $semester->id,
                'slot_count' => $slots->count(),
                'replaced' => $this->versionSummary($previous),
                'message' => $previous
                    ? "{$version->label} is now the active timetable and replaces {$previous->label}."
                    : "{$version->label} is now the active timetable.",
            ];
        });
    }

    public function activeFor(object $currentSchool, string $schoolSemesterId): array
    {
        $semester = SchoolSemester::where('school_branch_id', $currentSchool->id)->find($schoolSemesterId);

        if (!$semester) {
            throw new AppException(
                "School semester {$schoolSemesterId} not found for school branch {$currentSchool->id}.",
                404,
                'School Semester Not Found',
                "We couldn't find this semester. It may have been deleted.",
                null
            );
        }

        $version = $this->currentlyActive($currentSchool, $semester->id);

        return [
            'school_semester_id' => $semester->id,
            'semester_still_running' => $semester->isStillRunning(),
            'active_version' => $this->versionSummary($version),
            'slot_count' => $version ? $version->timeTableSlot()->count() : 0,
            'message' => $version
                ? "{$version->label} is the active timetable for this semester."
                : 'No timetable is active for this semester yet.',
        ];
    }

    private function prepare(object $currentSchool, string $versionId): array
    {
        $version = SemesterTimetableVersion::where('school_branch_id', $currentSchool->id)
            ->with('schoolSemester.semester')
            ->find($versionId);

        if (!$version) {
            throw new AppException(
                "Timetable version {$versionId} not found for school branch {$currentSchool->id}.",
                404,
                'Timetable Version Not Found',
                "We couldn't find this timetable version. It may have been deleted.",
                null
            );
        }

        $semester = $version->schoolSemester;

        if (!$semester || !$semester->isStillRunning()) {
            throw new AppException(
                "School semester for version {$versionId} has ended.",
                422,
                'Semester Has Ended',
                'This semester ended on ' . ($semester?->end_date ?? 'an earlier date') . ', so its timetable can no longer be made active.',
                null
            );
        }

        if (!in_array($version->scheduler_status, self::ACTIVATABLE, true)) {
            throw new AppException(
                "Timetable version {$versionId} has status {$version->scheduler_status} and cannot be activated.",
                422,
                'Timetable Not Ready',
                match ($version->scheduler_status) {
                    SemesterTimetableVersion::STATUS_PENDING => 'This version has not been generated yet. Generate it first, then activate it.',
                    SemesterTimetableVersion::STATUS_GENERATING => 'This version is still being generated. Wait for it to finish, then activate it.',
                    default => 'This version could not be generated, so there is no timetable to activate. Fix its problems and generate it again.',
                },
                null
            );
        }

        $slots = $this->generatedSlots($currentSchool, $version);

        if ($slots->isEmpty()) {
            throw new AppException(
                "Timetable version {$versionId} has no generated slots.",
                422,
                'Nothing To Activate',
                'This version has no lessons in its timetable. Generate it again before activating it.',
                null
            );
        }

        return [$version, $semester, $slots];
    }

    private function generatedSlots(object $currentSchool, SemesterTimetableVersion $version): Collection
    {
        $timetable = SemesterTimetable::where('school_branch_id', $currentSchool->id)
            ->where('timetable_version_id', $version->id)
            ->orderBy('created_at', 'desc')
            ->first();

        $days = json_decode(json_encode($timetable?->timetable_slots ?? []), true) ?? [];

        return collect($days)
            ->flatMap(fn(array $day) => collect($day['slots'] ?? [])->map(fn(array $slot) => [...$slot, 'day' => strtolower($slot['day'] ?? $day['day'] ?? '')]))
            ->map(fn(array $slot) => $this->normalize($slot, $slot['slot_type'] ?? 'regular'))
            ->filter(fn(?array $slot) => $slot && ($slot['type'] === 'break' || $slot['course_id']))
            ->values();
    }

    private function conflicts(object $currentSchool, SemesterTimetableVersion $version, SchoolSemester $semester, Collection $slots): array
    {
        $lessons = $slots->reject(fn(array $slot) => $slot['type'] === 'break')->values();
        $conflicts = [];

        foreach ($lessons as $i => $slot) {
            foreach ($lessons->slice($i + 1) as $other) {
                if ($clash = $this->clash($slot, $other)) {
                    $conflicts[] = $this->conflict($clash, 'this_timetable', $slot, $other);
                }
            }
        }

        $others = $this->commitments->timetableLessons($currentSchool->id, exceptSemesterId: $semester->id);

        foreach ($lessons as $slot) {
            foreach ($others as $other) {
                if ($slot['type'] === 'joint' && $this->sameOccurrence($slot, $other)) {
                    continue;
                }

                if ($clash = $this->clash($slot, $other)) {
                    $conflicts[] = $this->conflict($clash, 'active_timetable', $slot, $other);
                }
            }
        }

        $jointSlots = $this->commitments->jointCourseSlots($currentSchool->id, sharedWithSemesterId: $semester->id);

        foreach ($lessons->where('type', 'regular') as $slot) {
            foreach ($jointSlots as $joint) {
                $clash = $this->clash($slot, $joint)
                    ?? ($joint['shared_with_semester'] && $this->overlaps($slot, $joint) ? 'students' : null);

                if ($clash) {
                    $conflicts[] = $this->conflict($clash, 'joint_course', $slot, $joint);
                }
            }
        }

        return collect($conflicts)
            ->unique(fn(array $conflict) => implode('|', [
                $conflict['type'],
                $conflict['day'],
                $conflict['time'],
                $conflict['slot']['course']['id'],
                $conflict['conflicts_with']['course']['id'],
                $conflict['conflicts_with']['time'],
            ]))
            ->values()
            ->all();
    }

    private function clash(array $a, array $b): ?string
    {
        if (!$this->overlaps($a, $b)) {
            return null;
        }

        return match (true) {
            $a['teacher_id'] && $a['teacher_id'] === $b['teacher_id'] => 'teacher',
            $a['hall_id'] && $a['hall_id'] === $b['hall_id'] => 'hall',
            default => null,
        };
    }

    private function overlaps(array $a, array $b): bool
    {
        return $a['day'] === $b['day'] && Time::overlaps($a['start'], $a['end'], $b['start'], $b['end']);
    }

    private function sameOccurrence(array $a, array $b): bool
    {
        return $a['course_id'] === $b['course_id'] && $a['day'] === $b['day'] && $a['start'] === $b['start'] && $a['end'] === $b['end'];
    }

    private function conflict(string $clash, string $against, array $slot, array $other): array
    {
        $time = Text::time($slot['start_time'], $slot['end_time']);
        $when = Text::day($slot['day']) . ' ' . $time;
        $course = $slot['course_name'] ?? 'This lesson';
        $otherCourse = $other['course_name'] ?? 'another lesson';
        $where = match ($against) {
            'this_timetable' => 'in this timetable',
            'active_timetable' => 'in ' . ($other['source'] ?? 'another active timetable'),
            default => 'a joint course still in use',
        };

        $message = match ($clash) {
            'teacher' => ($slot['teacher_name'] ?? 'The teacher') . " would teach {$course} and {$otherCourse} at the same time on {$when} ({$otherCourse} is {$where}).",
            'hall' => ($slot['hall_name'] ?? 'The hall') . " would host {$course} and {$otherCourse} at the same time on {$when} ({$otherCourse} is {$where}).",
            default => "{$course} on {$when} overlaps the joint course {$otherCourse}, which this class also attends.",
        };

        return [
            'type' => $clash,
            'against' => $against,
            'day' => $slot['day'],
            'day_label' => Text::day($slot['day']),
            'time' => $time,
            'message' => $message,
            'slot' => $this->slotSummary($slot),
            'conflicts_with' => [...$this->slotSummary($other), 'source' => $other['source'] ?? null],
        ];
    }

    private function normalize(array $slot, string $type): ?array
    {
        return ActiveCommitments::normalize($slot, $type);
    }

    private function slotSummary(array $slot): array
    {
        return [
            'day' => $slot['day'],
            'time' => Text::time($slot['start_time'], $slot['end_time']),
            'course' => ['id' => $slot['course_id'], 'name' => $slot['course_name'] ?? null],
            'teacher' => ['id' => $slot['teacher_id'], 'name' => $slot['teacher_name'] ?? null],
            'hall' => ['id' => $slot['hall_id'], 'name' => $slot['hall_name'] ?? null],
        ];
    }

    private function currentlyActive(object $currentSchool, string $schoolSemesterId, ?string $exceptId = null): ?SemesterTimetableVersion
    {
        return SemesterTimetableVersion::where('school_branch_id', $currentSchool->id)
            ->where('school_semester_id', $schoolSemesterId)
            ->when($exceptId, fn($query) => $query->where('id', '!=', $exceptId))
            ->whereHas('timeTableSlot')
            ->latest('updated_at')
            ->first();
    }

    private function versionSummary(?SemesterTimetableVersion $version): ?array
    {
        return $version?->only(['id', 'label', 'version_number', 'scheduler_status']);
    }
}
