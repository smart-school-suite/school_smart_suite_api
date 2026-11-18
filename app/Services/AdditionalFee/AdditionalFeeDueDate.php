<?php

namespace App\Services\AdditionalFee;

use App\Models\SchoolBranchSetting;
use App\Models\SettingDefination;
use Carbon\Carbon;

/**
 * The due date of a student additional fee: the date the admin gave, or,
 * when none is given, the billing day plus "Default Due Period"
 * (additionalFee.default_due_days) days.
 */
class AdditionalFeeDueDate
{
    public const KEY = 'additionalFee.default_due_days';
    public const DEFAULT_DAYS = 30;
    public const MAX_DAYS = 365;

    public function resolve(string $schoolBranchId, mixed $given = null): string
    {
        if ($given !== null && $given !== '') {
            return Carbon::parse($given)->toDateString();
        }

        return now()->startOfDay()->addDays($this->days($schoolBranchId))->toDateString();
    }

    /** The branch's setting, else the setting's default, else DEFAULT_DAYS. */
    public function days(string $schoolBranchId): int
    {
        $value = SchoolBranchSetting::where('school_branch_id', $schoolBranchId)
            ->whereHas('settingDefination', fn($query) => $query->where('key', self::KEY))
            ->first()?->value;

        if (!is_numeric($value)) {
            $default = SettingDefination::where('key', self::KEY)->value('default_value');
            $value = is_string($default) && json_validate($default) ? json_decode($default, true) : $default;
        }

        return is_numeric($value) ? max(0, min(self::MAX_DAYS, (int) $value)) : self::DEFAULT_DAYS;
    }
}
