<?php

namespace App\Services\Import;

/**
 * Collects the problems found while importing a file and groups them by
 * reason, so a report reads "Rows 2–21: a teacher with this email already
 * exists" instead of twenty separate lines.
 *
 * A group is one reason + one message (+ the column it concerns). Messages
 * must therefore not carry row-specific values; those go in `$value`, which
 * is kept as a short list of examples on the group.
 */
class ImportErrorCollector
{
    /** Examples kept per group, enough to recognise the rows without bloating the job. */
    private const MAX_EXAMPLES = 5;

    /** @var array<string, array> */
    private array $groups = [];

    /** @var array<int, true> */
    private array $failedRows = [];

    public function add(
        int $row,
        string $reason,
        string $message,
        ?string $field = null,
        mixed $value = null,
    ): void {
        $key = implode('|', [$reason, $field ?? '', $message]);

        $this->groups[$key] ??= [
            'reason' => $reason,
            'title' => ImportErrorReason::title($reason),
            'field' => $field,
            'message' => $message,
            'rows' => [],
            'examples' => [],
        ];

        $this->groups[$key]['rows'][$row] = $row;

        if ($this->isPresentable($value) && count($this->groups[$key]['examples']) < self::MAX_EXAMPLES) {
            $this->groups[$key]['examples'][] = ['row' => $row, 'value' => (string) $value];
        }

        $this->failedRows[$row] = true;
    }

    public function addException(int $row, ImportRowException $exception): void
    {
        $this->add($row, $exception->reason, $exception->getMessage(), $exception->field, $exception->value);
    }

    public function isEmpty(): bool
    {
        return $this->groups === [];
    }

    /** Rows with at least one problem. A row can appear in several groups but counts once here. */
    public function failedRowCount(): int
    {
        return count($this->failedRows);
    }

    public function hasFailed(int $row): bool
    {
        return isset($this->failedRows[$row]);
    }

    /**
     * The groups, largest first. Each has the row numbers, a readable range
     * label ("2–21, 25") and a one-line summary.
     *
     * @return array<int, array>
     */
    public function groups(): array
    {
        return collect($this->groups)
            ->map(function (array $group) {
                $rows = array_values($group['rows']);
                sort($rows);
                $ranges = self::ranges($rows);

                return [
                    'reason' => $group['reason'],
                    'title' => $group['title'],
                    'field' => $group['field'],
                    'message' => $group['message'],
                    'count' => count($rows),
                    'rows' => $rows,
                    'row_ranges' => $ranges,
                    'summary' => self::rowsLabel($rows, $ranges) . ': ' . $group['message'],
                    'examples' => $group['examples'],
                ];
            })
            ->sortByDesc('count')
            ->values()
            ->all();
    }

    /**
     * Compress sorted row numbers into "2–21, 25, 30–31".
     *
     * @param array<int, int> $rows
     */
    public static function ranges(array $rows): string
    {
        $parts = [];
        $start = $previous = null;

        foreach ($rows as $row) {
            if ($start === null) {
                $start = $previous = $row;
                continue;
            }

            if ($row === $previous + 1) {
                $previous = $row;
                continue;
            }

            $parts[] = self::range($start, $previous);
            $start = $previous = $row;
        }

        if ($start !== null) {
            $parts[] = self::range($start, $previous);
        }

        return implode(', ', $parts);
    }

    private static function range(int $start, int $end): string
    {
        return $start === $end ? (string) $start : "{$start}–{$end}";
    }

    private static function rowsLabel(array $rows, string $ranges): string
    {
        $single = count($rows) === 1;

        return ($single ? 'Row ' : 'Rows ') . $ranges;
    }

    private function isPresentable(mixed $value): bool
    {
        return (is_string($value) && trim($value) !== '') || is_int($value) || is_float($value);
    }
}
