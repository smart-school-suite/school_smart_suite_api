<?php

namespace App\Services\Promotion;

use App\DTOs\Promotion\StudentRuleLogDTO;
use App\Models\Promotion\PromotionRun;

/**
 * Builds a promotion run's log: what happened, every problem found (not
 * just the first), and why each student who was not promoted was not.
 *
 * The stored log (PromotionRunLog.rule_logs, version 2):
 *   status   completed | failed | cancelled
 *   title, summary
 *   counts   { total, processed, promoted, retained, needs_review }
 *   issues   [{ severity: error | warning, title, message, details }]
 *   students [{ student_id, name, outcome, problems: [{ rule, message, needs_review }] }]
 *
 * A problem every evaluated student shares (e.g. a missing promotion
 * setting) is reported once as a run-level warning instead of per student.
 */
class PromotionRunLogBuilder
{
    public const VERSION = 2;

    private array $issues = [];
    private array $students = [];

    public function error(string $title, string $message, array $details = []): self
    {
        $this->issues[] = ['severity' => 'error', 'title' => $title, 'message' => $message, 'details' => $details];
        return $this;
    }

    public function warning(string $title, string $message, array $details = []): self
    {
        $this->issues[] = ['severity' => 'warning', 'title' => $title, 'message' => $message, 'details' => $details];
        return $this;
    }

    public function hasErrors(): bool
    {
        return collect($this->issues)->contains('severity', 'error');
    }

    /**
     * Records a student's outcome and the rules that kept them from being
     * promoted (failed or needing review).
     *
     * @param StudentRuleLogDTO[] $rules
     */
    public function student(string $studentId, ?string $name, string $outcome, iterable $rules): self
    {
        $problems = [];
        foreach ($rules as $rule) {
            if ($rule->passed && !$rule->needsReview) {
                continue;
            }
            foreach ($rule->reasons as $reason) {
                $problems[] = ['rule' => $rule->rule, 'message' => $reason->reason, 'needs_review' => $rule->needsReview];
            }
        }

        $this->students[] = ['student_id' => $studentId, 'name' => $name, 'outcome' => $outcome, 'problems' => $problems];
        return $this;
    }

    /** The log as stored (see the class docblock). */
    public function build(string $status, string $title, array $counts = []): array
    {
        $students = $this->hoistSharedProblems();

        return [
            'version' => self::VERSION,
            'status' => $status,
            'title' => $title,
            'summary' => $this->summary($status, $counts),
            'finished_at' => now()->toIso8601String(),
            'counts' => $counts,
            'issues' => $this->issues,
            // Promoted students without problems add nothing to read.
            'students' => array_values(array_filter($students, fn($s) => $s['outcome'] !== 'promoted' || $s['problems'])),
        ];
    }

    /** Builds and stores the log for the run, replacing the previous attempt's. */
    public function save(PromotionRun $run, string $status, string $title, array $counts = []): array
    {
        $log = $this->build($status, $title, $counts);
        app(PromotionRunLogStore::class)->put($run, $log);

        return $log;
    }

    /** Problems every student has become one run-level warning each. */
    private function hoistSharedProblems(): array
    {
        $students = $this->students;
        if (count($students) < 2) {
            return $students;
        }

        $key = fn(array $problem) => $problem['rule'] . '|' . $problem['message'];
        $shared = null;
        foreach ($students as $student) {
            $keys = array_unique(array_map($key, $student['problems']));
            $shared = $shared === null ? $keys : array_values(array_intersect($shared, $keys));
            if (!$shared) {
                return $students;
            }
        }

        foreach ($shared as $sharedKey) {
            [$rule, $message] = explode('|', $sharedKey, 2);
            $this->warning(
                self::ruleTitle($rule),
                $message,
                ['rule' => $rule, 'affected_students' => count($students), 'applies_to_all_students' => true]
            );
        }

        return array_map(function (array $student) use ($shared, $key) {
            $student['problems'] = array_values(array_filter($student['problems'], fn($p) => !in_array($key($p), $shared, true)));
            return $student;
        }, $students);
    }

    private function summary(string $status, array $counts): string
    {
        $errors = collect($this->issues)->where('severity', 'error')->count();
        return match ($status) {
            'failed' => $errors === 1
                ? 'The run could not start because of 1 problem.'
                : "The run could not start because of {$errors} problems.",
            'cancelled' => sprintf('The run was cancelled after %d of %d students were evaluated.', $counts['processed'] ?? 0, $counts['total'] ?? 0),
            default => sprintf(
                '%d of %d students evaluated: %d promoted, %d retained, %d need review.',
                $counts['processed'] ?? 0,
                $counts['total'] ?? 0,
                $counts['promoted'] ?? 0,
                $counts['retained'] ?? 0,
                $counts['needs_review'] ?? 0,
            ),
        };
    }

    public static function ruleTitle(string $rule): string
    {
        return [
            'gpa' => 'GPA requirement',
            'tuition_fee' => 'Tuition fee requirement',
            'additional_fee' => 'Additional fee requirement',
            'carry_over' => 'Carry-over requirement',
            'resit' => 'Carry-over requirement',
            'exam_scores' => 'Exam scores',
            'incomplete_exam_scores' => 'Exam scores',
        ][$rule] ?? ucfirst(str_replace('_', ' ', $rule));
    }

    /**
     * A stored log in the current shape. Logs written before version 2
     * ({ status, title, reasons }) are converted so every client reads one
     * shape.
     */
    public static function normalize(array $log): array
    {
        if (($log['version'] ?? null) === self::VERSION) {
            return $log;
        }

        $failed = ($log['status'] ?? null) === 'failed';
        return [
            'version' => self::VERSION,
            'status' => $failed ? 'failed' : (($log['status'] ?? null) === 'cancelled' ? 'cancelled' : 'completed'),
            'title' => $log['title'] ?? 'Promotion run',
            'summary' => $log['title'] ?? '',
            'finished_at' => null,
            'counts' => [],
            'issues' => array_values(array_map(fn($reason) => [
                'severity' => $failed ? 'error' : 'warning',
                'title' => $log['title'] ?? 'Promotion run',
                'message' => $reason['reason'] ?? '',
                'details' => $reason['details'] ?? [],
            ], array_filter($log['reasons'] ?? [], fn($reason) => $failed))),
            'students' => [],
        ];
    }
}
