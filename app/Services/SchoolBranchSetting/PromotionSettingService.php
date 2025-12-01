<?php

namespace App\Services\SchoolBranchSetting;

use App\Exceptions\AppException;
use App\Models\SchoolBranchSetting;
use Illuminate\Support\Facades\DB;
use Throwable;
use App\Events\Actions\AdminActionEvent;
class PromotionSettingService
{
    public function updatePromotionSetting($currentSchool, $updateData, $authAdmin)
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
                case 'promotion.min_gpa':
                    $setting->value = $newValue;
                    $setting->save();
                    break;
                case 'promotion.max_tuition_fee_debt':
                    $setting->value = $newValue;
                    $setting->save();
                    break;
                case 'promotion.max_additional_fee_debt':
                    $setting->value = $newValue;
                    $setting->save();
                    break;
                case 'promotion.max_carry_overs':
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
                "permissions" =>  ["schoolAdmin.promotionSetting.update"],
                "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                "schoolBranch" =>  $currentSchool->id,
                "feature" => "schoolSettingManagement",
                "authAdmin" => $authAdmin,
                "data" => $setting,
                "message" => "Promotion Setting Updated",
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
}
