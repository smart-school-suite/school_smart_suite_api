<?php

namespace App\Services;

use App\Models\AnnouncementLabel;
use Throwable;

class AnnouncementLabelService
{
   public function createLabel($data){
       try{
          $label = AnnouncementLabel::create([
           'name' => $data['name']
       ]);
       return $label;
       }
       catch(Throwable $e){
         throw $e;
       }
   }

   public function updateLabel($data, $labelId){
       try{
          $label = AnnouncementLabel::findOrFail($labelId);
          $filterData = array_filter($data);
          $label->update($filterData);
          return $label;
       }
       catch(Throwable $e){
          throw $e;
       }
   }

   public function deleteLabel($labelId){
       try{
          $label = AnnouncementLabel::findOrFail($labelId);
          $label->delete();
          return $label;
       }
       catch(Throwable $e){
          throw $e;
       }
   }

   public function getLabels(){
       try{
          return AnnouncementLabel::all();
       }
       catch(Throwable $e){
          throw $e;
       }
   }
}
