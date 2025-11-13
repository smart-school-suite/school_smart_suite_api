<?php

namespace App\Services\SchoolBranchSetting;

use App\Exceptions\AppException;
use App\Models\SchoolBranchSetting;
use Illuminate\Support\Facades\DB;
use Throwable;

class TimetableSettingService
{
    public function updateTimetableSetting($currentSchool, $updateData)
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
                case 'timetable.ignore_teacher_preference':
                    $this->handleTimetableToggle($schoolId, $setting, true, 'timetable.respect_teacher_preference');
                    break;
                case 'timetable.respect_teacher_preference':
                    $this->handleTimetableToggle($schoolId, $setting, true, 'timetable.ignore_teacher_preference');
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

    public function handleTimetableToggle(string $schoolId, SchoolBranchSetting $currentSetting,  $status, string $otherKey)
    {
        $otherSetting = $this->getSettingByKey($schoolId, $otherKey);

        if (!$otherSetting) {
            throw new AppException(
                "Dependent setting '{$otherKey}' not found. Cannot toggle billing.",
                500,
                "Configuration Error ⚠️",
                "The billing setting configuration is incomplete. Missing the related toggle key.",
                null
            );
        }

        $currentSetting->value = $status;
        $currentSetting->save();

        $otherSetting->value = !$status;
        $otherSetting->save();
    }
    private function getSettingByKey($schoolId, $key)
    {
        return SchoolBranchSetting::where("school_branch_id", $schoolId)
            ->whereHas('settingDefination', fn($query) => $query->where("key", $key))
            ->first();
    }
}
