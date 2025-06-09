<?php

namespace App\Services;

use App\Models\EventTag;
use Throwable;

class EventTagService
{
   public function createEventTag($tagData, $currentSchool){
       try{
            $tag = EventTag::create([
                 'name' => $tagData['name'],
                 'school_branch_id' => $currentSchool->id
             ]);
            return $tag;
       }
       catch(Throwable $e){
          throw $e;
       }
   }

   public function getTags($currentSchool){
      try{
         $tags = EventTag::where("school_branch_id", $currentSchool->id)->all();
         return $tags;
      }
      catch(Throwable $e){
        throw $e;
      }
   }

   public function deleteTag($currentSchool, $tagId){
      try{
         $tag = EventTag::where("school_branch_id", $currentSchool->id)->findOrFail($tagId);
         $tag->delete();
         return $tag;
      }
      catch(Throwable $e){
        throw $e;
      }
   }

   public function updateTag($tagData, $currentSchool, $tagId){
      try{
         $tag = EventTag::where("school_branch_id", $currentSchool->id)->findOrFail($tagId);
         $cleanData = array_filter($tagData);
         $tag->update($cleanData);
         return $tag;
      }
      catch(Throwable $e){
        throw $e;
      }
   }


}
