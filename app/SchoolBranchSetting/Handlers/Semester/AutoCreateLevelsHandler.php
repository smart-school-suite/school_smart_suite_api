<?php

namespace App\SchoolBranchSetting\Handlers\Semester;

use App\Exceptions\AppException;
use App\Models\Educationlevels;
use App\Models\SchoolBranchSetting;
use App\SchoolBranchSetting\Contracts\SettingUpdateHandler;
use Override;

/**
 * Applicable Academic Levels: a list of level ids from the school's level
 * type (the "level.start" setting). May be empty.
 */
class AutoCreateLevelsHandler implements SettingUpdateHandler
{
    #[Override]
    public function handle(
        SchoolBranchSetting $setting,
        mixed $value,
        object $currentSchool,
        mixed $authAdmin
    ): SchoolBranchSetting {
        if (!is_array($value) || !array_is_list($value) || collect($value)->contains(fn($id) => !is_string($id) || $id === '')) {
            throw new AppException(
                "The applicable academic levels must be a list of level IDs.",
                400,
                "Invalid Applicable Academic Levels Format",
                "The applicable academic levels must be a list of level IDs.",
                "value"
            );
        }

        $levelIds = array_values(array_unique($value));

        $levelStart = SchoolBranchSetting::where('school_branch_id', $currentSchool->id)
            ->whereHas('settingDefination', fn($query) => $query->where('key', 'level.start'))
            ->first()?->value;

        $knownLevelIds = Educationlevels::query()
            ->when($levelStart, fn($query) => $query->whereHas('levelType', fn($q) => $q->where('program_name', $levelStart)))
            ->whereIn('id', $levelIds)
            ->pluck('id');

        $unknown = array_diff($levelIds, $knownLevelIds->all());
        if (!empty($unknown)) {
            throw new AppException(
                "Some selected levels do not exist for this school: " . implode(', ', $unknown) . ".",
                400,
                "Unknown Levels",
                "One or more selected levels are not part of this school's level structure.",
                "value"
            );
        }

        $setting->update(['value' => $levelIds]);

        return $setting->fresh();
    }
}
