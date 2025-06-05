<?php

namespace App\Services;
use App\Models\AnnouncementSetting;
use Throwable;

class AnnouncementSettingService
{
    public function createAnnouncementSetting(array $data){
        try{
            $createSettings = AnnouncementSetting::create([
                 'title' => $data['title'],
                 'description' => $data['description']
            ]);
            return $createSettings;
        }
        catch(Throwable $e){
            throw $e;
        }
    }

    public function updateAnnouncementSetting(array $data, string $settingId){
        try{
           $setting = AnnouncementSetting::findOrFail($settingId);
           $filteredData = array_filter($data, function($value) {
               return $value !== null && $value !== '';
           });
           $setting->update($filteredData);
           return $setting;
        }
        catch(Throwable $e){
            throw $e;
        }
    }

    public function deleteAnnouncementSetting(string $settingId){
         try{
            $setting = AnnouncementSetting::findOrFail($settingId);
            $setting->delete();
            return $setting;
         }
         catch(Throwable $e){
            throw $e;
         }
    }

    public function getAnnouncementSettings(){
        try{
            return AnnouncementSetting::all();
        }
        catch(Throwable $e){
            throw $e;
        }
    }

}
