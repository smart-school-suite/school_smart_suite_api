<?php

namespace App\Services\Semester;

use App\Models\SchoolBranchSetting;
use App\Models\Semester;
use App\Models\SettingDefination;

class SemesterLimit
{
    public const KEY = 'semester.max_per_academic_year';
    public const DEFAULT = 2;

    public function forBranch(string $schoolBranchId): int
    {
        $value = SchoolBranchSetting::where('school_branch_id', $schoolBranchId)
            ->whereHas('settingDefination', fn ($query) => $query->where('key', self::KEY))
            ->first()?->value;

        if (!is_numeric($value)) {
            $default = SettingDefination::where('key', self::KEY)->value('default_value');
            $value = is_string($default) && json_validate($default) ? json_decode($default, true) : $default;
        }

        $limit = is_numeric($value) ? (int) $value : self::DEFAULT;

        return max(1, min($limit, $this->available()));
    }

    public function available(): int
    {
        return max(1, (int) Semester::query()->max('count'));
    }
}
