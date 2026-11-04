<?php

namespace App\Schedular\ExamTimetable\Solution\DTO;

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
