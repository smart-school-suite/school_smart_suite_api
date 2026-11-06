<?php

namespace App\SchoolBranchSetting\Handlers\SemesterTimetable;

use App\Exceptions\AppException;
use App\Models\SchoolBranchSetting;
use App\SchoolBranchSetting\Contracts\SettingUpdateHandler;
use Override;

/**
 * Handles timetable.school_day_start and timetable.school_day_end: both are
 * 24-hour HH:MM times, and opening must stay before closing.
 */
class SchoolDayHoursHandler implements SettingUpdateHandler
{
    private const START_KEY = 'timetable.school_day_start';
    private const END_KEY = 'timetable.school_day_end';

    #[Override]
    public function handle(
        SchoolBranchSetting $setting,
        mixed $value,
        object $currentSchool,
        mixed $authAdmin
    ): SchoolBranchSetting {
        $time = is_string($value) ? trim($value) : '';

        if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $time)) {
            throw new AppException(
                "The school day time must be in HH:MM format.",
                400,
                "Invalid Time Format",
                "Enter the time in 24-hour HH:MM format, for example 08:00 or 17:30.",
                "value"
            );
        }

        $isStart = $setting->settingDefination?->key === self::START_KEY;
        $other = $this->otherTime($currentSchool->id, $isStart ? self::END_KEY : self::START_KEY);
        [$start, $end] = $isStart ? [$time, $other] : [$other, $time];

        if ($start && $end && $start >= $end) {
            throw new AppException(
                "The school day must open before it closes.",
                400,
                "Invalid School Day Hours",
                "The opening time ({$start}) must be earlier than the closing time ({$end}).",
                "value"
            );
        }

        $setting->update(['value' => $time]);

        return $setting->fresh();
    }

    private function otherTime(string $schoolId, string $key): ?string
    {
        return SchoolBranchSetting::where('school_branch_id', $schoolId)
            ->whereHas('settingDefination', fn($query) => $query->where('key', $key))
            ->value('value');
    }
}
