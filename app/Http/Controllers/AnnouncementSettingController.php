<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnnouncementSetting\CreateAnnouncementSettingRequest;
use App\Http\Requests\AnnouncementSetting\UpdateAnnouncementSettingRequest;
use App\Services\AnnouncementSettingService;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use Throwable;

class AnnouncementSettingController extends Controller
{
    protected AnnouncementSettingService $announcementSettingService;
    public function __construct(AnnouncementSettingService $announcementSettingService){
        $this->announcementSettingService = $announcementSettingService;
    }

    public function createSetting(CreateAnnouncementSettingRequest $request){
        try{
           $createSetting = $this->announcementSettingService->createAnnouncementSetting($request->validated());
           return ApiResponseService::success("Announcement Setting Created Successfully", $createSetting, null, 201);
        }
        catch(Throwable $e){
            return ApiResponseService::error($e->getMessage(), null, 500);
        }
    }

    public function updateSetting(UpdateAnnouncementSettingRequest $request, $settingId){
        try{
            $updateSetting = $this->announcementSettingService->updateAnnouncementSetting($request->validated(), $settingId);
            return ApiResponseService::success("Annoucement Setting Updated Successfully", $updateSetting, null, 200);
        }
        catch(Throwable $e){
           return ApiResponseService::error($e->getMessage(), null, 500);
        }
    }

    public function deleteSetting($settingId){
        try{
            $deleteSetting = $this->announcementSettingService->deleteAnnouncementSetting($settingId);
            return ApiResponseService::success("Announcement Setting Deleted Successfully", $deleteSetting, null, 200);
        }
        catch(Throwable $e){
            return ApiResponseService::error($e->getMessage(), null, 500);
        }
    }

    public function getSettings(){
        try{
            $getSettings = $this->announcementSettingService->getAnnouncementSettings();
            return ApiResponseService::success("Announcement Setting Fetched Successfully", $getSettings, null, 200);
        }
        catch(Throwable $e){
            return ApiResponseService::error($e->getMessage(), null, 500);
        }
    }
}
