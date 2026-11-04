<?php

namespace App\Schedular\ExamTimetable\DTO;

use App\Schedular\ExamTimetable\Support\Time;

class ExamSessionDTO
{
    public const TYPE_REGULAR = 'regular';
    public const TYPE_JOINT = 'joint';

    /**
     * @param array $halls [{hall_id, hall_name, capacity, candidate_count, shared, invigilators: string[]}]
     * @param string|null $source the constraint key that pinned this session, null when placed by the engine
     */
    public function __construct(
        public string $type,
        public string $courseId,
        public string $date,
        public int $start,
        public int $end,
        public int $candidateCount,
        public array $halls = [],
        public ?string $source = null,
    ) {}

    public function invigilatorIds(): array
    {
        return collect($this->halls)->flatMap(fn(array $hall) => $hall['invigilators'] ?? [])->unique()->values()->all();
    }

    public function hallIds(): array
    {
        return array_column($this->halls, 'hall_id');
    }

    public function startTime(): string
    {
        return Time::toTime($this->start);
    }

    public function endTime(): string
    {
        return Time::toTime($this->end);
    }

    public function overlaps(int $start, int $end): bool
    {
        return Time::overlaps($this->start, $this->end, $start, $end);
    }
}
