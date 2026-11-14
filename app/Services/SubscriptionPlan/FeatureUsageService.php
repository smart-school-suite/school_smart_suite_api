<?php

namespace App\Services\SubscriptionPlan;

use App\Models\SchoolSubscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Measures how much of each plan feature a school branch is really using.
 *
 * `subscription_usage.current_usage` is not incremented everywhere yet, so the
 * usage of countable features is read from the tables that hold them. The
 * larger of the stored and the measured value wins, so a feature whose usage
 * is tracked elsewhere (e.g. rate limits) still reports what was stored.
 */
class FeatureUsageService
{
    /**
     * feature key => [tables counted, whether only rows created during the
     * current subscription period count].
     *
     * Capacity features (courses, departments, ...) count everything the school
     * has; consumable features (generations, announcements, ...) count what was
     * used since the subscription started.
     */
    public const SOURCES = [
        'school.admins'        => ['tables' => ['school_admins'], 'period' => false],
        'school.departments'   => ['tables' => ['departments'], 'period' => false],
        'school.specialties'   => ['tables' => ['specialties'], 'period' => false],
        'school.courses'       => ['tables' => ['courses'], 'period' => false],
        'school.announcements' => ['tables' => ['announcements'], 'period' => true],
        'school.events'        => ['tables' => ['school_events'], 'period' => true],
        'school.elections'     => ['tables' => ['elections'], 'period' => true],
        'timetable.generation' => ['tables' => ['timetable_versions', 'exam_timetable_versions'], 'period' => true],
    ];

    /** @var array<string, bool> */
    private array $columnCache = [];

    /**
     * Measured usage for a feature key, or null when it cannot be measured.
     */
    public function measure(string $featureKey, string $schoolBranchId, ?SchoolSubscription $subscription = null): ?int
    {
        $source = self::SOURCES[$featureKey] ?? null;
        if (!$source) {
            return null;
        }

        $total = 0;
        $measured = false;
        foreach ($source['tables'] as $table) {
            if (!$this->hasColumn($table, 'school_branch_id')) {
                continue;
            }

            $query = DB::table($table)->where('school_branch_id', $schoolBranchId);

            if ($source['period'] && $subscription?->start_date && $this->hasColumn($table, 'created_at')) {
                $query->where('created_at', '>=', $subscription->start_date);
            }

            $total += $query->count();
            $measured = true;
        }

        return $measured ? $total : null;
    }

    /**
     * Usage to show and evaluate: the larger of the stored and measured values.
     */
    public function resolve(string $featureKey, string $schoolBranchId, ?SchoolSubscription $subscription, int $stored = 0): int
    {
        $measured = $this->measure($featureKey, $schoolBranchId, $subscription);

        return max($stored, $measured ?? 0);
    }

    private function hasColumn(string $table, string $column): bool
    {
        $key = "{$table}.{$column}";

        return $this->columnCache[$key] ??= Schema::hasTable($table) && Schema::hasColumn($table, $column);
    }
}
