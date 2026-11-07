<?php

namespace App\Schedular\ExamTimetable\Solution\DTO;

/** A failed request, or something in its way, with the details from its diagnostic. */
final class Subject
{
    public const ROLE_REQUEST = 'request';
    public const ROLE_OBSTACLE = 'obstacle';

    public function __construct(
        public readonly string $id,
        public readonly string $type,
        public readonly string $role,
        public readonly array $details = [],
    ) {}

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->details[$key] ?? $default;
    }

    /** The request this obstacle is in the way of, or a field of it. */
    public function conflict(?string $key = null): mixed
    {
        $conflict = $this->details['conflict'] ?? [];
        return $key === null ? $conflict : ($conflict[$key] ?? null);
    }

    public function date(): ?string
    {
        return $this->details['date'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'role' => $this->role,
        ];
    }
}
