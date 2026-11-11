<?php

namespace App\Services\Level;

use App\Models\SchoolBranchSetting;

/**
 * Reads the per-level fee settings, "Registration Fees by Academic Level"
 * (level.registration_fees) and "Tuition Fees by Academic Level"
 * (level.tuition_fees). Each is a list of {level_id, fee}.
 */
class LevelFeeSettings
{
    public const REGISTRATION = 'level.registration_fees';
    public const TUITION = 'level.tuition_fees';

    /** The fee configured for the level, or null when none is set. */
    public function feeFor(string $schoolBranchId, ?string $levelId, string $key): ?float
    {
        if (!$levelId) {
            return null;
        }

        $fees = SchoolBranchSetting::where('school_branch_id', $schoolBranchId)
            ->whereHas('settingDefination', fn($query) => $query->where('key', $key))
            ->first()?->value;

        if (is_string($fees)) {
            $fees = json_decode($fees, true);
        }

        $entry = collect(is_array($fees) ? $fees : [])
            ->first(fn($item) => is_array($item) && ($item['level_id'] ?? null) === $levelId);

        return isset($entry['fee']) && is_numeric($entry['fee']) ? (float) $entry['fee'] : null;
    }
}
