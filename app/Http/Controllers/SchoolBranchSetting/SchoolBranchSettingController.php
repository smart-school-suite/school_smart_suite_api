<?php

namespace App\Http\Controllers\SchoolBranchSetting;

use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use App\Models\SchoolBranchSetting;
use App\Services\SchoolBranchSetting\SchoolBranchSettingService;

class SchoolBranchSettingController extends Controller
{
    protected SchoolBranchSettingService $schoolBranchSettingService;

    public function __construct(SchoolBranchSettingService $schoolBranchSettingService)
    {
        $this->schoolBranchSettingService = $schoolBranchSettingService;
    }

    public function getSchoolBranchSetting(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $schoolBranchSetting = $this->schoolBranchSettingService->getSchoolBranchSetting($currentSchool);
        return ApiResponseService::success("School Branch Setting Fetched Successfully", $schoolBranchSetting, null, 200);
    }

   public function testSettingController(Request $request)
{
    // Get current school from request attributes
    $currentSchool = $request->attributes->get('currentSchool');

    // Validate currentSchool
    if (!$currentSchool || !isset($currentSchool->id)) {
        return response()->json(['error' => 'School not found'], 404);
    }

    // Get key and categoryName from request, with defaults
    $key = $request->input('key', 'resitFee.levelBilling');
    $categoryName = "Resit Settings";

    // Query the settings collection
    $settings = SchoolBranchSetting::where('school_branch_id', $currentSchool->id)
        ->whereHas('settingDefination', function ($query) use ($categoryName) {
            $query->whereHas('settingCategory', function ($query) use ($categoryName) {
                $query->where('name', $categoryName);
            });
        })
        ->with(['settingDefination' => function ($query) {
            $query->with('settingCategory'); // Ensure settingCategory is loaded
        }])
        ->get();
    //resitFee.generalBilling
    //resitFee.levelBilling
    // Filter the collection for the specific key
    $setting = $settings->first(function ($setting) {
        return $setting->settingDefination && $setting->settingDefination->key === "resitFee.generalBilling";
    });
    $settingTwo = $settings->first(function ($setting) {
        return $setting->settingDefination && $setting->settingDefination->key === "resitFee.generalBillingFee";
    });

    // Return 404 if no setting is found
    if (!$setting) {
        return response()->json(['error' => 'Setting not found for the specified key and category'], 404);
    }

    // Format the response
    $response = [
        'data_type' => $setting->settingDefination->data_type ?? null,
        'value' => $setting->value ?? null,
        'name' => $setting->settingDefination->name ?? null,
        'description' => $setting->settingDefination->description ?? null,
    ];

    return response()->json([
        'setting_one' => $setting,
        'setting_two' => $settingTwo->value
    ]);
}
}
