<?php

namespace App\Services\PeriodDuration;

use App\Constant\Enums\SystemState;
use App\Exceptions\AppException;
use App\Models\PeriodDuration\PeriodDurationType;
use Illuminate\Support\Collection;

class PeriodDurationTypeService
{
    public function createDurationType(array $data): PeriodDurationType
    {
        // Check if type with same key already exists
        $existingType = PeriodDurationType::where('key', $data['key'])->first();

        if ($existingType) {
            throw new AppException(
                "period_duration_type_key_exists",
                409,
                "Duration Type Already Exists",
                "A duration type with this key already exists. Please use a unique key."
            );
        }

        // Check if type with same name exists (optional but good for data quality)
        $existingName = PeriodDurationType::where('name', $data['name'])->first();

        if ($existingName) {
            throw new AppException(
                "period_duration_type_name_exists",
                409,
                "Duration Type Name Already Exists",
                "A duration type with this name already exists. Please use a unique name."
            );
        }

        $durationType = PeriodDurationType::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? '',
            'key' => $data['key'],
            'status' => SystemState::ACTIVE
        ]);

        return $durationType->fresh();
    }
    public function updateDurationType(string $periodDurationTypeId, array $data): PeriodDurationType
    {
        $durationType = PeriodDurationType::find($periodDurationTypeId);

        if (!$durationType) {
            throw new AppException(
                "period_duration_type_not_found",
                404,
                "Duration Type Not Found",
                "The duration type you're trying to update could not be found."
            );
        }

        // Check if new key conflicts with existing (excluding current record)
        if (isset($data['key']) && $data['key'] !== $durationType->key) {
            $keyExists = PeriodDurationType::where('key', $data['key'])
                ->where('id', '!=', $periodDurationTypeId)
                ->exists();

            if ($keyExists) {
                throw new AppException(
                    "period_duration_type_key_exists",
                    409,
                    "Duplicate Duration Type Key",
                    "Another duration type with this key already exists. Please use a unique key."
                );
            }
        }

        // Check if new name conflicts with existing (excluding current record)
        if (isset($data['name']) && $data['name'] !== $durationType->name) {
            $nameExists = PeriodDurationType::where('name', $data['name'])
                ->where('id', '!=', $periodDurationTypeId)
                ->exists();

            if ($nameExists) {
                throw new AppException(
                    "period_duration_type_name_exists",
                    409,
                    "Duplicate Duration Type Name",
                    "Another duration type with this name already exists. Please use a unique name."
                );
            }
        }

        // Update only provided fields
        $durationType->update(array_filter($data, function ($value) {
            return $value !== null;
        }));

        return $durationType->fresh();
    }
    public function deleteDurationType(string $periodDurationTypeId): bool
    {
        $durationType = PeriodDurationType::find($periodDurationTypeId);

        if (!$durationType) {
            throw new AppException(
                "period_duration_type_not_found",
                404,
                "Duration Type Not Found",
                "The duration type you're trying to delete could not be found."
            );
        }

        // Check if this type is being used by any period durations
        $hasDurations = $durationType->periodDurations()->exists();

        if ($hasDurations) {
            throw new AppException(
                "period_duration_type_in_use",
                409,
                "Cannot Delete Duration Type",
                "This duration type cannot be deleted because it has associated period durations. Please delete or reassign the durations first."
            );
        }

        // Soft delete or hard delete based on your requirements
        // Using soft delete is recommended to preserve historical data
        if (method_exists($durationType, 'trashed')) {
            return $durationType->delete();
        }

        // Hard delete if soft delete isn't enabled
        return (bool) $durationType->delete();
    }
    public function getDurationTypes(array $filters = []): Collection
    {
        $query = PeriodDurationType::query();

        // Filter by status if provided
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by search term (name or key)
        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'LIKE', '%' . $filters['search'] . '%')
                  ->orWhere('key', 'LIKE', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'LIKE', '%' . $filters['search'] . '%');
            });
        }

        // Filter by specific keys
        if (isset($filters['keys']) && is_array($filters['keys'])) {
            $query->whereIn('key', $filters['keys']);
        }

        // Order by specified column
        $orderBy = $filters['order_by'] ?? 'created_at';
        $orderDirection = $filters['order_direction'] ?? 'desc';
        $query->orderBy($orderBy, $orderDirection);

        // Include duration counts if requested
        if (isset($filters['with_counts']) && $filters['with_counts'] === true) {
            $query->withCount('periodDurations');
        }

        // Include active durations if requested
        if (isset($filters['with_active_durations']) && $filters['with_active_durations'] === true) {
            $query->with(['periodDurations' => function ($q) {
                $q->where('status', SystemState::ACTIVE);
            }]);
        } else if (isset($filters['with_durations']) && $filters['with_durations'] === true) {
            $query->with('periodDurations');
        }

        return $query->get();
    }
    public function getDurationTypeById(string $periodDurationTypeId): PeriodDurationType
    {
        $durationType = PeriodDurationType::with('periodDurations')
            ->find($periodDurationTypeId);

        if (!$durationType) {
            throw new AppException(
                "period_duration_type_not_found",
                404,
                "Duration Type Not Found",
                "The requested duration type could not be found."
            );
        }

        return $durationType;
    }
    public function getDurationTypeByKey(string $key): PeriodDurationType
    {
        $durationType = PeriodDurationType::with('periodDurations')
            ->where('key', $key)
            ->first();

        if (!$durationType) {
            throw new AppException(
                "period_duration_type_key_not_found",
                404,
                "Duration Type Not Found",
                "The requested duration type could not be found."
            );
        }

        return $durationType;
    }
    public function activateDurationType(string $periodDurationTypeId): PeriodDurationType
    {
        $durationType = PeriodDurationType::find($periodDurationTypeId);

        if (!$durationType) {
            throw new AppException(
                "period_duration_type_not_found",
                404,
                "Duration Type Not Found",
                "The duration type you're trying to activate could not be found."
            );
        }

        if ($durationType->status === SystemState::ACTIVE) {
            throw new AppException(
                "period_duration_type_already_active",
                422,
                "Duration Type Already Active",
                "This duration type is already active. No changes were made."
            );
        }

        $durationType->status = SystemState::ACTIVE;
        $durationType->save();

        return $durationType->fresh();
    }
    public function deactivateDurationType(string $periodDurationTypeId): PeriodDurationType
    {
        $durationType = PeriodDurationType::find($periodDurationTypeId);

        if (!$durationType) {
            throw new AppException(
                "period_duration_type_not_found",
                404,
                "Duration Type Not Found",
                "The duration type you're trying to deactivate could not be found."
            );
        }

        if ($durationType->status === SystemState::INACTIVE) {
            throw new AppException(
                "period_duration_type_already_inactive",
                422,
                "Duration Type Already Inactive",
                "This duration type is already inactive. No changes were made."
            );
        }

        // Check if there are active durations before deactivating
        $hasActiveDurations = $durationType->periodDurations()
            ->where('status', SystemState::ACTIVE)
            ->exists();

        if ($hasActiveDurations) {
            throw new AppException(
                "period_duration_type_has_active_durations",
                409,
                "Cannot Deactivate Duration Type",
                "This duration type cannot be deactivated because it has active period durations. Please deactivate all associated durations first."
            );
        }

        $durationType->status = SystemState::INACTIVE;
        $durationType->save();

        return $durationType->fresh();
    }

}
