<?php

namespace App\Services\Graduation;

use App\Exceptions\AppException;
use App\Models\Graduation\GraduationBatch;

class GraduationBatchService
{
    public function getGraduationBatches(object $currentSchool)
    {
        $graduationBatches = GraduationBatch::where("school_branch_id", $currentSchool->id)
            ->with(['academicYear'])
            ->withCount('graduationRecord')
            ->get();

        if ($graduationBatches->isEmpty()) {
            throw new AppException(
                "Graduation Batches Not Found",
                404,
                "Resource Not Found",
                "No graduation batches found for the specified school branch."
            );
        }

        return $graduationBatches;
    }

    public function getGraduationBatchDetails(object $currentSchool, string $batchId)
    {
        $graduationDetails = GraduationBatch::where("school_branch_id", $currentSchool->id)
            ->where("id", $batchId)
            ->with(['academicYear', 'graduationRecord.specialty.level'])
            ->first();

        if (!$graduationDetails) {
            throw new AppException(
                "Graduation Batch Not Found",
                404,
                "Resource Not Found",
                "The requested graduation batch does not exist or does not belong to the current school branch."
            );
        }

        return $this->presentDetails($graduationDetails);
    }

    /**
     * The batch for the details drawer: its own fields, its academic year,
     * how many graduates it has by status, eligibility and clearance, and by
     * specialty.
     */
    private function presentDetails(GraduationBatch $batch): array
    {
        $records = $batch->graduationRecord;
        $countBy = fn(string $column, array $values) => collect($values)
            ->mapWithKeys(fn($value) => [$value => $records->where($column, $value)->count()])
            ->all();

        $start = $batch->graduation_start_date;
        $end = $batch->graduation_end_date;
        $now = now();

        return [
            'id' => $batch->id,
            'name' => $batch->name,
            'description' => $batch->description,
            'location' => $batch->location,
            'graduation_start_date' => $start,
            'graduation_end_date' => $end,
            'ceremony_status' => match (true) {
                !$start => 'not_scheduled',
                $end && $end->lt($now) => 'held',
                $start->lte($now) => 'in_progress',
                default => 'upcoming',
            },
            'academic_year' => $batch->academicYear?->only(['id', 'name', 'year_start', 'year_end']),
            'total_graduates' => $records->count(),
            'status_counts' => $countBy('status', ['pending', 'continuing', 'alumni']),
            'eligibility_counts' => $countBy('eligibility_status', ['eligible', 'pending_review', 'ineligible']),
            'clearance_counts' => $countBy('clearance_status', ['cleared', 'pending', 'blocked']),
            'specialties' => $records->groupBy('specialty_id')
                ->map(fn($group) => [
                    'specialty_id' => $group->first()->specialty_id,
                    'specialty_name' => $group->first()->specialty?->specialty_name,
                    'level_name' => $group->first()->specialty?->level?->name,
                    'graduates' => $group->count(),
                ])
                ->sortByDesc('graduates')
                ->values()
                ->all(),
            'created_at' => $batch->created_at,
            'updated_at' => $batch->updated_at,
        ];
    }

    public function updateGraduationBatch(object $currentSchool, array $payload, string $batchId): GraduationBatch
    {
        $graduationDetails = GraduationBatch::where("school_branch_id", $currentSchool->id)
            ->where("id", $batchId)
            ->with(['academicYear', 'graduationRecord'])
            ->first();

        if (!$graduationDetails) {
            throw new AppException(
                "Graduation Batch Not Found",
                404,
                "Resource Not Found",
                "The requested graduation batch does not exist or does not belong to the current school branch."
            );
        }

        $allowedColumns = [
            'name',
            'description',
            'graduation_start_date',
            'graduation_end_date',
            'location',
        ];

        $updateData = array_intersect_key($payload, array_flip($allowedColumns));

        if (empty($updateData)) {
            throw new AppException(
                "Invalid Update Payload",
                400,
                "No Valid Fields Provided",
                "None of the provided fields are allowed to be updated for this graduation batch."
            );
        }

        $startDate = $updateData['graduation_start_date'] ?? $graduationDetails->graduation_start_date;
        $endDate = $updateData['graduation_end_date'] ?? $graduationDetails->graduation_end_date;

        if ($startDate && $endDate && strtotime($startDate) > strtotime($endDate)) {
            throw new AppException(
                "Invalid Date Range",
                422,
                "Unprocessable Date Parameters",
                "The graduation start date cannot be later than the graduation end date."
            );
        }

        $graduationDetails->update($updateData);

        return $graduationDetails;
    }
}
