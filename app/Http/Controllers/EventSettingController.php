<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventSetting\CreateEventSettingRequest;
use App\Http\Requests\EventSetting\UpdateEventSettingRequest;
use App\Services\ApiResponseService;
use App\Services\EventSettingService;
use Throwable;

class EventSettingController extends Controller
{
    protected EventSettingService $eventSettingService;
    public function __construct(EventSettingService $eventSettingService){
        $this->eventSettingService = $eventSettingService;
    }

    public function createSetting(CreateEventSettingRequest $request){
        try{
           $createSetting = $this->eventSettingService->createEventSetting($request->validated());
           return ApiResponseService::success("Setting Created Successfully", $createSetting, null, 201);
        }
        catch(Throwable $e){
            return ApiResponseService::error($e->getMessage(), null, 500);
        }
    }

    public function updateSetting(UpdateEventSettingRequest $request, $settingId) {
        try{
           $updateEventSetting = $this->eventSettingService->updateEventSetting($request->validated(), $settingId);
           return ApiResponseService::success("Event Setting Updated Successfully", $updateEventSetting, null, 200);
        }
        catch(Throwable $e){
            return ApiResponseService::error($e->getMessage(), null, 500);
        }
    }

    public function deleteSetting($settingId) {
        try{
          $deleteSetting = $this->eventSettingService->deleteEventSetting($settingId);
          return ApiResponseService::success("Event Setting Deleted Successfully", $deleteSetting, null, 200);
        }
        catch(Throwable $e){
            return ApiResponseService::error($e->getMessage(), null, 500);
        }
    }

    public function getSetting() {
        try{
           $getSettings = $this->eventSettingService->getEventSettings();
           return ApiResponseService::success("Setting Fetched Successfully", $getSettings, null, 200);
        }
        catch(Throwable $e){
            return ApiResponseService::error($e->getMessage(), null, 500);
        }
    }
}
