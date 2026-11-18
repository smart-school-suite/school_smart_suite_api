<?php

namespace App\Services\Helpers\DateRules;

use App\Exceptions\AppException;
use Carbon\Carbon;

/**
 * Shared date-rule helpers for update policies (academic years, semesters):
 * each date limit is collected as ['date', 'why', 'kind'], bounds() keeps the
 * tightest one on each side and every limit for clients, and assertWithin()
 * refuses a value with the reason of the limit it breaks.
 */
trait DateRangeRules
{
    protected function bounds(array $min, array $max): array
    {
        $tightestMin = collect($min)->sortByDesc('date')->first();
        $tightestMax = collect($max)->sortBy('date')->first();

        $limit = fn(array $item) => ['date' => $item['date'], 'reason' => $item['why'], 'kind' => $item['kind'] ?? 'other'];

        return [
            'min' => $tightestMin['date'] ?? null,
            'max' => $tightestMax['date'] ?? null,
            'min_reason' => $tightestMin['why'] ?? null,
            'max_reason' => $tightestMax['why'] ?? null,
            // Every limit, so a client can re-derive the range when the
            // system academic year (kind "system_year") is changed.
            'limits' => ['min' => array_map($limit, $min), 'max' => array_map($limit, $max)],
        ];
    }

    protected function assertWithin(string $label, string $value, array $bounds): void
    {
        if ($bounds['min'] && $bounds['max'] && $bounds['min'] > $bounds['max']) {
            $this->fail('No Valid Date', "There is no valid {$label}: {$bounds['min_reason']}, but {$bounds['max_reason']}.");
        }
        if ($bounds['min'] && $value < $bounds['min']) {
            $this->fail('Date Too Early', "The {$label} must be on or after {$this->human($bounds['min'])}: {$bounds['min_reason']}.");
        }
        if ($bounds['max'] && $value > $bounds['max']) {
            $this->fail('Date Too Late', "The {$label} must be on or before {$this->human($bounds['max'])}: {$bounds['max_reason']}.");
        }
    }

    protected function fail(string $title, string $description, int $code = 422): never
    {
        throw new AppException($description, $code, $title, $description);
    }

    protected function plural(int $count, string $noun): string
    {
        return $count . ' ' . $noun . ($count === 1 ? '' : 's');
    }

    protected function today(): string
    {
        return now()->toDateString();
    }

    /** "1 May 2027", for messages. */
    protected function human($date): string
    {
        return Carbon::parse($this->date($date))->format('j M Y');
    }

    protected function shift(string $date, int $days): string
    {
        return Carbon::parse($date)->addDays($days)->toDateString();
    }

    protected function date($value): ?string
    {
        return $value ? substr((string) ($value instanceof \DateTimeInterface ? $value->format('Y-m-d') : $value), 0, 10) : null;
    }
}
