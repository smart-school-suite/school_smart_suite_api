<?php

namespace App\Http\Resources\Job;

use App\Models\Job\SystemJob;
use App\Services\Import\ImportType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/**
 * A system job as the app shows it. The same shape is sent in every
 * "job.update" event, so the app can render a job the same way whether it
 * came from the list, a poll or the live event.
 */
class SystemJobResource extends JsonResource
{
    /** A running job not updated for this long is reported as stalled. */
    public const STALE_AFTER_MINUTES = 15;

    private const LEGACY_TYPES = [
        'assign_teacher_specialties' => ['Teacher Specialties Assignment', 'Teacher Specialty', 'Bulk Assignment'],
    ];

    public function toArray(Request $request): array
    {
        $result = is_array($this->result) ? $this->result : [];
        $status = strtolower((string) $this->status);

        return [
            "id" => $this->id,
            "title" => $this->title(),
            "module" => $this->module(),
            "type" => $this->kind(),
            "status" => $status,
            "stage" => $this->stage,
            "summary" => $result['summary'] ?? $this->stage,
            "total_items" => $this->total_items ?? 0,
            "processed_items" => $this->processed_items ?? 0,
            "successful_items" => $this->successful_items ?? 0,
            "failed_items" => $this->failed_items ?? 0,
            "remaining_items" => $this->remainingItems(),
            "progress_percentage" => $this->progressPercentage(),
            "is_finished" => in_array($status, SystemJob::FINISHED_STATUSES, true),
            "is_stale" => $this->isStale($status),
            "has_issues" => ($this->failed_items ?? 0) > 0 || $status === SystemJob::STATUS_FAILED,
            "error_message" => $this->error_message,
            "result" => $this->when($result !== [], fn() => [
                'total' => $result['total'] ?? null,
                'created' => $result['created'] ?? 0,
                'updated' => $result['updated'] ?? 0,
                'failed' => $result['failed'] ?? 0,
                'blank_rows' => $result['blank_rows'] ?? 0,
                'issue_count' => count($result['error_groups'] ?? []),
            ]),
            "started_at" => $this->started_at,
            "finished_at" => $this->finished_at,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
            "category_id" => $this->category?->id,
            "category_name" => $this->category?->name,
        ];
    }

    private function importType(): ?ImportType
    {
        return ImportType::tryFrom((string) $this->type);
    }

    private function title(): string
    {
        return $this->importType()?->title()
            ?? self::LEGACY_TYPES[$this->type][0]
            ?? $this->readable($this->type);
    }

    private function module(): string
    {
        return $this->importType()?->module()
            ?? self::LEGACY_TYPES[$this->type][1]
            ?? $this->category?->name
            ?? $this->readable($this->type);
    }

    private function kind(): string
    {
        if ($this->importType()) {
            return 'Import';
        }

        if (isset(self::LEGACY_TYPES[$this->type])) {
            return self::LEGACY_TYPES[$this->type][2];
        }

        return Str::contains(strtolower((string) $this->type), 'generation') ? 'Generation' : 'Background Job';
    }

    /** "semester_timetable_generation" or "Semester Timetable Generation" -> "Semester Timetable Generation". */
    private function readable(?string $type): string
    {
        return $type ? Str::headline($type) : 'Background Job';
    }

    private function progressPercentage(): int
    {
        if (in_array(strtolower((string) $this->status), [SystemJob::STATUS_COMPLETED, SystemJob::STATUS_COMPLETED_WITH_ISSUES], true)) {
            return 100;
        }

        if ($this->progress !== null && (int) $this->progress > 0) {
            return (int) $this->progress;
        }

        if (($this->total_items ?? 0) <= 0) {
            return 0;
        }

        return (int) round((($this->processed_items ?? 0) / $this->total_items) * 100);
    }

    private function remainingItems(): int
    {
        return max(0, ($this->total_items ?? 0) - ($this->processed_items ?? 0));
    }

    private function isStale(string $status): bool
    {
        if (!in_array($status, [SystemJob::STATUS_QUEUED, SystemJob::STATUS_PROCESSING], true)) {
            return false;
        }

        return $this->updated_at !== null
            && $this->updated_at->lt(now()->subMinutes(self::STALE_AFTER_MINUTES));
    }
}
