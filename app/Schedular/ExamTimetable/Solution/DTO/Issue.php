<?php

namespace App\Schedular\ExamTimetable\Solution\DTO;

use App\Schedular\ExamTimetable\Support\Time;

final class Issue
{
    public const HARD = 'hard';
    public const SOFT = 'soft';

    /** Group used for problems that are not tied to one date (a course that could not be scheduled). */
    public const UNSCHEDULED = 'unscheduled';

    public function __construct(
        public readonly Subject $subject,
        public readonly string $severity,
        public readonly array $obstacles,
    ) {}

    public static function fromDiagnostic(object|array $diagnostic, string $severity): ?self
    {
        $diagnostic = json_decode(json_encode($diagnostic), true) ?? [];
        $failed = $diagnostic['constraint_failed'] ?? [];
        $type = $failed['type'] ?? null;

        if (!$type) {
            return null;
        }

        $details = $failed['details'] ?? [];
        $id = $failed['id'] ?? md5($type . json_encode($details));

        $obstacles = collect($diagnostic['blockers'] ?? [])
            ->filter(fn($blocker) => !empty($blocker['type']))
            ->map(fn($blocker) => new Subject(
                id: $blocker['id'] ?? md5($blocker['type'] . json_encode($blocker['entity'] ?? [])),
                type: $blocker['type'],
                role: Subject::ROLE_OBSTACLE,
                details: array_filter([
                    ...($blocker['evidence'] ?? []),
                    ...collect($blocker['entity'] ?? [])->except('type')->all(),
                    'conflict' => $blocker['conflict'] ?? [],
                ], fn($value) => $value !== null),
            ))
            ->unique('id')
            ->values()
            ->all();

        return new self(new Subject($id, $type, Subject::ROLE_REQUEST, $details), $severity, $obstacles);
    }

    public function id(): string
    {
        return $this->subject->id;
    }

    public function type(): string
    {
        return $this->subject->type;
    }

    public function date(): string
    {
        return $this->subject->date() ?? self::UNSCHEDULED;
    }

    public function window(): ?array
    {
        $start = Time::toMinutes($this->subject->get('start_time'));
        $end = Time::toMinutes($this->subject->get('end_time'));

        return $start !== null && $end !== null && $this->subject->date() ? [$start, $end] : null;
    }

    public function obstacleIds(): array
    {
        return array_map(fn(Subject $obstacle) => $obstacle->id, $this->obstacles);
    }

    public function isBlockedBy(string $subjectId): bool
    {
        return in_array($subjectId, $this->obstacleIds(), true);
    }
}
