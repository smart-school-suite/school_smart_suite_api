<?php

namespace App\Jobs\DataCreationJob;

use App\Models\Exams;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;
use Throwable;
class UpdateExamStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5;
    public $backoff = [10, 30, 60, 120, 300];
    public $timeout = 60;

    public $examId;
    public $schoolBranchId;

    // Define allowed statuses as constants
    public const STATUS_PENDING   = 'pending';
    public const STATUS_ONGOING   = 'ongoing';
    public const STATUS_COMPLETED = 'completed';

    public function __construct(string $examId, string $schoolBranchId)
    {
        $this->examId = $examId;
        $this->schoolBranchId = $schoolBranchId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $exam = Exams::where('school_branch_id', $this->schoolBranchId)
            ->with(['examType'])
                         ->find($this->examId);

            if (!$exam) {
                Log::warning("Exam not found for status update", [
                    'exam_id' => $this->examId,
                    'school_branch_id' => $this->schoolBranchId,
                ]);
                return;
            }

            $now = Carbon::now();
            $oldStatus = $exam->status;
            $newStatus = $this->determineExamStatus($exam, $now);

            if ($oldStatus !== $newStatus) {
                $exam->status = $newStatus;
                $exam->status_updated_at = $now;
                $exam->save();

                Log::info('Exam status updated', [
                    'exam_id' => $exam->id,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'school_branch_id' => $this->schoolBranchId,
                ]);
            }

        } catch (Exception $e) {
            Log::error('Failed to update exam status', [
                'exam_id' => $this->examId,
                'school_branch_id' => $this->schoolBranchId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    private function determineExamStatus(Exams $exam, Carbon $now): string
    {
        if (!$exam->start_date) {
            return self::STATUS_PENDING;
        }

        if (!$exam->end_date) {
            return $now->greaterThanOrEqualTo($exam->start_date)
                ? self::STATUS_ONGOING
                : self::STATUS_PENDING;
        }

        if ($now->lessThan($exam->start_date)) {
            return self::STATUS_PENDING;
        }

        if ($now->greaterThanOrEqualTo($exam->start_date) && $now->lessThan($exam->end_date)) {
            return self::STATUS_ONGOING;
        }

        return self::STATUS_COMPLETED;
    }

    public function failed(Throwable $exception): void
    {
        Log::critical('UpdateExamStatusJob permanently failed after retries', [
            'exam_id' => $this->examId,
            'school_branch_id' => $this->schoolBranchId,
            'error' => $exception->getMessage(),
        ]);

    }
}
