<?php

namespace App\Services\SchoolBranchSetting;

use App\Exceptions\AppException;
use App\Models\Examtype;
use App\Models\SchoolBranchSetting;
use Illuminate\Support\Facades\DB;
use Throwable;

class ExamSettingService
{
    public function updateExamSetting($currentSchool, $updateData)
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
                case 'exam.final_exam':
                    $this->updateFinalExam($setting, $newValue);
                    break;

                case 'exam.auto_create':
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

    protected function updateFinalExam($setting, $value)
    {
        $examType = Examtype::find($value);
        if (!$examType) {
            throw new AppException(
                ""
            );
        }
        $setting->value = $examType->toArray();
        $setting->save();
    }
}
