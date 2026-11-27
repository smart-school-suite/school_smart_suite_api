<?php

namespace App\SchoolBranchSetting\Handlers\Exam;

use App\Exceptions\AppException;
use App\Models\SchoolBranchSetting;
use App\SchoolBranchSetting\Contracts\SettingUpdateHandler;
use App\Services\Exam\ExamAutoCreationService;
use Override;

/** "Exams to Create": which exams are created automatically (CA and Exam, or CA only). */
class AutoCreateExamTypesHandler implements SettingUpdateHandler
{
    #[Override]
    public function handle(
        SchoolBranchSetting $setting,
        mixed $value,
        object $currentSchool,
        mixed $authAdmin
    ): SchoolBranchSetting {
        if (!is_string($value) || !in_array($value, ExamAutoCreationService::TYPE_OPTIONS, true)) {
            throw new AppException(
                "Exams to Create must be one of: " . implode(', ', ExamAutoCreationService::TYPE_OPTIONS) . ".",
                400,
                "Invalid Exams to Create",
                "Choose either CA and Exam, or CA only.",
                "value"
            );
        }

        $setting->update(['value' => $value]);

        return $setting->fresh();
    }
}
