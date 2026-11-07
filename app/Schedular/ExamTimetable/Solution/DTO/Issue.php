<?php

namespace App\Schedular\ExamTimetable\Solution\DTO;

use App\Schedular\ExamTimetable\Placement\Support\Time;

/** One failed constraint (the request) and what is in its way (the obstacles). */
final class Issue
{
    public const HARD = 'hard';
    public const SOFT = 'soft';

    /** Issues that are not about one date (an exam duration, a course that fits nowhere...). */
    public const NO_DATE = 'general';

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
            ->filter(fn(array $blocker) => !empty($blocker['type']))
            ->map(fn(array $blocker) => new Subject(
                id: $blocker['id'] ?? md5($blocker['type'] . json_encode($blocker['entity'] ?? [])),
                type: $blocker['type'],
                role: Subject::ROLE_OBSTACLE,
                details: array_filter([
                    ...($blocker['entity'] ?? []),
                    'conflict' => $blocker['conflict'] ?? [],
                ], fn($value) => $value !== null),
            ))
            ->unique(fn(Subject $subject) => $subject->id)
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
        return $this->subject->date() ?? self::NO_DATE;
    }

    public function window(): ?array
    {
        $start = $this->subject->get('start_time');
        $end = $this->subject->get('end_time');

        return $start && $end ? [Time::toMinutes($start), Time::toMinutes($end)] : null;
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
