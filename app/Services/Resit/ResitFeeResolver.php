<?php

namespace App\Services\Resit;

use App\Models\Exam\Exam;
use App\Models\SchoolBranchSetting;
use App\Models\SettingDefination;

/**
 * The resit fee a student is billed for one course, from the resit
 * settings:
 *
 *  - resitFee.levelBilling on: the fee set for the student's level in
 *    "Resit Fees by Academic Level" (resitFee.levelBillingFee). A level
 *    with no fee set falls back to the standard fee.
 *  - otherwise (resitFee.generalBilling): "Standard Resit Fee Amount"
 *    (resitFee.generalBillingFee).
 *
 * A branch without a setting row uses the setting's default. The level is
 * that of the specialty whose academic year the failed exam belongs to.
 */
class ResitFeeResolver
{
    public const GENERAL_BILLING = 'resitFee.generalBilling';
    public const LEVEL_BILLING = 'resitFee.levelBilling';
    public const GENERAL_FEE = 'resitFee.generalBillingFee';
    public const LEVEL_FEES = 'resitFee.levelBillingFee';

    /** Level id by exam id, so a batch of scores looks each exam up once. */
    private array $examLevels = [];

    public function feeForExam(string $schoolBranchId, string $examId): float
    {
        if (!array_key_exists($examId, $this->examLevels)) {
            $this->examLevels[$examId] = Exam::with('schoolYear.specialty')->find($examId)?->schoolYear?->specialty?->level_id;
        }

        return $this->feeForLevel($schoolBranchId, $this->examLevels[$examId]);
    }

    public function feeForLevel(string $schoolBranchId, ?string $levelId): float
    {
        $settings = $this->settings($schoolBranchId);

        if (filter_var($settings[self::LEVEL_BILLING] ?? false, FILTER_VALIDATE_BOOLEAN) && $levelId) {
            $fees = $settings[self::LEVEL_FEES] ?? [];
            if (is_string($fees)) {
                $fees = json_decode($fees, true);
            }
            $entry = collect(is_array($fees) ? $fees : [])
                ->first(fn($item) => is_array($item) && ($item['level_id'] ?? null) === $levelId);
            if (isset($entry['fee']) && is_numeric($entry['fee'])) {
                return round((float) $entry['fee'], 2);
            }
        }

        $general = $settings[self::GENERAL_FEE] ?? null;

        return is_numeric($general) ? round((float) $general, 2) : 0.0;
    }

    /** The four resit fee settings for the branch, each falling back to the setting's default. */
    private function settings(string $schoolBranchId): array
    {
        $keys = [self::GENERAL_BILLING, self::LEVEL_BILLING, self::GENERAL_FEE, self::LEVEL_FEES];

        $values = SettingDefination::whereIn('key', $keys)->pluck('default_value', 'key')
            ->map(fn($value) => is_string($value) && json_validate($value) ? json_decode($value, true) : $value)
            ->all();

        SchoolBranchSetting::where('school_branch_id', $schoolBranchId)
            ->with('settingDefination:id,key')
            ->whereHas('settingDefination', fn($query) => $query->whereIn('key', $keys))
            ->get()
            ->each(function ($setting) use (&$values) {
                $values[$setting->settingDefination->key] = $setting->value;
            });

        return $values;
    }
}
