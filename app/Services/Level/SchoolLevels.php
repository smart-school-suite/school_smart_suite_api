<?php

namespace App\Services\Level;

use App\Models\Educationlevels;
use App\Models\SchoolBranchSetting;
use Illuminate\Support\Collection;

/**
 * The academic levels a school branch works with.
 *
 *   system()   every level of the branch's level system (level.start)
 *   offered()  the levels the school runs ("Levels Offered by the School",
 *              level.allowed_levels); every level list, specialty and import
 *              uses these
 *   autoSpecialtyLevels()  the offered levels automatic specialty creation
 *              uses ("Levels for Automatic Specialty Creation",
 *              specialties.auto_generate_for_levels)
 *
 * A saved list that matches none of the current system's levels (no setting
 * yet, or the starting level was changed) means "all", so changing the
 * level system never leaves a school without levels.
 */
class SchoolLevels
{
    public const SYSTEM = 'level.start';
    public const OFFERED = 'level.allowed_levels';
    public const AUTO_SPECIALTY = 'specialties.auto_generate_for_levels';

    /** @return Collection<int, Educationlevels> lowest level first */
    public function system(string $schoolBranchId): Collection
    {
        $system = $this->value($schoolBranchId, self::SYSTEM);

        return Educationlevels::query()
            ->when($system, fn($query) => $query->whereHas('levelType', fn($q) => $q->where('program_name', $system)))
            ->get()
            ->sortBy(fn($level) => (int) $level->level)
            ->values();
    }

    /** @return Collection<int, Educationlevels> */
    public function offered(string $schoolBranchId): Collection
    {
        return $this->narrow($this->system($schoolBranchId), $this->value($schoolBranchId, self::OFFERED));
    }

    /** @return Collection<int, Educationlevels> */
    public function autoSpecialtyLevels(string $schoolBranchId): Collection
    {
        return $this->narrow($this->offered($schoolBranchId), $this->value($schoolBranchId, self::AUTO_SPECIALTY));
    }

    public function isOffered(string $schoolBranchId, ?string $levelId): bool
    {
        return $levelId !== null && $this->offered($schoolBranchId)->contains('id', $levelId);
    }

    /** Ids of a saved list (array or JSON string). */
    public static function ids(mixed $value): array
    {
        if (is_string($value)) {
            $value = json_decode($value, true);
        }

        return is_array($value)
            ? array_values(array_unique(array_filter($value, fn($id) => is_string($id) && $id !== '')))
            : [];
    }

    private function narrow(Collection $levels, mixed $saved): Collection
    {
        $ids = self::ids($saved);
        $kept = $levels->whereIn('id', $ids)->values();

        return $kept->isEmpty() ? $levels : $kept;
    }

    private function value(string $schoolBranchId, string $key): mixed
    {
        return SchoolBranchSetting::where('school_branch_id', $schoolBranchId)
            ->whereHas('settingDefination', fn($query) => $query->where('key', $key))
            ->first()?->value;
    }
}
