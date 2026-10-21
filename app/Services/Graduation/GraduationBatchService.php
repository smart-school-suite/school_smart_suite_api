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

        return $graduationDetails;
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
