<?php

namespace App\Services;

use App\Models\Audiences;
use App\Models\PresetAudiences;
use Throwable;

class PresetAudienceService
{
    public function createPresetAudience(array $audienceData){
        try{
            $audience = PresetAudiences::create([
                 'name' => $audienceData['name'],
                 'target' => $audienceData['target'],
                 'description' => $audienceData['description'] ?? null
            ]);
            return $audience;
        }
        catch(Throwable $e){
           throw $e;
        }
    }

    public function updatePresetAudience(array $updateAudienceData, string $audienceId){
         try{
            $audience = PresetAudiences::findOrFail($audienceId);
            $cleanedData = array_filter($updateAudienceData, function($value) {
                return $value !== null && $value !== '';
            });
            $audience->update($cleanedData);
            return $audience;
         }
         catch(Throwable $e){
            throw $e;
         }
    }

    public function deletePresetAudience(string $audienceId){
        try{
             $audience = PresetAudiences::findOrFail($audienceId);
             $audience->delete();
                return $audience;
        }
        catch(Throwable $e){
           throw $e;
        }
    }

    public function getPresetAudiences(){
        try{
            return PresetAudiences::all();
        }
        catch(Throwable $e){
            throw $e;
        }
    }

    public function deactivatePresetAudiences(string $audienceId){
        try{
            $audience = PresetAudiences::findOrFail($audienceId);
            $audience->status = 'inactive';
            $audience->save();
            return $audience;
        }
        catch(Throwable $e){
            throw $e;
        }
    }

    public function activatePresetAudiences(string $audienceId) {
        try{
             $audience = PresetAudiences::findOrFail($audienceId);
            $audience->status = 'active';
            $audience->save();
            return $audience;
        }
        catch(Throwable $e){
            throw $e;
        }
    }

    public function getActivePresetAudiences() {
        try{
            return PresetAudiences::where('status', 'active')->get();
        }
        catch(Throwable $e){
            throw $e;
        }
    }
}
