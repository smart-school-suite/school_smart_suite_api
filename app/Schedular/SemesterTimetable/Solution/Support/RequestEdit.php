<?php

namespace App\Schedular\SemesterTimetable\Solution\Support;

use App\Schedular\SemesterTimetable\Solution\Catalog\ConstraintCatalog;
use App\Schedular\SemesterTimetable\Solution\Contracts\ConstraintDefinition;
use App\Schedular\SemesterTimetable\Solution\DTO\Subject;
use App\Schedular\SemesterTimetable\Solution\Exceptions\CannotApplyRemedy;

final class RequestEdit
{
    public function __construct(
        private ConstraintCatalog $catalog,
        private array $subjects,
    ) {}

    public function subject(string $id): Subject
    {
        return $this->subjects[$id] ?? throw new CannotApplyRemedy('The problem this step refers to is no longer part of this timetable version.');
    }

    public function definition(string $type): ConstraintDefinition
    {
        return $this->catalog->for($type);
    }

    public static function findEntry(array $entries, Subject $subject, array $fields): int
    {
        foreach ($entries as $index => $entry) {
            $sameTime = strtolower($entry['day'] ?? '') === $subject->day()
                && Time::toMinutes($entry['start_time'] ?? null) === Time::toMinutes($subject->get('start_time'))
                && Time::toMinutes($entry['end_time'] ?? null) === Time::toMinutes($subject->get('end_time'));

            if ($sameTime && collect($fields)->every(fn($field) => ($entry[$field] ?? null) === $subject->get($field))) {
                return $index;
            }
        }

        throw new CannotApplyRemedy('The original request could not be found in this version\'s settings. It may already have been changed.');
    }

    public static function dayException(array $request, string $key, string $day, array $values, string $match = 'day'): array
    {
        $exceptions = $request[$key]['day_exceptions'] ?? [];
        $exceptions = is_array($exceptions) ? array_values($exceptions) : [];
        $found = false;

        foreach ($exceptions as $index => $exception) {
            if (strtolower((string) ($exception[$match] ?? '')) === strtolower($day)) {
                $exceptions[$index] = [...$exception, ...$values];
                $found = true;
            }
        }

        if (!$found) {
            $exceptions[] = [$match => $day, ...$values];
        }

        $request[$key]['day_exceptions'] = $exceptions;

        return $request;
    }
}
