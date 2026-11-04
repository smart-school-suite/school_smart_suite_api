<?php

namespace App\Schedular\ExamTimetable\Solution\Support;

use App\Schedular\ExamTimetable\Solution\Catalog\ConstraintCatalog;
use App\Schedular\ExamTimetable\Solution\Contracts\ConstraintDefinition;
use App\Schedular\ExamTimetable\Solution\DTO\Subject;
use App\Schedular\ExamTimetable\Solution\Exceptions\CannotApplyRemedy;
use App\Schedular\ExamTimetable\Support\Time;

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

    /** Finds the request entry a subject came from; fields the subject does not carry are not compared. */
    public static function findEntry(array $entries, Subject $subject, array $fields): int
    {
        foreach ($entries as $index => $entry) {
            $sameTime = Time::date($entry['date'] ?? null) === $subject->date()
                && Time::toMinutes($entry['start_time'] ?? null) === Time::toMinutes($subject->get('start_time'))
                && Time::toMinutes($entry['end_time'] ?? null) === Time::toMinutes($subject->get('end_time'));

            $sameFields = collect($fields)
                ->filter(fn($field) => $subject->get($field) !== null)
                ->every(fn($field) => ($entry[$field] ?? null) === $subject->get($field));

            if ($sameTime && $sameFields) {
                return $index;
            }
        }

        throw new CannotApplyRemedy('The original request could not be found in this version\'s settings. It may already have been changed.');
    }

    /** Adds or updates one entry of a list of exceptions, matched by a field (date or course_id). */
    public static function upsertException(array $request, string $key, string $list, string $match, string $value, array $values, array $defaults = []): array
    {
        $exceptions = $request[$key][$list] ?? [];
        $exceptions = is_array($exceptions) ? array_values($exceptions) : [];
        $found = false;

        foreach ($exceptions as $index => $exception) {
            $current = $match === 'date' ? Time::date($exception[$match] ?? null) : ($exception[$match] ?? null);

            if ($current === $value) {
                $exceptions[$index] = [...$exception, ...$values];
                $found = true;
            }
        }

        if (!$found) {
            $exceptions[] = [$match => $value, ...$defaults, ...$values];
        }

        $request[$key][$list] = $exceptions;

        return $request;
    }
}
