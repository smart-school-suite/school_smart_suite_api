<?php

namespace App\Schedular\SemesterTimetable\Solution\DTO;

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

    public function day(): ?string
    {
        $day = $this->details['day'] ?? null;

        return $day ? strtolower($day) : null;
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
