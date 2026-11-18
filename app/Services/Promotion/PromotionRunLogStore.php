<?php

namespace App\Services\Promotion;

use App\Models\Promotion\PromotionRun;
use App\Models\Promotion\PromotionRunLog;
use App\Models\Promotion\StudentPromotionLog;

/**
 * Stores promotion logs in the log collections (MongoDB): a run's log (one
 * per run, replacing the previous attempt's) and each student's rule log.
 */
class PromotionRunLogStore
{
    public function put(PromotionRun $run, array $log): void
    {
        PromotionRunLog::updateOrCreate(
            ['promotion_run_id' => $run->id, 'school_branch_id' => $run->school_branch_id],
            ['rule_logs' => $log],
        );
    }

    /** One student's rule results for a run, replacing the previous attempt's. */
    public function putStudent(string $recordId, string $studentId, string $schoolBranchId, array $ruleLogs): void
    {
        StudentPromotionLog::updateOrCreate(
            ['record_id' => $recordId, 'student_id' => $studentId, 'school_branch_id' => $schoolBranchId],
            ['rule_logs' => $ruleLogs],
        );
    }

    /** The student's stored rule logs for a record, or null when there are none. */
    public function getStudent(string $recordId, string $schoolBranchId): ?array
    {
        $log = StudentPromotionLog::where('record_id', $recordId)->where('school_branch_id', $schoolBranchId)->first();
        return $log ? (array) $log->rule_logs : null;
    }
}
