<?php

namespace App\Services\SchoolBranchSetting;

use App\Exceptions\AppException;
use App\Models\LetterGrade;
use App\Models\SchoolBranchSetting;
use Illuminate\Support\Facades\DB;
use Throwable;

class GradeSettingService
{
    public function updateGradeSetting($currentSchool, $updateData)
    {
        $settingId = $updateData['school_branch_setting_id'] ?? null;
        $newValue = $updateData['value'] ?? null;
        $schoolId = $currentSchool->id;

        if (is_null($settingId)) {
            throw new AppException(
                "Missing setting ID in update payload.",
                400,
                "Invalid Request 📝",
                "The setting identifier is required to perform the update.",
                null
            );
        }

        try {
            DB::beginTransaction();

            $setting = SchoolBranchSetting::where("school_branch_id", $schoolId)
                ->with(['settingDefination'])
                ->findOrFail($settingId);

            $settingKey = $setting->settingDefination->key;

            switch ($settingKey) {
                case 'grade.allowed_letter_grades':
                    $this->updateLetterGrades($setting, $newValue);
                    break;

                case 'grade.passing_gpa':
                    $setting->value = $newValue;
                    $setting->save();
                    break;
                case 'grade.max_gpa':
                    $setting->value = $newValue;
                    $setting->save();
                    break;
                default:
                    $setting->value = $newValue;
                    $setting->save();
                    break;
            }

            DB::commit();
            return $setting;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            throw new AppException(
                "Setting ID '{$settingId}' not found for school branch.",
                404,
                "Setting Not Found 🔎",
                "The specified school branch setting could not be found.",
                null
            );
        } catch (AppException $e) {
            DB::rollBack();
            throw $e;
        } catch (Throwable $e) {
            DB::rollBack();
            throw new AppException(
                "Failed to update setting '{$settingKey}'. Error: " . $e->getMessage(),
                500,
                "System Update Failed 🛑",
                "A critical error occurred while saving the setting. Please check server logs.",
                null
            );
        }
    }

    protected function updateLetterGrades($setting, $newValue)
    {
        $decodedValue = json_decode($newValue, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decodedValue)) {
            throw new AppException(
                "Invalid JSON format for level billing fee array.",
                400,
                "JSON Error ❌",
                "The level billing fee structure must be provided as a valid JSON array.",
                null
            );
        }

        $levelGradeIds = collect($decodedValue)->pluck('letter_grade_id')->filter()->unique()->toArray();

        $letterGrades = LetterGrade::whereIn("id", $levelGradeIds)->get()->toArray();
        if (empty($letterGrades)) {
            throw new AppException(
                "One or more Letter Grades specified in the letter Grades structure do not exist.",
                404,
                "Invalid Letter Grades 🎓",
                "Please ensure all Letter Grade Ids in the letter Grades are valid.",
                null
            );
        }
        $setting->value = $letterGrades;
        $setting->save();
    }
}
