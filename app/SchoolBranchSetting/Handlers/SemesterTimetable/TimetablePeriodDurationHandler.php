<?php

namespace App\SchoolBranchSetting\Handlers\SemesterTimetable;

use App\Exceptions\AppException;
use App\Models\PeriodDuration\PeriodDuration;
use App\Models\SchoolBranchSetting;
use App\SchoolBranchSetting\Contracts\SettingUpdateHandler;
use Override;

class TimetablePeriodDurationHandler implements SettingUpdateHandler
{
    #[Override]
    public function handle(
        SchoolBranchSetting $setting,
        mixed $value,
        object $currentSchool,
        mixed $authAdmin
    ): SchoolBranchSetting {
        if (empty($value)) {
            throw new AppException(
                "The period duration ID must be provided.",
                400,
                "Invalid Period Duration Format",
                "The period duration ID must be provided.",
                null
            );
        }

        $periodDuration = PeriodDuration::where('id', $value)->first();

        if (!$periodDuration) {
            throw new AppException(
                "The specified period duration does not exist.",
                400,
                "Period Duration Not Found",
                "The specified period duration does not exist.",
                null
            );
        }

        $setting->update([
            'value' => $periodDuration->minutes,
        ]);

        return $setting->fresh();
    }
}
