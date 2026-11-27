<?php

namespace App\SchoolBranchSetting\Handlers\GradeScale;

use App\Exceptions\AppException;
use App\Models\GradeScale\SchoolGradeScale;
use App\Models\SchoolBranchSetting;
use App\Models\LetterGrade;
use App\SchoolBranchSetting\Contracts\SettingUpdateHandler;
use Override;

class AllowedLetterGradeHandler implements SettingUpdateHandler
{
    #[Override]
    public function handle(
        SchoolBranchSetting $setting,
        mixed $value,
        object $currentSchool,
        mixed $authAdmin
    ): SchoolBranchSetting {
        if (!is_array($value)) {
            throw new AppException(
                "The allowed letter grades must be provided as an array.",
                400,
                "Invalid Letter Grade Format",
                "The allowed letter grades must be provided as an array.",
                "value"
            );
        }

        $valueCollection = collect($value)->filter()->unique()->values();

        if ($valueCollection->isEmpty()) {
            throw new AppException(
                "At least one letter grade must be selected.",
                400,
                "Invalid Letter Grade Selection",
                "At least one letter grade must be selected.",
                "value"
            );
        }

        $existingCount = LetterGrade::whereIn('id', $valueCollection)->count();

        if ($existingCount !== $valueCollection->count()) {
            throw new AppException(
                "One or more selected letter grades do not exist.",
                400,
                "Invalid Letter Grade",
                "One or more selected letter grades do not exist.",
                "value"
            );
        }

        // Grade scales only use letters of the scheme: a letter in use stays.
        $inUse = SchoolGradeScale::where('school_branch_id', $currentSchool->id)
            ->whereNotIn('letter_grade_id', $valueCollection->all())
            ->distinct()
            ->pluck('letter_grade_id');
        if ($inUse->isNotEmpty()) {
            $names = LetterGrade::whereIn('id', $inUse)->orderBy('letter_grade')->pluck('letter_grade')->implode(', ');
            throw new AppException(
                "Letter grades still used by grade scales: {$names}.",
                422,
                "Letter Grades in Use",
                "{$names} " . ($inUse->count() === 1 ? 'is' : 'are') . " used by your grade scales. Remove " . ($inUse->count() === 1 ? 'it' : 'them') . " from those grade scales before taking " . ($inUse->count() === 1 ? 'it' : 'them') . " out of the scheme.",
                "value"
            );
        }

        $setting->update(['value' => $valueCollection->toArray()]);

        return $setting->fresh();
    }
}
