<?php

namespace App\Schedular\ExamTimetable\Solution\Support;

use App\Schedular\ExamTimetable\Placement\Support\Time;
use App\Schedular\ExamTimetable\Solution\Catalog\ConstraintCatalog;
use App\Schedular\ExamTimetable\Solution\Contracts\ConstraintDefinition;
use App\Schedular\ExamTimetable\Solution\DTO\Subject;
use App\Schedular\ExamTimetable\Solution\Exceptions\CannotApplyRemedy;

/** What a definition needs to apply one step to a generation request. */
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

    /** Index of the request entry the subject came from. */
    public static function findEntry(array $entries, Subject $subject, array $fields): int
    {
        foreach ($entries as $index => $entry) {
            $sameTime = ($entry['date'] ?? null) === $subject->date()
                && Time::normalize($entry['start_time'] ?? null) === Time::normalize($subject->get('start_time'))
                && Time::normalize($entry['end_time'] ?? null) === Time::normalize($subject->get('end_time'));

            if ($sameTime && collect($fields)->every(fn($field) => ($entry[$field] ?? null) === $subject->get($field))) {
                return $index;
            }
        }

        throw new CannotApplyRemedy('The original request could not be found in this version\'s settings. It may already have been changed.');
    }

    /** Adds or updates the date exception of a constraint for one date. */
    public static function dateException(array $request, string $key, string $date, array $values): array
    {
        $exceptions = array_values($request[$key]['date_exceptions'] ?? []);
        $found = false;

        foreach ($exceptions as $index => $exception) {
            if (($exception['date'] ?? null) === $date) {
                $exceptions[$index] = [...$exception, ...$values];
                $found = true;
            }
        }

        if (!$found) {
            $exceptions[] = ['date' => $date, ...$values];
        }

        $request[$key]['date_exceptions'] = $exceptions;

        return $request;
    }
}
