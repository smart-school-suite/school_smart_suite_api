<?php

namespace App\Services;
use Throwable;
use App\Models\EventSetting;
class EventSettingService
{
       public function createEventSetting(array $data){
        try{
            $createSettings = EventSetting::create([
                 'title' => $data['title'],
                 'description' => $data['description']
            ]);
            return $createSettings;
        }
        catch(Throwable $e){
            throw $e;
        }
    }

    public function updateEventSetting(array $data, string $settingId){
        try{
           $setting = EventSetting::findOrFail($settingId);
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

    public function deleteEventSetting(string $settingId){
         try{
            $setting = EventSetting::findOrFail($settingId);
            $setting->delete();
            return $setting;
         }
         catch(Throwable $e){
            throw $e;
         }
    }

    public function getEventSettings(){
        try{
            return EventSetting::all();
        }
        catch(Throwable $e){
            throw $e;
        }
    }

}
