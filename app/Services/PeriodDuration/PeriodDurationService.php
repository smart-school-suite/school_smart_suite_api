<?php

namespace App\Services\PeriodDuration;

use App\Constant\Enums\SystemState;
use App\Exceptions\AppException;
use App\Models\PeriodDuration\PeriodDuration;
use App\Models\PeriodDuration\PeriodDurationType;

class PeriodDurationService
{

    protected const EXAM_TIMETABLE_DURATION = "exam_timetable_duration";
    protected const SEMESTER_TIMETABLE_DURATION = "semester_timetable_duration";
    protected const RESIT_TIMETABLE_DURATION = "resit_timetable_duration";

    public function createDuration(array $data): PeriodDuration
    {
        $type = PeriodDurationType::find($data['type_id']);

        if (!$type) {
            throw new AppException(
                "period_duration_type_not_found",
                404,
                "Invalid Duration Type",
                "The selected duration type does not exist. Please select a valid duration type and try again."
            );
        }

        // Check if duration with same key and type already exists
        $existingDuration = PeriodDuration::where('key', $data['key'])
            ->where('type_id', $type->id)
            ->first();

        if ($existingDuration) {
            throw new AppException(
                "period_duration_already_exists",
                409,
                "Duration Already Exists",
                "A period duration with this key already exists for the selected type. Please use a unique key."
            );
        }

        $periodDuration = PeriodDuration::create([
            'name' => $data['name'],
            'minutes' => $data['minutes'],
            'description' => $data['description'],
            'key' => $data['key'],
            'type_id' => $type->id,
            'status' => SystemState::ACTIVE // Default to active when created
        ]);

        return $periodDuration->fresh();
    }

    public function activateDuration(string $periodDurationId): PeriodDuration
    {
        $periodDuration = PeriodDuration::find($periodDurationId);

        if (!$periodDuration) {
            throw new AppException(
                "period_duration_not_found",
                404,
                "Duration Not Found",
                "The requested period duration could not be found. Please verify the ID and try again."
            );
        }

        if ($periodDuration->status === SystemState::ACTIVE) {
            throw new AppException(
                "period_duration_already_active",
                422,
                "Duration Already Active",
                "This period duration is already active. No changes were made."
            );
        }

        $periodDuration->status = SystemState::ACTIVE;
        $periodDuration->save();

        return $periodDuration->fresh();
    }

    public function deactivateDuration(string $periodDurationId): PeriodDuration
    {
        $periodDuration = PeriodDuration::find($periodDurationId);

        if (!$periodDuration) {
            throw new AppException(
                "period_duration_not_found",
                404,
                "Duration Not Found",
                "The requested period duration could not be found. Please verify the ID and try again."
            );
        }

        if ($periodDuration->status === SystemState::INACTIVE) {
            throw new AppException(
                "period_duration_already_inactive",
                422,
                "Duration Already Inactive",
                "This period duration is already inactive. No changes were made."
            );
        }

        $periodDuration->status = SystemState::INACTIVE;
        $periodDuration->save();

        return $periodDuration->fresh();
    }

    public function getExamTimetableDurations()
    {
        return PeriodDuration::whereHas('type', function ($query) {
            $query->where('key', self::EXAM_TIMETABLE_DURATION);
        })
        ->where('status', SystemState::ACTIVE)
        ->with('type')
        ->orderBy('minutes')
        ->get();
    }

    public function getSemesterTimetableDuration()
    {
        return PeriodDuration::whereHas('type', function ($query) {
            $query->where('key', self::SEMESTER_TIMETABLE_DURATION);
        })
        ->where('status', SystemState::ACTIVE)
        ->with('type')
        ->orderBy('minutes')
        ->get();
    }

    public function getResitTimetableDuration()
    {
        return PeriodDuration::whereHas('type', function ($query) {
            $query->where('key', self::RESIT_TIMETABLE_DURATION);
        })
        ->where('status', SystemState::ACTIVE)
        ->with('type')
        ->orderBy('minutes')
        ->get();
    }

    public function getAllTimetableDurations()
    {
        return PeriodDuration::with('type')
            ->orderBy('type_id')
            ->orderBy('minutes')
            ->get();
    }

    public function getDurationById(string $periodDurationId): PeriodDuration
    {
        $periodDuration = PeriodDuration::with('type')->find($periodDurationId);

        if (!$periodDuration) {
            throw new AppException(
                "period_duration_not_found",
                404,
                "Duration Not Found",
                "The requested period duration could not be found. Please verify the ID and try again."
            );
        }

        return $periodDuration;
    }

    public function updateDuration(string $periodDurationId, array $data): PeriodDuration
    {
        $periodDuration = PeriodDuration::find($periodDurationId);

        if (!$periodDuration) {
            throw new AppException(
                "period_duration_not_found",
                404,
                "Duration Not Found",
                "The period duration you're trying to update could not be found."
            );
        }

        // If type_id is being updated, verify the new type exists
        if (isset($data['type_id'])) {
            $type = PeriodDurationType::find($data['type_id']);
            if (!$type) {
                throw new AppException(
                    "period_duration_type_not_found",
                    404,
                    "Invalid Duration Type",
                    "The selected duration type does not exist."
                );
            }
        }

        // If key is being updated, check for uniqueness with the type
        if (isset($data['key'])) {
            $typeId = $data['type_id'] ?? $periodDuration->type_id;
            $existingDuration = PeriodDuration::where('key', $data['key'])
                ->where('type_id', $typeId)
                ->where('id', '!=', $periodDurationId)
                ->first();

            if ($existingDuration) {
                throw new AppException(
                    "period_duration_key_exists",
                    409,
                    "Duplicate Duration Key",
                    "A period duration with this key already exists for the selected type."
                );
            }
        }

        $periodDuration->update($data);

        return $periodDuration->fresh();
    }

    public function deleteDuration(string $periodDurationId): bool
    {
        $periodDuration = PeriodDuration::find($periodDurationId);

        if (!$periodDuration) {
            throw new AppException(
                "period_duration_not_found",
                404,
                "Duration Not Found",
                "The period duration you're trying to delete could not be found."
            );
        }

        if ($periodDuration->timetableSlots()->exists()) {
            throw new AppException(
                "period_duration_in_use",
                409,
                "Duration In Use",
                "This duration cannot be deleted as it is currently being used in existing timetables."
            );
        }

        return $periodDuration->delete();
    }

    public function getDurationsByType(string $typeKey)
    {
        $allowedTypes = [
            self::EXAM_TIMETABLE_DURATION,
            self::SEMESTER_TIMETABLE_DURATION,
            self::RESIT_TIMETABLE_DURATION
        ];

        if (!in_array($typeKey, $allowedTypes)) {
            throw new AppException(
                "invalid_duration_type",
                400,
                "Invalid Duration Type",
                "The specified duration type is not recognized."
            );
        }

        return PeriodDuration::whereHas('type', function ($query) use ($typeKey) {
            $query->where('key', $typeKey);
        })
        ->with('type')
        ->orderBy('minutes')
        ->get();
    }
}
