<?php

namespace App\Services;

use App\Models\SchoolAnnouncementSetting;
use Throwable;

class SchoolAnnouncementSettingService
{
    // Implement your logic here
    public function getSettings($currentSchool){
       try{
          $settings = SchoolAnnouncementSetting::where("school_branch_id", $currentSchool->id)
                ->with('announcementSetting')
                ->get();
                return $settings;
       }
       catch(Throwable $e){
          throw $e;
       }
    }

    public function updateSetting($currentSchool, $settingData, $settingId){
         try{
            $setting = SchoolAnnouncementSetting::where('school_branch_id', $currentSchool->id)
                ->find($settingId);
            $cleanedData = array_filter($settingData, function($value) {
                return $value !== null && $value !== '';
            });
            $setting->update($cleanedData);
            return $setting;
         }
         catch(Throwable $e){
            throw $e;
         }
    }
}
