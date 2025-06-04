<?php

namespace App\Http\Controllers;

use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use App\Services\PresetAudienceService;
use App\Http\Requests\PresetAudience\CreatePresetAudience;
use App\Http\Requests\PresetAudience\UpdatePresetAudience;
use Throwable;

class PresetAudienceController extends Controller
{
    //
    protected $presetAudienceService;
    public function __construct(PresetAudienceService $presetAudienceService)
    {
        $this->presetAudienceService = $presetAudienceService;
    }

    public function createPresetAudience(CreatePresetAudience $request){
        try{
            $createAudience = $this->presetAudienceService->createPresetAudience($request->validated());
            return ApiResponseService::success('Preset Audience created successfully', $createAudience, null, 201);
        }
        catch(Throwable $e){
            return ApiResponseService::error($e->getMessage(), null, 500);
        }
    }


    public function updatePresetAudience(UpdatePresetAudience $request, string $audienceId){
        try{
            $updateAudience = $this->presetAudienceService->updatePresetAudience($request->validated(), $audienceId);
            return ApiResponseService::success('Preset Audience updated successfully', $updateAudience, null, 200);
        }
        catch(Throwable $e){
            return ApiResponseService::error($e->getMessage(), null, 500);
        }
    }

    public function deletePresetAudience(string $audienceId){
        try{
            $deletedAudience = $this->presetAudienceService->deletePresetAudience($audienceId);
            return ApiResponseService::success('Preset Audience deleted successfully', $deletedAudience, null, 200);
        }
        catch(Throwable $e){
            return ApiResponseService::error($e->getMessage(), null, 500);
        }
    }
    public function getPresetAudiences(){
        try{
            $presetAudiences = $this->presetAudienceService->getPresetAudiences();
            return ApiResponseService::success('Preset Audiences retrieved successfully', $presetAudiences, null, 200);
        }
        catch(Throwable $e){
            return ApiResponseService::error($e->getMessage(), null, 500);
        }
    }
    public function deactivatePresetAudience(string $audienceId){
        try{
            $deactivatedAudience = $this->presetAudienceService->deactivatePresetAudiences($audienceId);
            return ApiResponseService::success('Preset Audience deactivated successfully', $deactivatedAudience, null, 200);
        }
        catch(Throwable $e){
            return ApiResponseService::error($e->getMessage(), null, 500);
        }
    }
    public function activatePresetAudience(string $audienceId){
        try{
            $activatedAudience = $this->presetAudienceService->activatePresetAudiences($audienceId);
            return ApiResponseService::success('Preset Audience activated successfully', $activatedAudience, null, 200);
        }
        catch(Throwable $e){
            return ApiResponseService::error($e->getMessage(), null, 500);
        }
    }
    public function getActivePresetAudiences(){
        try{
            $activePresetAudiences = $this->presetAudienceService->getActivePresetAudiences();
            return ApiResponseService::success('Active Preset Audiences retrieved successfully', $activePresetAudiences, null, 200);
        }
        catch(Throwable $e){
            return ApiResponseService::error($e->getMessage(), null, 500);
        }
    }


}
