<?php

namespace App\SchoolBranchSetting\Handlers\SemesterTimetable;

use App\Exceptions\AppException;
use App\Models\SchoolBranchSetting;
use App\SchoolBranchSetting\Contracts\SettingUpdateHandler;
use Override;

class RespectTeacherPreferenceHandler implements SettingUpdateHandler
{
    #[Override]
    public function handle(
        SchoolBranchSetting $setting,
        mixed $value,
        object $currentSchool,
        mixed $authAdmin
    ): SchoolBranchSetting {
        $filteredValue = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        if ($filteredValue === null) {
            throw new AppException(
                "The respect teacher preference setting must be a valid boolean.",
                400,
                "Invalid Respect Teacher Preference Format",
                "The respect teacher preference setting must be a valid boolean.",
                "value"
            );
        }

        if ($filteredValue) {
            $this->updateMutuallyExclusiveSetting($currentSchool->id, 'timetable.ignore_teacher_preference', !$filteredValue);
        }

        $setting->update(['value' => $filteredValue]);

        return $setting->fresh();
    }

    private function updateMutuallyExclusiveSetting(string $schoolId, string $key, mixed $value): void
    {
        $conflictingSetting = SchoolBranchSetting::where('school_branch_id', $schoolId)
            ->whereHas('settingDefination', fn($query) => $query->where('key', $key))
            ->first();

        if ($conflictingSetting) {
            $conflictingSetting->update(['value' => $value]);
        }
    }
}
