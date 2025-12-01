<?php

namespace App\Services\SchoolBranchSetting;

use App\Exceptions\AppException;
use App\Models\Educationlevels;
use App\Models\SchoolBranchSetting;
use Illuminate\Support\Facades\DB;
use Throwable;
use App\Events\Actions\AdminActionEvent;

class ResitSettingService
{

    public function updateResitSetting($currentSchool, $updateData, $authAdmin)
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
                case 'resitFee.generalBilling':
                    $this->handleBillingToggle($schoolId, $setting, true, 'resitFee.levelBilling');
                    break;

                case 'resitFee.levelBilling':
                    $this->handleBillingToggle($schoolId, $setting, true, 'resitFee.generalBilling');
                    break;

                case 'resitFee.generalBillingFee':
                    $this->handleGeneralFeeUpdate($setting, $newValue);
                    break;

                case 'resitFee.levelBillingFee':
                    $this->handleLevelFeeUpdate($setting, $newValue);
                    break;

                case 'resit.period':
                    $setting->value = $newValue;
                    $setting->save();
                    break;

                default:
                    $setting->value = $newValue;
                    $setting->save();
                    break;
            }

            DB::commit();
            AdminActionEvent::dispatch(
            [
                "permissions" =>  ["schoolAdmin.timetableSetting.update"],
                "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                "schoolBranch" =>  $currentSchool->id,
                "feature" => "schoolSettingManagement",
                "authAdmin" => $authAdmin,
                "data" => $setting,
                "message" => "Resit Setting Updated",
            ]
        );
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

    private function handleBillingToggle(string $schoolId, SchoolBranchSetting $currentSetting,  $status, string $otherKey)
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

    private function handleGeneralFeeUpdate(SchoolBranchSetting $setting, $newValue)
    {
        if (!is_numeric($newValue)) {
            throw new AppException(
                "Invalid fee value provided for general billing fee.",
                400,
                "Invalid Input 💰",
                "The general billing fee must be a valid numerical amount.",
                null
            );
        }

        //$setting->value = $newValue;
        $setting->update([
             "value" => $newValue
        ]);
    }

    private function handleLevelFeeUpdate(SchoolBranchSetting $setting, $newValue)
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

        $levelIds = collect($decodedValue)->pluck('level_id')->filter()->unique()->toArray();

        if (!empty($levelIds)) {
            $levels = Educationlevels::whereIn("id", $levelIds)->get();
            if ($levels->count() !== count($levelIds)) {
                throw new AppException(
                    "One or more Education Levels specified in the level fee structure do not exist.",
                    404,
                    "Invalid Level ID 🎓",
                    "Please ensure all level IDs in the fee structure are valid.",
                    null
                );
            }
        }

        $setting->value = $newValue;
        $setting->save();
    }

    private function getSettingByKey($schoolId, $key)
    {
        return SchoolBranchSetting::where("school_branch_id", $schoolId)
            ->whereHas('settingDefination', fn($query) => $query->where("key", $key))
            ->first();
    }
}
