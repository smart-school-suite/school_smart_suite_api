<?php

namespace App\Schedular\SemesterTimetable\Solution\DTO;

use App\Schedular\SemesterTimetable\Solution\Support\Time;

final class Issue
{
    public const HARD = 'hard';
    public const SOFT = 'soft';

    public function __construct(
        public readonly Subject $subject,
        public readonly string $severity,
        public readonly array $obstacles,
    ) {}

    public static function fromDiagnostic(object|array $diagnostic, string $severity): ?self
    {
        $diagnostic = (object) $diagnostic;
        $failed = (array) ($diagnostic->constraint_failed ?? []);
        $type = $failed['type'] ?? null;

        if (!$type) {
            return null;
        }

        $details = (array) ($failed['details'] ?? []);
        $id = $failed['id'] ?? md5($type . json_encode($details));

        $obstacles = collect($diagnostic->blockers ?? [])
            ->map(fn($blocker) => (object) $blocker)
            ->filter(fn($blocker) => !empty($blocker->type))
            ->map(fn($blocker) => new Subject(
                id: $blocker->id ?? md5($blocker->type . json_encode($blocker->entity ?? [])),
                type: $blocker->type,
                role: Subject::ROLE_OBSTACLE,
                details: array_filter([
                    ...((array) ($blocker->evidence ?? [])),
                    ...((array) ($blocker->entity ?? [])),
                    'conflict' => (array) ($blocker->conflict ?? []),
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

    public function day(): string
    {
        return $this->subject->day() ?? 'unknown';
    }

    public function window(): ?array
    {
        $start = Time::toMinutes($this->subject->get('start_time'));
        $end = Time::toMinutes($this->subject->get('end_time'));

        return $start !== null && $end !== null ? [$start, $end] : null;
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
